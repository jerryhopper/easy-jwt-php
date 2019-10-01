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
                        "timeToLiveInSeconds"=>3
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



    }

    public function test_token()
    {
        $issuer = false;
        $audience = false;

        $decoded = new Decode($this->token,$this->discoveryUrl,$audience,$issuer); //

        //print_r($decoded);
        //$this->assertTrue(true);
    }
    public function test_expiredtoken()
    {
        $this->expectException(\Jose\Component\Checker\InvalidClaimException::class);
        sleep(6);
        $issuer = false;
        $audience = false;

        $decoded = new Decode($this->token,$this->discoveryUrl,$audience,$issuer); //

        //print_r($decoded);
        $this->assertTrue(true);
    }



    public function tearDown()
    {
        // Remove the created application.
        //if($this->applicationId==false){
            $this->fusionAuthClient->deleteApplication($this->applicationId);
        //}
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