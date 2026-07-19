<?php
namespace App\Controllers\CRM;

use App\Core\Database;
use PDO;

class CustomerController {
    
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /dms/index_v2.php/login");
            exit;
        }
        $role = $_SESSION['role_slug'] ?? '';
        if (!in_array($role, ['admin', 'sales-representative', 'customer-support'])) {
            header("Location: /dms/index_v2.php/");
            exit;
        }
    }

    public function index() {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM customers WHERE deleted_at IS NULL ORDER BY name ASC");
        $stmt->execute();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/CRM/index.php';
    }

    public function create() {
        require_once __DIR__ . '/../../views/CRM/create.php';
    }

    public function store() {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $customer_type = trim($_POST['customer_type'] ?? 'Retailer');

        if (empty($name)) {
            $_SESSION['error'] = "Company Name is required.";
            header("Location: /dms/index_v2.php/customers/create");
            exit;
        }

        try {
            $db = Database::getInstance();
            
            // We ensure we insert with company_id 1 which is the default
            $stmt = $db->prepare("INSERT INTO customers (uuid, company_id, name, email, phone, address, customer_type, is_active) VALUES (UUID(), 1, :name, :email, :phone, :address, :customer_type, 1)");
            $stmt->execute([
                ':name' => $name,
                ':email' => !empty($email) ? $email : null,
                ':phone' => !empty($phone) ? $phone : null,
                ':address' => !empty($address) ? $address : null,
                ':customer_type' => $customer_type
            ]);

            $_SESSION['success'] = "Customer created successfully.";
            header("Location: /dms/index_v2.php/customers");
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = "Failed to add customer: " . $e->getMessage();
            header("Location: /dms/index_v2.php/customers/create");
            exit;
        }
    }
}
