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

/* Page layout for the login form.  */

if (!isset ($fromIndex) || $fromIndex !== "yes")
  die ("Invalid page load.\n");

?>

<form method="post" action="?action=login&amp;view=login">

<p><label for="identity">Identity:</label>
  <?php echo $html->escape ($namePrefix); ?>/
  <input type="text" name="identity" id="identity" />
</p>

<p><label for="message">Please use <kbd>namecoind signmessage</kbd> with the
address corresponding to your identity to sign the following
message:</label></p>
<textarea id="message" readonly="readonly">Enter your ID to see the message to sign.</textarea>

<p><label for="signature">Put the signature below:</label></p>
<textarea name="signature" id="signature"></textarea>

<p><button type="submit">Sign In</button></p>

</form>

<!-- ======================================================================= -->

<!-- A little JavaScript to construct the message to sign dynamically
     based on the entered id.  It is probably not necessary to include
     the id in the challenge message, but it doesn't matter.  -->
<script type="text/javascript">

function updateChallenge (evt)
{
  var id = document.getElementById ("identity").value;
  var data = <?php
$data = array ("nonce" => $loginNonce,
               "url" => $serverUri);
echo json_encode ($data);
    ?>;

  /* The code below must be in sync with authenticator.inc.php!  */

  var fullId = data.url + "?name=" + encodeURIComponent (id);
  var msg = "login " + fullId + " " + data.nonce;

  document.getElementById ("message").value = msg;
}

function setup (evt)
{
  var idEntry = document.getElementById ("identity");
  idEntry.addEventListener ("change", updateChallenge);
}
window.addEventListener ("load", setup);

</script>
<noscript>Since you have JavaScript disabled, you will have to construct
the message to sign on your own.  Good luck with that!
The nonce is: <?php echo $html->escape ($loginNonce); ?></noscript>
