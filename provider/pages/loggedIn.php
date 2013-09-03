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

/* Page layout when the user is logged in.  */

if (!isset ($fromIndex) || $fromIndex !== "yes")
  die ("Invalid page load.\n");

$fullId = "$namePrefix/$loggedInUser";
$idUrl = "$serverUri?name=" . urlencode ($loggedInUser);

?>

<h1>Welcome, <?php echo $html->escape ($fullId); ?>!</h1>

<?php
$msg->finish ();
?>

<p>You are currently logged in as
<code><?php echo $html->escape ($fullId); ?></code>.
Your identity URL is:</p>
<address><a href="<?php echo $html->escape ($idUrl); ?>"><?php
  echo $html->escape ($idUrl);
?></a></address>

<form method="post" action="?action=logout&amp;view=login">
  <p><button class="btn btn-primary" type="submit">Log Out</button></p>
</form>
