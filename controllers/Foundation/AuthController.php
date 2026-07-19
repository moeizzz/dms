<?php
namespace App\Controllers\Foundation;

use App\Core\Database;
use PDO;

class AuthController {
    
    public function index() {
        if (isset($_SESSION['user_id'])) {
            header("Location: /dms/index_v2.php/");
            exit;
        }
        require_once __DIR__ . '/../../views/Auth/login.php';
    }

    public function login() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['error'] = "Username and password required.";
            header("Location: /dms/index_v2.php/login");
            exit;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT u.id, u.username, u.password, u.full_name, u.is_active, r.slug as role_slug 
            FROM users u
            LEFT JOIN user_roles ur ON u.id = ur.user_id
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE u.username = :username AND u.deleted_at IS NULL 
            LIMIT 1
        ");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Uses password_verify assuming passwords are hashed using password_hash()
        // If prototype used plain text, we check both for backward compatibility temporarily, but enforce hash going forward.
        if ($user && ($user['password'] === $password || password_verify($password, $user['password']))) {
            if (!$user['is_active']) {
                $_SESSION['error'] = "Account is disabled.";
                header("Location: /dms/index_v2.php/login");
                exit;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = !empty($user['full_name']) ? $user['full_name'] : $user['username'];
            $_SESSION['role_slug'] = $user['role_slug'] ?? 'guest';
            
            // Update last login
            $update = $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id");
            $update->execute([':id' => $user['id']]);

            header("Location: /dms/index_v2.php/");
            exit;
        }

        $_SESSION['error'] = "Invalid credentials.";
        header("Location: /dms/index_v2.php/login");
        exit;
    }

    public function logout() {
        session_destroy();
        header("Location: /dms/index_v2.php/login");
        exit;
    }
}
