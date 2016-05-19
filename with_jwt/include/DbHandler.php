<?php
 
/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 */
class DbHandler {
 
    private $conn;
 
    function __construct() {
        require_once dirname(__FILE__) . './DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }
 
    /**
     * Creating new Sample
     * @param String $sample_name : name of the new sample
     * Only Authorised User can do this
     */
    public function createSample($sample_name) {        
        $stmt = $this->conn->prepare("INSERT INTO sample(name) VALUES(?)");
        $stmt->bind_param("s", $sample_name);
        $result = $stmt->execute();
        $stmt->close();
 
        if ($result) {
            // sample row created
            return $this->conn->insert_id;
        } else {
            // sample failed to create
            return NULL;
        }
    }
}
?>