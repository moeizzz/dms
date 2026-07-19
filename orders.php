<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Order.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

// Pass the specific user's ID and Role to securely filter the history
$stmt = $order->readAll($_SESSION['user_id'], $_SESSION['role']);
?>
<?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📋 Recent Orders (Outward Supply)</h2>
            <a href="add_order.php" class="btn btn-primary fw-bold">🛒 Book Order</a>
        </div>
        
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Order ID</th>
                            <th>Retailer Name</th>
                            <th>Sales Rep</th>
                            <th>Total Bill</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td class="fw-bold text-muted">#<?php echo $row['id']; ?></td>
                            <td class="fw-bold"><?php echo $row['customer_name']; ?></td>
                            <td>@<?php echo $row['salesman']; ?></td>
                            <td class="fw-bold">Rs. <?php echo number_format($row['total_amount'], 2); ?></td>
                            <td>
                                <span class="badge bg-warning text-dark text-uppercase">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y, h:i A', strtotime($row['order_date'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>