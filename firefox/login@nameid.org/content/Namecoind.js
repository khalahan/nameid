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

/* Interface to the used namecoind.  */

Components.utils.import ("chrome://nameid-login/content/Utils.js");
Components.utils.import ("resource://gre/modules/NetUtil.jsm");

var EXPORTED_SYMBOLS = ["Namecoind"];

/**
 * The main object encapsulating a namecoind connection.
 */
function Namecoind (host, port, user, pass)
{
  this.url = "http://" + host + ":" + port;
  this.user = user;
  this.pass = pass;
}

Namecoind.prototype =
  {

    /**
     * Clean up everything.
     */
    close: function ()
    {
      // Nothing to be done for now.
    },

    /**
     * Send an HTTP request and return the result.
     * @param req Request data to send.
     * @return The returned data.
     */
    requestHTTP: function (req)
    {
      var ch = NetUtil.newChannel (this.url);
      ch.QueryInterface (Components.interfaces.nsIHttpChannel);
      var auth = this.user + ":" + this.pass;
      ch.setRequestHeader ("Authorization", "Basic " + btoa (auth), false);

      var stream = ch.open ();
      var avail = stream.available ();
      if (avail === 0)
        {
          stream.close ();
          throw "The connection to your local namecoind failed.  Please"
                + " check the connection settings.";
        }
      var string = NetUtil.readInputStreamToString (stream, avail, null);
      stream.close ();

      log ("Response code: " + ch.responseStatus);
      log (string);

      if (ch.responseStatus === 401)
        throw "The NameID add-on could not authenticate with the locally"
              + " running namecoind.  Please check the authentication data.";
      else if (ch.responseStatus !== 200)
        throw "Unknown error connecting to namecoind.";
    }

  };
