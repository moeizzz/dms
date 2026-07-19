<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Customer.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    $customer = new Customer($db);
    
    if($customer->create($_POST['name'], $_POST['phone'], $_POST['address'], $_POST['credit_limit'])) {
        $success = "Retailer added successfully!";
    } else {
        $error = "Failed to add retailer.";
    }
}
?>
<?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>🤝 Add New Retailer (Customer)</h2>
            <a href="customers.php" class="btn btn-outline-secondary">Back to Directory</a>
        </div>
        
        <?php 
        if(isset($success)) echo "<div class='alert alert-success fw-bold'>$success</div>"; 
        if(isset($error)) echo "<div class='alert alert-danger fw-bold'>$error</div>"; 
        ?>
        
        <div class="card shadow-sm border-0" style="max-width: 600px;">
            <div class="card-body">
                <form action="add_customer.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Shop / Retailer Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Shop Address</label>
                        <textarea name="address" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Maximum Credit Limit (Rs.)</label>
                        <input type="number" step="0.01" name="credit_limit" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-info text-white fw-bold w-100">Save Retailer</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>