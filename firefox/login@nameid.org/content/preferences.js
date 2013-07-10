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

/* JavaScript code for the preferences dialog.  */

Components.utils.import ("chrome://nameid-login/content/Utils.js");
Components.utils.import ("chrome://nameid-login/content/Namecoind.js");
Components.utils.import ("chrome://nameid-login/content/PrefHandler.js");

/**
 * Handle a click on the "test" button.
 * @param evt The event object.
 */
function performTest (evt)
{
  document.getElementById ("testResult").value = "Testing...";
  
  var msg;
  try
    {
      var pref = new PrefHandler ();
      var nc = new Namecoind (pref);

      var res = nc.executeRPC ("getinfo", []);
      var vers = res.version;
      var v3 = vers % 100;
      vers = Math.floor (vers / 100);
      var v2 = vers % 100;
      vers = Math.floor (vers / 100);
      var v1 = vers;

      var vStr;
      if (v3 !== 0)
        vStr = "0." + v1 + "." + v2 + "." + v3;
      else
        vStr = "0." + v1 + "." + v2;

      msg = "Success!  Namecoind version " + vStr + " running.";

      nc.close ();
      pref.close ();
    }
  catch (exc)
    {
      msg = "Error: " + exc; 
    }

  document.getElementById ("testResult").value = msg;
}

/* Register the event listener.  */
var btn = document.getElementById ("testButton");
btn.addEventListener ("command", performTest, true);
