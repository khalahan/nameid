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

/* Main driver object for the addon.  */

Components.utils.import ("chrome://nameid-login/content/Namecoind.js");
Components.utils.import ("chrome://nameid-login/content/Utils.js");
Components.utils.import ("resource://gre/modules/Services.jsm");

var EXPORTED_SYMBOLS = ["NameIdAddon"];

/**
 * The main object encapsulating the addon's state.
 * @param pref Preferences handler to use.
 */
function NameIdAddon (pref)
{
  this.pref = pref;
}

NameIdAddon.prototype =
  {

    /**
     * Initialise the observer to "start" this addon.
     */
    register: function ()
    {
      Services.obs.addObserver (this, "document-element-inserted", false);
    },

    /**
     * Stop the observer on shutdown.
     */
    unregister: function ()
    {
      Services.obs.removeObserver (this, "document-element-inserted");
    },

    /**
     * Observe events, in particular loads of new documents that have to be
     * scanned for signs of a NameID login form.
     * @param subject Subject of the event.
     * @param topic Topic of the event.
     * @param data Further data.
     */
    observe: function (subject, topic, data)
    {
      if (topic !== "document-element-inserted")
        return;

      log ("Observing page load: " + subject.URL);
      var me = this;
      function handler (evt)
        {
          me.scanPage (evt.target.ownerDocument);
        }
      subject.addEventListener ("load", handler, true);
    },

    /**
     * Handle events of fully loaded pages, which are then scanned
     * for signs of a NameID form.
     * @param doc The page's document.
     */
    scanPage: function (doc)
    {
      var nonceEl = doc.getElementById ("nameid-nonce");
      var uriEl = doc.getElementById ("nameid-uri");
      var form = doc.getElementById ("loginForm");
      if (!nonceEl || !uriEl || !form)
        {
          log ("Found no NameID login form.");
          return;
        }

      /* Ignore duplicate page load events.  */
      if (form.dataset.nameidLoginObserved === "yes")
        {
          log ("Duplicate event, ignoring.");
          return;
        }
      form.dataset.nameidLoginObserved = "yes";

      /* Ask the user about trust for this page.  */
      var text = "The page at '" + doc.URL + "' contains a NameID"
                 + " login form.  Do you want to permit it to automatically"
                 + " sign challenge messages for you?";
      var ok = Services.prompt.confirm (null, "Allow NameID?", text);
      if (!ok)
        return;

      this.nonce = nonceEl.textContent;
      this.uri = uriEl.textContent;
      log ("Found NameID login form with nonce: " + this.nonce);

      /* Hide the manual entry forms.  */
      doc.documentElement.className = "withAddon";

      /* Connect a handler to intercept the form submit.  Note that we don't
         want to intercept if the cancel button was clicked.  */
      var cancel = doc.getElementById ("cancel");
      this.cancelClicked = false;
      var me = this;
      function handlerSubmit (e)
        {
          if (!me.cancelClicked)
            {
              var submit = me.interceptSubmit (doc);
              if (!submit)
                e.preventDefault ();
            }
        }
      function handlerCancel (e)
        {
          me.cancelClicked = true;
        }
      form.addEventListener ("submit", handlerSubmit, true);
      cancel.addEventListener ("click", handlerCancel, true);
    },

    /**
     * Intercept the form submit.
     * @param doc The document we're on.
     * @return False in case we want to abort the submission.
     */
    interceptSubmit: function (doc)
    {
      var idEntry = doc.getElementById ("identity");
      var id = idEntry.value;
      var msg = this.getChallenge (id);
      log ("Attempting to sign challenge: " + msg);

      /* Custom error handler that understands some error codes.  */
      function errHandler (err)
        {
          switch (err.code)
            {
            case -4:
              throw "The specified name 'id/" + id + "' is not registered.";

            case -14:
              throw "The provided passphrase is incorrect.";

            default:
              break;
            }

          return false;
        }

      try
        {
          var nc = new Namecoind (this.pref);

          var res = nc.executeRPC ("name_show", ["id/" + id], errHandler);
          var addr = res.address;
          log ("Found address for name 'id/" + id + "': " + addr);

          res = nc.executeRPC ("validateaddress", [addr]);
          if (!res.ismine)
            throw "You don't own the private key for 'id/" + id + "'.";

          res = nc.executeRPC ("getinfo", []);
          var didUnlock = false;
          if (res.unlocked_until !== undefined && res.unlocked_until === 0)
            {
              var title = "Unlock Namecoin Wallet";
              var text = "Please provide the password to temporarily unlock"
                         + " your namecoin wallet:";

              var pwd = {};
              var btn = Services.prompt.promptPassword (null, title, text, pwd, 
                                                        null, {});
              /* Abort if cancel was clicked.  */
              if (!btn)
                {
                  log ("Wallet unlock cancelled by user.");
                  nc.close ();
                  return false;
                }

              nc.executeRPC ("walletpassphrase", [pwd.value, 10], errHandler);
              didUnlock = true;
            }

          var signature = nc.executeRPC ("signmessage", [addr, msg]);
          doc.getElementById ("signature").value = signature;
          log ("Successfully provided signature.");

          if (didUnlock)
            nc.executeRPC ("walletlock", []);

          nc.close ();
        }
      catch (err)
        {
          Services.prompt.alert (null, "NameID Connection Error", err);
          return false;
        }

      return true;
    },

    /**
     * Construct the challenge message for a given ID.  Page URI and
     * nonce are stored already as variables.
     * @param id The user entered ID.
     * @return The full challenge message.
     */
    getChallenge: function (id)
    {
      /* This must of course be in sync with the PHP code as well as
         the "ordinary" page JavaScript!  */

      var fullId = this.uri + "?name=" + encodeURIComponent (id);
      var msg = "login " + fullId + " " + this.nonce;

      return msg;
    }

  };
