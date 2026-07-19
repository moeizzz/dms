<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Supplier.php';
require_once 'classes/Product.php';
require_once 'classes/Purchase.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $purchase = new Purchase($db);
    
    if($purchase->receiveStock($_POST['supplier_id'], $_POST['product_id'], $_POST['quantity'], $_POST['unit_cost'])) {
        $success = "Stock received successfully! Inventory has been updated.";
    } else {
        $error = "Failed to receive stock. Please try again.";
    }
}

// Fetch lists for the dropdown menus
$supplierObj = new Supplier($db);
$suppliers = $supplierObj->readAll();

$productObj = new Product($db);
$products = $productObj->readAll();
?>

<?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>🚛 Receive Stock (Inward Supply)</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary fw-bold">Back to Dashboard</a>
        </div>
        
        <?php 
        if(isset($success)) echo "<div class='alert alert-success fw-bold'>✅ $success</div>"; 
        if(isset($error)) echo "<div class='alert alert-danger fw-bold'>❌ $error</div>"; 
        ?>
        
        <div class="card shadow-sm border-0 border-success border-start border-4" style="max-width: 600px;">
            <div class="card-body">
                <form action="add_purchase.php" method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Supplier (Vendor)</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">-- Choose a Supplier --</option>
                            <?php while ($s = $suppliers->fetch(PDO::FETCH_ASSOC)) : ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo $s['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Product to Restock</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">-- Choose a Product --</option>
                            <?php while ($p = $products->fetch(PDO::FETCH_ASSOC)) : ?>
                                <option value="<?php echo $p['id']; ?>">
                                    <?php echo $p['name']; ?> (Current Stock: <?php echo $p['stock_level']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Quantity Received</label>
                            <input type="number" name="quantity" class="form-control" required min="1">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold">Buying Cost (Per Unit in Rs.)</label>
                            <input type="number" step="0.01" name="unit_cost" class="form-control" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success fw-bold w-100">Log Delivery & Update Stock</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>