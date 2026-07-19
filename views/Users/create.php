<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/dms/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require_once __DIR__ . '/../Foundation/nav.php'; ?>
    <div class="container mt-5">
        <h2 class="mb-4">➕ Add New User</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="/dms/index_v2.php/users/store" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Username *</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Password *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Full Name *</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Role *</label>
                            <select name="role_id" class="form-select" required>
                                <option value="">Select a role</option>
                                <?php if (!empty($roles)): ?>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?php echo htmlspecialchars((string)$role['id']); ?>"><?php echo htmlspecialchars($role['name']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary fw-bold">Save User</button>
                        <a href="/dms/index_v2.php/users" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
