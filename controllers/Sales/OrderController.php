<?php
namespace App\Controllers\Sales;

use App\Core\Database;
use PDO;

class OrderController {
    
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /dms/index_v2.php/login");
            exit;
        }
        $role = $_SESSION['role_slug'] ?? '';
        if (!in_array($role, ['admin', 'sales-representative'])) {
            header("Location: /dms/index_v2.php/");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT o.*, c.name as customer_name 
            FROM sales_orders o
            JOIN customers c ON o.customer_id = c.id
            WHERE o.deleted_at IS NULL
            ORDER BY o.order_date DESC
        ");
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/Sales/index.php';
    }

    public function create() {
        $db = Database::getInstance();
        $stmtC = $db->query("SELECT id, name FROM customers WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name ASC");
        $customers = $stmtC->fetchAll(PDO::FETCH_ASSOC);

        $stmtP = $db->query("SELECT id, name FROM products WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name ASC");
        $products = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/Sales/create.php';
    }

    public function store() {
        $customer_id = $_POST['customer_id'] ?? null;
        $product_id = $_POST['product_id'] ?? null;
        $quantity = $_POST['quantity'] ?? 0;

        if (!$customer_id || !$product_id || $quantity <= 0) {
            $_SESSION['error'] = "Valid Customer, Product, and positive Quantity are required.";
            header("Location: /dms/index_v2.php/orders/create");
            exit;
        }

        try {
            $db = Database::getInstance();
            $db->beginTransaction();

            // Step 0: Strict Inventory Validation
            $stmtCheckInv = $db->prepare("SELECT quantity_on_hand FROM inventory WHERE product_id = :pid AND warehouse_id = 1");
            $stmtCheckInv->execute([':pid' => $product_id]);
            $currentStock = $stmtCheckInv->fetchColumn();

            if ($currentStock === false || $quantity > $currentStock) {
                $db->rollBack();
                $_SESSION['error'] = "Error: Insufficient stock for this product.";
                header("Location: /dms/index_v2.php/orders/create");
                exit;
            }

            // Step 1 & 2: Dynamic Pricing & Order Metadata
            $orderNo = 'ORD-' . strtoupper(uniqid());
            $now = date('Y-m-d H:i:s');
            
            $price = 0;
            try {
                $stmtPrice = $db->prepare("SELECT price FROM product_prices WHERE product_id = :pid ORDER BY id DESC LIMIT 1");
                $stmtPrice->execute([':pid' => $product_id]);
                $price = $stmtPrice->fetchColumn();
            } catch (\Exception $e) {
                // Ignore if table/column does not exist
            }
            if (!$price) {
                $price = 150.00; // default fallback if no price is seeded
            }
            
            $line_total = $price * $quantity;

            $salesman_id = $_SESSION['user_id'] ?? null;
            
            $stmtOrder = $db->prepare("INSERT INTO sales_orders (uuid, company_id, customer_id, branch_id, salesman_id, warehouse_id, order_number, order_date, total_amount) VALUES (UUID(), :company_id, :cid, 1, :salesman_id, :warehouse_id, :order_no, :order_date, :total)");
            $stmtOrder->execute([
                ':company_id' => 1,
                ':cid' => $customer_id,
                ':salesman_id' => $salesman_id,
                ':warehouse_id' => 1,
                ':order_no' => $orderNo,
                ':order_date' => $now,
                ':total' => $line_total
            ]);
            $orderId = $db->lastInsertId();

            $stmtItem = $db->prepare("INSERT INTO sales_order_items (sales_order_id, product_id, quantity, uom_id, unit_price, line_total) VALUES (:oid, :pid, :qty, 1, :price, :line_total)");
            $stmtItem->execute([
                ':oid' => $orderId,
                ':pid' => $product_id,
                ':qty' => $quantity,
                ':price' => $price,
                ':line_total' => $line_total
            ]);

            // Step 3: Inventory Deduction
            $stmtInv = $db->prepare("UPDATE inventory SET quantity_on_hand = quantity_on_hand - :qty WHERE product_id = :pid AND warehouse_id = 1");
            $stmtInv->execute([
                ':qty' => $quantity,
                ':pid' => $product_id
            ]);

            $db->commit();
            $_SESSION['success'] = "Order created successfully.";
            header("Location: /dms/index_v2.php/orders");
            exit;
        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = "Failed to create order: " . $e->getMessage();
            header("Location: /dms/index_v2.php/orders/create");
            exit;
        }
    }
}
