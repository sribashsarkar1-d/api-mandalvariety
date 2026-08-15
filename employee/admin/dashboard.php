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

// Customer Due Stats
$stmt = $pdo->query("
    SELECT SUM(t.current_due) as total_baki, COUNT(t.customer_id) as customers_with_due
    FROM (
        SELECT l.customer_id, 
        (
            SELECT new_due 
            FROM employee_customer_ledger cl 
            WHERE cl.customer_id = l.customer_id 
            ORDER BY cl.created_at DESC, cl.id DESC 
            LIMIT 1
        ) as current_due
        FROM employee_customer_ledger l
        GROUP BY l.customer_id
    ) t
    WHERE t.current_due > 0
");
$dueStats = $stmt->fetch();
$totalBaki = $dueStats['total_baki'] ?? 0;
$customersWithDue = $dueStats['customers_with_due'] ?? 0;

// Recent Activities (Latest 5 Sales)
$stmt = $pdo->query("
    SELECT s.id, s.invoice_number, s.grand_total, s.created_at, u.name as employee_name
    FROM employee_sales s
    LEFT JOIN employee_users u ON s.employee_id = u.id
    ORDER BY s.created_at DESC
    LIMIT 5
");
$recentSales = $stmt->fetchAll();

// Top Selling Products
$stmt = $pdo->query("
    SELECT p.name, p.image, c.name as category_name, p.selling_price, SUM(i.quantity) as total_sold
    FROM employee_sale_items i
    JOIN employee_products p ON i.product_id = p.id
    LEFT JOIN employee_categories c ON p.category_id = c.id
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 3
");
$topProducts = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<!-- Desktop Date Filter -->
<div class="d-none d-lg-flex justify-content-end mb-4">
    <div class="dropdown">
        <button class="btn btn-light bg-white border shadow-sm px-4 py-2" type="button" data-bs-toggle="dropdown" style="border-radius: 8px;">
            Today - <?= date('d M Y') ?>
            <i class="bi bi-chevron-down ms-3 text-muted"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
            <li><a class="dropdown-item py-2" href="#">Today</a></li>
            <li><a class="dropdown-item py-2" href="#">Yesterday</a></li>
            <li><a class="dropdown-item py-2" href="#">This Week</a></li>
            <li><a class="dropdown-item py-2" href="#">This Month</a></li>
        </ul>
    </div>
</div>

<div class="row g-3 g-lg-4 mb-4">
    <div class="col-6 col-md-4">
        <div class="stat-card stat-sales">
            <div class="stat-card-header">
                <span class="stat-card-label">Today's Sales</span>
                <i class="bi bi-graph-up-arrow stat-card-icon"></i>
            </div>
            <div class="stat-card-value"><?= format_currency($todaySales) ?></div>
            <div class="stat-card-meta"><span class="text-success-accent">+12.5%</span> vs yesterday</div>
        </div>
    </div>
    
    <div class="col-6 col-md-4">
        <div class="stat-card stat-bills">
            <div class="stat-card-header">
                <span class="stat-card-label">Today's Bills</span>
                <i class="bi bi-receipt stat-card-icon"></i>
            </div>
            <div class="stat-card-value"><?= $todayBills ?></div>
            <div class="stat-card-meta"><span class="text-success-accent">+5%</span> vs yesterday</div>
        </div>
    </div>
    
    <div class="col-6 col-md-4">
        <div class="stat-card stat-products">
            <div class="stat-card-header">
                <span class="stat-card-label">Total Products</span>
                <i class="bi bi-box-seam stat-card-icon"></i>
            </div>
            <div class="stat-card-value"><?= $totalProducts ?></div>
            <div class="stat-card-meta"><span class="text-success-accent">+3.2%</span> vs last month</div>
        </div>
    </div>
    
    <div class="col-6 col-md-4">
        <div class="stat-card stat-lowstock">
            <div class="stat-card-header">
                <span class="stat-card-label">Low Stock</span>
                <i class="bi bi-exclamation-triangle stat-card-icon"></i>
            </div>
            <div class="stat-card-value"><?= $lowStock ?></div>
            <div class="stat-card-meta">Products need attention</div>
        </div>
    </div>
    
    <div class="col-6 col-md-4">
        <div class="stat-card stat-outstock">
            <div class="stat-card-header">
                <span class="stat-card-label">Out of Stock</span>
                <i class="bi bi-x-circle stat-card-icon"></i>
            </div>
            <div class="stat-card-value text-danger"><?= $outOfStock ?></div>
            <div class="stat-card-meta">Items unavailable</div>
        </div>
    </div>
    
    <div class="col-6 col-md-4">
        <div class="stat-card stat-expiring">
            <div class="stat-card-header">
                <span class="stat-card-label">Expiring Soon</span>
                <i class="bi bi-calendar-x stat-card-icon"></i>
            </div>
            <div class="stat-card-value"><?= $expiringSoon ?></div>
            <div class="stat-card-meta">Check inventory</div>
        </div>
    </div>
</div>

<!-- Add Customer Baki Card Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="custom-card p-3 border-start border-danger border-4 d-flex justify-content-between align-items-center bg-light">
            <div>
                <div class="text-danger fw-bold small mb-1">TOTAL OUTSTANDING BAKI</div>
                <h3 class="mb-0 fw-bold"><?= format_currency($totalBaki) ?></h3>
                <div class="text-muted small mt-1"><?= $customersWithDue ?> Customers with due</div>
            </div>
            <div>
                <a href="customer-dues.php" class="btn btn-outline-danger">View Due Customers</a>
            </div>
        </div>
    </div>
</div>

<div class="custom-card p-4">
    <div class="d-flex justify-content-between align-items-start mb-1">
        <h6 class="fw-bold mb-0">Today's Sales Overview</h6>
        <div class="d-none d-lg-block dropdown">
            <button class="btn btn-sm btn-light bg-white border text-muted" data-bs-toggle="dropdown">
                Today <i class="bi bi-chevron-down ms-1"></i>
            </button>
        </div>
        <span class="text-success small fw-bold d-lg-none">+12.5%</span>
    </div>
    <div class="d-flex justify-content-between align-items-end mb-4">
        <h3 class="fw-bold mb-0"><?= format_currency($todaySales) ?></h3>
        <div class="d-none d-lg-block text-end">
            <span class="text-success small fw-bold d-block">+12.5%</span>
            <span class="text-muted small" style="font-size: 0.7rem;">vs yesterday</span>
        </div>
    </div>
    <div style="position: relative; height:250px;">
        <canvas id="salesChart"></canvas>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-12 col-lg-6">
        <div class="custom-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                <h6 class="fw-bold mb-0">Top Selling Products</h6>
                <a href="products.php" class="text-primary text-decoration-none small fw-semibold">View all</a>
            </div>
            
            <div class="list-group list-group-flush">
                <?php if (empty($topProducts)): ?>
                    <p class="text-muted small">No products sold yet.</p>
                <?php else: ?>
                    <?php foreach ($topProducts as $tp): ?>
                        <?php $img = empty($tp['image']) ? BASE_URL . '/assets/images/no-image.png' : BASE_URL . '/uploads/products/' . $tp['image']; ?>
                        <div class="activity-item">
                            <img src="<?= $img ?>" class="activity-icon border bg-light" style="object-fit: cover;">
                            <div class="activity-details">
                                <div class="activity-title"><?= htmlspecialchars($tp['name']) ?></div>
                                <div class="activity-meta"><?= htmlspecialchars($tp['category_name'] ?? 'Uncategorized') ?></div>
                            </div>
                            <div class="text-end">
                                <div class="activity-value"><?= format_currency($tp['selling_price']) ?></div>
                                <div class="activity-meta"><?= $tp['total_sold'] ?> sold</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-lg-6">
        <div class="custom-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                <h6 class="fw-bold mb-0">Recent Activities</h6>
                <a href="sales.php" class="text-primary text-decoration-none small fw-semibold">View all</a>
            </div>
            
            <div class="list-group list-group-flush">
                <?php if (empty($recentSales)): ?>
                    <p class="text-muted small">No recent activity.</p>
                <?php else: ?>
                    <?php foreach ($recentSales as $rs): ?>
                        <div class="activity-item">
                            <div class="activity-icon bg-success bg-opacity-10 text-success">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <div class="activity-details">
                                <div class="activity-title">New sale of <?= format_currency($rs['grand_total']) ?></div>
                                <div class="activity-meta"><?= date('d M Y, h:i A', strtotime($rs['created_at'])) ?> • By <?= htmlspecialchars($rs['employee_name']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
$extra_js = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById("salesChart").getContext("2d");
        
        let gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, "rgba(159, 122, 234, 0.4)");   
        gradient.addColorStop(1, "rgba(159, 122, 234, 0.0)");
        
        new Chart(ctx, {
            type: "line",
            data: {
                labels: ["12 AM", "3 AM", "6 AM", "9 AM", "12 PM", "3 PM", "6 PM", "9 PM"],
                datasets: [{
                    label: "Sales",
                    data: [0, 100, 250, 400, 800, 650, 1200, 950],
                    borderColor: "#9f7aea",
                    borderWidth: 3,
                    pointBackgroundColor: "#fff",
                    pointBorderColor: "#9f7aea",
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4,
                    fill: true,
                    backgroundColor: gradient
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { padding: 12, backgroundColor: "#2d3748", titleFont: { size: 13, family: "Inter" }, bodyFont: { size: 14, weight: "bold", family: "Inter" } } },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { borderDash: [4, 4], color: "#e2e8f0" },
                        border: { display: false },
                        ticks: { font: { family: "Inter", size: 11 }, color: "#718096" }
                    },
                    x: { 
                        grid: { display: false },
                        border: { display: false },
                        ticks: { font: { family: "Inter", size: 11 }, color: "#718096" }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: "index",
                },
            }
        });
    });
</script>
';
require_once '../includes/footer.php'; 
?>
