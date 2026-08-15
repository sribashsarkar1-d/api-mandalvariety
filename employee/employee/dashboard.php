<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_employee();

$page_title = 'Home';

// Fetch stats for employee
$today = date('Y-m-d');

// Today's Sales for this employee
$stmt = $pdo->prepare("SELECT SUM(grand_total) as total_sales, COUNT(id) as total_bills FROM employee_sales WHERE DATE(created_at) = ? AND employee_id = ?");
$stmt->execute([$today, $_SESSION['user_id']]);
$salesData = $stmt->fetch();
$todaySales = $salesData['total_sales'] ?? 0;
$todayBills = $salesData['total_bills'] ?? 0;

require_once '../includes/header.php';
?>

<!-- Desktop Date Filter (similar to admin) -->
<div class="d-none d-lg-flex justify-content-end mb-4">
    <div class="dropdown">
        <button class="btn btn-light bg-white border shadow-sm px-4 py-2" type="button" data-bs-toggle="dropdown" style="border-radius: 8px;">
            Today - <?= date('d M Y') ?>
            <i class="bi bi-chevron-down ms-3 text-muted"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
            <li><a class="dropdown-item py-2" href="#">Today</a></li>
            <li><a class="dropdown-item py-2" href="#">Yesterday</a></li>
        </ul>
    </div>
</div>

<div class="row g-3 g-lg-4 mb-4 mt-2">
    <div class="col-6 col-md-6">
        <div class="stat-card stat-sales">
            <div class="stat-card-header">
                <span class="stat-card-label">My Sales Today</span>
                <i class="bi bi-graph-up-arrow stat-card-icon"></i>
            </div>
            <div class="stat-card-value"><?= format_currency($todaySales) ?></div>
            <div class="stat-card-meta"><span class="text-success-accent">+5%</span> vs yesterday</div>
        </div>
    </div>
    
    <div class="col-6 col-md-6">
        <div class="stat-card stat-bills">
            <div class="stat-card-header">
                <span class="stat-card-label">My Bills Today</span>
                <i class="bi bi-receipt stat-card-icon"></i>
            </div>
            <div class="stat-card-value"><?= $todayBills ?></div>
            <div class="stat-card-meta"><span class="text-success-accent">+2</span> vs yesterday</div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="pos.php" class="btn btn-primary w-100 py-3 shadow-sm d-flex justify-content-between align-items-center" style="border-radius: var(--radius-lg);">
        <span class="fs-5 fw-bold"><i class="bi bi-cart me-2"></i> Start New Bill</span>
        <i class="bi bi-arrow-right-circle fs-4"></i>
    </a>
</div>

<?php 
require_once '../includes/footer.php'; 
?>
