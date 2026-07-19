<?php
class DashboardStats {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // THE FIX: Context-Aware Revenue
    public function getTotalSales($user_id, $role) {
        if ($role === 'admin') {
            $query = "SELECT SUM(total_amount) as revenue FROM orders";
            $stmt = $this->conn->prepare($query);
        } else {
            $query = "SELECT SUM(total_amount) as revenue FROM orders WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['revenue'] ? $row['revenue'] : 0;
    }

    public function getCustomerCount() {
        $query = "SELECT COUNT(id) as total FROM customers";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function getLowStockCount() {
        $query = "SELECT COUNT(id) as total FROM products WHERE stock_level < 20";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>