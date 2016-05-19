<?php
 
require_once '../include/DbHandler.php';
require '.././libs/Slim/Slim.php';
 
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
 * Creating new sample in db
 * method POST
 * params - name
 * url - /create/$SAMPLE_NAME
 */
$app->post('/create', function() use ($app) {
    
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
 
$app->run();
?>