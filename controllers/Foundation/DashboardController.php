<?php
namespace App\Controllers\Foundation;

use App\Core\Database;
use PDO;

class DashboardController {
    
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /dms/index_v2.php/login");
            exit;
        }

        $db = Database::getInstance();

        // 1. Total Products
        $stmt1 = $db->query("SELECT COUNT(*) as total FROM products WHERE is_active = 1 AND deleted_at IS NULL");
        $totalProducts = $stmt1->fetchColumn() ?: 0;

        // 2. Total Customers
        $stmt2 = $db->query("SELECT COUNT(*) as total FROM customers WHERE is_active = 1 AND deleted_at IS NULL");
        $totalCustomers = $stmt2->fetchColumn() ?: 0;

        // 3. Total Orders
        $stmt3 = $db->query("SELECT COUNT(*) as total FROM sales_orders WHERE deleted_at IS NULL");
        $totalOrders = $stmt3->fetchColumn() ?: 0;

        // 4. Active Inventory
        $stmt4 = $db->query("SELECT SUM(quantity_on_hand) as total FROM inventory");
        $activeInventory = $stmt4->fetchColumn() ?: 0;

        // 5. Low Stock Alerts
        $stmt5 = $db->query("
            SELECT p.name as product_name, c.name as category_name, i.quantity_on_hand
            FROM inventory i
            JOIN products p ON i.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            WHERE i.quantity_on_hand < 15 AND p.is_active = 1 AND p.deleted_at IS NULL
            ORDER BY i.quantity_on_hand ASC
            LIMIT 10
        ");
        $lowStockItems = $stmt5->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/Foundation/dashboard.php';
    }
}
