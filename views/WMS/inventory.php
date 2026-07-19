<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/dms/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require_once __DIR__ . '/../Foundation/nav.php'; ?>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📦 Warehouse Inventory</h2>
            <a href="/dms/index_v2.php/inventory/receive" class="btn btn-primary fw-bold">📥 Receive Stock (GRN)</a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Warehouse</th>
                            <th>SKU</th>
                            <th>Product Name</th>
                            <th>Qty On Hand</th>
                            <th>Qty Available</th>
                            <th>Last Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['warehouse_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['sku']); ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td>
                                <span class="badge <?php echo ($row['quantity_on_hand'] < 20) ? 'bg-danger' : 'bg-success'; ?>">
                                    <?php echo htmlspecialchars($row['quantity_on_hand']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['quantity_available']); ?></td>
                            <td><?php echo htmlspecialchars($row['last_count_date']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($inventory)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No inventory records found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
