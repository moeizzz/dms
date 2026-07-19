<?php
class Supplier {
    private $conn;
    private $table_name = "suppliers";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Fetch all suppliers for our directory and dropdown menus
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Insert a new vendor/supplier
    public function create($name, $contact_person, $phone, $address) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, contact_person, phone, address) 
                  VALUES (:name, :contact_person, :phone, :address)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":contact_person", $contact_person);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":address", $address);
        
        return $stmt->execute();
    }
}
?>