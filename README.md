# easy-jwt-php
Absurdly simple jwt decoder/verifier using .well-known service discovery oauth/openid spec.

````
$issuer = false; // Issuer check. False or String.

$audience = false; // Audience check.  False or String.

$discoveryUrl='https://fusionauth:9011/.well-known/openid-configuration';

$token='<JWT-TOKEN>'

$jwtPayloadData = new Decode( $token,$discoveryUrl,$audience,$issuer );

````
 
## This library is for  PHP7.2 ++