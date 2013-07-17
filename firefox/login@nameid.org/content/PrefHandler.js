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
Components.utils.import ("resource://gre/modules/FileUtils.jsm");
Components.utils.import ("resource://gre/modules/NetUtil.jsm");
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
     * Get the black/whitelist for trusted sites.  It is fetched from the
     * preference and decoded already into a string array.
     * @param type Whether we want the black or whitelist.
     * @return The corresponding list as string array.
     */
    getTrustList: function (type)
    {
      assert (type === "black" || type === "white");
      var strval = this.prefs.getCharPref ("trust." + type);

      return JSON.parse (strval);
    },

    /**
     * Set the black/whitelist for trusted sites.  The passed in string array
     * is automatically converted to a JSON string for storage.
     * @param type Whether we want the black or whiteliste.
     * @param lst The string array to store.
     */
    setTrustList: function (type, lst)
    {
      assert (type === "black" || type === "white");
      var strval = JSON.stringify (lst);

      this.prefs.setCharPref ("trust." + type, strval);
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

      this.defaults.setCharPref ("trust.white", "[\"https://nameid.org/\"]");
      this.defaults.setCharPref ("trust.black", "[]");

      /* Try to figure out the credentials from the namecoin config file,
         if it can be found.  */

      var me = this;
      function readFile (stream, status)
        {
          try
            {
              if (!Components.isSuccessCode (status))
                throw "Error reading namecoin.conf file.";

              log ("Reading namecoin configuration file...");
              var available = stream.available ();
              var data = NetUtil.readInputStreamToString (stream, available);

              var re = /^rpcuser=(.*)$/m;
              var arr = re.exec (data);
              if (arr)
                {
                  me.defaults.setCharPref ("rpc.user", arr[1]);
                  log ("Found RPC username.");
                }

              re = /^rpcpassword=(.*)$/m;
              arr = re.exec (data);
              if (arr)
                {
                  me.defaults.setCharPref ("rpc.password", arr[1]);
                  log ("Found RPC password.");
                }
            }
          catch (err)
            {
              logError ("Error reading namecoin config file: " + err);
            }
        }

      try
        {
          /* FIXME: Implement this also for Windows and OS X.  */
          var file = FileUtils.getFile ("Home", [".namecoin"]);
          if (!file.exists () || !file.isDirectory ())
            throw "Couldn't locate existing .namecoin directory.";
          file.append ("namecoin.conf");
          if (!file.exists () || !file.isFile ())
            throw "Could not find namecoin.conf.";

          NetUtil.asyncFetch (file, readFile);
        }
      catch (err)
        {
          logError ("Error loading namecoin default credentials: " + err);
        }
    }

  };
