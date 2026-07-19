<?php
namespace App\Controllers\Foundation;

use App\Core\Database;
use PDO;
use Exception;

class UserController {
    public function __construct() {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role_slug'] ?? '') !== 'admin') {
            header('Location: /dms/index_v2.php/');
            exit;
        }
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /dms/index_v2.php/login");
            exit;
        }

        $db = Database::getInstance();
        $stmt = $db->query("
            SELECT u.id, u.username, u.full_name, r.name as role_name, r.slug as role_slug
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE u.deleted_at IS NULL
            ORDER BY u.full_name ASC
        ");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/Users/index.php';
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /dms/index_v2.php/login");
            exit;
        }

        $db = Database::getInstance();
        $stmt = $db->query("SELECT id, name FROM roles ORDER BY name ASC");
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../../views/Users/create.php';
    }

    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /dms/index_v2.php/login");
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $role_id = $_POST['role_id'] ?? null;

        if (empty($username) || empty($password) || empty($full_name) || empty($role_id)) {
            $_SESSION['error'] = "All fields are required.";
            header("Location: /dms/index_v2.php/users/create");
            exit;
        }

        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();

            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmtUser = $db->prepare("INSERT INTO users (uuid, username, password, full_name, is_active) VALUES (UUID(), :username, :password, :full_name, 1)");
            $stmtUser->execute([
                ':username' => $username,
                ':password' => $hashedPassword,
                ':full_name' => $full_name
            ]);
            
            $userId = $db->lastInsertId();

            // Insert into user_roles
            $stmtRole = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)");
            $stmtRole->execute([
                ':user_id' => $userId,
                ':role_id' => $role_id
            ]);

            $db->commit();
            
            $_SESSION['success'] = "User created successfully.";
            header("Location: /dms/index_v2.php/users");
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = "Failed to create user: " . $e->getMessage();
            header("Location: /dms/index_v2.php/users/create");
            exit;
        }
    }

    public function delete($id) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: /dms/index_v2.php/login");
            exit;
        }

        $db = Database::getInstance();

        // Admin Deletion Protection
        $stmtCheck = $db->prepare("
            SELECT r.slug as role_slug 
            FROM users u 
            LEFT JOIN user_roles ur ON u.id = ur.user_id 
            LEFT JOIN roles r ON ur.role_id = r.id 
            WHERE u.id = :id
        ");
        $stmtCheck->execute([':id' => $id]);
        $targetRole = $stmtCheck->fetchColumn();

        if ($targetRole === 'admin') {
            $_SESSION['error'] = "Action Denied: Cannot delete an Administrator.";
            header("Location: /dms/index_v2.php/users");
            exit;
        }

        $stmt = $db->prepare("UPDATE users SET deleted_at = NOW(), is_active = 0 WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $_SESSION['success'] = "User deleted successfully.";
        header("Location: /dms/index_v2.php/users");
        exit;
    }
}
