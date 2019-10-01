<?php
namespace JerryHopper\EasyJwt;

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



class php72compat implements decoderInterface{

    private $token;
    private $discoverydata;
    private $jwt;
    private $jwkSet;
    private $jwsVerifier;




    function __construct($token, $discovery){
        $this->token = $token;
        $this->discoverydata = $discovery;
        $this->prepare();
    }

    public function getPayload(){
        return json_decode($this->jwt->getPayload());
    }

    public function validate( $audience=false,$issuer=false ){

        if($issuer==false){
            $issuer = array($this->discoverydata->get('issuer'));
        }elseif( is_string($issuer) ){
            $issuer = array($issuer);
        }elseif(!is_array($issuer)){
            throw new \Exception('claimcheck error: Invalid issuer');
        }

        $this->claimcheck($this->jwt,$issuer,$audience);



        // returns true or exception.
        return $this->jwsVerifier->verifyWithKeySet($this->jwt, $this->jwkSet,0);
    }



    private function prepare(){

        // The serializer manager. We only use the JWS Compact Serialization Mode.
        $serializerManager = new JWSSerializerManager([
            new CompactSerializer(),
        ]);

        $this->jwt = $serializerManager->unserialize($this->token);


        // create the jwkset
        //echo "create the jwkset\n\r";
        $this->jwkSet = JWKSet::createFromKeyData( $this->discoverydata->get('jwks_uri'));

        //echo "create the Algomanager\n\r";
        $algorithmManager = $this->createAlgoManagerFromDiscovery($this->discoverydata);

        // We instantiate our JWS Verifier.
        $this->jwsVerifier = new JWSVerifier($algorithmManager);


    }

    private function createAlgoManagerFromDiscovery($discover){
        $algos = array();

        foreach( $discover->get('id_token_signing_alg_values_supported') as $item){
            try{
                $item = '\\Jose\\Component\\Signature\\Algorithm\\'.$item;
                $algos[] = new $item();
            }catch(\Exception $e){

            }
        }
        return new AlgorithmManager($algos);
    }

    private function claimcheck($jwt,array $issuer,string $audience){
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