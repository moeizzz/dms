<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Purchase.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$purchase = new Purchase($db);
$stmt = $purchase->readAll();
?>

<?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>🧾 Purchase Invoices (Inward Supply)</h2>
            <a href="add_purchase.php" class="btn btn-success fw-bold">📥 Receive Stock</a>
        </div>
        
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Invoice ID</th>
                            <th>Supplier (Vendor)</th>
                            <th>Product Received</th>
                            <th>Qty</th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                            <th>Date Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td class="fw-bold text-muted">#<?php echo $row['id']; ?></td>
                            <td class="fw-bold"><?php echo $row['supplier_name']; ?></td>
                            <td class="text-primary fw-bold"><?php echo $row['product_name']; ?></td>
                            <td><span class="badge bg-success">+<?php echo $row['quantity']; ?></span></td>
                            <td>Rs. <?php echo number_format($row['unit_cost'], 2); ?></td>
                            <td class="fw-bold">Rs. <?php echo number_format($row['total_cost'], 2); ?></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>