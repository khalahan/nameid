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

<h1>Sign In</h1>

<?php
$msg->finish ();
?>

<div class="hideWithAddon">
  <div class="alert alert-info">Manually signing the challenge is very
troublesome.  Take a look at our <a href="?view=addon">add-on</a>.</div>
</div>

<p>In order to sign in with your Namecoin identity, you have to
<a href="https://en.wikipedia.org/wiki/Digital_signature">sign</a>
a <b>challenge message</b> with the Namecoin address
holding your identity.  This process does not reveal your private key
to this website or anyone else, but can be used to prove that you are indeed
the owner of your identity.</p>

<form id="loginForm" method="post" action="?action=login&amp;view=login">

<div class="input-prepend">
  <label class="add-on" for="identity"><?php
echo $html->escape ($namePrefix);
?>/</label>
  <input type="text" name="identity" id="identity" />
</div>

<div class="hideWithAddon">
  <p><label for="message">Please use <code>namecoind signmessage</code> with the
address corresponding to your identity to sign the following
message:</label></p>
  <textarea id="message" readonly="readonly">Enter your ID to see the message to sign.</textarea>
  <textarea name="signature" id="signature"
            placeholder="Put the signature here."></textarea>
</div>

<p>
  <button type="submit" name="login" class="btn btn-primary">Sign In</button>
  <button type="submit" name="cancel" class="btn" id="cancel">Cancel</button>
</p>

</form>

<!-- Store values for constructing the challenge here for the
     NameID addon.  -->
<div class="hidden">
  <span id="nameid-nonce"><?php echo $html->escape ($loginNonce); ?></span>
  <span id="nameid-uri"><?php echo $html->escape ($serverUri); ?></span>
</div>

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
<noscript class="hideWithAddon">Since you have JavaScript disabled, you will
have to construct the message to sign on your own.  Good luck with that!
The nonce is: <?php echo $html->escape ($loginNonce); ?></noscript>
