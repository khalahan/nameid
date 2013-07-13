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

/* Manage trusting (or distrusting) specific sites for enabling the add-on.  */

Components.utils.import ("chrome://nameid-login/content/Utils.js");
Components.utils.import ("resource://gre/modules/Services.jsm");

var EXPORTED_SYMBOLS = ["TrustManager"];

/**
 * This object manages the black- and whitelisting, and asks the user
 * about his/her choice if a site is neither (yet).
 * @param pref PrefHandler to use.
 */
function TrustManager (pref)
{
  this.pref = pref;
}

TrustManager.prototype =
  {

    /**
     * Clean up everything.
     */
    close: function ()
    {
      // Nothing to be done for now.
    },

    /**
     * Given a page URL containing a NameID login form, decide whether or not
     * to trust it.  Ask the user if not on the black- or whitelist already.
     * @param url Page url to decide about.
     * @return True if the page should be trusted, false if not.
     */
    decide: function (url)
    {
      /* Remove request parameters from the URL, since they may depend on
         the consumer site for instance.  */
      var re = /^([^?]*)\?/;
      var arr = re.exec (url);
      if (arr)
        url = arr[1];

      log ("Deciding trust for " + url + "...");

      var white = this.pref.getTrustList ("white");
      var black = this.pref.getTrustList ("black");

      for (var i = 0; i < black.length; ++i)
        if (black[i] === url)
          {
            log ("Found on black list, distrusting.");
            return false;
          }
      for (var i = 0; i < white.length; ++i)
        if (white[i] === url)
          {
            log ("Found on white list, trusting.");
            return true;
          }

      log ("Not found, asking user.");
      var text = "The page at '" + url + "' contains a NameID"
                 + " login form.  Do you want to permit it to automatically"
                 + " sign challenge messages for you?";
      var checkText = "Remember my decision.";
      var check = {value: false};
      var ok = Services.prompt.confirmCheck (null, "Allow NameID?", text,
                                             checkText, check);

      if (check.value)
        {
          log ("Remembering decision.");
          if (ok)
            {
              white.push (url);
              this.pref.setTrustList ("white", white);
            }
          else
            {
              black.push (url);
              this.pref.setTrustList ("black", black);
            }
        }

      return ok;
    }

  };
