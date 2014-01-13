<?php
/*
    NameID, a namecoin based OpenID identity provider.
    Copyright (C) 2013-2014 by Daniel Kraft <d@domob.eu>

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

/* Sanitise a link.  This makes sure that it starts with 'http://'
   or 'https://', in order to prevent in particular javascript: links
   to be inserted by an attacker.  */
function sanitiseLink ($href)
{
  $allowed = array ("http://", "https://");
  foreach ($allowed as $prefix)
    if (substr ($href, 0, strlen ($prefix)) === $prefix)
      return $href;

  return "http://$href";
}

/* Helper routine to check a GPG fingerprint and format it nicely.  This
   makes all hex characters upper case, removes spaces and colons, and
   puts back spaces to group the characters in a uniform way.  Finally,
   it formats the final digits (as in the usual GPG fingerprint key ID)
   in <strong> tags.  */
function formatGPG ($fpr)
{
  $fpr = strtoupper ($fpr);
  $fpr = preg_replace ("/[ :]/", "", $fpr);

  if (!preg_match ("/^[0-9A-F]{40}$/", $fpr))
    return NULL;

  $fpr = preg_replace ("/(.{4})/", "$1 ", $fpr);
  $fpr = preg_replace ("/(.{4} .{4}) $/", "<strong>$1</strong>", $fpr);

  return $fpr;
}

?>

<h1><?php echo $html->escape ("$namePrefix/$identityName"); ?></h1>

<?php

$displayInfo = array ("name" => "Real Name",
                      "nick" => "Nickname",
                      "website" => "Website",
                      "email" => "Email",
                      "gpg" => "OpenPGP",
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
            $content = NULL;
            switch ($key)
              {
              case "website":
                $text = $html->escape ($identityPage->$key);
                $href = $html->escape (sanitiseLink ($identityPage->$key));
                $content = "<a href='$href'>$text</a>";
                break;

              case "email":
                $text = $html->escape ($identityPage->$key);
                $content = "<a href='mailto:$text'>$text</a>";
                break;

              case "gpg":
                if (is_object ($identityPage->$key)
                    && isset ($identityPage->$key->v)
                    && $identityPage->$key->v === "pka1"
                    && isset ($identityPage->$key->fpr))
                  {
                    $val = formatGPG ($identityPage->$key->fpr);
                    if ($val !== NULL)
                      {
                        $href = NULL;
                        $content = "";
                        if (isset ($identityPage->$key->uri))
                          {
                            $href = $identityPage->$key->uri;
                            $href = $html->escape (sanitiseLink ($href));
                            $content .= "<a href='$href'>";
                          }
                        $content .= $val;
                        if ($href !== NULL)
                          $content .= "</a>";
                      }
                  }
                break;

              default:
                $content = $html->escape ($identityPage->$key);
                break;
              }

            if ($content !== NULL)
              {
                echo "<dt>" . $html->escape ($label) . "</dt>\n";
                echo "<dd>$content</dd>\n";
              }
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
