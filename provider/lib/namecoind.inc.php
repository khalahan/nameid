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

/* Code to interface with namecoind to verify signed messages and
   extract id information.  */

require_once ("config.inc.php");

/**
 * Exception thrown when executing namecoind fails.
 */
class NamecoindException extends RuntimeException
{

  /**
   * The error result as JSON object.  May be null if no object was returned,
   * for instance, because namecoind itself could not even be launched.
   */
  public $error;

  /** Status code returned by the shell.  */
  public $exitCode;

  /**
   * Construct it with the given error result.
   * @param exitCode The shell's exit code returned.
   * @param error The error object returned from namecoind, if any.
   */
  public function __construct ($exitCode, $error = NULL)
  {
    if ($error && isset ($error->message))
      $msg = "Namecoind execution failed: {$error->message}";
    else
      $msg = "Namecoind execution failed.";
    parent::__construct ($msg);

    $this->exitCode = $exitCode;
    $this->error = $error;
  }

}

/**
 * Exception thrown when a given name is not found.
 */
class NameNotFoundException extends RuntimeException
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
 * This class encapsulates access to the namecoind running on the server.
 */
class Namecoind
{

  /** Store here the namecoind command.  */
  private $namecoind;
  /** Namespace used for name lookups.  */
  private $ns;

  /**
   * Construct, which does nothing.
   */
  public function __construct ()
  {
    global $namecoind, $namePrefix;

    $this->namecoind = $namecoind;
    $this->ns = $namePrefix;
  }

  /**
   * Close the connection.
   */
  public function close ()
  {
    // Nothing to do (at least for now).
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
        $res = $this->executeRPC ("name_show", array ($fullname));
        assert ($fullname === $res->name);
      }
    catch (NamecoindException $exc)
      {
        /* Handle name not found error, otherwise rethrow the original one.  */
        if ($exc->error && isset ($exc->error->code)
            && $exc->error->code === -4)
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

    return $this->decode ($val, TRUE);
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

    $args = array ($data->address, $sig, $msg);
    $res = $this->executeRPC ("verifymessage", $args);

    return ($res === TRUE);
  }

  /**
   * Execute a namecoind RPC call and return its result or throw an
   * error if it fails.  Strings are shell-escaped as necessary.
   * @param method RPC method to call.
   * @param args Arguments to the method as array of strings.
   * @return JSON result as object.
   */
  private function executeRPC ($method, $args)
  {
    $args = array_map ("escapeshellarg", $args);
    $descr = array (1 => array ("pipe", "w"),
                    2 => array ("pipe", "w"));
    $cmd = "{$this->namecoind} $method " . implode (" ", $args);

    $proc = proc_open ($cmd, $descr, $pipes);
    if (!is_resource ($proc))
      throw new RuntimeException ("Failed to start namecoind process.");

    $out = stream_get_contents ($pipes[1]);
    $err = stream_get_contents ($pipes[2]);
    fclose ($pipes[1]);
    fclose ($pipes[2]);

    $retval = proc_close ($proc);

    if (substr ($err, 0, 7) === "error: ")
      {
        $json = substr ($err, 7);
        $obj = $this->decode ($json);
        throw new NamecoindException ($retval, $obj);
      }

    if ($retval !== 0)
      throw new NamecoindException ($retval);

    return $this->decode ($out);
  }

  /**
   * JSON decode and throw if it fails.
   * @param str JSON string.
   * @param nothrow If set, return NULL instead of throwing.
   * @return Decoded object.
   */
  private function decode ($str, $nothrow = FALSE)
  {
    if ($str === "true\n")
      return TRUE;
    if ($str === "false\n")
      return FALSE;

    $res = json_decode ($str);
    if (!is_object ($res) && !$nothrow)
      throw new RuntimeException ("JSON decoding of '$str' failed.");

    return $res;
  }

}

?>
