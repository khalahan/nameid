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

Components.utils.import ("resource://gre/modules/Services.jsm");

function log (msg)
{
  Services.console.logStringMessage (msg);
}

/* Observe page loads.  */
function PageLoadObserver ()
{
  this.register ();
}
PageLoadObserver.prototype =
  {
    observe: function (subject, topic, data)
    {
      log ("Observing page load: " + subject.domain);
      function doit ()
        {
          var tags = subject.getElementsByTagName ("h1");
          log ("Found " + tags.length + " h1 elements.");
          for (var i = 0; i < tags.length; ++i)
            tags[i].textContent = "foobar";
        }
      subject.addEventListener ("load", doit, true);
    },

    register: function ()
    {
      Services.obs.addObserver (this, "document-element-inserted", false);
    },

    unregister: function ()
    {
      Services.obs.removeObserver (this, "document-element-inserted");
    }
  };

var myobs = null;

function startup (data, reason)
{
  myobs = new PageLoadObserver ();
}

function shutdown (data, reason)
{
  myobs.unregister ();
  myobs = null;
}

function install (data, reason)
{

}

function uninstall (data, reason)
{

}
