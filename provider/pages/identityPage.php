<?php
/*
    NameID, a namecoin based OpenID identity provider.
    Copyright (C) 2013 by Daniel Kraft <d@domob.eu>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/* Page layout to show the identity page.  */

if (!isset ($fromIndex) || $fromIndex !== "yes")
  die ("Invalid page load.\n");

?>

<h1><?php echo $html->escape ("$namePrefix/$identityName"); ?></h1>

<?php

$displayInfo = array ("name" => "Real Name",
                      "nick" => "Nickname",
                      "website" => "Website",
                      "email" => "Email",
                      "bitmessage" => "Bitmessage",
                      "xmpp" => "XMPP",
                      "bitcoin" => "Bitcoin",
                      "namecoin" => "Namecoin",
                      "litecoin" => "Litecoin",
                      "ppcoin" => "PPCoin");
if ($identityPage)
  {
?>

<p>The Namecoin identity
<code><?php echo $html->escape ("$namePrefix/$identityName"); ?></code> has some
public profile information registered:</p>

<?php
    echo "<dl class='dl-horizontal'>\n";
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
  {
?>
<p>There's no public information for
<code><?php echo $html->escape ("$namePrefix/$identityName"); ?></code>.</p>
<?php
  }
?>
