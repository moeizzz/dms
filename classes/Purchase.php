<?php
class Purchase {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // This transaction logs the purchase AND increases the stock
    public function receiveStock($supplier_id, $product_id, $quantity, $unit_cost) {
        try {
            // 🛑 1. Start the Transaction
            $this->conn->beginTransaction();

            $total_cost = $quantity * $unit_cost;

            // 🧾 2. Insert the purchase invoice record
            $query = "INSERT INTO purchases (supplier_id, product_id, quantity, unit_cost, total_cost) 
                      VALUES (:supplier_id, :product_id, :quantity, :unit_cost, :total_cost)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":supplier_id", $supplier_id);
            $stmt->bindParam(":product_id", $product_id);
            $stmt->bindParam(":quantity", $quantity);
            $stmt->bindParam(":unit_cost", $unit_cost);
            $stmt->bindParam(":total_cost", $total_cost);
            $stmt->execute();

            // 📈 3. INCREASE the purchased quantity in the warehouse
            $stockQuery = "UPDATE products SET stock_level = stock_level + :quantity WHERE id = :product_id";
            $stockStmt = $this->conn->prepare($stockQuery);
            $stockStmt->bindParam(":quantity", $quantity);
            $stockStmt->bindParam(":product_id", $product_id);
            $stockStmt->execute();

            // ✅ 4. Commit the changes if everything succeeds
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // ❌ Rollback if anything fails
            $this->conn->rollBack();
            return false;
        }
    }

    // Fetch all inward supply invoices for the history page
    public function readAll() {
        $query = "SELECT purchases.id, purchases.quantity, purchases.unit_cost, purchases.total_cost, purchases.created_at, 
                         suppliers.name as supplier_name, products.name as product_name 
                  FROM purchases 
                  LEFT JOIN suppliers ON purchases.supplier_id = suppliers.id 
                  LEFT JOIN products ON purchases.product_id = products.id 
                  ORDER BY purchases.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>