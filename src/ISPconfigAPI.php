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
    private string $url;
    private string $user;
    private string $pass;

    private string $session;
    private GuzzleHttp\Client $httpClient;

    public function __construct(array $config)
    {
        $this->url = $config['url']."/remote/json.php";
        $this->user = $config['user'];
        $this->pass = $config['pass'];

        if (!isset($config['verifySSL']) && empty($config['verifySSL'])) {$config['verifySSL'] = true;}
        $this->httpClient = new GuzzleHttp\Client(['verify' => $config['verifySSL']]);
        $this->login();
    }

    public function call($method, $data = array())
    {
        $data = array_merge(['session_id' => $this->session], $data);
        $res = $this->httpClient->request('PUT', $this->url . '?'.$method, [
            'json' => $data
        ]);
        $res = json_decode($res->getBody(), true);

        return $res;
    }

    public function login()
    {
        $res = $this->httpClient->request('PUT', $this->url . '?login', [
            'json' => ['username' => $this->user, 'password' => $this->pass]
        ]);

        $res = json_decode($res->getBody(), true);

        if ($res["code"] == "ok") {
            $this->session = $res["response"];
        } elseif ($res["code"] == "remote_fault") {
            throw new \Exception($res["message"]);
        }
    }

    public function logout()
    {
        $res = $this->httpClient->request('PUT', $this->url . '?logout', [
            'json' => ['session_id' => $this->session]
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
