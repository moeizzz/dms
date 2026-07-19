<?php
namespace App\Models\WMS;

use App\Core\Database;
use PDO;
use Exception;

class InventoryService {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllStock() {
        $stmt = $this->db->prepare("
            SELECT i.*, p.name as product_name, p.sku, w.name as warehouse_name
            FROM inventory i
            JOIN products p ON i.product_id = p.id
            JOIN warehouses w ON i.warehouse_id = w.id
            ORDER BY w.name, p.name
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // THE BUG FIX: Atomic Upsert for receiving stock without duplicates
    public function receiveStock($product_id, $warehouse_id, $quantity, $user_id) {
        try {
            $this->db->beginTransaction();

            // 1. Insert into immutable transaction log
            $logStmt = $this->db->prepare("
                INSERT INTO inventory_transactions 
                (product_id, warehouse_id, transaction_type, quantity, created_by)
                VALUES (:p, :w, 'grn_in', :q, :u)
            ");
            $logStmt->execute([
                ':p' => $product_id,
                ':w' => $warehouse_id,
                ':q' => $quantity,
                ':u' => $user_id
            ]);

            // 2. Atomic UPSERT into inventory table using unique key constraints
            // This prevents the duplicates bug
            $upsertStmt = $this->db->prepare("
                INSERT INTO inventory (product_id, warehouse_id, quantity_on_hand, last_count_date)
                VALUES (:p, :w, :q, CURDATE())
                ON DUPLICATE KEY UPDATE 
                quantity_on_hand = quantity_on_hand + VALUES(quantity_on_hand)
            ");
            $upsertStmt->execute([
                ':p' => $product_id,
                ':w' => $warehouse_id,
                ':q' => $quantity
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
