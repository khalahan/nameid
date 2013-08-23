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

/* Interfacing to the php-openid library.  */

require_once ("config.inc.php");
require_once ("Auth/OpenID/FileStore.php");
require_once ("Auth/OpenID/Server.php");
require_once ("Auth/OpenID/SReg.php");

/**
 * Encapsulate an php-openid OpenID-Server class plus the corresponding
 * data store.
 */
class OpenID
{
  
  /** The server object of php-openid.  */
  private $server;

  /** Session management object.  */
  private $session;

  /** Namecoin connection.  */
  private $nc;

  /**
   * Construct a fresh connection.
   * @param session The session object to use.
   * @param nc Namecoin connection to use.
   */
  public function __construct (Session $session, NamecoinInterface $nc)
  {
    global $serverUri, $openidStorageDir;

    $store = new Auth_OpenID_FileStore ($openidStorageDir);
    $this->server = new Auth_OpenID_Server ($store, "$serverUri?view=openid");

    $this->session = $session;
    $this->nc = $nc;
  }

  /**
   * Close at the end.
   */
  public function close ()
  {
    // Nothing to do (at least for now).
  }

  /**
   * Decode the request info from the server.  This already handles
   * the request types with the server which do not need authentication.
   * @return OpenID request info or NULL if no request.
   */
  public function decodeRequest ()
  {
    $request = $this->server->decodeRequest ();
    $this->checkError ($request);
    if (!$request)
      {
        $this->session->setRequestInfo (NULL);
        return NULL;
      }
    $this->session->setRequestInfo ($request);

    /* The following is based on the php-openid server example.  */
    $checkIdRequests = array ("checkid_immediate", "checkid_setup");
    if (in_array ($request->mode, $checkIdRequests))
      {
        if ($request->idSelect ())
          {
            if ($request->mode === "checkid_immediate")
              $resp = $request->answer (false);
            else
              return $request;
          }
        else if (!$request->identity)
          {
            /* If we get a request without asking for an identity,
               cancel it immediately.  */
            $this->cancel ();
          }
        else if ($request->immediate)
          $resp = $request->answer (false);
        else
          return $request;
      }
    else
      $resp = $this->server->handleRequest ($request);

    $this->sendResponse ($resp);
    // This is not reached as sending the response is no-return.
    assert (false);
  }

  /**
   * Cancel the authentication with the server.  This redirects to the
   * cancel url, and is no-return.
   * @param info The request info.
   */
  public function cancel ()
  {
    global $serverUri;

    $info = $this->session->getRequestInfo ();
    $this->session->setRequestInfo (NULL);

    if ($info)
      $url = $info->getCancelURL ();
    else
      $url = $serverUri;

    header ("Location: $url");
    exit (0);
  }

  /**
   * Authenticate the currently logged in user.  This sends the
   * result, and is thus no-return.
   */
  public function authenticate ()
  {
    global $serverUri;

    $id = $this->session->getUser ();
    if (!$id)
      $this->cancel ();

    $req = $this->session->getRequestInfo ();
    if (!$req)
      $this->cancel ();

    assert ($id && $req);

    $fullid = "$serverUri?name=" . urlencode ($id);
    $resp = $req->answer (true, NULL, $fullid);
    $this->checkError ($resp);

    /* Get more data to send.  */
    $data = $this->nc->getIdValue ($id);
    $sregData = array ();
    $sregKeys = array ("name" => "fullname",
                       "email" => "email",
                       "website" => "website",
                       "nick" => "nickname");
    if ($data)
      foreach ($sregKeys as $key => $val)
        if (isset ($data->$key))
          $sregData[$val] = $data->$key;

    /* Add simple registration data to response.  */
    $sregReq = Auth_OpenID_SRegRequest::fromOpenIDRequest ($req);
    $sregResp = Auth_OpenID_SRegResponse::extractResponse ($sregReq, $sregData);
    $sregResp->toMessage ($resp->fields);

    /* Send response and clean out request info.  */
    $this->session->setRequestInfo (NULL);
    $this->sendResponse ($resp);
  }

  /**
   * Send a response back to the client and exit the script.
   * @param response The response to send, as given by php-openid.
   */
  private function sendResponse ($response)
  {
    $webResp = $this->server->encodeResponse ($response);

    if ($webResp->code !== AUTH_OPENID_HTTP_OK)
      header ("HTTP/1.1 {$webResp->code} ", true, $webResp->code);
    foreach ($webResp->headers as $key => $val)
      header ("$key: $val");
    header ("Connection: close");

    echo $webResp->body;
    exit (0);
  }

  /**
   * Check for a server error and throw in that case.
   * @param obj Response object.
   */
  private function checkError ($obj)
  {
    if ($obj instanceof Auth_OpenID_ServerError)
      throw new RuntimeException ($obj->text);
  }

}

?>
