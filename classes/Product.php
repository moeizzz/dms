<?php
class Product {
    private $conn;
    private $table_name = "products";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Function to fetch all products for our dashboard
    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Function to insert a new product into the database
    public function create($name, $category, $primary_rate, $secondary_rate, $stock_level) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, category, primary_rate, secondary_rate, stock_level) 
                  VALUES (:name, :category, :primary_rate, :secondary_rate, :stock_level)";
        
        $stmt = $this->conn->prepare($query);
        
        // We use bindParam to protect against SQL injection
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":category", $category);
        $stmt->bindParam(":primary_rate", $primary_rate);
        $stmt->bindParam(":secondary_rate", $secondary_rate);
        $stmt->bindParam(":stock_level", $stock_level);
        
        return $stmt->execute();
    }
}
?>