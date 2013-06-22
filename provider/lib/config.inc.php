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

/* Configuration variables are set here.  This file needs to be updated
   when setting up the identity provider on a server accordingly.  */

/* Path to the namecoind binary (or rather, command prefix to be used for
   executing RPC commands).  */
$namecoind = "/usr/local/bin/namecoind-qt";

/* Namespace used for name lookups.  */
$namePrefix = "id";

?>
