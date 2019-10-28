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

        // initiate the discovery
        $discovery = new Discovery($discoveryUrl);


        // INIT  decoder
        $compatibleDecoder = new php72compat($token,$discovery);

        // validate token
        $is_valid = $compatibleDecoder->validate($audience,$issuer);


        if( !$is_valid ){
            throw new \Exception("Invalid token");
        }
        // Signature is verified.


        $this->payload = (array)$compatibleDecoder->getPayload();;
    }
    function issuerCheck($i){

    }

    public function __debugInfo(){
        return $this->payload;
    }

    function __get($name)
    {
        if( isset($this->payload[$name]) ){
            return $this->payload[$name];
        }
        return null;
    }




}




interface decoderInterface {

    public function validate();
    public function getPayload();


}
