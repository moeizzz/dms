<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/User.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Handle Delete Request
if(isset($_GET['delete'])) {
    if($user->delete($_GET['delete'])) {
        $success = "Employee account securely deleted.";
    } else {
        $error = "Failed to delete account. You cannot delete an Admin.";
    }
}

// Handle New User Creation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if($user->create($_POST['username'], $_POST['password'], $_POST['role'])) {
        $success = "New employee account created successfully!";
    } else {
        $error = "Failed to create account. Username might already exist.";
    }
}

$stmt = $user->readAll();
?>

<?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>👥 Staff Management (HR)</h2>
        </div>
        
        <?php 
        if(isset($success)) echo "<div class='alert alert-success fw-bold'>✅ $success</div>"; 
        if(isset($error)) echo "<div class='alert alert-danger fw-bold'>❌ $error</div>"; 
        ?>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0 border-primary border-start border-4">
                    <div class="card-header bg-white fw-bold">
                        ➕ Add New Employee
                    </div>
                    <div class="card-body">
                        <form action="users.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Username</label>
                                <input type="text" name="username" class="form-control" required placeholder="e.g., ali_sales">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="passInput" class="form-control" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePass">👁️</button>
                                </div>
                                <small class="text-muted">Passwords are securely hashed. They cannot be recovered, only reset.</small>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold">Assign Role</label>
                                <select name="role" class="form-select" required>
                                    <option value="salesman">Salesman (Field Staff)</option>
                                    <option value="admin">Admin (Manager)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary fw-bold w-100">Create Account</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>System Role</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                                <tr>
                                    <td class="fw-bold text-muted">#<?php echo $row['id']; ?></td>
                                    <td class="fw-bold">@<?php echo $row['username']; ?></td>
                                    <td>
                                        <?php if($row['role'] == 'admin'): ?>
                                            <span class="badge bg-danger text-uppercase">Admin</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary text-uppercase">Salesman</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if($row['role'] != 'admin'): ?>
                                            <a href="users.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this employee?');">🗑️ Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('togglePass').addEventListener('click', function() {
            const passInput = document.getElementById('passInput');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                this.innerHTML = '🙈'; // Change icon to closed eye
            } else {
                passInput.type = 'password';
                this.innerHTML = '👁️'; // Change back to open eye
            }
        });
    </script>
</body>
</html>