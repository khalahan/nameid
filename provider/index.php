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
switch ($status)
  {
  case "identityPage":
    $displayInfo = array ("name" => "Real Name",
                          "nick" => "Nickname",
                          "website" => "Website",
                          "email" => "Email Address",
                          "xmpp" => "XMPP",
                          "bitcoin" => "Bitcoin Address",
                          "namecoin" => "Namecoin Address",
                          "litecoin" => "Litecoin Address",
                          "ppcoin" => "PPCoin Address");
    if ($identityPage)
      {
        echo "<dl>\n";
        foreach ($displayInfo as $key => $label)
          {
            if (isset ($identityPage->$key))
              {
                echo "<dt>" . $html->escape ($label) . "</dt>\n";
                echo "<dd>";
                switch ($key)
                  {
                  case "website":
                    $href = $identityPage->$key;
                    break;
                  case "email":
                    $href = "mailto:" . $identityPage->$key;
                    break;
                  default:
                    $href = NULL;
                    break;
                  }
                if ($href)
                  echo "<a href='" . $html->escape ($href) . "'>";
                echo $html->escape ($identityPage->$key);
                if ($href)
                  echo "</a>";
                echo "</dd>\n";
              }
          }
        echo "</dl>\n";
      }
    else
      echo "<p>There's no public information for this profile.</p>\n";

    break;

  case "identityNotFound":
    echo "<p>The name " . $html->escape ("$namePrefix/$identityName")
         . " is not yet registered.</p>\n";
    break;

  default:
    echo "<p>I don't know what to do.</p>\n";
  }
?>

</body>
</html>
<?php
$html->close ();
?>
