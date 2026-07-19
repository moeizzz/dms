<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receive Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/dms/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4">📥 Receive Stock (GRN)</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="/dms/index_v2.php/inventory/store" method="POST">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Product *</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select a product</option>
                                <?php if (!empty($products)): ?>
                                    <?php foreach ($products as $product): ?>
                                        <option value="<?php echo htmlspecialchars((string)$product['id']); ?>">
                                            <?php echo htmlspecialchars($product['name'] . ' (' . $product['sku'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Warehouse *</label>
                            <select name="warehouse_id" class="form-select" required>
                                <option value="">Select a warehouse</option>
                                <?php if (!empty($warehouses)): ?>
                                    <?php foreach ($warehouses as $warehouse): ?>
                                        <option value="<?php echo htmlspecialchars((string)$warehouse['id']); ?>">
                                            <?php echo htmlspecialchars($warehouse['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold">Quantity Received *</label>
                            <input type="number" step="0.001" name="quantity" class="form-control" required>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary fw-bold">Process Receipt</button>
                        <a href="/dms/index_v2.php/inventory" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
