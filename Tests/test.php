<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload



use JerryHopper\EasyJwt\Decode;


$token        = "<yourtoken, string>";
$discoveryUrl = "https://<your-idp-server>/.well-known/openid-configuration";
$audience     = false; //'<your audience, string or false>';
$issuer       = false; //'<your issuer, string or array or false>';



try{
    $decoded = new Decode($token,$discoveryUrl,$audience,$issuer); //
}catch(\Exception $e){
    die("Exception! => ".$e->getMessage());
}

print_r($decoded);

