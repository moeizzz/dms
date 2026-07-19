<?php
namespace App\Controllers\WMS;

use App\Models\WMS\InventoryService;

class InventoryController {
    
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /dms/index_v2.php/login");
            exit;
        }

        $service = new InventoryService();
        $inventory = $service->getAllStock();

        require_once __DIR__ . '/../../views/WMS/inventory.php';
    }

    public function receive() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /dms/index_v2.php/login");
            exit;
        }

        $db = \App\Core\Database::getInstance();
        
        $stmtProd = $db->query("SELECT id, name, sku FROM products WHERE deleted_at IS NULL ORDER BY name");
        $products = $stmtProd->fetchAll(\PDO::FETCH_ASSOC);

        $stmtWh = $db->query("SELECT id, name FROM warehouses ORDER BY name");
        $warehouses = $stmtWh->fetchAll(\PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/WMS/receive.php';
    }

    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /dms/index_v2.php/login");
            exit;
        }

        $product_id = $_POST['product_id'] ?? null;
        $warehouse_id = $_POST['warehouse_id'] ?? null;
        $quantity = $_POST['quantity'] ?? 0;

        if (!$product_id || !$warehouse_id || $quantity <= 0) {
            $_SESSION['error'] = "Valid Product, Warehouse, and positive Quantity are required.";
            header("Location: /dms/index_v2.php/inventory/receive");
            exit;
        }

        try {
            $db = \App\Core\Database::getInstance();
            
            // Check if record exists
            $checkStmt = $db->prepare("SELECT id FROM inventory WHERE product_id = :pid AND warehouse_id = :wid LIMIT 1");
            $checkStmt->execute([':pid' => $product_id, ':wid' => $warehouse_id]);
            $existsId = $checkStmt->fetchColumn();

            if ($existsId) {
                // Update existing record safely
                $updateStmt = $db->prepare("UPDATE inventory SET quantity_on_hand = quantity_on_hand + :qty WHERE id = :id");
                $updateStmt->execute([':qty' => $quantity, ':id' => $existsId]);
            } else {
                // Insert new stock record
                $insertStmt = $db->prepare("INSERT INTO inventory (product_id, warehouse_id, quantity_on_hand) VALUES (:pid, :wid, :qty)");
                $insertStmt->execute([':pid' => $product_id, ':wid' => $warehouse_id, ':qty' => $quantity]);
            }

            $_SESSION['success'] = "Stock received successfully and updated atomically.";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Failed to update stock: " . $e->getMessage();
        }

        header("Location: /dms/index_v2.php/inventory");
        exit;
    }
}
