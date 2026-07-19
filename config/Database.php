<?php
class Database {
    // These are the default credentials for XAMPP
    private $host = "localhost";
    private $db_name = "dms";
    private $username = "root";
    private $password = "";
    
    public $conn;

    // This function creates the actual connection
    public function getConnection() {
        $this->conn = null;

        try {
            // Attempt to connect using PDO
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            
            // Tell PDO to throw an exception (error) if something goes wrong
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch(PDOException $exception) {
            // If the connection fails, print the exact error message
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>