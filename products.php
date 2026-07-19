<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Product.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);
$stmt = $product->readAll();
?>

<?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📦 Product Catalog & Inventory</h2>
            <a href="add_product.php" class="btn btn-success fw-bold">➕ Add Product</a>
        </div>
        
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Primary Rate</th>
                            <th>Secondary Rate</th>
                            <th>Stock Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td class="fw-bold"><?php echo $row['name']; ?></td>
                            <td><?php echo $row['category']; ?></td>
                            <td>Rs. <?php echo $row['primary_rate']; ?></td>
                            <td>Rs. <?php echo $row['secondary_rate']; ?></td>
                            <td>
                                <span class="badge <?php echo ($row['stock_level'] < 20) ? 'bg-danger' : 'bg-success'; ?>">
                                    <?php echo $row['stock_level']; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>