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

/* Code handling the RPC interface to namecoin.  This can either be done
   by calling the namecoind binary (old method) or using the RPC
   interface via HTTP (new method).  */

/* ************************************************************************** */
/* Common stuff between the access methods.  */

/**
 * Exception thrown whenever something fails with calling RPC functions.
 */
class RpcException extends RuntimeException
{

  /**
   * Construct with the given message.
   * @param msg The error message.
   */
  public function __construct ($msg)
  {
    parent::__construct ($msg);
  }

}

/**
 * Exception thrown when JSON-RPC returns an error object.
 */
class JsonRpcError extends RpcException
{

  /** The error message returned.  */
  public $message;
  /** The error code returned.  */
  public $code;

  /**
   * Construct it from the supplied JSON object.
   * @param obj The error object.
   */
  public function __construct ($obj)
  {
    if ($obj === NULL)
      {
        $this->message = "Unknown error.";
        $this->code = NULL;
      }
    else
      {
        assert (isset ($obj->message) && isset ($obj->code));
        $this->message = $obj->message;
        $this->code = $obj->code;
      }

    parent::__construct ("JSON-RPC error {$this->code}: '{$this->message}'");
  }

}

/**
 * Generic RPC interface object.
 */
abstract class NamecoinRPC
{

  /**
   * Perform an RPC query with the given parameters.  Throws in case of failure.
   * @param method RPC method to call.
   * @param args Arguments to pass to it as array of values.
   * @return JSON result as object.
   */
  abstract public function executeRPC ($method, $args);

  /**
   * Constructor to be called by subclasses.  Does nothing, though.
   */
  protected function __construct ()
  {
    // Nothing to do.
  }

  /**
   * Close the connection.
   */
  public function close ()
  {
    // Nothing to do.
  }

  /**
   * JSON decode and throw if it fails.  This is a utility method that
   * can be used for easy decoding plus error checking.
   * @param str JSON string.
   * @param nothrow If set, return NULL instead of throwing.
   * @return Decoded object.
   */
  public function decode ($str, $nothrow = FALSE)
  {
    if ($str === "true\n")
      return TRUE;
    if ($str === "false\n")
      return FALSE;

    $res = json_decode ($str);
    if (!is_object ($res) && !$nothrow)
      throw new RpcException ("JSON decoding of '$str' failed.");

    return $res;
  }

}

/* ************************************************************************** */
/* Interface via the binary.  */

/**
 * Exception thrown when executing the namecoind binary fails.
 */
class NamecoindException extends RpcException
{

  /** Status code returned by the shell.  */
  public $exitCode;

  /**
   * Construct it with the given error result.
   * @param exitCode The shell's exit code returned.
   */
  public function __construct ($exitCode)
  {
    parent::__construct ("Namecoind execution failed"
                         ." with exit code $exitCode.");
    $this->exitCode = $exitCode;
  }

}

/**
 * Interface method via executing the namecoind binary.
 */
class Namecoind extends NamecoinRpc
{

  /** Store here the namecoind command.  */
  private $namecoind;

  /**
   * Construct it.
   * @param cmd The namecoind command to use.
   */
  public function __construct ($cmd)
  {
    parent::__construct ();
    $this->namecoind = $cmd;
  }

  /**
   * Execute a namecoind RPC call and return its result or throw an
   * error if it fails.  Strings are shell-escaped as necessary.
   * @param method RPC method to call.
   * @param args Arguments to the method as array of strings.
   * @return JSON result as object.
   */
  public function executeRPC ($method, $args)
  {
    $args = array_map ("escapeshellarg", $args);
    $descr = array (1 => array ("pipe", "w"),
                    2 => array ("pipe", "w"));
    $cmd = "{$this->namecoind} $method " . implode (" ", $args);

    $proc = proc_open ($cmd, $descr, $pipes);
    if (!is_resource ($proc))
      throw new RpcException ("Failed to start namecoind process.");

    $out = stream_get_contents ($pipes[1]);
    $err = stream_get_contents ($pipes[2]);
    fclose ($pipes[1]);
    fclose ($pipes[2]);

    $retval = proc_close ($proc);

    if (substr ($err, 0, 7) === "error: ")
      {
        $json = substr ($err, 7);
        $obj = $this->decode ($json);
        throw new JsonRpcError ($obj);
      }

    if ($retval !== 0)
      throw new NamecoindException ($retval);

    return $this->decode ($out);
  }

}

?>
