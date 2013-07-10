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

/* Page layout to show if nothing else applied.  */

if (!isset ($fromIndex) || $fromIndex !== "yes")
  die ("Invalid page load.\n");

?>

<p><a href="https://dot-bit.org/"><strong>Namecoin</strong></a>
is a technology based on
a modified <a href="https://www.bitcoin.org/">Bitcoin</a> protocol that
allows registering of arbitrary name-value pairs in a completely decentralised
and highly censorship-resistant manner.  The main use is currently to
provide a DNS alternative, but it is also possible to register names
for personal online identities (starting with <strong>id/</strong>).
Those names can hold all the information you want to provide about that
identity, like your email address, website or public key fingerprints.
Once you register a name, it is yours and can't be manipulated or taken
away except if you consent.  Thus it can perfectly represent your
online identity.</p>

<p>However, unfortunately at the moment namecoin is not yet widely used
and such a personal identity name is of no real use.  This is where
<strong>NameID</strong> comes in:  It tries to build a bridge between
namecoin identities and
<a href="https://openid.net/"><strong>OpenID</strong></a>,
which allows one to sign into multiple websites with a single
identity.  Ordinarily, such an OpenID is provided by a website where you
have an account (like <a href="http://www.google.com/">Google</a>,
<a href="http://www.wordpress.com/">WordPress</a> or
<a href="http://www.stackexchange.com/">StackExchange</a>), but that
is no requirement.  <strong>With NameID, you can instantly turn your
namecoin identity
into an OpenID, and use it to readily sign into millions of OpenID-enabled
websites!</strong></p>

<p>You don't need (and in fact can't) register here and don't have to remember
a password.  Since namecoin is built on
<a href="https://en.wikipedia.org/wiki/Public_key_cryptography">public
key cryptography</a> and your identity is connected to an address which
in turn is more or less a public key for which you (and only you) own
the private key, you can use a <strong>public key signature</strong>
to prove to NameID that you own an identity.</p>

<p>Interested?  If you already have an ID, just
<a href="?view=login">sign in</a> to try it out, or log into an OpenID-enabled
website using <strong>http://nameid.org/</strong> as your identity
provider.  If you don't yet have a name,
<a href="https://dot-bit.org/">get yourself one</a>!</p>

<p>You can also use this site to view (some) information associated
to namecoin IDs.  For instance <a href="?name=daniel">mine</a>, or use
the form to query for an arbitrary identity page:</p>
<form method="get" action="">
  <p>
    <label for="name">id/</label><input type="text" name="name" id="name" />
   <button type="submit">Query</button>
  </p>
</form>

<p>NameID is <strong>free software</strong>,
<a href="https://www.gitorious.org/nameid">check out the code</a> if
you are interested.</p>

<p><strong>Note:  NameID is still experimental, use at your own risk!</strong>
It also doesn't yet support a secure TLS connection, but that will hopefully
come in the future.  It is also not yet reachable over a namecoin domain
(what a shame!), but will be over
<a href="http://www.nameid.bit/">nameid.bit</a> as well as
<a href="http://www.myid.bit/">myid.bit</a>.  The main domain will still
be <a href="http://nameid.org/">nameid.org</a> though, since when you want to
sign into an OpenID consumer, it will try to connect to your identity
provider directly, which in case of a bit-domain would most probably
fail.  I'm also planning to create a browser plugin which automates
the namecoin signature when you log in, since that is really, really
troublesome at the moment....</p>

<p><strong>Help wanted!</strong>  If you have any suggestions and ideas for
improvements, <a href="mailto:d@domob.eu">let me know</a>.  I'm also looking
for volunteers to help me turn this into a pretty page with a nice graphical
design and logo, since I'm not very talented in that respect.  If you want
to help out, also <a href="mailto:d@domob.eu">let me know</a>!</p>

<p>I'm developing NameID in my free time because I find namecoin a really
interesting project and want to help the community make best use of it,
but if you like it, I
<a href="http://www.domob.eu/donate.php">welcome any donations</a>:</p>
<blockquote>
BTC: 1<strong>Nameid</strong>3brhZrbTN1M7t6afMAfVBiGioJT<br />
NMC: NFUJUGVzjTuef8bX7dd3BfXekfu8cdzkuH
</blockquote>

<p>Copyright &copy; 2013 by <a href="http://www.domob.eu/">Daniel Kraft</a>
&mdash;
<a href="http://www.domob.eu/imprint.php">Imprint</a></p>
