<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Representative Dashboard</title>
    <!-- Bootstrap 5 for Layout and Components -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/dms/assets/css/style.css" rel="stylesheet">
    <style>
        /* Strict CSS from dist.html blueprint */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Roboto, Arial, sans-serif; background: #e9ecef; display: flex; flex-direction: column; align-items: center; }
        .nav-wrapper { width: 100%; padding: 20px 20px 0 20px; }
        .document-wrapper { width: 100%; background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.1); padding: 40px 50px; border-radius: 8px; margin: 20px 0 60px 0; }
        h1 { font-size: 32px; color: #0b2b4a; border-bottom: 4px solid #f39c12; padding-bottom: 15px; margin-bottom: 10px; letter-spacing: 1px; }
        h2 { font-size: 22px; color: #1a5276; background: #ebf5fb; padding: 12px 20px; margin-top: 35px; margin-bottom: 20px; border-left: 6px solid #2980b9; border-radius: 0 4px 4px 0; }
        p.dist-p { line-height: 1.7; color: #2d3e4f; margin-bottom: 12px; font-size: 15px; }
        .dist-badge { display: inline-block; background: #f39c12; color: white; font-size: 13px; font-weight: bold; padding: 0 12px; border-radius: 20px; line-height: 24px; margin-right: 8px; }
        .summary-table { width: 100%; border-collapse: collapse; margin: 30px 0 20px; font-size: 15px; }
        .summary-table th { background: #0b2b4a; color: white; padding: 12px 15px; text-align: left; }
        .summary-table td { padding: 12px 15px; border-bottom: 1px solid #e0e6ed; }
        .summary-table tr:nth-child(even) { background: #f8fafc; }
        ul.dist-ul { list-style: none; padding-left: 10px; margin-bottom: 15px; }
        ul.dist-ul li { padding: 6px 0 6px 30px; background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="%232980b9" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>') left center no-repeat; background-size: 18px; margin-bottom: 2px; color: #1e2b38; font-size: 15px; }
        ul.dist-ul li strong { color: #0b2b4a; font-weight: 700; }
        hr.dist-hr { border: 0; border-top: 1px solid #e5e8ea; margin: 30px 0; }
        .hover-elevate { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .hover-elevate:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    </style>
</head>
<body class="bg-light">

    <div class="nav-wrapper">
        <?php require_once __DIR__ . '/../nav.php'; ?>
    </div>
    
    <div class="container-fluid px-4 py-4 mt-3 bg-white shadow-sm rounded-3 mb-5" style="width: 98%;">
        <!-- COVER SECTION -->
        <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-3">
            <div>
                <h3 class="mb-1 text-dark fw-bold">Sales Overview</h3>
                <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>. Here is your current performance.</p>
            </div>
        </div>
        
        <!-- Scalable Bootstrap 5 UI Placeholders -->
        <div class="row g-4 mb-4 mt-2">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3 hover-elevate h-100" style="background: #ebf5fb; border-left: 4px solid #2980b9 !important;">
                    <div class="card-body text-center py-4">
                        <h6 class="text-uppercase text-muted fw-bold">Monthly Target</h6>
                        <h3 class="fw-bold text-dark mb-0">Rs. 500,000</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3 hover-elevate h-100" style="background: #e8f8f5; border-left: 4px solid #1abc9c !important;">
                    <div class="card-body text-center py-4">
                        <h6 class="text-uppercase text-muted fw-bold">Achieved Sales</h6>
                        <h3 class="fw-bold text-success mb-0">Rs. 250,000</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-3 hover-elevate h-100" style="background: #fef9e7; border-left: 4px solid #f39c12 !important;">
                    <div class="card-body text-center py-4">
                        <h6 class="text-uppercase text-muted fw-bold">Active Leads</h6>
                        <h3 class="fw-bold text-warning mb-0">12</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 2 -->
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 text-dark fw-bold">Recent Sales Orders</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 15px;">
                        <thead class="table-light">
                            <tr>
                                <th class="border-bottom-0 py-3 px-4">Order ID</th>
                                <th class="border-bottom-0 py-3 px-4">Customer</th>
                                <th class="border-bottom-0 py-3 px-4">Date</th>
                                <th class="border-bottom-0 py-3 px-4">Amount</th>
                                <th class="border-bottom-0 py-3 px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="py-3 px-4"><strong>#ORD-0012</strong></td>
                                <td class="py-3 px-4">Supermart Downtown</td>
                                <td class="py-3 px-4">Today</td>
                                <td class="py-3 px-4">Rs. 15,000</td>
                                <td class="py-3 px-4"><span class="dist-badge" style="background:#2ecc71;">Delivered</span></td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4"><strong>#ORD-0011</strong></td>
                                <td class="py-3 px-4">City Grocers</td>
                                <td class="py-3 px-4">Yesterday</td>
                                <td class="py-3 px-4">Rs. 8,500</td>
                                <td class="py-3 px-4"><span class="dist-badge" style="background:#3498db;">In Transit</span></td>
                            </tr>
                            <tr>
                                <td class="py-3 px-4"><strong>#ORD-0010</strong></td>
                                <td class="py-3 px-4">Express Grocers</td>
                                <td class="py-3 px-4">17 Jul 2026</td>
                                <td class="py-3 px-4">Rs. 25,000</td>
                                <td class="py-3 px-4"><span class="dist-badge" style="background:#e74c3c;">Pending</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
