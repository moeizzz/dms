<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Supplier.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $supplier = new Supplier($db);
    
    if($supplier->create($_POST['name'], $_POST['contact_person'], $_POST['phone'], $_POST['address'])) {
        $success = "Supplier added successfully!";
    } else {
        $error = "Failed to add supplier.";
    }
}
?>

<?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>🏢 Add New Supplier (Vendor)</h2>
            <a href="dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
        
        <?php 
        if(isset($success)) echo "<div class='alert alert-success fw-bold'>$success</div>"; 
        if(isset($error)) echo "<div class='alert alert-danger fw-bold'>$error</div>"; 
        ?>
        
        <div class="card shadow-sm border-0" style="max-width: 600px;">
            <div class="card-body">
                <form action="add_supplier.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Company / Supplier Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., National Beverages Ltd." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" placeholder="e.g., Mr. Ahmed" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Company Address</label>
                        <textarea name="address" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning text-dark fw-bold w-100">Save Supplier</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>