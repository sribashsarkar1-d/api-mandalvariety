<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<?php
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

function orderStatusBadge($status)
{
    $status = strtolower(trim((string)$status));

    return match ($status) {
        'pending' => '<span class="badge text-bg-warning">Pending</span>',
        'confirmed' => '<span class="badge text-bg-info">Confirmed</span>',
        'processing' => '<span class="badge text-bg-primary">Processing</span>',
        'shipped' => '<span class="badge text-bg-secondary">Shipped</span>',
        'out_for_delivery' => '<span class="badge text-bg-dark">Out for Delivery</span>',
        'delivered' => '<span class="badge text-bg-success">Delivered</span>',
        'cancelled' => '<span class="badge text-bg-danger">Cancelled</span>',
        default => '<span class="badge bg-light text-dark border">' . e(ucwords(str_replace('_', ' ', $status ?: 'unknown'))) . '</span>',
    };
}

function paymentStatusBadge($status)
{
    $status = strtolower(trim((string)$status));

    return match ($status) {
        'paid' => '<span class="badge text-bg-success">Paid</span>',
        'pending' => '<span class="badge text-bg-warning">Pending</span>',
        'failed' => '<span class="badge text-bg-danger">Failed</span>',
        'refunded' => '<span class="badge text-bg-info">Refunded</span>',
        default => '<span class="badge bg-light text-dark border">' . e(ucwords($status ?: 'unknown')) . '</span>',
    };
}

function trackingBadge($status)
{
    $status = strtolower(trim((string)$status));

    return match ($status) {
        'ordered' => '<span class="badge text-bg-secondary">Ordered</span>',
        'packed' => '<span class="badge text-bg-info">Packed</span>',
        'shipped' => '<span class="badge text-bg-primary">Shipped</span>',
        'on_the_way' => '<span class="badge text-bg-warning">On The Way</span>',
        'delivered' => '<span class="badge text-bg-success">Delivered</span>',
        default => '<span class="badge bg-light text-dark border">' . e(ucwords(str_replace('_', ' ', $status ?: 'n/a'))) . '</span>',
    };
}

$search = trim($_GET['search'] ?? '');
$status = trim($_GET['status'] ?? '');
$payment = trim($_GET['payment'] ?? '');
$dateFilter = trim($_GET['date_filter'] ?? '');
$limit = (int)($_GET['limit'] ?? 20);
if (!in_array($limit, [10, 20, 50, 100], true)) {
    $limit = 20;
}

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(u.name LIKE :search OR u.email LIKE :search OR o.order_number LIKE :search OR o.pincode LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($status !== '') {
    $where[] = "o.status = :status";
    $params[':status'] = $status;
}

if ($payment !== '') {
    $where[] = "o.payment_status = :payment";
    $params[':payment'] = $payment;
}

if ($dateFilter === 'today') {
    $where[] = "DATE(o.created_at) = CURDATE()";
} elseif ($dateFilter === 'recent7') {
    $where[] = "DATE(o.created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($dateFilter === 'recent30') {
    $where[] = "DATE(o.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$statsSql = "
    SELECT
        COUNT(*) AS total_orders,
        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today_orders,
        SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS recent_orders,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS delivered_orders,
        SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) AS paid_orders
    FROM orders
";
$stats = $conn->query($statsSql)->fetch(PDO::FETCH_ASSOC);

$sql = "
    SELECT 
        o.*,
        u.name AS user_name,
        u.email AS user_email,
        u.phone AS user_phone,
        COUNT(oi.id) AS total_items,
        COALESCE(SUM(oi.quantity), 0) AS total_qty
    FROM orders o
    INNER JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON oi.order_id = o.id
    $whereSql
    GROUP BY o.id
    ORDER BY o.id DESC
    LIMIT :limit_rows
";

$stmt = $conn->prepare($sql);

foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val, PDO::PARAM_STR);
}
$stmt->bindValue(':limit_rows', $limit, PDO::PARAM_INT);
$stmt->execute();

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

  <style>
.orders-page {
    background: #f8fafc;
    min-height: 100vh;
    overflow-x: hidden;
}

.orders-page .page-title {
    font-weight: 800;
    font-size: 1.75rem;
    line-height: 1.2;
    color: #0f172a;
    margin-bottom: 4px;
}

.orders-page .page-subtitle {
    color: #64748b;
    font-size: .9rem;
    margin-bottom: 0;
}

.orders-page .stats-card,
.orders-page .filter-card,
.orders-page .table-card {
    border: 0;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(2, 6, 23, 0.06);
    overflow: hidden;
    background: #fff;
}

.orders-page .stats-card .card-body,
.orders-page .filter-card .card-body,
.orders-page .table-card .card-body {
    padding: 18px;
}

.orders-page .stats-label {
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #64748b;
    margin-bottom: 6px;
    font-weight: 700;
}

.orders-page .stats-value {
    font-size: 1.8rem;
    line-height: 1.1;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 4px;
}

.orders-page .stats-note {
    font-size: .88rem;
    color: #94a3b8;
}

.orders-page .gradient-card-1 {
    background: linear-gradient(135deg, #ffffff 0%, #eef6ff 100%);
}

.orders-page .gradient-card-2 {
    background: linear-gradient(135deg, #ffffff 0%, #ecfdf5 100%);
}

.orders-page .gradient-card-3 {
    background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%);
}

.orders-page .gradient-card-4 {
    background: linear-gradient(135deg, #ffffff 0%, #fff7ed 100%);
}

.orders-page .form-label {
    font-weight: 600;
    color: #334155;
    font-size: .88rem;
    margin-bottom: 6px;
}

.orders-page .form-control,
.orders-page .form-select {
    min-height: 42px;
    padding: 0.4rem 0.8rem;
    border-radius: 10px;
    border: 1px solid #dbe2ea;
    box-shadow: none;
    background: #fff;
}

.orders-page .form-control:focus,
.orders-page .form-select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.12);
}

.orders-page .btn {
    border-radius: 10px;
    min-height: 40px;
    font-weight: 600;
    font-size: 0.88rem;
    white-space: nowrap;
}

.orders-page .btn-sm {
    min-height: 32px;
    padding: 0.25rem 0.5rem;
    font-size: 0.85rem;
}

.orders-page .table-card-header {
    padding: 18px;
}

.orders-page .table-title {
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 4px;
    font-size: 1.1rem;
}

.orders-page .table-note {
    color: #64748b;
    font-size: .88rem;
}

.orders-page .table-scroll-area {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    max-height: 65vh;
    border-top: 1px solid #eef2f7;
    scrollbar-width: thin;
    scrollbar-color: #94a3b8 #e2e8f0;
}

.orders-page .table-scroll-area::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}

.orders-page .table-scroll-area::-webkit-scrollbar-track {
    background: #e2e8f0;
    border-radius: 16px;
}

.orders-page .table-scroll-area::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 16px;
}

.orders-page .table-scroll-area::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

.orders-page .table {
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    min-width: auto;
}

.orders-page .table thead th {
    position: sticky;
    top: 0;
    z-index: 5;
    background: #0f172a !important;
    color: #fff !important;
    border: none !important;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    white-space: nowrap;
    padding: 12px 10px;
    text-align: left;
}

.orders-page .table tbody td {
    vertical-align: middle;
    padding: 12px 10px;
    border-top: 1px solid #eef2f7;
    border-right: none;
    border-left: none;
    background: #fff;
    word-break: break-word;
    max-width: 200px;
}

.orders-page .table tbody tr:hover td {
    background: #f8fbff;
}

.orders-page .order-no {
    font-weight: 700;
    color: #0f172a;
}

.orders-page .sub-text {
    color: #64748b;
    font-size: 0.84rem;
    line-height: 1.45;
    white-space: normal;
}

.orders-page .customer-name {
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 2px;
}

.orders-page .customer-box {
    min-width: 200px;
}

.orders-page .price-main {
    font-weight: 800;
    color: #111827;
}

.orders-page .mini-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f1f5f9;
    color: #334155;
    font-size: 0.78rem;
    padding: 4px 8px;
    border-radius: 999px;
    font-weight: 600;
    white-space: nowrap;
}

.orders-page .action-wrap {
    display: flex;
    flex-wrap: nowrap;
    gap: 6px;
    justify-content: flex-end;
}

.orders-page .action-wrap .btn {
    min-width: 72px;
    min-height: 32px;
    padding: 0.3rem 0.6rem;
    border-radius: 8px;
    font-size: 0.8rem;
}

/* Responsive layout */

@media (max-width: 1199.98px) {
    .orders-page .page-title {
        font-size: 1.6rem;
    }

    .orders-page .stats-value {
        font-size: 1.5rem;
    }

    .orders-page .table-card-header {
        padding: 14px;
    }

    .orders-page .table {
        font-size: 0.88rem;
    }
}

@media (max-width: 991.98px) {
    .orders-page .page-title {
        font-size: 1.4rem;
    }

    .orders-page .stats-value {
        font-size: 1.3rem;
    }

    .orders-page .table-scroll-area {
        max-height: 60vh;
    }

    .orders-page .table {
        font-size: 0.84rem;
    }
}

@media (max-width: 767.98px) {
    .orders-page .container-fluid {
        padding-left: 12px;
        padding-right: 12px;
    }

    .orders-page .page-title {
        font-size: 1.3rem;
    }

    .orders-page .stats-card .card-body,
    .orders-page .filter-card .card-body,
    .orders-page .table-card .card-body {
        padding: 12px;
    }

    .orders-page .table-card-header {
        padding: 12px 12px 8px;
    }

    .orders-page .table-card {
        font-size: 0.8rem;
    }

    .orders-page .table-scroll-area {
        max-height: 58vh;
    }

    .orders-page .mini-chip {
        font-size: 0.76rem;
        padding: 3px 6px;
    }

    .orders-page .action-wrap .btn {
        min-width: 64px;
        padding: 0.25rem 0.5rem;
        font-size: 0.78rem;
    }
}
</style>
    <div class="container-fluid mt-4 mb-5 orders-page">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h4 class="page-title">📦 Order Management</h4>
                <div class="page-subtitle">Track all customer orders, today's activity, recent orders, payment status, and delivery progress.</div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stats-card gradient-card-1 h-100">
                    <div class="card-body">
                        <div class="stats-label">Total Orders</div>
                        <div class="stats-value"><?= (int)($stats['total_orders'] ?? 0) ?></div>
                        <div class="stats-note">All orders in database</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stats-card gradient-card-2 h-100">
                    <div class="card-body">
                        <div class="stats-label">Today Orders</div>
                        <div class="stats-value"><?= (int)($stats['today_orders'] ?? 0) ?></div>
                        <div class="stats-note">Orders created today</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stats-card gradient-card-3 h-100">
                    <div class="card-body">
                        <div class="stats-label">Recent Orders</div>
                        <div class="stats-value"><?= (int)($stats['recent_orders'] ?? 0) ?></div>
                        <div class="stats-note">Orders from last 7 days</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stats-card gradient-card-4 h-100">
                    <div class="card-body">
                        <div class="stats-label">Delivered / Paid</div>
                        <div class="stats-value"><?= (int)($stats['delivered_orders'] ?? 0) ?> / <?= (int)($stats['paid_orders'] ?? 0) ?></div>
                        <div class="stats-note">Completed delivery and payment</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card filter-card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-12 col-md-6 col-xl-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" value="<?= e($search) ?>" placeholder="Order no, user, email, pincode">
                    </div>

                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label">Order Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                            <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="shipped" <?= $status === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="out_for_delivery" <?= $status === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                            <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label">Payment</label>
                        <select name="payment" class="form-select">
                            <option value="">All</option>
                            <option value="paid" <?= $payment === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="pending" <?= $payment === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="failed" <?= $payment === 'failed' ? 'selected' : '' ?>>Failed</option>
                            <option value="refunded" <?= $payment === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3 col-xl-2">
                        <label class="form-label">Date Filter</label>
                        <select name="date_filter" class="form-select">
                            <option value="">All</option>
                            <option value="today" <?= $dateFilter === 'today' ? 'selected' : '' ?>>Today</option>
                            <option value="recent7" <?= $dateFilter === 'recent7' ? 'selected' : '' ?>>Last 7 Days</option>
                            <option value="recent30" <?= $dateFilter === 'recent30' ? 'selected' : '' ?>>Last 30 Days</option>
                        </select>
                    </div>

                    <div class="col-6 col-md-3 col-xl-1">
                        <label class="form-label">Show</label>
                        <select name="limit" class="form-select">
                            <option value="10" <?= $limit === 10 ? 'selected' : '' ?>>10</option>
                            <option value="20" <?= $limit === 20 ? 'selected' : '' ?>>20</option>
                            <option value="50" <?= $limit === 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $limit === 100 ? 'selected' : '' ?>>100</option>
                        </select>
                    </div>

                    <div class="col-12 col-xl-2 d-grid d-md-flex gap-2">
                        <button type="submit" class="btn btn-dark flex-fill">Apply</button>
                        <a href="list.php" class="btn btn-outline-secondary flex-fill">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card table-card">
            <div class="table-card-header">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h6 class="table-title">Orders List</h6>
                        <div class="table-note">Showing <?= count($orders) ?> order(s) based on selected filters.</div>
                    </div>
                </div>
            </div>  

            <div class="card-body pt-3">
                <div class="table-scroll-area">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Order Status</th>
                                <th>Payment</th>
                                <th>Tracking</th>
                                <th>Delivery</th>
                                <th>Date</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($orders)): ?>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?= (int)$o['id'] ?></strong>
                                        </td>

                                        <td>
                                            <div class="order-no"><?= e($o['order_number'] ?: 'N/A') ?></div>
                                            <div class="sub-text">Pin: <?= e($o['pincode'] ?: 'N/A') ?></div>
                                        </td>

                                        <td class="customer-box">
                                            <div class="customer-name"><?= e($o['user_name'] ?: 'N/A') ?></div>
                                            <div class="sub-text">
                                                <?= e($o['user_email'] ?: 'N/A') ?><br>
                                                <?= e($o['user_phone'] ?: 'No phone') ?>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="mini-chip"><?= (int)$o['total_items'] ?> line(s)</div>
                                            <div class="sub-text mt-1">Qty: <?= (int)$o['total_qty'] ?></div>
                                        </td>

                                        <td>
                                            <div class="price-main">₹<?= number_format((float)($o['grand_total'] ?? 0), 2) ?></div>
                                            <div class="sub-text">
                                                Items: ₹<?= number_format((float)($o['total_amount'] ?? 0), 2) ?><br>
                                                Delivery: ₹<?= number_format((float)($o['delivery_charge'] ?? 0), 2) ?>
                                            </div>
                                        </td>

                                        <td><?= orderStatusBadge($o['status']) ?></td>
                                        <td><?= paymentStatusBadge($o['payment_status']) ?></td>
                                        <td><?= trackingBadge($o['tracking_status']) ?></td>

                                        <td>
                                            <div class="sub-text">
                                                ETA:
                                                <?= !empty($o['delivery_eta']) ? e(date('d M Y', strtotime($o['delivery_eta']))) : 'N/A' ?>
                                            </div>
                                            <div class="sub-text">
                                                Delivery ID:
                                                <?= e($o['assigned_delivery_id'] ?: 'N/A') ?>
                                            </div>
                                        </td>

                                        <td>
                                            <div><?= !empty($o['created_at']) ? e(date('d M Y', strtotime($o['created_at']))) : '—' ?></div>
                                            <div class="sub-text"><?= !empty($o['created_at']) ? e(date('h:i A', strtotime($o['created_at']))) : '' ?></div>
                                        </td>

                                        <td class="text-end">
                                            <div class="action-wrap">
                                                <a href="view.php?id=<?= (int)$o['id'] ?>" class="btn btn-info btn-sm">View</a>
                                                <a href="update_status.php?id=<?= (int)$o['id'] ?>" class="btn btn-warning btn-sm">Update</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center py-5 text-muted">No orders found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>