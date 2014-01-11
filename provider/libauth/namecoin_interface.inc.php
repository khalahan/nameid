<?php
/*
    NameID, a namecoin based OpenID identity provider.
    Copyright (C) 2013-2014 by Daniel Kraft <d@domob.eu>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/* Code that uses the JSON-RPC interface classes and implements the high-level
   stuff like name info extraction and message verification on top.  */

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
   * Construct given the namecoin RPC interface to use.
   * @param rpc The RPC interface to use.
   * @param ns Namecoin namespace to use for names.
   */
  public function __construct ($rpc, $ns)
  {
    $this->rpc = $rpc;
    $this->ns = $ns;
  }

  /**
   * Close the connection.  This closes the RPC automatically.
   */
  public function close ()
  {
    $this->rpc->close ();
  }

  /**
   * Check if the argument is a valid address.
   * @param str Object to check.
   * @return True iff str is a string and a valid address.
   */
  public function isValidAddress ($str)
  {
    if (!is_string ($str))
      return FALSE;

    $res = $this->rpc->executeRPC ("validateaddress", array ($str));
    assert (isset ($res->isvalid));

    return $res->isvalid;
  }

  /**
   * Get all data associated with an id as object.  In case the namecoind call
   * fails or the name is not found, NameNotFound is thrown.
   * @param name The name to look up.
   * @return Associated data as object corresponding to the JSON data.
   * @throws NameNotFoundException if the name does not exist.
   * @throws JsonRpcError in case of another exception.
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
   * Returns value associated to a name.  Returns NULL in case of invalid
   * JSON data associated with the name.
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
   * Verify a signed message for an address.
   * @param addr The address in question.
   * @param msg The signed message.
   * @param sig The message signature.
   * @return True or false, depending on the message validity.
   */
  public function verifyMessage ($addr, $msg, $sig)
  {
    /* Catch the error for invalid base64 in the signature, which can easily
       be triggered by the user.  Report it simply as invalid.  */
    try
      {
        $args = array ($addr, $sig, $msg);
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

  /**
   * Get the current namespace.
   * @return Namespace (prefix) used.
   */
  public function getNamespace ()
  {
    return $this->ns;
  }

}

?>
