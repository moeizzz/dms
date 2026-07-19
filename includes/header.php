<!DOCTYPE html>
<html lang="en">
<head>
    <title>DMS Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">📦 DMS Portal</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php">Inventory</a></li>
                    <li class="nav-item"><a class="nav-link" href="customers.php">CRM</a></li>
                    <li class="nav-item"><a class="nav-link" href="orders.php">Sales (Outward)</a></li>
                    <li class="nav-item"><a class="nav-link" href="purchases.php">Purchases (Inward)</a></li>
                    <li class="nav-item"><a class="nav-link text-warning fw-bold" href="reports.php">📊 Reports</a></li>
                    
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link text-info fw-bold" href="users.php">👥 Staff</a></li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link text-danger fw-bold" href="logout.php">🚪 Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>