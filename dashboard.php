<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/DashboardStats.php';

// Kick out unauthenticated users
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Connect to DB and fetch stats
$database = new Database();
$db = $database->getConnection();
$stats = new DashboardStats($db);

$total_sales = $stats->getTotalSales($_SESSION['user_id'], $_SESSION['role']);
$total_customers = $stats->getCustomerCount();
$low_stock = $stats->getLowStockCount();
?>

<?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-0">System Dashboard</h2>
                <p class="text-muted">Logged in as: <span class="badge bg-secondary text-uppercase"><?php echo $_SESSION['role']; ?></span></p>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-md-4">
                <div class="card text-center p-3 shadow-sm border-0">
                    <h6 class="text-muted text-uppercase fw-bold">Total Revenue</h6>
                    <h3 class="text-success mb-0">Rs. <?php echo number_format($total_sales ?? 0, 2); ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-3 shadow-sm border-0">
                    <h6 class="text-muted text-uppercase fw-bold">Active Retailers</h6>
                    <h3 class="text-primary mb-0"><?php echo $total_customers ?? 0; ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-3 shadow-sm border-0">
                    <h6 class="text-muted text-uppercase fw-bold">Low Stock Alerts</h6>
                    <h3 class="<?php echo ($low_stock > 0) ? 'text-danger' : 'text-success'; ?> mb-0">
                        <?php echo $low_stock ?? 0; ?> Items
                    </h3>
                </div>
            </div>
        </div>

        <h4 class="mb-3">⚡ Quick Actions</h4>
        <div class="row g-3 mb-5">
            
            <div class="col-md-4">
                <div class="card h-100 p-3 shadow-sm border-primary border-start border-4">
                    <h5 class="card-title">🛒 Sales (Outward)</h5>
                    <div class="d-grid gap-2 mt-3">
                        <a href="add_order.php" class="btn btn-primary fw-bold">Book New Order</a>
                        <a href="orders.php" class="btn btn-outline-primary">View Order History</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 p-3 shadow-sm border-info border-start border-4">
                    <h5 class="card-title">📦 Inventory</h5>
                    <div class="d-grid gap-2 mt-3">
                        <a href="add_product.php" class="btn btn-info text-white fw-bold">Add New Product</a>
                        <a href="products.php" class="btn btn-outline-info">View Product List</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 p-3 shadow-sm border-secondary border-start border-4">
                    <h5 class="card-title">🤝 CRM (Retailers)</h5>
                    <div class="d-grid gap-2 mt-3">
                        <a href="add_customer.php" class="btn btn-secondary fw-bold text-white">Add Retailer</a>
                        <a href="customers.php" class="btn btn-outline-secondary">View Retailer List</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 p-3 shadow-sm border-success border-start border-4">
                    <h5 class="card-title">🚛 Inward Supply</h5>
                    <div class="d-grid gap-2 mt-3">
                        <a href="add_supplier.php" class="btn btn-warning text-dark fw-bold">Add Supplier</a>
                        <a href="suppliers.php" class="btn btn-outline-dark">View Supplier Directory</a>
                        <a href="add_purchase.php" class="btn btn-success mt-2 fw-bold">📥 Receive Stock</a>
                        <a href="purchases.php" class="btn btn-outline-success">🧾 View Purchase History</a>
                    </div>
                </div>
            </div>

        </div>
    </main>
</body>
</html>