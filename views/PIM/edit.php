<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/dms/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4">✏️ Edit Product</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="/dms/index_v2.php/products/update/<?php echo $product['id']; ?>" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">SKU *</label>
                            <input type="text" name="sku" class="form-control" value="<?php echo htmlspecialchars($product['sku']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Product Name *</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['id']); ?>" <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Wholesale Price (Rs.)</label>
                            <input type="number" step="0.01" name="wholesale_price" class="form-control" value="<?php echo htmlspecialchars($product['wholesale_price'] ?? '0.00'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Retail Price (Rs.)</label>
                            <input type="number" step="0.01" name="retail_price" class="form-control" value="<?php echo htmlspecialchars($product['retail_price'] ?? '0.00'); ?>">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary fw-bold">Update Product</button>
                        <a href="/dms/index_v2.php/products" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
