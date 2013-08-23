#!/usr/bin/php
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

/* Test the namecoin interface.  */

require_once ("../lib/namecoin_interface.inc.php");

$nc = new NamecoinInterface ();

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
$res = $nc->verifyMessage ("domob", $msg, $sig);
assert ($res);
$res = $nc->verifyMessage ("domob", "forged message", $sig);
assert (!$res);
$res = $nc->verifyMessage ("domob", $msg, base64_encode ("forged sig"));
assert (!$res);

$nc->close ();

?>
