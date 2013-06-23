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

/* Page layout asking a logged in user to confirm trust in the
   requesting page.  */

if (!isset ($fromIndex) || $fromIndex !== "yes")
  die ("Invalid page load.\n");

$fullId = "$namePrefix/$loggedInUser";

?>

<p>You are currently logged in as
<strong><?php echo $html->escape ($fullId); ?></strong>.
Should we confirm your identity to the requesting page below?</p>
<p><a href="<?php echo $html->escape ($trustRoot); ?>"><?php
  echo $html->escape ($trustRoot);
?></a></p>

<form method="post" action="?action=trust">
  <p>
    <button type="submit" name="trust">Yes</button>
    <button type="submit" name="notrust">No</button>
  </p>
</form>
