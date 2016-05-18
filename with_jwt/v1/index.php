<?php
 
require_once '../include/DbHandler.php';
require '.././libs/Slim/Slim.php';
require_once '.././libs/Firebase/php_jwt/JWT.php';
 
\Slim\Slim::registerAutoloader();
 
$app = new \Slim\Slim();
 
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
 * Create token for client
 * method POST
 * url - /auth/token
 */
$app->post('/auth/token', function() use ($app) {
            
            // check credential validity here, like login, or something else
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
                
            }
            
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
            //$secretKey = JWT_KEY;
            
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
        });
        
 /**
 * Create token for client
 * method POST
 * url - /auth/token
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

/**
 * Creating new sample in db
 * method POST
 * params - name
 * url - /create/$SAMPLE_NAME
 */
$app->post('/create', function() {
    
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
        });
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
//        
//
///**
// * User Registration
// * url - /register
// * method - POST
// * params - name, email, password
// */
//$app->post('/create', function() use ($app) {
//            // check for required params
//            verifyRequiredParams(array('name', 'email', 'password'));
// 
//            $response = array();
// 
//            // reading post params
//            $name = $app->request->post('name');
//            $email = $app->request->post('email');
//            $password = $app->request->post('password');
// 
//            // validating email address
//            validateEmail($email);
// 
//            $db = new DbHandler();
//            $res = $db->createUser($name, $email, $password);
// 
//            if ($res == USER_CREATED_SUCCESSFULLY) {
//                $response["error"] = false;
//                $response["message"] = "You are successfully registered";
//                echoRespnse(201, $response);
//            } else if ($res == USER_CREATE_FAILED) {
//                $response["error"] = true;
//                $response["message"] = "Oops! An error occurred while registereing";
//                echoRespnse(200, $response);
//            } else if ($res == USER_ALREADY_EXISTED) {
//                $response["error"] = true;
//                $response["message"] = "Sorry, this email already existed";
//                echoRespnse(200, $response);
//            }
//        });
//		
///**
// * User Login
// * url - /login
// * method - POST
// * params - email, password
// */
//$app->post('/login', function() use ($app) {
//            // check for required params
//            verifyRequiredParams(array('email', 'password'));
// 
//            // reading post params
//            $email = $app->request()->post('email');
//            $password = $app->request()->post('password');
//            $response = array();
// 
//            $db = new DbHandler();
//            // check for correct email and password
//            if ($db->checkLogin($email, $password)) {
//                // get the user by email
//                $user = $db->getUserByEmail($email);
// 
//                if ($user != NULL) {
//                    $response["error"] = false;
//                    $response['name'] = $user['name'];
//                    $response['email'] = $user['email'];
//                    $response['apiKey'] = $user['api_key'];
//                    $response['createdAt'] = $user['created_at'];
//                } else {
//                    // unknown error occurred
//                    $response['error'] = true;
//                    $response['message'] = "An error occurred. Please try again";
//                }
//            } else {
//                // user credentials are wrong
//                $response['error'] = true;
//                $response['message'] = 'Login failed. Incorrect credentials';
//            }
// 
//            echoRespnse(200, $response);
//        });
//
//
//		
///**
// * Listing all tasks of particual user
// * method GET
// * url /tasks          
// */
//$app->get('/tasks', 'authenticate', function() {
//            global $user_id;
//            $response = array();
//            $db = new DbHandler();
// 
//            // fetching all user tasks
//            $result = $db->getAllUserTasks($user_id);
// 
//            $response["error"] = false;
//            $response["tasks"] = array();
// 
//            // looping through result and preparing tasks array
//            while ($task = $result->fetch_assoc()) {
//                $tmp = array();
//                $tmp["id"] = $task["id"];
//                $tmp["task"] = $task["task"];
//                $tmp["status"] = $task["status"];
//                $tmp["createdAt"] = $task["created_at"];
//                array_push($response["tasks"], $tmp);
//            }
// 
//            echoRespnse(200, $response);
//        });
//		
///**
// * Listing single task of particual user
// * method GET
// * url /tasks/:id
// * Will return 404 if the task doesn't belongs to user
// */
//$app->get('/tasks/:id', 'authenticate', function($task_id) {
//            global $user_id;
//            $response = array();
//            $db = new DbHandler();
// 
//            // fetch task
//            $result = $db->getTask($task_id, $user_id);
// 
//            if ($result != NULL) {
//                $response["error"] = false;
//                $response["id"] = $result["id"];
//                $response["task"] = $result["task"];
//                $response["status"] = $result["status"];
//                $response["createdAt"] = $result["created_at"];
//                echoRespnse(200, $response);
//            } else {
//                $response["error"] = true;
//                $response["message"] = "The requested resource doesn't exists";
//                echoRespnse(404, $response);
//            }
//        });
//		
///**
// * Updating existing task
// * method PUT
// * params task, status
// * url - /tasks/:id
// */
//$app->put('/tasks/:id', 'authenticate', function($task_id) use($app) {
//            // check for required params
//            verifyRequiredParams(array('task', 'status'));
// 
//            global $user_id;            
//            $task = $app->request->put('task');
//            $status = $app->request->put('status');
// 
//            $db = new DbHandler();
//            $response = array();
// 
//            // updating task
//            $result = $db->updateTask($user_id, $task_id, $task, $status);
//            if ($result) {
//                // task updated successfully
//                $response["error"] = false;
//                $response["message"] = "Task updated successfully";
//            } else {
//                // task failed to update
//                $response["error"] = true;
//                $response["message"] = "Task failed to update. Please try again!";
//            }
//            echoRespnse(200, $response);
//        });
//		
///**
// * Deleting task. Users can delete only their tasks
// * method DELETE
// * url /tasks
// */
//$app->delete('/tasks/:id', 'authenticate', function($task_id) use($app) {
//            global $user_id;
// 
//            $db = new DbHandler();
//            $response = array();
//            $result = $db->deleteTask($user_id, $task_id);
//            if ($result) {
//                // task deleted successfully
//                $response["error"] = false;
//                $response["message"] = "Task deleted succesfully";
//            } else {
//                // task failed to delete
//                $response["error"] = true;
//                $response["message"] = "Task failed to delete. Please try again!";
//            }
//            echoRespnse(200, $response);
//        });
		
$app->run();
?>