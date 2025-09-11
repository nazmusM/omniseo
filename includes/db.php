<?php
require_once 'config.php';

class Database {
    private $connection;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset("utf8mb4");
        } catch (Exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function prepare($query) {
        return $this->connection->prepare($query);
    }
    
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    public function begin_transaction(){
        if($this->connection){
            $this->connection->begin_transaction();
        }
    }

    public function commit(){
        if($this->connection){
            $this->connection->commit();
        }
    }

     public function rollback(){
        if($this->connection){
            $this->connection->rollback();
        }
    }
}

// Global database instance
$db = new Database();
?>
