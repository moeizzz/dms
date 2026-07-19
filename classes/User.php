<?php
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // THE FIX: Smart Login that checks Hashes
    public function login($username, $password) {
        $query = "SELECT id, username, password, role FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if the password matches the hash, OR if it's an old plain-text test account
            if(password_verify($password, $row['password']) || $password === $row['password']) {
                return $row;
            }
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT id, username, role FROM users ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create($username, $password, $role) {
        try {
            $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
            $stmt = $this->conn->prepare($query);
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":password", $hashed_password);
            $stmt->bindParam(":role", $role);
            
            if($stmt->execute()) return true;
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // NEW: Fire an employee
    public function delete($id) {
        // Security: Prevent the deletion of any admin accounts!
        $query = "DELETE FROM users WHERE id = :id AND role != 'admin'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        if($stmt->execute() && $stmt->rowCount() > 0) return true;
        return false;
    }
}
?>