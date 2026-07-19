<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/dms/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php require_once __DIR__ . '/../Foundation/nav.php'; ?>
    <div class="container mt-5">
        <h2 class="mb-4">➕ Add Customer</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4 p-md-5">
                <form action="/dms/index_v2.php/customers/store" method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-secondary mb-2">Company Name *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-secondary mb-2">Customer Type *</label>
                            <select name="customer_type" class="form-select" required>
                                <option value="Retailer">Retailer</option>
                                <option value="Wholesaler">Wholesaler</option>
                                <option value="Direct Consumer">Direct Consumer</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-secondary mb-2">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-secondary mb-2">Phone</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold text-secondary mb-2">Address</label>
                            <textarea name="address" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2 pt-3 mt-4 border-top">
                        <button type="submit" class="btn btn-primary fw-bold">Save Customer</button>
                        <a href="/dms/index_v2.php/customers" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
