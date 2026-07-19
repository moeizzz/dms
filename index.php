<?php
// Start the session so the server remembers who is logged in
session_start();

require_once 'config/Database.php';
require_once 'classes/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Check if the login form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $loggedInUser = $user->login($_POST['username'], $_POST['password']);
    
    if ($loggedInUser) {
        // Save user data in the session
        $_SESSION['user_id'] = $loggedInUser['id'];
        $_SESSION['role'] = $loggedInUser['role'];
        
        // Redirect to the dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DMS - Secure Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
    <div class="card p-4" style="width: 400px;">
        <div class="text-center mb-4">
            <h2 class="fw-bold">📦 DMS Portal</h2>
            <p class="text-muted">Sign in to manage distribution</p>
        </div>

        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <form action="index.php" method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-bold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 fw-bold">Secure Login</button>
        </form>
    </div>
</body>
</html>