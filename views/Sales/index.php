<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/dms/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require_once __DIR__ . '/../Foundation/nav.php'; ?>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>🛒 Sales Orders</h2>
            <a href="/dms/index_v2.php/orders/create" class="btn btn-primary fw-bold">➕ Create Order</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Order No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $row): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['order_number']); ?></td>
                            <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td>Rs. <?php echo htmlspecialchars(number_format($row['total_amount'], 2)); ?></td>
                            <td><span class="badge bg-info"><?php echo htmlspecialchars($row['status']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($orders)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No sales orders found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
