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

/* Firefox plugin bootstrapping code.  */

/** The instance of NameIdAddon used.  */
var instance = null;

/**
 * Bootstrap the addon.
 * @param data Bootstrap data.
 * @param reason Why startup is called.
 */
function startup (data, reason)
{
  Components.utils.import ("chrome://nameid-login/content/NameIdAddon.js");
  instance = new NameIdAddon ();
}

/**
 * Disable the addon.
 * @param data Bootstrap data.
 * @param reason Why shutdown is called.
 */
function shutdown (data, reason)
{
  instance.unregister ();
  instance = null;
}

/**
 * Install the addon.
 * @param data Bootstrap data.
 * @param reason Why install is called.
 */
function install (data, reason)
{
  // Nothing to do.
}

/**
 * Uninstall the addon.
 * @param data Bootstrap data.
 * @param reason Why uninstall is called.
 */
function uninstall (data, reason)
{
  // Nothing to do.
}
