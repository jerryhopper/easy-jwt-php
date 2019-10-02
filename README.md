[![Build Status](https://travis-ci.org/jerryhopper/easy-jwt-php.svg?branch=master)](https://travis-ci.org/jerryhopper/easy-jwt-php?branch=master)

# easy-jwt-php
Absurdly simple jwt decoder/verifier using .well-known service discovery oauth/openid spec.

.

# Installation
````
composer require jerryhopper/service-discovery-php
````

.

## Usage
$token = the obtained JWT token.

$discoveryUrl = the location of the openid discovery information. 

(Example: https://fusionauth:9011/.well-known/openid-configuration )





````
use JerryHopper\EasyJwt;

$jwtPayloadData = new Decode($token,$discoveryUrl);
````
The result is either a Exception, or the decoded JWT object.


.


## Advanced usage

$issuer = false; // Issuer check. False or String.

$audience = false; // Audience check.  False or String.


````
use JerryHopper\EasyJwt;

$jwtPayloadData = new Decode($token,$discoveryUrl,$audience,$issuer);
````

.




 
## This library is for  PHP7.2 ++
