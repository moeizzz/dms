<?php
class Report {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get the total revenue amount between two dates
    public function getSalesTotal($start_date, $end_date) {
        // We append the times to make sure it captures the entire end day perfectly
        $start = $start_date . " 00:00:00";
        $end = $end_date . " 23:59:59";

        $query = "SELECT SUM(total_amount) as total FROM orders WHERE order_date BETWEEN :start AND :end";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":start", $start);
        $stmt->bindParam(":end", $end);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'] ? $row['total'] : 0;
    }

    // Get the actual list of orders between two dates
    public function getSalesReport($start_date, $end_date) {
        $start = $start_date . " 00:00:00";
        $end = $end_date . " 23:59:59";

        $query = "SELECT orders.id, orders.total_amount, orders.order_date, customers.name as customer_name, users.username as salesman 
                  FROM orders 
                  LEFT JOIN customers ON orders.customer_id = customers.id
                  LEFT JOIN users ON orders.user_id = users.id
                  WHERE orders.order_date BETWEEN :start AND :end
                  ORDER BY orders.order_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":start", $start);
        $stmt->bindParam(":end", $end);
        $stmt->execute();
        
        return $stmt;
    }
}
?>