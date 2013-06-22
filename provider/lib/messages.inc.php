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

/* Handle message and error display.  */

/**
 * Class for an exception representing a UI error shown to the user.
 */
class UIError extends Exception
{

  /** The message.  */
  public $msg;

  /**
   * Construct it with the given message.
   * @param msg The error message to show.
   */
  public function __construct ($msg)
  {
    parent::__construct ("UI Error: $msg");
    $this->msg = $msg;
  }

}

/**
 * Keep track of messages and errors and show them afterwards on request.
 */
class MessageList
{

  /** List of all messages.  */
  private $messages;
  /** List of all errors.  */
  private $errors;

  /** The HTML output object to use.  */
  private $html;

  /** Already finished?  */
  private $finished;

  /**
   * Construct with empty lists.
   * @param html HTML output object.
   */
  public function __construct (HtmlOutput $html)
  {
    $this->messages = array ();
    $this->errors = array ();
    $this->html = $html;
    $this->finished = FALSE;
  }

  /**
   * Finish off, writing out all collected pieces of information.
   */
  public function finish ()
  {
    if ($this->finished)
      throw new RuntimeException ("MessageList is already finished!");
    $this->finished = TRUE;

    if (count ($this->errors) > 0)
      {
        echo "<ul class='errors'>\n";
        foreach ($this->errors as $msg)
          echo "<li>" . $this->html->escape ($msg) . "</li>\n";
        echo "</ul>\n";
      }

    if (count ($this->messages) > 0)
      {
        echo "<ul class='messages'>\n";
        foreach ($this->messages as $msg)
          echo "<li>" . $this->html->escape ($msg) . "</li>\n";
        echo "</ul>\n";
      }
  }

  /**
   * Add a message to the list.
   * @param msg The new message.
   */
  public function addMessage ($msg)
  {
    if ($this->finished)
      throw new RuntimeException ("MessageList is already finished!");

    array_push ($this->messages, $msg);
  }

  /**
   * Execute a given code fragment and catch UIErrors thrown.
   * @param code The code to execute.
   */
  public function runWithErrors (callable $code)
  {
    if ($this->finished)
      throw new RuntimeException ("MessageList is already finished!");

    try
      {
        $code ();
      }
    catch (UIError $exc)
      {
        array_push ($this->errors, $exc->msg);
      }
  }

}

?>
