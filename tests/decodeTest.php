<?php
namespace fusionauth;


use FusionAuth\FusionAuthClient;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

use JerryHopper\EasyJwt\Decode;
use JerryHopper\ServiceDiscovery\Discovery;
use PHPUnit\Framework\TestCase;

/**
 * @covers FusionAuthClient
 */
final class decodeTest extends TestCase
{
    private $sessionIdentifier;

    private $applicationId = false; //'8d76e134-e62f-4f14-944f-84c3a9585537';//false;// false=create application   string=use applicationid
    private $applicationName = 'easy-jwt-php';
    private $discoveryUrl;

    private $discoveryData;


    public $clientId;
    public $clientSecret;
    public $token;
    /**
     * @var FusionAuthClient
     */
    private $fusionAuthClient;

    private $userId;

    public function setUp()
    {
        // Set some unique identifiers.
        $this->sessionIdentifier = date("dmY",time()).rand(0,100);
        $this->applicationName = $this->applicationName.'-'.$this->sessionIdentifier;

        // Create the fusionAuthclient.
        $this->fusionAuthClient = new FusionAuthClient(getenv('FUSIONAUTH_APIKEY'), getenv('FUSIONAUTH_BASEURL'));
        $this->discoveryUrl = getenv('FUSIONAUTH_DISCOVERYURL'); //'https://fusionauth.devpoc.nl/.well-known/openid-configuration';


        $this->setup_setupapplication();
    }
    public function setup_setupapplication()
    {
        //
        // Create Application
        //
        if($this->applicationId==false){
            $response = $this->fusionAuthClient->createApplication(null, [
                "application" => [
                    "name" => $this->applicationName,
                    "oauthConfiguration"=> [
                        "enabledGrants"=>["password"]
                    ],
                    "jwtConfiguration"=>[
                        "enabled"=> true,
                        "refreshTokenTimeToLiveInMinutes"=>100,
                        "timeToLiveInSeconds"=>100
                    ]
                ]
            ]);
            $this->handleResponse($response);
            $this->applicationId = $response->successResponse->application->id;
        }
        //
        // Retrieve it
        //
        $response = $this->fusionAuthClient->retrieveApplication($this->applicationId);
        $this->handleResponse($response);
        //$applicationId = $response->application->id;


        $this->clientId = $response->successResponse->application->oauthConfiguration->clientId;
        $this->clientSecret = $response->successResponse->application->oauthConfiguration->clientSecret;
        //echo "\n*************************************************\r\n".$this->clientId."\r\n**********************************";


        $this->discoveryData = new Discovery($this->discoveryUrl);



        $this->setup_createuser();
    }
    public function setup_createuser()
    {
        //
        // Create user
        //
        $response = $this->fusionAuthClient->createUser(null, ["user" => [
            "email" => "unittest" . $this->sessionIdentifier . "@fusionauth.localdomain",
            "password" => "password",
            "firstName" => "unittest"
        ]]);
        $this->handleResponse($response);
        $this->userId = $response->successResponse->user->id;
        $this->setup_loginuser();
    }


    function setup_loginuser(){
        // login via tokenEndpoint.
        $guzzle = new Client();
        $formParams = [
            'grant_type' => 'password',
            'client_id'=> $this->clientId,
            'client_secret'=>$this->clientSecret,
            'username'=>"unittest" . $this->sessionIdentifier . "@fusionauth.localdomain",
            'password'=>'password',
        ];

        $response = $guzzle->post($this->discoveryData->get('token_endpoint'), [
            \GuzzleHttp\RequestOptions::FORM_PARAMS => $formParams,
            \GuzzleHttp\RequestOptions::DEBUG => false,
            \GuzzleHttp\RequestOptions::HEADERS => array("Content-Type"=>"application/x-www-form-urlencoded")
        ]);
        $response =json_decode( $response->getBody()->getContents() );


        $token = $response->access_token;
        #echo "User loggedin \n\r";
        #print_r($token);
        $this->token = $token;



        /*

                $randomId = rand(0,100);
                // Create it
                $response = $this->client->createUser(null, ["user" => ["email" => "test".$randomId."@fusionauth.io", "password" => "password", "firstName" => "Jäne"]]);
                $this->handleResponse($response);
                $this->userId = $response->successResponse->user->id;

                // Retrieve it
                $response = $this->client->retrieveUser($this->userId);
                $this->handleResponse($response);
                $this->assertEquals($response->successResponse->user->email, "test".$randomId."@fusionauth.io");

                // Login
                $response = $this->client->login(["loginId" => "test".$randomId."@fusionauth.io", "password" => "password"]);
                $this->handleResponse($response);

                $this->token = $response->successResponse->token;

                // Delete it
                $response = $this->client->deleteUser($this->userId);
                $this->handleResponse($response);

                // Retrieve it again
                $response = $this->client->retrieveUser($this->userId);
                $this->assertEquals($response->status, 404);


            }
        */
    }

    public function test_token()
    {
        $issuer = false;
        $audience = false;

        $decoded = new Decode($this->token,$this->discoveryUrl,$audience,$issuer); //

        //print_r($decoded);
        $this->assertTrue(is_object($decoded));
    }


/*
    public function test_applications()
    {
        $randomId = rand(0,100);
        // Create it
        $response = $this->client->createApplication(null, ["application" => ["name" => "PHP Client Application".$randomId ]]);
        $this->handleResponse($response);
        $this->applicationId = $response->successResponse->application->id;

        // Retrieve it
        $response = $this->client->retrieveApplication($this->applicationId);
        $this->handleResponse($response);
        $this->assertEquals($response->successResponse->application->name, "PHP Client Application".$randomId );

        // Update it
        $response = $this->client->updateApplication($this->applicationId, [ "application" => ["name" => "PHP Client Application Updated".$randomId]]);
        $this->handleResponse($response);
        $this->assertEquals($response->successResponse->application->name, "PHP Client Application Updated".$randomId);

        // Retrieve it again
        $response = $this->client->retrieveApplication($this->applicationId);
        $this->handleResponse($response);
        $this->assertEquals($response->successResponse->application->name, "PHP Client Application Updated".$randomId);

        // Deactivate it
        $response = $this->client->deactivateApplication($this->applicationId);
        $this->handleResponse($response);

        // Retrieve it again
        $response = $this->client->retrieveApplication($this->applicationId);
        $this->handleResponse($response);
        $this->assertFalse($response->successResponse->application->active);

        // Retrieve inactive
        $response = $this->client->retrieveInactiveApplications();
        $this->assertEquals($response->successResponse->applications[0]->name, "PHP Client Application Updated".$randomId);

        // Reactivate it
        $response = $this->client->reactivateApplication($this->applicationId);
        $this->handleResponse($response);

        // Retrieve it again
        $response = $this->client->retrieveApplication($this->applicationId);
        $this->handleResponse($response);
        $this->assertEquals($response->successResponse->application->name, "PHP Client Application Updated".$randomId);
        $this->assertTrue($response->successResponse->application->active);

        // Delete it
        $response = $this->client->deleteApplication($this->applicationId);
        $this->handleResponse($response);

        // Retrieve it again
        $response = $this->client->retrieveApplication($this->applicationId);
        $this->assertEquals($response->status, 404);

        // Retrieve inactive
        $response = $this->client->retrieveInactiveApplications();
        $this->assertFalse(isset($response->successResponse->applications));
    }
*/


    public function tearDown()
    {
        // Remove the created application.
        if($this->applicationId==false){
            $this->fusionAuthClient->deleteApplication($this->applicationId);
        }
        //
        // Remove the created user.
        $this->fusionAuthClient->deleteUser($this->userId);
    }
/*


    public function test_logout() {
        // Without parameter
        $response = $this->client->logout(true);
        $this->handleResponse($response);

        // With NULL
        $response = $this->client->logout(true, NULL);
        $this->handleResponse($response);

        // With bogus token
        $response = $this->client->logout(false, "token");
        $this->handleResponse($response);
    }
*/
    /**
     * @param $response ClientResponse
     */
    private function handleResponse($response)
    {
        if (!$response->wasSuccessful()) {
            print "Status: " . $response->status . "\n";
            print json_encode($response->errorResponse, JSON_PRETTY_PRINT);
        }

        $this->assertTrue($response->wasSuccessful());
    }
}