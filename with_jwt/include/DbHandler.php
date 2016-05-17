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
 
    /* ------------- `sample` table method ------------------ */
 
    /**
     * Creating new Sample
     * @param String $sample_name : name of the new sample
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




















    /**
     * Fetching a single Sample
     * @param String $task_id id of the task
     */
//    public function getTask($task_id, $user_id) {
//        $stmt = $this->conn->prepare("SELECT t.id, t.task, t.status, t.created_at from tasks t, user_tasks ut WHERE t.id = ? AND ut.task_id = t.id AND ut.user_id = ?");
//        $stmt->bind_param("ii", $task_id, $user_id);
//        if ($stmt->execute()) {
//            $task = $stmt->get_result()->fetch_assoc();
//            $stmt->close();
//            return $task;
//        } else {
//            return NULL;
//        }
//    }
 
    /**
     * Fetching all samples
     * @param String $user_id id of the user
     */
//    public function getAllUserTasks($user_id) {
//        $stmt = $this->conn->prepare("SELECT t.* FROM tasks t, user_tasks ut WHERE t.id = ut.task_id AND ut.user_id = ?");
//        $stmt->bind_param("i", $user_id);
//        $stmt->execute();
//        $tasks = $stmt->get_result();
//        $stmt->close();
//        return $tasks;
//    }
 
    /**
     * Updating a sample
     * @param String $task_id id of the task
     * @param String $task task text
     * @param String $status task status
     */
//    public function updateTask($user_id, $task_id, $task, $status) {
//        $stmt = $this->conn->prepare("UPDATE tasks t, user_tasks ut set t.task = ?, t.status = ? WHERE t.id = ? AND t.id = ut.task_id AND ut.user_id = ?");
//        $stmt->bind_param("siii", $task, $status, $task_id, $user_id);
//        $stmt->execute();
//        $num_affected_rows = $stmt->affected_rows;
//        $stmt->close();
//        return $num_affected_rows > 0;
//    }
 
    /**
     * Deleting a sample
     * @param String $task_id id of the task to delete
     */
//    public function deleteTask($user_id, $task_id) {
//        $stmt = $this->conn->prepare("DELETE t FROM tasks t, user_tasks ut WHERE t.id = ? AND ut.task_id = t.id AND ut.user_id = ?");
//        $stmt->bind_param("ii", $task_id, $user_id);
//        $stmt->execute();
//        $num_affected_rows = $stmt->affected_rows;
//        $stmt->close();
//        return $num_affected_rows > 0;
//    }
//}
 
//?>