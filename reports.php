<?php
session_start();
require_once 'config/Database.php';
require_once 'classes/Report.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$report = new Report($db);

$has_data = false;

// Check if the user submitted the date form
if(isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    
    $total_revenue = $report->getSalesTotal($start_date, $end_date);
    $sales_data = $report->getSalesReport($start_date, $end_date);
    $has_data = true;

    // 🌟 THE FLEX: CSV Export Logic
    if(isset($_GET['export']) && $_GET['export'] == 'csv') {
        // Tell the browser to download a CSV instead of showing a webpage
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=sales_report_' . $start_date . '_to_' . $end_date . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Write the Column Headers
        fputcsv($output, array('Order ID', 'Retailer Name', 'Sales Rep', 'Amount (Rs)', 'Date & Time'));
        
        // Loop through the database and write the rows
        while ($row = $sales_data->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, array('#'.$row['id'], $row['customer_name'], '@'.$row['salesman'], $row['total_amount'], $row['order_date']));
        }
        
        fclose($output);
        exit(); // Stop the script so no HTML leaks into the spreadsheet
    }
}
?>

<?php include 'includes/header.php'; ?>

    <main class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📈 Financial Reports</h2>
            <?php if($has_data && $sales_data->rowCount() > 0): ?>
                <a href="reports.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&export=csv" class="btn btn-success fw-bold">
                    ⬇️ Download CSV
                </a>
            <?php endif; ?>
        </div>
        
        <div class="card shadow-sm border-0 mb-4 bg-white p-3">
            <form action="reports.php" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary fw-bold w-100">📊 Generate Report</button>
                </div>
            </form>
        </div>

        <?php if($has_data): ?>
            <div class="alert alert-info fw-bold mb-4">
                Showing results from <?php echo date('d M Y', strtotime($start_date)); ?> to <?php echo date('d M Y', strtotime($end_date)); ?>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card bg-primary text-white shadow-sm border-0 p-3 text-center">
                        <h5 class="text-uppercase mb-1">Total Revenue in Period</h5>
                        <h2 class="mb-0 fw-bold">Rs. <?php echo number_format($total_revenue, 2); ?></h2>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Order ID</th>
                                <th>Retailer Name</th>
                                <th>Sales Rep</th>
                                <th>Amount</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // We have to execute the query again because the while-loop in the CSV logic consumes the data
                            $display_data = $report->getSalesReport($start_date, $end_date);
                            while ($row = $display_data->fetch(PDO::FETCH_ASSOC)) : 
                            ?>
                            <tr>
                                <td class="fw-bold text-muted">#<?php echo $row['id']; ?></td>
                                <td class="fw-bold"><?php echo $row['customer_name']; ?></td>
                                <td>@<?php echo $row['salesman']; ?></td>
                                <td class="fw-bold text-success">Rs. <?php echo number_format($row['total_amount'], 2); ?></td>
                                <td><?php echo date('d M Y, h:i A', strtotime($row['order_date'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            
                            <?php if($display_data->rowCount() == 0): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No sales found in this date range.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center text-muted mt-5">
                <h4 class="fw-light">Select a date range to view sales data.</h4>
            </div>
        <?php endif; ?>

    </main>
</body>
</html>