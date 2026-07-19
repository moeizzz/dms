<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Customer.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$customer = new Customer($db);
$stmt = $customer->readAll();
?>

<?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>🏬 Retailer Directory & CRM</h2>
            <a href="add_customer.php" class="btn btn-info text-white fw-bold">➕ Add Retailer</a>
        </div>
        
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Shop Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Credit Limit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td class="fw-bold"><?php echo $row['name']; ?></td>
                            <td><?php echo $row['phone']; ?></td>
                            <td><?php echo $row['address']; ?></td>
                            <td class="text-primary fw-bold">Rs. <?php echo number_format($row['credit_limit'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>