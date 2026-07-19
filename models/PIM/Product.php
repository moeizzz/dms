<?php
namespace App\Models\PIM;

use App\Core\Database;
use PDO;

class Product {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllActive() {
        // Fetch products that are NOT soft deleted
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, u.name as uom_name 
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN units_of_measure u ON p.base_uom_id = u.id
            WHERE p.deleted_at IS NULL AND p.is_active = 1
            ORDER BY p.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function create(array $data) {
        $stmt = $this->db->prepare("
            INSERT INTO products (uuid, company_id, category_id, sku, name, description, base_uom_id, wholesale_price, retail_price)
            VALUES (UUID(), :company_id, :category_id, :sku, :name, :description, :base_uom_id, :wholesale_price, :retail_price)
        ");
        return $stmt->execute([
            ':company_id' => $data['company_id'],
            ':category_id' => $data['category_id'],
            ':sku' => $data['sku'],
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':base_uom_id' => $data['base_uom_id'],
            ':wholesale_price' => $data['wholesale_price'] ?? 0.00,
            ':retail_price' => $data['retail_price'] ?? 0.00
        ]);
    }

    public function update($id, array $data) {
        $stmt = $this->db->prepare("
            UPDATE products 
            SET category_id = :category_id, 
                sku = :sku, 
                name = :name, 
                description = :description, 
                wholesale_price = :wholesale_price, 
                retail_price = :retail_price
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':category_id' => $data['category_id'],
            ':sku' => $data['sku'],
            ':name' => $data['name'],
            ':description' => $data['description'],
            ':wholesale_price' => $data['wholesale_price'] ?? 0.00,
            ':retail_price' => $data['retail_price'] ?? 0.00
        ]);
    }

    // THE BUG FIX: Soft Deletes
    public function softDelete($id) {
        $stmt = $this->db->prepare("UPDATE products SET deleted_at = NOW(), is_active = 0 WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
