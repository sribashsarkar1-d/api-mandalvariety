<?php
require_once '../config/database.php';
require_once '../config/constants.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'Reports';

// Basic summary stats
$stmt = $pdo->query("SELECT SUM(grand_total) as total_sales FROM employee_sales WHERE DATE(created_at) = CURDATE()");
$today_sales = $stmt->fetch()['total_sales'] ?? 0;

$stmt = $pdo->query("SELECT SUM(amount) as total_expenses FROM employee_expenses WHERE expense_date = CURDATE()");
$today_expenses = $stmt->fetch()['total_expenses'] ?? 0;

require_once '../includes/header.php';
?>

<div class="row g-2 mb-3">
    <div class="col-6">
        <div class="custom-card p-3 text-center h-100 bg-primary text-white border-0">
            <h6 class="mb-1" style="color: rgba(255,255,255,0.8);">Today's Sales</h6>
            <h4 class="fw-bold mb-0"><?= format_currency($today_sales) ?></h4>
        </div>
    </div>
    <div class="col-6">
        <div class="custom-card p-3 text-center h-100 bg-danger text-white border-0">
            <h6 class="mb-1" style="color: rgba(255,255,255,0.8);">Today's Expenses</h6>
            <h4 class="fw-bold mb-0"><?= format_currency($today_expenses) ?></h4>
        </div>
    </div>
</div>

<div class="custom-card p-3">
    <h6 class="fw-bold mb-3"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Generate Report</h6>
    
    <form action="sales.php" method="GET">
        <div class="mb-3">
            <label class="form-label text-muted small">Report Type</label>
            <select class="form-select">
                <option value="sales">Sales Report</option>
                <option value="purchases">Purchase Report</option>
                <option value="expenses">Expense Report</option>
                <option value="inventory">Inventory Report</option>
            </select>
        </div>
        
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label class="form-label text-muted small">From Date</label>
                <input type="date" class="form-control" value="<?= date('Y-m-01') ?>">
            </div>
            <div class="col-6">
                <label class="form-label text-muted small">To Date</label>
                <input type="date" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 py-2"><i class="bi bi-file-earmark-text me-2"></i>Generate</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
