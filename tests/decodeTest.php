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
        $this->sessionIdentifier = time().rand(10,100);
        $this->applicationName = $this->applicationName.'-'.$this->sessionIdentifier;

        // Create the fusionAuthclient.
        $this->fusionAuthClient = new FusionAuthClient(getenv('FUSIONAUTH_APIKEY'), getenv('FUSIONAUTH_BASEURL'));
        $this->discoveryUrl = getenv('FUSIONAUTH_DISCOVERYURL'); //'https://fusionauth.devpoc.nl/.well-known/openid-configuration';


        $issuer = getenv('FUSIONAUTH_CHECK_ISS');
        if( $issuer==="false"){
            $this->issuer = false;
        }else{
            $this->issuer = $issuer;
        }

        $audience = getenv('FUSIONAUTH_CHECK_AUD');
        if( $audience==="false"){
            $this->audience = false;
        }else{
            $this->audience = $audience;
        }




        $this->setup_setupapplication();
    }
    public function setup_setupapplication()
    {
        //fwrite(STDOUT, __METHOD__ . "\n");
        //
        // Create Application
        //
        if($this->applicationId===false){
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

            //echo $response;
            $this->handleResponse($response);

            $this->applicationId = $response->successResponse->application->id;
        }


        //die($this->applicationId);
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
        //fwrite(STDOUT, __METHOD__ . "\n");
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
        //fwrite(STDOUT, __METHOD__ . "\n");

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

        //echo ">>>".getenv('FUSIONAUTH_APIKEY');

        //fwrite(STDOUT, __METHOD__ . "\n");
        //fwrite(STDOUT, json_encode([$this->issuer]) . "\n");
        //fwrite(STDOUT, json_encode([$this->audience]) . "\n");
        //var_dump("----------------------");
        //var_dump($this->token);
        sleep(1);
        $decoded = new Decode($this->token,$this->discoveryUrl,false,false); //
        //var_dump($decoded);
        $this->assertTrue(true);
        //print_r($this->audience);
        //$this->assertTrue(true);
    }

    public function test_token_with_bad_audience()
    {
        sleep(1);
        $this->expectException(\Jose\Component\Checker\InvalidClaimException::class);
        $decoded = new Decode($this->token,$this->discoveryUrl,"faulty",false); //
        //$this->assertTrue(true);
    }

    public function test_token_with_manual_issuer()
    {
        sleep(1);
        $decoded = new Decode($this->token,$this->discoveryUrl,$this->audience,"fusionauth.devpoc.nl"); //
        $this->assertTrue(true);
    }

    public function test_token_with_bad_issuer()
    {
        $this->expectException(\Jose\Component\Checker\InvalidClaimException::class);
        $decoded = new Decode($this->token,$this->discoveryUrl,$this->audience,"faulty"); //
        $this->assertTrue(true);
        //$this->assert
    }

    public function test_expiredtoken()
    {
        $this->expectException(\Jose\Component\Checker\InvalidClaimException::class);
        sleep(6);
        $issuer = false;
        $audience = false;

        $decoded = new Decode($this->token,$this->discoveryUrl,$this->audience,$this->issuer); //

        //print_r($decoded);
        //$this->assertTrue(true);
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
