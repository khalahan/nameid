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

/* Perform the user authentication on login requests.  */

/**
 * Check user login signatures.
 */
class Authenticator
{

  /** Namecoind connection we have.  */
  private $nc;
  /** Session handler to use for the nonce.  */
  private $session;

  /**
   * Construct.
   * @param nc The namecoind connection to use.
   * @param session The session handler to use.
   */
  public function __construct (Namecoind $nc, Session $session)
  {
    $this->nc = $nc;
    $this->session = $session;
  }

  /**
   * Try to log a user in with the given data.
   * @param id The user's id.
   * @param signature The user's signature.
   */
  public function login ($id, $signature)
  {
    global $namePrefix;

    $nonce = $this->session->getNonce ();
    assert ($nonce !== NULL);
    $msg = $this->getChallenge ($id, $nonce);

    try
      {
        $res = $this->nc->verifyMessage ($id, $msg, $signature);
        if (!$res)
          throw new UIError ("The signature is invalid.");
      }
    catch (NameNotFoundException $exc)
      {
        throw new UIError ("The identity $namePrefix/$id is not registered.");
      }

    /* If no exception was thrown until here, the login was successful.  */
    $this->session->setUser ($id);
  }

  /**
   * Build up the challenge message.
   * @param id The identity of the user.
   * @param nonce The nonce used.
   * @return The challenge message that should be signed.
   */
  private function getChallenge ($id, $nonce)
  {
    global $namePrefix, $serverUri;

    /* This must of course be in sync with the JavaScript
       code on the loginForm page!  */

    $fullId = "$serverUri?name=" . urlencode ($id);
    return "login $fullId $nonce";
  }

}

?>
