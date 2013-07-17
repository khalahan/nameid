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

/** Preference handler used in this module.  */
var pref = new PrefHandler ();

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
    }
  catch (exc)
    {
      msg = "Error: " + exc; 
    }

  document.getElementById ("testResult").value = msg;
}

/**
 * Set up the trust list element.
 * @param list The element to use as "anchor".
 */
function setupTrustList (list)
{
  /* Currently shown list of sites and their black/white status.  This
     array is part of closures registered as event handlers for
     deleting.  */
  var shown;

  /* Routine to update it from the preferences.  */
  function update ()
    {
      /* Clean up first all existing items.  */
      var children = list.getElementsByTagName ("listitem");
      while (children.length > 0)
        list.removeChild (children[0]);

      /* Retrieve and sort current values.  */
      var white = pref.getTrustList ("white");
      var black = pref.getTrustList ("black");
      shown = [];
      for (var i = 0; i < white.length; ++i)
        shown.push ({site: white[i], status: "white"});
      for (var i = 0; i < black.length; ++i)
        shown.push ({site: black[i], status: "black"});
      function compareThem (a, b)
        {
          return a.site > b.site;
        }
      shown.sort (compareThem);

      /* Show the values.  */
      log ("Found " + shown.length + " values to show in trust list.");
      for (var i = 0; i < shown.length; ++i)
        {
          var row = document.createElement ("listitem");

          var cell = document.createElement ("listcell");
          cell.setAttribute ("label", shown[i].site);
          row.appendChild (cell);

          cell = document.createElement ("listcell");
          var status;
          switch (shown[i].status)
            {
            case "white":
              status = "Allow";
              break;
            case "black":
              status = "Block";
              break;
            default:
              assert (false);
            }
          cell.setAttribute ("label", status);
          row.appendChild (cell);

          list.appendChild (row);
        }
    }

  /* Save back the shown list (after modifying it possibly) to the
     preferences settings.  */
  function saveBack ()
    {
      log ("Saving back " + shown.length + " trust entries.");
      var lists = {white: [], black: []};
      for (var i = 0; i < shown.length; ++i)
        {
          assert (lists[shown[i].status] !== undefined);
          lists[shown[i].status].push (shown[i].site);
        }

      pref.setTrustList ("white", lists.white);
      pref.setTrustList ("black", lists.black);
    }

  /* Handle key press on the list and delete entries on del key.  */
  function handleKey (evt)
    {
      if (evt.keyCode !== 46)
        return;
      log ("Trying to delete item off trust list.");
      var ind = list.selectedIndex;
      if (ind === -1)
        return;

      /* Swap element to end and pop off.  */
      var endInd = shown.length - 1;
      if (ind !== endInd)
        {
          assert (ind < endInd);
          shown[ind] = shown[endInd];
        }
      shown.pop ();

      saveBack ();
      update ();
    }

  /* Perform initial update and register handler.  */
  update ();
  list.addEventListener ("keyup", handleKey, true);
}

/* Register the event listener.  */
var btn = document.getElementById ("testButton");
btn.addEventListener ("command", performTest, true);

/* Initialise the trust UI.  */
setupTrustList (document.getElementById ("trustList"));
