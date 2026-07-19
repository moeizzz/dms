<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/dms/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require_once __DIR__ . '/nav.php'; ?>
    <div class="container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark mb-0">Dashboard</h2>
            <span class="d-lg-none text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></span>
        </div>
        
        <?php 
        $role = $_SESSION['role_slug'] ?? '';
        $showKpi = in_array($role, ['admin', 'sales-representative']);
        $showLowStock = in_array($role, ['admin', 'warehouse-manager']);
        ?>

        <?php if ($showKpi): ?>
        <div class="row g-4">
            <!-- KPI Card 1 -->
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card bg-primary text-white shadow-sm border-0 h-100 rounded-4">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <h6 class="card-title text-uppercase text-white-50 fw-bold mb-3 tracking-wide">Total Products</h6>
                        <h2 class="display-5 fw-bold mb-0"><?php echo htmlspecialchars((string)($totalProducts ?? 0)); ?></h2>
                    </div>
                </div>
            </div>
            
            <!-- KPI Card 2 -->
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card bg-success text-white shadow-sm border-0 h-100 rounded-4">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <h6 class="card-title text-uppercase text-white-50 fw-bold mb-3 tracking-wide">Active Inventory</h6>
                        <h2 class="display-5 fw-bold mb-0"><?php echo htmlspecialchars((string)($activeInventory ?? 0)); ?></h2>
                    </div>
                </div>
            </div>
            
            <!-- KPI Card 3 -->
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card bg-info text-white shadow-sm border-0 h-100 rounded-4">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <h6 class="card-title text-uppercase text-white-50 fw-bold mb-3 tracking-wide">Total Customers</h6>
                        <h2 class="display-5 fw-bold mb-0"><?php echo htmlspecialchars((string)($totalCustomers ?? 0)); ?></h2>
                    </div>
                </div>
            </div>
            
            <!-- KPI Card 4 -->
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card bg-warning text-dark shadow-sm border-0 h-100 rounded-4">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <h6 class="card-title text-uppercase text-black-50 fw-bold mb-3 tracking-wide">Total Orders</h6>
                        <h2 class="display-5 fw-bold mb-0"><?php echo htmlspecialchars((string)($totalOrders ?? 0)); ?></h2>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($showLowStock): ?>
        <div class="mt-5">
            <h4 class="fw-bold text-dark mb-3">⚠️ Low Stock Alerts</h4>
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Current Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($lowStockItems)): ?>
                                    <?php foreach ($lowStockItems as $item): ?>
                                    <tr class="table-warning">
                                        <td class="fw-bold text-danger"><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                        <td>
                                            <span class="badge bg-danger rounded-pill px-3 py-2 fs-6">
                                                <?php echo htmlspecialchars((string)$item['quantity_on_hand']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">All stock levels are optimal.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!$showKpi && !$showLowStock): ?>
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-body text-center py-5">
                <h3 class="fw-bold text-muted">Welcome to Enterprise DMS</h3>
                <p class="text-secondary mb-0">Select a module from the navigation menu to begin.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
