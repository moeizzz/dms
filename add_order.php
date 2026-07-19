<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Order.php';
require_once 'classes/Customer.php';
require_once 'classes/Product.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order = new Order($db);
    
    // 🔒 ENTERPRISE SECURITY FIX: Calculate the price on the server!
    // Never trust the HTML form for money. We look up the real price in the database.
    $priceQuery = "SELECT secondary_rate FROM products WHERE id = :id";
    $priceStmt = $db->prepare($priceQuery);
    $priceStmt->bindParam(":id", $_POST['product_id']);
    $priceStmt->execute();
    $productRow = $priceStmt->fetch(PDO::FETCH_ASSOC);
    
    // Math: True Selling Price x Quantity Requested
    $secure_total_amount = $productRow['secondary_rate'] * $_POST['quantity'];
    
    // Attempt to book the order using the SECURE calculated total amount
    $status = $order->bookOrder($_POST['customer_id'], $_SESSION['user_id'], $_POST['product_id'], $_POST['quantity'], $secure_total_amount);
    
    if ($status === 'success') {
        $success = "Order booked successfully! Stock has been updated.";
    } elseif ($status === 'insufficient_stock') {
        $error = "CRITICAL: Order Failed. Not enough stock in the warehouse to fulfill this quantity.";
    } else {
        $error = "System Error: Failed to book order.";
    }
}

$customerObj = new Customer($db);
$customers = $customerObj->readAll();

$productObj = new Product($db);
$products = $productObj->readAll();
?>
<?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>🛒 Book New Order (Van Sales)</h2>
            <a href="orders.php" class="btn btn-outline-secondary">View Order History</a>
        </div>
        
        <?php 
        if(isset($success)) echo "<div class='alert alert-success fw-bold'>✅ $success</div>"; 
        if(isset($error)) echo "<div class='alert alert-danger fw-bold'>❌ $error</div>"; 
        ?>
        
        <div class="card shadow-sm border-0" style="max-width: 600px;">
            <div class="card-body">
                <form action="add_order.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Retailer (Customer)</label>
                        <select name="customer_id" class="form-select" required>
                            <option value="">-- Choose a Retailer --</option>
                            <?php while ($c = $customers->fetch(PDO::FETCH_ASSOC)) : ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Product</label>
                        <select name="product_id" class="form-select" required>
                            <option value="">-- Choose a Product --</option>
                            <?php while ($p = $products->fetch(PDO::FETCH_ASSOC)) : ?>
                                <option value="<?php echo $p['id']; ?>">
                                    <?php echo $p['name']; ?> (Stock: <?php echo $p['stock_level']; ?>) - Rs. <?php echo $p['secondary_rate']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Quantity</label>
                            <input type="number" name="quantity" class="form-control" required min="1">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold">Selling Price (Per Item in Rs.)</label>
                            <input type="number" step="0.01" name="price" class="form-control" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary fw-bold w-100">Submit Order</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>