<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="/dms/index_v2.php/">Enterprise DMS</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php $role = $_SESSION['role_slug'] ?? ''; ?>
                <?php if (in_array($role, ['admin', 'warehouse-manager', 'product-manager'])): ?>
                <li class="nav-item"><a class="nav-link" href="/dms/index_v2.php/products">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="/dms/index_v2.php/inventory">Inventory</a></li>
                <?php endif; ?>
                <?php if (in_array($role, ['admin', 'sales-representative', 'customer-support'])): ?>
                <li class="nav-item"><a class="nav-link" href="/dms/index_v2.php/customers">Customers</a></li>
                <li class="nav-item"><a class="nav-link" href="/dms/index_v2.php/orders">Orders</a></li>
                <?php endif; ?>
                <?php if ($role === 'admin'): ?>
                <li class="nav-item"><a class="nav-link" href="/dms/index_v2.php/users">System Admin</a></li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center">
                <span class="text-light me-3 fw-medium d-none d-lg-inline">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></span>
                <a href="/dms/index_v2.php/logout" class="btn btn-outline-light btn-sm fw-semibold">Logout</a>
            </div>
        </div>
    </div>
</nav>
