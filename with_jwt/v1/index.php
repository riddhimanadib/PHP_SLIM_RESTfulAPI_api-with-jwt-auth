<?php
 
require_once '../include/DbHandler.php';
require '.././libs/Slim/Slim.php';
require_once '.././libs/Firebase/php_jwt/JWT.php';
 
\Slim\Slim::registerAutoloader();
 
$app = new \Slim\Slim();

$is_valid_user = false;
 
/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    
    // Http response code
    $app->status($status_code);
 
    // setting response content type to json
    $app->contentType('application/json');
 
    echo json_encode($response);
}
        
/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
 
    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        // get the api key
        $jwt = $headers['Authorization'];
        
        // decoding the jwt key
        include_once '../include/Config.php';
        $secret_key_encoded = JWT_KEY;
        $secretKey = base64_decode($secret_key_encoded);
        
        $decoded = \Firebase\JWT\JWT::decode(
               $jwt,      //Data to be encoded in the JWT
               $secretKey, // The signing key
               array('HS256')    // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
               );
        
        // TODO : IMPLEMENT THE JWT::verify FUNCTION
        
        // validating api key, successful only if jwt is decoded correctly and verified
        // Alternatively, we can use the JWT::verify function
        if (strcmp($decoded->iss, "Sample API Server") != 0) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(401, $response);
            $app->stop();
        } else {
            // make the user valid
            global $is_valid_user;
            $is_valid_user = true;
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "JWT is misssing";
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Create token for client
 * method POST
 * url - /auth/token
 */
$app->post('/auth/token', function() use ($app) {
            
            // check credential validity here, using Database, like login, or something else
            $credentialsAreValid = true;
            
            if ($credentialsAreValid) {

                include_once '../include/Config.php';
                
                $tokenId    = base64_encode(mcrypt_create_iv(32));
                $issuedAt   = time();
                $notBefore  = $issuedAt + 10;             //Adding 10 seconds
                $expire     = $notBefore + 60;            // Adding 60 seconds
                $serverName = ISSUER;                     // Retrieve the server name from config file

                /*
                 * Create the token as an array
                 */
                $data = [
                    'iat'  => $issuedAt,         // Issued at: time when the token was generated
                    'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
                    'iss'  => $serverName,       // Issuer
                    'nbf'  => $notBefore,        // Not before
                    'exp'  => $expire,           // Expire
                    'data' => [                  // Data related to the signer user
                        'samplekey'   => 'samplevalue', // just to show, we can send data too
                    ]
                ];


                /*
                * Extract the key, which is coming from the config file. 
                * 
                * Best suggestion is the key to be a binary string and 
                * store it in encoded in a config file. 
                *
                * Can be generated with base64_encode(openssl_random_pseudo_bytes(64));
                *
                * keep it secure! You'll need the exact key to verify the 
                * token later.
                */
                $secret_key_encoded = JWT_KEY;
                $secretKey = base64_decode($secret_key_encoded);

                /*
                * Encode the array to a JWT string.
                * Second parameter is the key to encode the token.
                * 
                * The output string can be validated at http://jwt.io/
                */
                $jwt = \Firebase\JWT\JWT::encode(
                   $data,      //Data to be encoded in the JWT
                   $secretKey, // The signing key
                   'HS256'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
                   );

                $unencodedArray = ['jwt' => $jwt];

                echoRespnse(200, $unencodedArray);
            } else {
                
                echoRespnse(404, ['success' => '0', 'message' => 'Unknown error occurred']);
                
            }
        });

/**
 * Creating new sample in db
 * THIS OPERATION NEEDS AUTHENTICATION
 * method POST
 * params - name
 * Authorisation - [JWT]
 * url - /create/$SAMPLE_NAME
 */
$app->post('/create', 'authenticate', function() use ($app) {
    global $is_valid_user;

    if ($is_valid_user) {
        $response = array();
        $name = $app->request->post('name');


        $db = new DbHandler();
        // creating new task
        $id = $db->createSample($name);

        if ($id != NULL) {
            $response["error"] = false;
            $response["message"] = "Sample created successfully";
            $response["sample_id"] = $id;
        } else {
            $response["error"] = true;
            $response["message"] = "Failed to create Sample. Please try again";
        }
        echoRespnse(201, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "Failed to create Sample. Please try again";

        echoRespnse(401, $response);
    }
});
        
 /**
 * just a method to decode and verify an existing token
 * method POST
 * params - jwt
 * url - /auth/token/decode
 */
$app->post('/auth/token/decode', function() use ($app) {
    
            $jwt = $app->request->post('jwt');
            
            include_once '../include/Config.php';
            $secret_key_encoded = JWT_KEY;
            $secretKey = base64_decode($secret_key_encoded);
            $decoded = \Firebase\JWT\JWT::decode(
               $jwt,      //Data to be encoded in the JWT
               $secretKey, // The signing key
               array('HS256')    // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
               );
                
            echoRespnse(200, (array) $decoded);
        });
		
$app->run();
?>