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

/* Page layout to show the identity page.  */

if (!isset ($fromIndex) || $fromIndex !== "yes")
  die ("Invalid page load.\n");

$displayInfo = array ("name" => "Real Name",
                      "nick" => "Nickname",
                      "website" => "Website",
                      "email" => "Email Address",
                      "bitmessage" => "Bitmessage Address",
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

?>
