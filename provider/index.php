<?php
/*
    NameID, a namecoin based OpenID identity provider.
    Copyright (C) 2013 by Daniel Kraft <d@domob.eu>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/* Main page.  */

require_once ("lib/authenticator.inc.php");
require_once ("lib/html.inc.php");
require_once ("lib/messages.inc.php");
require_once ("lib/namecoind.inc.php");
require_once ("lib/request.inc.php");
require_once ("lib/session.inc.php");

$status = "unknown";

// Construct the basic worker classes.
$session = new Session ();
$nc = new Namecoind ();
$req = new RequestHandler ();
$html = new HtmlOutput ();
$msg = new MessageList ($html);

/**
 * Try to get the data for an identity page and update the
 * global state accordingly.
 */
function tryIdentityPage ()
{
  global $req, $nc;
  global $status, $identityName, $identityPage;

  if ($req->check ("name"))
    {
      $name = $req->getString ("name");
      $identityName = $name;
      try
        {
          $identityPage = $nc->getIdValue ($name);
          $identityName = $name;
          $status = "identityPage";
        }
      catch (NameNotFoundException $exc)
        {
          $status = "identityNotFound";
        }
    }
}

/**
 * Try to perform a user requested action and update the global
 * state accordingly.
 */
function tryAction ()
{
  global $req, $session, $msg, $nc;
  global $status;

  if ($status === "unknown" && $req->check ("action"))
    {
      $action = $req->getString ("action");
      switch ($action)
        {
        case "login":
          $identity = $req->getString ("identity");
          $signature = $req->getString ("signature");

          /* Redirect to loginForm in case an exception is thrown
             below (i. e., authentication fails).  */
          $status = "loginForm";

          $auth = new Authenticator ($nc, $session);
          $auth->login ($identity, $signature);

          /* No exception thrown means success.  */
          $msg->addMessage ("You have logged in successfully.");
          $status = "loggedIn";
          break;

        case "logout":
          $session->setUser (NULL);
          $msg->addMessage ("You have been logged out successfully.");
          $status = "loginForm";
          break;

        default:
          // Ignore unknown action request.
          break;
        }
    }
}

/**
 * Try to interpret a requested view.
 */
function tryView ()
{
  global $req, $session;
  global $status;

  if ($status === "unknown" && $req->check ("view"))
    {
      $view = $req->getString ("view");
      switch ($view)
        {
        case "login":
          $userLoggedIn = $session->getUser ();
          if ($userLoggedIn === NULL)
            $status = "loginForm";
          else
            $status = "loggedIn";
          break;

        default:
          // Just leave status as unknown.
          break;
        }
    }
}

/**
 * Perform all page actions, possibly throwing a UIError.
 */
function performActions ()
{
  global $status;

  tryIdentityPage ();
  tryAction ();
  tryView ();

  /* If nothing matched, show the default page.  */
  if ($status === "unknown")
    $status = "default";
}

// Now perform the action and catch errors.
$msg->runWithErrors ("performActions");

// Set some global values for the pages.
switch ($status)
  {
  case "loginForm":
    $loginNonce = $session->generateNonce ();
    break;

  case "loggedIn":
    $loggedInUser = $session->getUser ();
    break;

  default:
    // Nothing to be done any more.
    break;
  }

// Clean up.  msg and html have to be kept for later.
$req->close ();
$nc->close ();
$session->close ();

/* ************************************************************************** */

// Construct page title.
switch ($status)
  {
  case "identityPage":
  case "identityNotFound":
    $pageTitle = "NameID: $identityName";
    break;

  default:
    $pageTitle = "NameID";
    break;
  }

/* Set encoding to UTF-8.  */
header ("Content-Type: text/html; charset=utf-8");

echo "<?xml version='1.0' encoding='utf-8' ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
                      "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>

<title><?php echo $html->escape ($pageTitle); ?></title>

<link rel="stylesheet" type="text/css" href="layout/main.css" />
<link rel="openid.server"
      href="<?php echo $html->escape ($serverUri); ?>?view=login" />

</head>
<body>

<h1><?php echo $html->escape ($pageTitle); ?></h1>

<?php
$msg->finish ();
?>

<?php
$fromIndex = "yes";
include ("pages/$status.php");
?>

</body>
</html>
<?php
$html->close ();
?>
