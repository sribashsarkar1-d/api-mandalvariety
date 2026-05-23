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

<div class="w-100">
    <?php include 'includes/topbar.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1">Dashboard</h4>
                <p class="text-muted mb-0">Welcome, <?= e($_SESSION['admin_name'] ?? 'Admin') ?></p>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card p-3 bg-primary text-white shadow-sm border-0">
                    <h6>Products</h6>
                    <h3><?= (int)$totalProducts ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 bg-success text-white shadow-sm border-0">
                    <h6>Orders</h6>
                    <h3><?= (int)$totalOrders ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 bg-warning text-dark shadow-sm border-0">
                    <h6>Customers</h6>
                    <h3><?= (int)$totalCustomers ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 bg-dark text-white shadow-sm border-0">
                    <h6>Revenue</h6>
                    <h3>₹<?= number_format((float)$totalRevenue, 2) ?></h3>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0">
                    <h6>Pending Orders</h6>
                    <h3><?= (int)$pendingOrders ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0">
                    <h6>Delivered Orders</h6>
                    <h3><?= (int)$deliveredOrders ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-3 shadow-sm border-0">
                    <h6>Cancelled Orders</h6>
                    <h3><?= (int)$cancelledOrders ?></h3>
                </div>
            </div>
        </div>

        <div class="card p-3 shadow-sm border-0">
            <div class="d-flex justify-content-between mb-3">
                <h5 class="mb-0">Recent Orders</h5>
                <a href="orders/list.php" class="btn btn-sm btn-primary">View All</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recentOrders)): ?>
                            <?php foreach ($recentOrders as $o): ?>
                                <tr>
                                    <td><?= (int)$o['id'] ?></td>
                                    <td><?= e($o['name'] ?? 'N/A') ?></td>
                                    <td>₹<?= number_format((float)$o['total_amount'], 2) ?></td>
                                    <td>
                                        <span class="badge <?= $o['status'] === 'pending' ? 'bg-warning text-dark' : ($o['status'] === 'delivered' ? 'bg-success' : ($o['status'] === 'cancelled' ? 'bg-danger' : 'bg-secondary')) ?>">
                                            <?= e(ucfirst($o['status'])) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No orders found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>