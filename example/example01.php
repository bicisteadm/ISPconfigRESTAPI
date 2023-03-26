<?php

require '../vendor/autoload.php';

use bicisteadm\ISPconfigAPI\ISPconfigAPI;

include "login.php";
$call = new ISPconfigAPI(['user' => $user, 'pass' => $pass, 'url' => $url]);

$client = $call->call("client_get", ["client_id" => 1]);

print_r($client);
