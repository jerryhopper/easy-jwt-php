<?php
namespace JerryHopper\EasyJwt;

use JerryHopper\ServiceDiscovery\Discovery;



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
        $TokenDecoder = new TokenDecoder($token,$discovery);

        // validate token
        $is_valid = $TokenDecoder->validate($audience,$issuer);


        if( !$is_valid ){
            throw new \Exception("Invalid token");
        }
        // Signature is verified.


        $this->payload = (array)$TokenDecoder->getPayload();;
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


