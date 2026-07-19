<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/dms/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require_once __DIR__ . '/../Foundation/nav.php'; ?>
    <div class="container mt-5">
        <h2 class="mb-4">➕ Create Order</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="/dms/index_v2.php/orders/store" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Customer *</label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">Select a customer</option>
                                <?php if (!empty($customers)): ?>
                                    <?php foreach ($customers as $c): ?>
                                        <option value="<?php echo htmlspecialchars((string)$c['id']); ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Product *</label>
                            <select name="product_id" class="form-select" required>
                                <option value="">Select a product</option>
                                <?php if (!empty($products)): ?>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?php echo htmlspecialchars((string)$p['id']); ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Quantity *</label>
                            <input type="number" name="quantity" class="form-control" min="1" required>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary fw-bold">Create Order</button>
                        <a href="/dms/index_v2.php/orders" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
