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

/* Code that uses the JSON-RPC interface classes and implements the high-level
   stuff like name info extraction and message verification on top.  */

require_once ("config.inc.php");
require_once ("namecoin_rpc.inc.php");

/**
 * Exception thrown when a given name is not found.
 */
class NameNotFoundException extends RpcException
{

  /** The name not found.  */
  public $name;

  /**
   * Construct it by building up an appropriate message.
   * @param name The name which was not found.
   */
  public function __construct ($name)
  {
    parent::__construct ("Name not found: '$name'");
    $this->name = $name;
  }

}

/**
 * This class encapsulates high-level access to Namecoin.
 */
class NamecoinInterface
{

  /** Store here the RPC interface object used.  */
  private $rpc;

  /** Namespace used for name lookups.  */
  private $ns;

  /**
   * Construct, which opens the RPC interface object automatically.
   */
  public function __construct ()
  {
    global $namecoind, $namePrefix;

    $this->rpc = new Namecoind ($namecoind);
    $this->ns = $namePrefix;
  }

  /**
   * Close the connection.
   */
  public function close ()
  {
    $this->rpc->close ();
  }

  /**
   * Get all data associated with an id as object.  In case the namecoind call
   * fails or the name is not found
   * @param name The name to look up.
   * @return Associated data as object corresponding to the JSON data.
   */
  public function getIdData ($name)
  {
    try
      {
        $fullname = "{$this->ns}/$name";
        $res = $this->rpc->executeRPC ("name_show", array ($fullname));
        assert ($fullname === $res->name);
      }
    catch (JsonRpcError $exc)
      {
        /* Handle name not found error, otherwise rethrow the original one.  */
        if ($exc->code === -4)
          throw new NameNotFoundException ($name);
        throw $exc;
      }

    return $res;
  }

  /**
   * Returns value associated to a name.
   * @param name The name to look up.
   * @return The value associated to it as JSON object.
   */
  public function getIdValue ($name)
  {
    $data = $this->getIdData ($name);
    $val = $data->value;

    return $this->rpc->decode ($val, TRUE);
  }

  /**
   * Verify a signed message for a name.  This first queries for the address
   * associated with a name, and then verifies the message.
   * @param name The name in question.
   * @param msg The signed message.
   * @param sig The message signature.
   * @return True or false, depending on the message validity.
   */
  public function verifyMessage ($name, $msg, $sig)
  {
    $data = $this->getIdData ($name);
    assert (isset ($data->address));

    /* Catch the error for invalid base64 in the signature, which can easily
       be triggered by the user.  Report it simply as invalid.  */
    try
      {
        $args = array ($data->address, $sig, $msg);
        $res = $this->rpc->executeRPC ("verifymessage", $args);
      }
    catch (JsonRpcError $exc)
      {
        if ($exc->code === -5)
          return FALSE;
        throw $exc;
      }

    return ($res === TRUE);
  }

}

?>
