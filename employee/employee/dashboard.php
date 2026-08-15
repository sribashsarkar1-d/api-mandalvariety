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

<div class="row align-items-center mb-4">
    <div class="col-8">
        <p class="text-muted mb-0 small">Welcome back,</p>
        <h4 class="fw-bold mb-0"><?= htmlspecialchars($_SESSION['user_name']) ?></h4>
    </div>
    <div class="col-4 text-end">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user_name']) ?>&background=0D6EFD&color=fff" alt="Profile" class="rounded-circle" width="45" height="45">
    </div>
</div>

<div class="row g-3">
    <div class="col-6">
        <div class="stat-card bg-teal">
            <div>
                <p class="label">My Sales Today</p>
                <h3 class="value"><?= format_currency($todaySales) ?></h3>
            </div>
            <i class="bi bi-graph-up-arrow"></i>
        </div>
    </div>
    <div class="col-6">
        <div class="stat-card bg-blue">
            <div>
                <p class="label">My Bills Today</p>
                <h3 class="value"><?= $todayBills ?></h3>
            </div>
            <i class="bi bi-receipt"></i>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="pos.php" class="btn btn-primary w-100 py-3 rounded-3 shadow-sm d-flex justify-content-between align-items-center">
        <span class="fs-5 fw-bold"><i class="bi bi-cart me-2"></i> Start New Bill</span>
        <i class="bi bi-arrow-right-circle fs-4"></i>
    </a>
</div>

<?php 
require_once '../includes/footer.php'; 
?>
