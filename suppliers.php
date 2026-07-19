<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Supplier.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$supplier = new Supplier($db);
$stmt = $supplier->readAll();
?>

<?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>🏢 Supplier Directory (Vendors)</h2>
            <a href="add_supplier.php" class="btn btn-warning text-dark fw-bold">➕ Add Supplier</a>
        </div>
        
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Company Name</th>
                            <th>Contact Person</th>
                            <th>Phone Number</th>
                            <th>Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) : ?>
                        <tr>
                            <td class="fw-bold"><?php echo $row['name']; ?></td>
                            <td><?php echo $row['contact_person']; ?></td>
                            <td><?php echo $row['phone']; ?></td>
                            <td><?php echo $row['address']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>