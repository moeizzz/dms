<?php
require_once __DIR__ . '/core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    // Insert 3 dummy categories
    $categories = [
        ['company_id' => 1, 'name' => 'Beverages', 'slug' => 'beverages', 'is_active' => 1],
        ['company_id' => 1, 'name' => 'Snacks', 'slug' => 'snacks', 'is_active' => 1],
        ['company_id' => 1, 'name' => 'Electronics', 'slug' => 'electronics', 'is_active' => 1]
    ];
    
    $stmtCat = $db->prepare("INSERT INTO categories (company_id, name, slug, is_active) VALUES (:company_id, :name, :slug, :is_active)");
    $catIds = [];
    foreach ($categories as $cat) {
        $stmtCat->execute($cat);
        $catIds[] = $db->lastInsertId();
    }
    
    // Ensure base_uom_id 1 exists
    $db->exec("INSERT IGNORE INTO units_of_measure (id, company_id, name, abbreviation) VALUES (1, 1, 'Pieces', 'pcs')");
    
    // Insert 15 realistic dummy products
    $products = [
        ['category_id' => $catIds[0], 'sku' => 'BEV-001', 'name' => 'Cola 500ml'],
        ['category_id' => $catIds[0], 'sku' => 'BEV-002', 'name' => 'Orange Juice 1L'],
        ['category_id' => $catIds[0], 'sku' => 'BEV-003', 'name' => 'Energy Drink 250ml'],
        ['category_id' => $catIds[0], 'sku' => 'BEV-004', 'name' => 'Mineral Water 1L'],
        ['category_id' => $catIds[0], 'sku' => 'BEV-005', 'name' => 'Green Tea Bottle'],
        ['category_id' => $catIds[1], 'sku' => 'SNK-001', 'name' => 'Potato Chips Salted'],
        ['category_id' => $catIds[1], 'sku' => 'SNK-002', 'name' => 'Chocolate Bar 50g'],
        ['category_id' => $catIds[1], 'sku' => 'SNK-003', 'name' => 'Mixed Nuts 100g'],
        ['category_id' => $catIds[1], 'sku' => 'SNK-004', 'name' => 'Cookies 150g'],
        ['category_id' => $catIds[1], 'sku' => 'SNK-005', 'name' => 'Popcorn Caramel'],
        ['category_id' => $catIds[2], 'sku' => 'ELE-001', 'name' => 'AA Batteries 4-pack'],
        ['category_id' => $catIds[2], 'sku' => 'ELE-002', 'name' => 'USB-C Cable 1m'],
        ['category_id' => $catIds[2], 'sku' => 'ELE-003', 'name' => 'Wireless Mouse'],
        ['category_id' => $catIds[2], 'sku' => 'ELE-004', 'name' => 'Earbuds In-ear'],
        ['category_id' => $catIds[2], 'sku' => 'ELE-005', 'name' => 'Power Bank 10000mAh'],
    ];
    
    $stmtProd = $db->prepare("INSERT INTO products (uuid, company_id, category_id, sku, name, base_uom_id, is_active) VALUES (UUID(), 1, :category_id, :sku, :name, 1, 1)");
    $productIds = [];
    foreach ($products as $prod) {
        $stmtProd->execute($prod);
        $productIds[] = $db->lastInsertId();
    }
    
    // Ensure warehouse exists
    $db->exec("INSERT IGNORE INTO branches (id, uuid, company_id, name, code) VALUES (1, UUID(), 1, 'Main Branch', 'BR01')");
    $db->exec("INSERT IGNORE INTO warehouses (id, uuid, company_id, branch_id, name, code) VALUES (1, UUID(), 1, 1, 'Central Warehouse', 'WH01')");
    
    // Insert inventory
    $stmtInv = $db->prepare("INSERT INTO inventory (product_id, warehouse_id, quantity_on_hand) VALUES (:product_id, 1, :qty)");
    foreach ($productIds as $index => $pid) {
        // Force some products to have low stock (<15)
        $qty = ($index % 3 == 0) ? rand(2, 12) : rand(20, 100);
        $stmtInv->execute(['product_id' => $pid, 'qty' => $qty]);
    }
    
    echo "Catalog seeded successfully with categories, products, and inventory!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
