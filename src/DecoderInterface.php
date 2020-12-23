<?php
namespace JerryHopper\EasyJwt;

interface DecoderInterface {

    public function validate();
    public function getPayload();


}
