<?php
// Simple autoloader so we can load App\Core\Database
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    // 1. Ensure a company exists (required by roles)
    $stmt = $db->query("SELECT id FROM companies LIMIT 1");
    $company = $stmt->fetch();
    
    if (!$company) {
        $db->query("INSERT INTO companies (uuid, name) VALUES (UUID(), 'Default Enterprise')");
        $company_id = $db->lastInsertId();
    } else {
        $company_id = $company['id'];
    }
    
    // 2. Ensure Admin Role exists
    $stmt = $db->prepare("SELECT id FROM roles WHERE slug = 'admin' AND company_id = :cid");
    $stmt->execute([':cid' => $company_id]);
    $role = $stmt->fetch();
    
    if (!$role) {
        $stmt = $db->prepare("INSERT INTO roles (company_id, name, slug, description, is_system) VALUES (:cid, 'Admin', 'admin', 'System Administrator', 1)");
        $stmt->execute([':cid' => $company_id]);
        $role_id = $db->lastInsertId();
    } else {
        $role_id = $role['id'];
    }
    
    // 3. Ensure admin user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    $user = $stmt->fetch();
    
    if (!$user) {
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (uuid, company_id, username, password, full_name, is_active) VALUES (UUID(), :cid, 'admin', :pass, 'Admin User', 1)");
        $stmt->execute([
            ':cid' => $company_id,
            ':pass' => $password_hash
        ]);
        $user_id = $db->lastInsertId();
    } else {
        $user_id = $user['id'];
        $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = :pass WHERE id = :id");
        $stmt->execute([':pass' => $password_hash, ':id' => $user_id]);
    }
    
    // 4. Link User to Role
    $stmt = $db->prepare("SELECT * FROM user_roles WHERE user_id = :uid AND role_id = :rid");
    $stmt->execute([':uid' => $user_id, ':rid' => $role_id]);
    if (!$stmt->fetch()) {
        $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:uid, :rid)");
        $stmt->execute([':uid' => $user_id, ':rid' => $role_id]);
    }
    
    echo "Success: Admin user and role seeded.\n";
    
} catch (\PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
