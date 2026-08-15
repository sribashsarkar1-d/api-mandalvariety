<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'Dashboard';

// Fetch stats
$today = date('Y-m-d');

// Today's Sales
$stmt = $pdo->prepare("SELECT SUM(grand_total) as total_sales, COUNT(id) as total_bills FROM employee_sales WHERE DATE(created_at) = ?");
$stmt->execute([$today]);
$salesData = $stmt->fetch();
$todaySales = $salesData['total_sales'] ?? 0;
$todayBills = $salesData['total_bills'] ?? 0;

// Total Products
$stmt = $pdo->query("SELECT COUNT(id) as total FROM employee_products WHERE status != 'expired'");
$totalProducts = $stmt->fetch()['total'];

// Low Stock & Out of Stock
$stmt = $pdo->query("
    SELECT 
        SUM(CASE WHEN stock_status = 'low_stock' THEN 1 ELSE 0 END) as low_stock,
        SUM(CASE WHEN stock_status = 'out_of_stock' THEN 1 ELSE 0 END) as out_of_stock
    FROM employee_product_stock
");
$stockData = $stmt->fetch();
$lowStock = $stockData['low_stock'] ?? 0;
$outOfStock = $stockData['out_of_stock'] ?? 0;

// Expiring Soon (Next 30 days)
$stmt = $pdo->query("SELECT COUNT(id) as total FROM employee_products WHERE expiry_date IS NOT NULL AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
$expiringSoon = $stmt->fetch()['total'];

require_once '../includes/header.php';
?>

<div class="row align-items-center mb-4">
    <div class="col-8">
        <p class="text-muted mb-0 small">Welcome back,</p>
        <h4 class="fw-bold mb-0"><?= htmlspecialchars($_SESSION['user_name']) ?></h4>
    </div>
    <div class="col-4 text-end">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name']) ?>&background=0D6EFD&color=fff" alt="Profile" class="rounded-circle" width="45" height="45">
    </div>
</div>

<div class="dropdown mb-4">
    <button class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown">
        Today - <?= date('d M Y') ?>
        <i class="bi bi-chevron-down"></i>
    </button>
    <ul class="dropdown-menu w-100">
        <li><a class="dropdown-item" href="#">Today</a></li>
        <li><a class="dropdown-item" href="#">Yesterday</a></li>
        <li><a class="dropdown-item" href="#">This Week</a></li>
        <li><a class="dropdown-item" href="#">This Month</a></li>
    </ul>
</div>

<div class="row g-3">
    <div class="col-6 col-md-4">
        <div class="stat-card bg-teal">
            <div>
                <p class="label">Today's Sales</p>
                <h3 class="value"><?= format_currency($todaySales) ?></h3>
            </div>
            <i class="bi bi-graph-up-arrow"></i>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card bg-blue">
            <div>
                <p class="label">Today's Bills</p>
                <h3 class="value"><?= $todayBills ?></h3>
            </div>
            <i class="bi bi-receipt"></i>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card bg-purple">
            <div>
                <p class="label">Total Products</p>
                <h3 class="value"><?= $totalProducts ?></h3>
            </div>
            <i class="bi bi-box-seam"></i>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card bg-orange">
            <div>
                <p class="label">Low Stock</p>
                <h3 class="value"><?= $lowStock ?></h3>
            </div>
            <i class="bi bi-exclamation-triangle"></i>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card bg-red">
            <div>
                <p class="label">Out of Stock</p>
                <h3 class="value"><?= $outOfStock ?></h3>
            </div>
            <i class="bi bi-x-circle"></i>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="stat-card" style="background-color: #20c997;">
            <div>
                <p class="label">Expiring Soon</p>
                <h3 class="value"><?= $expiringSoon ?></h3>
            </div>
            <i class="bi bi-calendar-x"></i>
        </div>
    </div>
</div>

<div class="custom-card p-3 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0">Today's Sales Overview</h6>
        <span class="text-success small fw-bold">+12.5%</span>
    </div>
    <h3 class="fw-bold mb-4"><?= format_currency($todaySales) ?></h3>
    <canvas id="salesChart" height="200"></canvas>
</div>

<?php 
$extra_js = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById("salesChart").getContext("2d");
    new Chart(ctx, {
        type: "line",
        data: {
            labels: ["12 AM", "6 AM", "12 PM", "6 PM", "12 AM"],
            datasets: [{
                label: "Sales",
                data: [0, 5000, 10000, 7000, 12450],
                borderColor: "#0d6efd",
                tension: 0.4,
                fill: true,
                backgroundColor: "rgba(13, 110, 253, 0.1)"
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false } }
            }
        }
    });
</script>
';
require_once '../includes/footer.php'; 
?>
