<?php
namespace App\Controllers\PIM;

use App\Models\PIM\Product;
use App\Core\Database;
use PDO;

class ProductController {
    
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /dms/login");
            exit;
        }

        $productModel = new Product();
        $products = $productModel->getAllActive();

        require_once __DIR__ . '/../../views/PIM/index.php';
    }

    public function create() {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Show create form
        require_once __DIR__ . '/../../views/PIM/create.php';
    }

    public function store() {
        $productModel = new Product();
        
        // Basic validation
        $sku = $_POST['sku'] ?? '';
        $name = $_POST['name'] ?? '';
        $category_id = $_POST['category_id'] ?? null;
        
        if (empty($sku) || empty($name) || empty($category_id)) {
            $_SESSION['error'] = "SKU, Name, and Category are required.";
            header("Location: /dms/index_v2.php/products/create");
            exit;
        }

        // In a real scenario we'd fetch company/category from session or dropdown
        // Using hardcoded 1 for the refactoring prototype
        $productModel->create([
            'company_id' => 1,
            'category_id' => $category_id,
            'sku' => $sku,
            'name' => $name,
            'description' => $_POST['description'] ?? '',
            'base_uom_id' => 1,
            'wholesale_price' => $_POST['wholesale_price'] ?? 0.00,
            'retail_price' => $_POST['retail_price'] ?? 0.00
        ]);

        $_SESSION['success'] = "Product created successfully.";
        header("Location: /dms/index_v2.php/products");
        exit;
    }

    public function edit($id) {
        $productModel = new Product();
        $product = $productModel->findById($id);

        if (!$product) {
            $_SESSION['error'] = "Product not found.";
            header("Location: /dms/index_v2.php/products");
            exit;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/PIM/edit.php';
    }

    public function update($id) {
        $productModel = new Product();
        
        $sku = $_POST['sku'] ?? '';
        $name = $_POST['name'] ?? '';
        $category_id = $_POST['category_id'] ?? null;
        
        if (empty($sku) || empty($name) || empty($category_id)) {
            $_SESSION['error'] = "SKU, Name, and Category are required.";
            header("Location: /dms/index_v2.php/products/edit/" . $id);
            exit;
        }

        $productModel->update($id, [
            'category_id' => $category_id,
            'sku' => $sku,
            'name' => $name,
            'description' => $_POST['description'] ?? '',
            'wholesale_price' => $_POST['wholesale_price'] ?? 0.00,
            'retail_price' => $_POST['retail_price'] ?? 0.00
        ]);

        $_SESSION['success'] = "Product updated successfully.";
        header("Location: /dms/index_v2.php/products");
        exit;
    }

    public function delete($id) {
        $productModel = new Product();
        $productModel->softDelete($id);
        
        $_SESSION['success'] = "Product deleted successfully.";
        header("Location: /dms/index_v2.php/products");
        exit;
    }
}
