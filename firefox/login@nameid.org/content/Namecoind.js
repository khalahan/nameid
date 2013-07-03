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

  /* Increment ID always to ensure we get matching responses to all
     requests sent.  */
  this.nextID = 1;
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
     * Call an RPC method.  If an error occurs, this method throws.
     * @param method The method to call.
     * @param args The arguments to pass it as array.
     * @param errHandler If given, call this method in cases of errors
     *                   reported from the RPC call instead of throwing.
     * @return The result in case of success.
     */
    executeRPC: function (method, args, errHandler)
    {
      var id = this.nextID++;
      var jsonData =
        {
          method: method,
          params: args,
          id: id
        };
      var resString = this.requestHTTP (JSON.stringify (jsonData));
      var res = JSON.parse (resString);

      /* Ensure the ID matches, should always be the case.  */
      assert (res.id === id);

      if (res.error !== null)
        {
          if (errHandler !== undefined && errHandler (res.error))
            return null;

          logError ("namecoind returned: " + res.error.message);
          throw "Namecoind failed to process the request successfully.";
        }

      return res.result;
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
      ch.setRequestHeader ("Accept", "application/json", false);

      var s = Components.classes["@mozilla.org/io/string-input-stream;1"]
                .createInstance (Components.interfaces.nsIStringInputStream);
      s.setData (req, req.length);
      ch.QueryInterface (Components.interfaces.nsIUploadChannel);
      ch.setUploadStream (s, "application/json", -1);
      ch.requestMethod = "POST";

      s = ch.open ();
      var avail = s.available ();
      if (avail === 0)
        {
          s.close ();
          throw "The connection to your local namecoind failed.  Please"
                + " check the connection settings.";
        }
      var string = NetUtil.readInputStreamToString (s, avail, null);
      s.close ();

      log ("Response code: " + ch.responseStatus);
      log ("namecoind response: " + string);

      switch (ch.responseStatus)
        {
        case 401:
          throw "The NameID add-on could not authenticate with the locally"
                + " running namecoind.  Please check the authentication data.";
          break;

        case 200:
        case 404:
        case 500:
          /* Everything ok.  500 means that the request was received fine,
             but an error occured during processing it.  This has to be
             handled elsewhere, though.  404 means that the requested method
             was not found.  */
          return string;

        default:
          throw "Unknown error connecting to namecoind.";
          break;
        }

      /* Should never be reached.  */
      assert (false);
    }

  };
