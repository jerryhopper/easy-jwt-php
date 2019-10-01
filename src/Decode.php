<?php
namespace JerryHopper\EasyJwt;

use JerryHopper\ServiceDiscovery\Discovery;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWKSet;
use Jose\Component\Checker;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\ES384;
use Jose\Component\Signature\Algorithm\ES512;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Algorithm\HS384;
use Jose\Component\Signature\Algorithm\HS512;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\Algorithm\RS384;
use Jose\Component\Signature\Algorithm\RS512;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;


class  Decode {

    /*
     * the payload
     */
    private $payload;

    function __construct( String $token, String $discoveryUrl, $audience=false,$issuer=false )
    {
        // The serializer manager. We only use the JWS Compact Serialization Mode.
        $serializerManager = new JWSSerializerManager([
            new CompactSerializer(),
        ]);


        // initiate the discovery
        $discovery = new Discovery($discoveryUrl);
        // create the jwkset
        //echo "create the jwkset\n\r";
        $jwkSet = JWKSet::createFromKeyData( $discovery->get('jwks_uri'));





        //echo "create the Algomanager\n\r";
        $algorithmManager = $this->createAlgoManagerFromDiscovery($discovery);
        // We instantiate our JWS Verifier.
        $jwsVerifier = new JWSVerifier($algorithmManager);
        //print_r($jwsVerifier);







        $jwt = $serializerManager->unserialize($token);






        $is_valid = $jwsVerifier->verifyWithKeySet($jwt, $jwkSet,0);


        if( !$is_valid ){
            throw new \Exception("Invalid token");
        }
        // Signature is verified.


        //$issuer = $this->issuerCheck($discovery->get('issuer'));

        if($issuer==false){
            $issuer = array($discovery->get('issuer'));
        }elseif( is_string($issuer) ){
            $issuer = array($issuer);
        }elseif(!is_array($issuer)){
            throw new \Exception('claimcheck error: Invalid issuer');
        }


        $this->claimcheck($jwt,$issuer,$audience);

        //echo "decoded payload\n\r";
        $decodePayload = json_decode($jwt->getPayload());

        //print_r($decodePayload);
        // get the audience and issuer from the token..
        //$audience   = $decodePayload->aud;
        //$issuer     = $decodePayload->iss;
        //print_r($decodePayload);

        $this->payload = (array)$decodePayload;
    }
    function issuerCheck($i){

    }

    public function __debugInfo(){
        return $this->payload;
    }

    function __get($name)
    {
        // TODO: Implement __get() method.
        if( isset($this->payload[$name]) ){
            return $this->payload[$name];
        }
        return null;
    }

    private function createAlgoManagerFromDiscovery($discover){
        $algos = array();

        foreach( $discover->get()['id_token_signing_alg_values_supported'] as $item){
            try{
                $item = '\\Jose\\Component\\Signature\\Algorithm\\'.$item;
                $algos[] = new $item();
            }catch(\Exception $e){

            }
        }
        return new AlgorithmManager($algos);
    }

    function claimcheck($jwt,array $issuer,string $audience){
        $claims = json_decode($jwt->getPayload(), true);

        $checks[]=new Checker\IssuedAtChecker();
        $checks[]=new Checker\NotBeforeChecker();
        $checks[]=new Checker\ExpirationTimeChecker();

        if($audience != false){
            $checks[]=new Checker\AudienceChecker($audience);
        }

        if( count($issuer)>0 ){
            $checks[]=new Checker\IssuerChecker( $issuer );
        }

        $claimCheckerManager = new ClaimCheckerManager($checks);
        $claimCheckerManager->check($claims);
    }
}

