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

/* Handle the preferences.  */

Components.utils.import ("chrome://nameid-login/content/Utils.js");
Components.utils.import ("resource://gre/modules/Services.jsm");

var EXPORTED_SYMBOLS = ["PrefHandler"];

/** The preferences branch used by nameid.  */
var prefBranch = "extensions.nameid-login.";

/**
 * This object handles retrieval and management of preferences.
 */
function PrefHandler ()
{
  this.prefs = Services.prefs.getBranch (prefBranch);
  this.defaults = Services.prefs.getDefaultBranch (prefBranch);
}

PrefHandler.prototype =
  {

    /**
     * Clean up everything.
     */
    close: function ()
    {
      // Nothing to be done for now.
    },

    /**
     * Query for the RPC connection options.
     * @return Object with the RPC connetion settings.
     */
    getConnectionSettings: function ()
    {
      var res =
        {
          host: this.prefs.getCharPref ("rpc.host"),
          port: this.prefs.getIntPref ("rpc.port"),
          user: this.prefs.getCharPref ("rpc.user"),
          password: this.prefs.getCharPref ("rpc.password")
        };

      return res;
    },

    /**
     * Install the default preferences.
     */
    installDefaults: function ()
    {
      this.defaults.setCharPref ("rpc.host", "localhost");
      this.defaults.setIntPref ("rpc.port", 8336);
      this.defaults.setCharPref ("rpc.user", "");
      this.defaults.setCharPref ("rpc.password", "");
    }

  };
