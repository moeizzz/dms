<?php
class Customer {
    private $conn;
    private $table_name = "customers";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Fetch all customers to display in the CRM
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Insert a new retailer/customer
    public function create($name, $phone, $address, $credit_limit) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, phone, address, credit_limit) 
                  VALUES (:name, :phone, :address, :credit_limit)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":credit_limit", $credit_limit);
        
        return $stmt->execute();
    }
}
?>