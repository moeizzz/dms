<?php
require_once __DIR__ . '/core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    // Fetch or create default company
    $compStmt = $db->query("SELECT id FROM companies LIMIT 1");
    $companyId = $compStmt->fetchColumn();

    if (!$companyId) {
        $insertCompStmt = $db->prepare("INSERT INTO companies (uuid, name, domain, is_active) VALUES (UUID(), 'Contour Enterprise', 'contour.local', 1)");
        $insertCompStmt->execute();
        $companyId = $db->lastInsertId();
        echo "Created default company with ID: {$companyId}\n";
    }

    // Define the roles to seed
    $rolesToSeed = [
        'Warehouse Manager',
        'Sales Representative',
        'Customer Support',
        'Product Manager'
    ];
    
    // Prepared statements for checking and inserting
    $checkStmt = $db->prepare("SELECT id FROM roles WHERE name = :name AND company_id = :company_id LIMIT 1");
    $insertStmt = $db->prepare("INSERT IGNORE INTO roles (name, slug, company_id) VALUES (:name, :slug, :company_id)");
    
    $insertedCount = 0;
    
    foreach ($rolesToSeed as $roleName) {
        $slug = strtolower(str_replace(' ', '-', $roleName));
        $checkStmt->execute([':name' => $roleName, ':company_id' => $companyId]);
        $exists = $checkStmt->fetchColumn();
        
        if (!$exists) {
            $insertStmt->execute([':name' => $roleName, ':slug' => $slug, ':company_id' => $companyId]);
            $insertedCount++;
            echo "Inserted role: {$roleName}\n";
        } else {
            echo "Role already exists: {$roleName}\n";
        }
    }
    
    echo "Seed completed. Successfully added {$insertedCount} new roles.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
