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

require_once ("lib/html.inc.php");
require_once ("lib/namecoind.inc.php");
require_once ("lib/request.inc.php");
require_once ("lib/session.inc.php");

$status = "unknown";

// Construct the basic worker classes.
$session = new Session ();
$nc = new Namecoind ();
$html = new HtmlOutput ();
$req = new RequestHandler ();

// See if this request is for an identity page.
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

// Nothing else.
if ($status === "unknown")
  $status = "default";

// Clean up.  Only the HtmlOutput is kept until the very end.
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

<link rel="openid.server"
      href="<?php echo $html->escape ($serverUri); ?>" />

</head>
<body>

<h1><?php echo $html->escape ($pageTitle); ?></h1>

<?php
$fromIndex = "yes";
include ("pages/$status.php");
?>

</body>
</html>
<?php
$html->close ();
?>
