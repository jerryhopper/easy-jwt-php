<?php

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload


use FusionAuth\FusionAuthClient;
use GuzzleHttp\Client;
use JerryHopper\EasyJwt\Decode;

$randomId = 1;//rand(0,100);
//$randomId = rand(0,100);
//
/*

    set FUSIONAUTH_BASEURL=https://fusionauth.devpoc.nl
    set FUSIONAUTH_APIKEY=fusionauth-demoserver-apikey
    set FUSIONAUTH_DISCOVERYURL=https://fusionauth.devpoc.nl/.well-known/openid-configuration

*/
putenv("FUSIONAUTH_BASEURL=https://fusionauth.devpoc.nl");
putenv("FUSIONAUTH_APIKEY=fusionauth-demoserver-apikey");
putenv("FUSIONAUTH_DISCOVERYURL=https://fusionauth.devpoc.nl/.well-known/openid-configuration");



//echo getenv('FUSIONAUTH_BASEURL' );


// Create the client.
$client = new FusionAuthClient(getenv('FUSIONAUTH_APIKEY'), getenv('FUSIONAUTH_BASEURL'));

//
// Create it
//
//$response = $client->createApplication(null, ["application" => ["name" => "PHP Client Application" ]]);
//$applicationId = $response->successResponse->application->id;
$applicationId ="f238413d-cb35-499a-a07b-a12374494874";
$applicationId ="8d76e134-e62f-4f14-944f-84c3a9585537";
//
// Retrieve it
//
$response = $client->retrieveApplication($applicationId);
//print_r($response->successResponse->application);
//$applicationId = $response->application->id;



$clientId       = $response->successResponse->application->oauthConfiguration->clientId;
$clientSecret   = $response->successResponse->application->oauthConfiguration->clientSecret;


//
// Create it
//
/// //$response = $client->createUser(null, ["user" => ["email" => "test".$randomId."@fusionauth.io", "password" => "password", "firstName" => "Jäne"]]);
//$userId = $response->successResponse->user->id;

//
// Login
//
//$response = $client->login(["applicationId"=>$applicationId,"loginId" => "test".$randomId."@fusionauth.io", "password" => "password"]);
#$token = $response->successResponse->token;
//print_r($response->successResponse);


#print_r($response->successResponse->application->oauthConfiguration->clientId);
#print_r($response->successResponse->application->oauthConfiguration->clientSecret);

//
// login via tokenEndpoint.
//
$guzzle = new Client();
$formParams = [
    'client_id'=>$clientId,
    'client_secret'=>$clientSecret,
    'grant_type' => 'password',
    'username'=>'test1@fusionauth.io',
    'password'=>'password'
];
$response = $guzzle->post('https://fusionauth.devpoc.nl/oauth2/token', [
    GuzzleHttp\RequestOptions::FORM_PARAMS => $formParams,
    GuzzleHttp\RequestOptions::DEBUG => false,
    GuzzleHttp\RequestOptions::HEADERS => array("application/x-www-form-urlencoded")
]);
$response =json_decode( $response->getBody()->getContents() );


$token = $response->access_token;
echo "User loggedin \n\r";
//print_r($token);




//die();

$discoveryUrl = getenv('FUSIONAUTH_DISCOVERYURL')."/".$applicationId;
$audience     = false; //'<your audience, string or false>';
$issuer       = false; //'<your issuer, string or array or false>';

try{
    $decoded = new Decode($token,$discoveryUrl,$audience,$issuer); //
}catch(\Exception $e){
    die("Exception! => ".$e->getMessage());
}

echo "\n\rToken verified!\n\r";
print_r($decoded);

// Delete it
//$response = $client->deleteUser($userId);





die();







$client = new FusionAuthClient(getenv('FUSIONAUTH_APIKEY'), getenv('FUSIONAUTH_BASEURL'));




die();

$token        = "<yourtoken, string>";
$discoveryUrl = "https://<your-idp-server>/.well-known/openid-configuration";
$audience     = false; //'<your audience, string or false>';
$issuer       = false; //'<your issuer, string or array or false>';

$token        = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6ImwtM2NRVGUyQnB0bEM0ajBEV2F5V3VwQUJiRSJ9.eyJhdWQiOiIzOGI5NTlkMy1mMGExLTRmZTQtOGM0NC03MDFlNDBiNGIzZDQiLCJleHAiOjE1NjgzNzA4MjAsImlhdCI6MTU2ODM2NzIyMCwiaXNzIjoiYWNjb3VudC50cnVzdG1hc3Rlci5vcmciLCJzdWIiOiI0YjBkZjQyMS0zNGM3LTQ5YjAtOGU2NS1jZjI1MzY3ZDdhYWIiLCJhdXRoZW50aWNhdGlvblR5cGUiOiJQQVNTV09SRCIsImVtYWlsIjoiaG9wcGVyLmplcnJ5QGdtYWlsLmNvbSIsImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJwcmVmZXJyZWRfdXNlcm5hbWUiOiJqZXJyeWhvcHBlciIsImFwcGxpY2F0aW9uSWQiOiIzOGI5NTlkMy1mMGExLTRmZTQtOGM0NC03MDFlNDBiNGIzZDQiLCJyb2xlcyI6W119.aIsJhEROIhLoKE_duKHTXOIJ3KK6iOOKf-5CqJdW6pyg8XUCKahtEViouXdvLGesmPxWNcgaz9dqGRC3l0na9EdAZkQiZYhT5NkzFnkeLFL8bmqgEjGBuYQe-uK4S6vPNRINvM-JeVKf6U7dsXZ1zS-0syEnygGCAGLwFJZ7c5LoKX7pxlRy6R3uwBcdmkDStlpZrcZvmT1Ep-vBpfPOs45LJd6HrVieFZPIexl0OspsvKGG7lK1-m_87YTxiBg3VCVRIUS3DUkohpt9SXi_yukqO6Z92SWU_mdLXZLYfbAck2tIZMRAJmS-GqDi4586i84Awih-zSF4arSo6fJuZw";
$discoveryUrl = "https://account.trustmaster.org/.well-known/openid-configuration";
$audience     = false;//'38b959d3-f0a1-4fe4-8c44-701e40b4b3d4';
$issuer       = false;//'account.trustmaster.org';


try{
    $decoded = new Decode($token,$discoveryUrl,$audience,$issuer); //
}catch(\Exception $e){
    die("Exception! => ".$e->getMessage());
}

print_r($decoded);

