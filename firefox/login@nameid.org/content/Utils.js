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

/* Some basic utility routines.  */

Components.utils.import ("resource://gre/modules/Services.jsm");

var EXPORTED_SYMBOLS = ["log", "assert"];

/**
 * Utility function to log a message to the ErrorConsole.  This is used
 * for debugging and may be disabled in release code.
 * @param msg The message to send.
 */
function log (msg)
{
  Services.console.logStringMessage (msg);
}

/**
 * Utility function to assert a fact for debugging.
 * @param cond The condition that must hold.
 */
function assert (cond)
{
  if (!cond)
    {
      Components.utils.reportError ("Assertion failure.");
      throw "Assertion failure.";
    }
}
