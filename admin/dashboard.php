<?php
require_once 'includes/config.php';

if (!isset($_SESSION['admin_id']) || ($_SESSION['admin_role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit;
}

$totalProducts = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalCustomers = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$totalCategories = $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn();

$pendingOrders = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$deliveredOrders = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'delivered'")->fetchColumn();
$cancelledOrders = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'cancelled'")->fetchColumn();

$totalRevenue = $conn->query("
    SELECT COALESCE(SUM(total_amount), 0)
    FROM orders
    WHERE status = 'delivered'
")->fetchColumn();

$recentOrders = $conn->query("
    SELECT 
        o.id,
        o.total_amount,
        o.status,
        o.created_at,
        u.name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.id DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>
<style>
    .dashboard-container {
        padding: 24px;
        background-color: #f8fafc;
        min-height: calc(100vh - 70px);
    }
    .stat-card {
        border-radius: 20px;
        border: none;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        z-index: 1;
        padding: 24px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.12);
    }
    .stat-card::after {
        content: '';
        position: absolute;
        top: 0; right: 0; bottom: 0; left: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 100%);
        z-index: -1;
    }
    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        background: rgba(255,255,255,0.2);
        color: #fff;
        backdrop-filter: blur(5px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .stat-title {
        font-size: 0.95rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.9;
        margin-bottom: 8px;
    }
    .stat-value {
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 0;
        line-height: 1.1;
    }
    
    .bg-gradient-primary { background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); color: #fff; }
    .bg-gradient-success { background: linear-gradient(135deg, #10b981 0%, #34d399 100%); color: #fff; }
    .bg-gradient-warning { background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); color: #fff; }
    .bg-gradient-info { background: linear-gradient(135deg, #0ea5e9 0%, #38bdf8 100%); color: #fff; }
    
    .status-card {
        border-radius: 20px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }
    .status-card:hover {
        box-shadow: 0 12px 24px rgba(0,0,0,0.06);
        border-color: #cbd5e1;
        transform: translateY(-4px);
    }
    .status-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        flex-shrink: 0;
    }
    .status-icon.pending { background: #fef3c7; color: #d97706; }
    .status-icon.delivered { background: #d1fae5; color: #059669; }
    .status-icon.cancelled { background: #fee2e2; color: #dc2626; }
    
    .status-info h6 {
        font-size: 1rem;
        color: #64748b;
        margin-bottom: 4px;
        font-weight: 600;
    }
    .status-info h3 {
        font-size: 1.8rem;
        color: #0f172a;
        margin-bottom: 0;
        font-weight: 800;
    }
    
    .recent-orders-card {
        border-radius: 20px;
        border: none;
        background: #fff;
        box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .recent-orders-card .card-header {
        background: transparent;
        border-bottom: 1px solid #f1f5f9;
        padding: 24px 28px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .recent-orders-card .card-header h5 {
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    .btn-custom {
        background: #f1f5f9;
        color: #334155;
        border-radius: 12px;
        font-weight: 600;
        padding: 8px 20px;
        transition: all 0.2s;
        border: none;
    }
    .btn-custom:hover {
        background: #3b82f6;
        color: #fff;
    }
    .table-modern {
        margin-bottom: 0;
    }
    .table-modern th {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 18px 28px;
        border-top: none;
    }
    .table-modern td {
        padding: 18px 28px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        font-weight: 500;
    }
    .table-modern tbody tr {
        transition: background-color 0.2s ease;
    }
    .table-modern tbody tr:hover {
        background-color: #f8fafc;
    }
    .badge-modern {
        padding: 8px 14px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.75rem;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }
    .badge-pending { background: #fef3c7; color: #d97706; }
    .badge-delivered { background: #d1fae5; color: #059669; }
    .badge-cancelled { background: #fee2e2; color: #dc2626; }
    .badge-secondary { background: #f1f5f9; color: #64748b; }
    
    .dark-mode .dashboard-container { background: #0f172a; }
    .dark-mode .status-card { background: #1e293b; border-color: #334155; }
    .dark-mode .status-info h3, .dark-mode .recent-orders-card .card-header h5 { color: #f8fafc; }
    .dark-mode .recent-orders-card { background: #1e293b; }
    .dark-mode .table-modern th { background: #0f172a; border-bottom-color: #334155; color: #94a3b8; }
    .dark-mode .table-modern td { border-bottom-color: #334155; color: #cbd5e1; }
    .dark-mode .table-modern tbody tr:hover { background-color: #0f172a; }
    .dark-mode .recent-orders-card .card-header { border-bottom-color: #334155; }
    .dark-mode .btn-custom { background: #334155; color: #f8fafc; }
    .dark-mode .btn-custom:hover { background: #3b82f6; color: #fff; }
</style>

<div class="w-100">
    <?php include 'includes/topbar.php'; ?>

    <div class="dashboard-container">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h3 class="mb-1 fw-bold" style="color: var(--bs-heading-color);">Dashboard Overview</h3>
                <p class="text-muted mb-0 fs-6">Welcome back, <strong><?= e($_SESSION['admin_name'] ?? 'Admin') ?></strong>! Here's what's happening.</p>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-gradient-primary">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-title">Total Products</div>
                            <h3 class="stat-value"><?= (int)$totalProducts ?></h3>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon" style="width:1em; height:1em;"><use href="#icon-products"></use></svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-gradient-success">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-title">Total Orders</div>
                            <h3 class="stat-value"><?= (int)$totalOrders ?></h3>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon" style="width:1em; height:1em;"><use href="#icon-orders"></use></svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-gradient-warning">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-title">Customers</div>
                            <h3 class="stat-value"><?= (int)$totalCustomers ?></h3>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon" style="width:1em; height:1em;"><use href="#icon-customers"></use></svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-gradient-info">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-title">Revenue</div>
                            <h3 class="stat-value">₹<?= number_format((float)$totalRevenue, 2) ?></h3>
                        </div>
                        <div class="stat-icon">
                            <svg class="icon" style="width:1em; height:1em;"><use href="#icon-analytics"></use></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="status-card">
                    <div class="status-icon pending">
                        <svg class="icon" style="width:1em; height:1em;"><use href="#icon-review"></use></svg>
                    </div>
                    <div class="status-info">
                        <h6>Pending Orders</h6>
                        <h3><?= (int)$pendingOrders ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="status-card">
                    <div class="status-icon delivered">
                        <svg class="icon" style="width:1em; height:1em;"><use href="#icon-orders"></use></svg>
                    </div>
                    <div class="status-info">
                        <h6>Delivered Orders</h6>
                        <h3><?= (int)$deliveredOrders ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="status-card">
                    <div class="status-icon cancelled">
                        <svg class="icon" style="width:1em; height:1em;"><use href="#icon-settings"></use></svg>
                    </div>
                    <div class="status-info">
                        <h6>Cancelled Orders</h6>
                        <h3><?= (int)$cancelledOrders ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="recent-orders-card">
            <div class="card-header">
                <h5>Recent Orders</h5>
                <a href="orders/list.php" class="btn-custom text-decoration-none">View All</a>
            </div>

            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentOrders)): ?>
                            <?php foreach ($recentOrders as $o): ?>
                                <tr>
                                    <td class="fw-bold">#<?= (int)$o['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 14px;">
                                                <?= strtoupper(substr($o['name'] ?? 'U', 0, 1)) ?>
                                            </div>
                                            <?= e($o['name'] ?? 'N/A') ?>
                                        </div>
                                    </td>
                                    <td class="fw-bold">₹<?= number_format((float)$o['total_amount'], 2) ?></td>
                                    <td>
                                        <?php 
                                            $badgeClass = 'badge-secondary';
                                            if ($o['status'] === 'pending') $badgeClass = 'badge-pending';
                                            elseif ($o['status'] === 'delivered') $badgeClass = 'badge-delivered';
                                            elseif ($o['status'] === 'cancelled') $badgeClass = 'badge-cancelled';
                                        ?>
                                        <span class="badge-modern <?= $badgeClass ?>">
                                            <?= e(ucfirst($o['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted"><?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No recent orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>