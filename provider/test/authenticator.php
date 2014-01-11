#!/usr/bin/php
<?php
/*
    NameID, a namecoin based OpenID identity provider.
    Copyright (C) 2014 by Daniel Kraft <d@domob.eu>

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

/* Test the authenticator stuff.  */

require_once ("../lib/config.inc.php");
require_once ("../libauth/authenticator.inc.php");
require_once ("../libauth/namecoin_interface.inc.php");
require_once ("../libauth/namecoin_rpc.inc.php");

$rpc = new HttpNamecoin ($rpcHost, $rpcPort, $rpcUser, $rpcPassword);
$nc = new TestInterface ($rpc, $namePrefix);

$serverURI = "http://localhost/";
$nonce = "nonce";
$auth = new Authenticator ($nc, $serverURI);

/* In order to be independent with this test from changes to the names
   themselves, use manufactured values instead of those from real Namecoin.
   For simplicity, we always use the same name and use different values
   for it, for which different addresses should be able to sign in.  */

$name = "tester";
$msg = "login $serverURI?name=$name $nonce";

$addrOwner = "NBLR56K5MLaj4UANhnFxVd3YojGLMJtLMz";
$addrSigner = "N7kHj9NTJEhfAe8hqEgdtzgoFesPiTGCKG";
$addrOther = "NFC6fwbcjAsyacKBg96awSGMw9xc8MfqmF";

$sigOwner = "G9V1eShLLJr31kLgI8zOAs5mjy3/08k1ZlKzvOZKcouJzmUJNWa8wdtMZ"
            ."+KFqEYb3TGuHNkMx5AZF9posLgailg=";
$sigSigner = "G1za+rWNCt19mLCQ5IRc4FMykg5qxbsooc0Ke+h89KAtfrhfEdWOXUCD"
             ."HCIdmdVxRBf9wRdhbPeHrkZ7o/K4GaY=";
$sigOther = "G08xOpzdKuxHSATuA7u3Zf/ueERY1yUc+mB5jkxK/oaZQnkvsmvVmvljE"
             ."nQSPyKpn1QOHkcBoeRdxSsOx5WwFHg=";

assert ($nc->verifyMessage ($addrOwner, $msg, $sigOwner));
assert ($nc->verifyMessage ($addrSigner, $msg, $sigSigner));
assert ($nc->verifyMessage ($addrOther, $msg, $sigOther));

/**
 * Check a single login attempt.
 * @param sig Signature given.
 * @param ok Whether or not the login should be ok.
 */
function checkLogin ($sig, $ok)
{
  global $auth, $name, $nonce;

  try
    {
      $res = $auth->login ($name, $sig, $nonce);
      assert ($res === TRUE);
      $ok2 = TRUE;
    }
  catch (LoginFailure $err)
    {
      $ok2 = FALSE;
    }

  assert ($ok === $ok2);
}

/**
 * Check one of the values.  We always assume that addrOwner should be able
 * to sign in, addrOther never, and addrSigner sometimes.
 * @param val The value to set the name to for this trial.
 * @param signerOk Whether addrSigner is allowed to sign in.
 */
function checkValue ($val, $signerOk)
{
  global $nc, $auth;
  global $name, $addrOwner, $sigOwner, $sigSigner, $sigOther;

  $nc->set ($name, $addrOwner, $val);

  checkLogin ($sigOwner, TRUE);
  checkLogin ($sigSigner, $signerOk);
  checkLogin ($sigOther, FALSE);
  checkLogin (base64_encode ("invalid sig"), FALSE);
}

/* Perform the checks.  */

checkValue ("invalid-json", FALSE);
checkValue ("{\"email\": \"d@domob.eu\"}", FALSE);
checkValue ("{\"signer\": 42}", FALSE);
checkValue ("{\"signer\": \"$addrSigner\"}", TRUE);
checkValue ("{\"signer\": []}", FALSE);
checkValue ("{\"signer\": [\"$addrSigner\"]}", TRUE);
checkValue ("{\"signer\": [\"$addrSigner\", 42, \"invalid\", null]}", TRUE);

$nc->close ();

?>
