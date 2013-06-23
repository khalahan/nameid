<?php
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

/* Manage request parameters.  */

/**
 * This class keeps track of request parameters.
 */
class RequestHandler
{

  /** The source for request values.  */
  private $req;

  /**
   * Construct.
   */
  public function __construct ()
  {
    $this->req = $_REQUEST;
  }

  /**
   * Close at the end.
   */
  public function close ()
  {
    // Nothing to be done.
  }

  /**
   * See whether we have a given key.
   * @param key The key we look for.
   * @return True if there is a value for it.
   */
  public function check ($key)
  {
    return isset ($this->req[$key]);
  }

  /**
   * Query for a request string.  Magic quotes are stripped if they are on.
   * The key must be present, or we will throw.
   * @param key The key.
   * @return The request value for key.
   */
  public function getString ($key)
  {
    if (!$this->check ($key))
      throw new RuntimeException ("No request value for '$key'.");

    $res = $this->req[$key];
    if (get_magic_quotes_gpc ())
      $res = stripslashes ($res);

    return $res;
  }

  /**
   * Query for a named button click.
   * @param key The key.
   * @return True if the button with that name submitted the form.
   */
  public function getSubmitButton ($key)
  {
    return $this->check ($key);
  }

}

?>
