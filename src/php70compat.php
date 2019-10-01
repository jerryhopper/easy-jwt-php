<?php
namespace JerryHopper\EasyJwt;

class php70compat implements decoderInterface{

    private $token;
    private $discoverydata;

    function __construct($token, $discovery){
        $this->token = $token;
        $this->discoverydata = $discovery

    }
    /*
     * returns true/false or raises exception.
     */
    public function validate($audience=false,$issuer=false){

    }
    /*
     * returns object or raises exception.
     */
    public function getPayload(){

    }

}