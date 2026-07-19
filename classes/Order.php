<?php
class Order {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // THE FIX: Smart Order Booking (Without touching non-existent columns)
    public function bookOrder($customer_id, $user_id, $product_id, $quantity, $total_amount) {
        try {
            $this->conn->beginTransaction();

            // 🛑 1. SECURITY CHECK: Do we actually have enough stock?
            $checkQuery = "SELECT stock_level FROM products WHERE id = :product_id FOR UPDATE";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(":product_id", $product_id);
            $checkStmt->execute();
            $product = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($product['stock_level'] < $quantity) {
                // Not enough stock! Cancel everything and send an error code.
                $this->conn->rollBack();
                return "insufficient_stock";
            }

            // 🧾 2. Insert the Order (Matching your exact database schema)
            $query = "INSERT INTO orders (customer_id, user_id, total_amount) 
                      VALUES (:customer_id, :user_id, :total_amount)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":customer_id", $customer_id);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":total_amount", $total_amount);
            $stmt->execute();

            // 📉 3. Deduct the Stock
            $updateQuery = "UPDATE products SET stock_level = stock_level - :quantity WHERE id = :product_id";
            $stockStmt = $this->conn->prepare($updateQuery);
            $stockStmt->bindParam(":quantity", $quantity);
            $stockStmt->bindParam(":product_id", $product_id);
            $stockStmt->execute();

            $this->conn->commit();
            return "success";

        } catch (Exception $e) {
            $this->conn->rollBack();
            return "error";
        }
    }

    // THE FIX: Context-Aware Order History (Removed the bad products join)
    public function readAll($user_id, $role) {
        if ($role === 'admin') {
            // Admins see EVERY order
            $query = "SELECT orders.id, orders.total_amount, orders.order_date, customers.name as customer_name, users.username as salesman 
                      FROM orders 
                      LEFT JOIN customers ON orders.customer_id = customers.id
                      LEFT JOIN users ON orders.user_id = users.id
                      ORDER BY orders.order_date DESC";
            $stmt = $this->conn->prepare($query);
        } else {
            // Salesmen ONLY see their own orders
            $query = "SELECT orders.id, orders.total_amount, orders.order_date, customers.name as customer_name, users.username as salesman 
                      FROM orders 
                      LEFT JOIN customers ON orders.customer_id = customers.id
                      LEFT JOIN users ON orders.user_id = users.id
                      WHERE orders.user_id = :user_id
                      ORDER BY orders.order_date DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
        }
        $stmt->execute();
        return $stmt;
    }
}
?>