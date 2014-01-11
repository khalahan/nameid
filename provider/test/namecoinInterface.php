#!/usr/bin/php
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

/* Test the namecoin interface.  */

require_once ("../lib/config.inc.php");
require_once ("../libauth/namecoin_interface.inc.php");

$rpc = new HttpNamecoin ($rpcHost, $rpcPort, $rpcUser, $rpcPassword);
$nc = new NamecoinInterface ($rpc, $namePrefix);

$res = $nc->getIdValue ("dani");
assert ($res->email === "d@domob.eu");

$thrown = FALSE;
try
  {
    $nc->getIdValue ("foo-bar-name-does-not-exist (hopefully still not)");
  }
catch (NameNotFoundException $exc)
  {
    $thrown = TRUE;
  }
assert ($thrown);

$msg = "My test message to be signed!\nAnother line.";
$sig = "HCpqMVqWfYuT0WJ8WXyLhMXF5lnZ0DwphVcV0rr8bCNxONddYJtINIs5I8Bd"
       ."Mqrk4wKaGQTK8035q+IMW3JVP0g=";
$addr = "NFppu8bRjGVYTjyVrFZE9cGmjvzD6VUo5m";
$res = $nc->verifyMessage ($addr, $msg, $sig);
assert ($res);
$res = $nc->verifyMessage ($addr, "forged message", $sig);
assert (!$res);
$res = $nc->verifyMessage ($addr, $msg, base64_encode ("forged sig"));
assert (!$res);

$res = $nc->isValidAddress ($addr);
assert ($res);
$res = $nc->isValidAddress (array (5));
assert (!$res);
$res = $nc->isValidAddress ("");
assert (!$res);
$res = $nc->isValidAddress (NULL);
assert (!$res);
$res = $nc->isValidAddress ("invalid-address");
assert (!$res);

$nc->close ();

?>
