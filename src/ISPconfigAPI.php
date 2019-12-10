<?php
/*
 * (c) Adam Biciste <adam@freshost.cz>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace bicisteadm\ISPconfigAPI;

use GuzzleHttp;

class ISPconfigAPI
{
    private $url;
    private $user;
    private $pass;

    /**
     * ISPconfigAPI constructor.
     * @param $user
     * @param $pass
     * @param $url
     */
    public function __construct($user, $pass, $url)
    {
        $this->url = $url."/remote/json.php";
        $this->user = $user;
        $this->pass = $pass;
    }

    /**
     * @param $method
     * @param array $data
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function call($method, $data = array())
    {
        $login = $this->login();
        if ($login["code"] != "ok")
        {
            return $login;
        }
        $session_id = $login["response"];

        $data = array_merge(['session_id' => $session_id], $data);
        $client = new GuzzleHttp\Client();
        $res = $client->request('PUT', $this->url . '?'.$method, [
            'json' => $data
        ]);

        $this->logout($session_id);

        $res = json_decode($res->getBody(), true);

        if ($res["code"] != "ok")
        {
            return $res;
        }

        return $res["response"];
    }

    /**
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    private function login()
    {
        $client = new GuzzleHttp\Client();
        $res = $client->request('PUT', $this->url . '?login', [
            'json' => ['username' => $this->user, 'password' => $this->pass]
        ]);

        $res = json_decode($res->getBody(), true);

        return $res;
    }

    /**
     * @param $session_id
     * @return bool
     */
    private function logout($session_id)
    {
        $client = new GuzzleHttp\Client();
        $res = $client->request('PUT', $this->url . '?logout', [
            'json' => ['session_id' => $session_id]
        ]);

        $res = json_decode($res->getBody(), true);

        if ($res["code"] == "ok")
        {
            return true;
        } else {
            return false;
        }
    }

}
