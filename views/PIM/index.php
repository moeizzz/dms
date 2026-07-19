<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/dms/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require_once __DIR__ . '/../Foundation/nav.php'; ?>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📦 Product Catalog</h2>
            <a href="/dms/index_v2.php/products/create" class="btn btn-success fw-bold">➕ Add Product</a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>UOM</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $row): ?>
                        <tr>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['sku']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['uom_name'] ?? 'N/A'); ?></td>
                            <td>
                                <!-- BUG FIX #1: Product Deletion using POST for security -->
                                <form action="/dms/index_v2.php/products/delete/<?php echo $row['id']; ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">🗑️ Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($products)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No active products found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
