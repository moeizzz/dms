<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Product.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $product = new Product($db);
    
    if($product->create($_POST['name'], $_POST['category'], $_POST['primary_rate'], $_POST['secondary_rate'], $_POST['stock_level'])) {
        $success = "Product added successfully!";
    } else {
        $error = "Failed to add product.";
    }
}
?>

<?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>➕ Add New Product</h2>
            <a href="products.php" class="btn btn-outline-secondary">Back to List</a>
        </div>
        
        <?php 
        if(isset($success)) echo "<div class='alert alert-success fw-bold'>$success</div>"; 
        if(isset($error)) echo "<div class='alert alert-danger fw-bold'>$error</div>"; 
        ?>
        
        <div class="card shadow-sm border-0" style="max-width: 600px;">
            <div class="card-body">
                <form action="add_product.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Product Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., 500ml Cola" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Category</label>
                        <input type="text" name="category" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Primary Rate (Rs.)</label>
                            <input type="number" step="0.01" name="primary_rate" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Secondary Rate (Rs.)</label>
                            <input type="number" step="0.01" name="secondary_rate" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Initial Stock Level</label>
                        <input type="number" name="stock_level" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success fw-bold w-100">Save Product</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>