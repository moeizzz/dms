<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/dms/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require_once __DIR__ . '/../Foundation/nav.php'; ?>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>👥 Retailers / Customers</h2>
            <a href="/dms/index_v2.php/customers/create" class="btn btn-primary fw-bold">➕ Add Customer</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['email'] ?? 'N/A'); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['customer_type'] ?? 'Retail'); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($customers)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No active customers found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
