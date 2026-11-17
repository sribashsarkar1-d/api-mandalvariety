<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = (int)$_SESSION['delivery_id'];
$period = $_GET['period'] ?? 'today'; // 'today', 'weekly', 'monthly'
if (!in_array($period, ['today', 'weekly', 'monthly'])) {
    $period = 'today';
}

// 1. Calculate Period Specific Earnings & Stats
$period_sql = match ($period) {
    'weekly' => "AND updated_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)",
    'monthly' => "AND updated_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
    default => "AND DATE(updated_at) = CURDATE()"
};

$stmtPeriod = $conn->prepare("
    SELECT 
        COUNT(*) as total_count,
        COALESCE(SUM(grand_total), 0) as total_earnings,
        COALESCE(SUM(CASE WHEN LOWER(payment_method) LIKE '%cod%' OR LOWER(payment_method) LIKE '%cash%' THEN grand_total ELSE 0 END), 0) as cod_cash,
        COALESCE(SUM(CASE WHEN LOWER(payment_method) NOT LIKE '%cod%' AND LOWER(payment_method) NOT LIKE '%cash%' THEN grand_total ELSE 0 END), 0) as online_cash
    FROM orders
    WHERE assigned_delivery_id = ? 
      AND (delivery_boy_status = 'delivered' OR status = 'delivered')
      $period_sql
");
$stmtPeriod->execute([$delivery_id]);
$periodStats = $stmtPeriod->fetch();

// 2. All-Time Stats
$stmtAll = $conn->prepare("
    SELECT 
        COUNT(*) as all_time_deliveries,
        COALESCE(SUM(grand_total), 0) as all_time_earnings
    FROM orders
    WHERE assigned_delivery_id = ? 
      AND (delivery_boy_status = 'delivered' OR status = 'delivered')
");
$stmtAll->execute([$delivery_id]);
$allStats = $stmtAll->fetch();

// 3. Completed Deliveries History
$stmtHistory = $conn->prepare("
    SELECT o.*, u.name as user_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.assigned_delivery_id = ? 
      AND (o.delivery_boy_status = 'delivered' OR o.status = 'delivered')
    ORDER BY o.updated_at DESC, o.id DESC
    LIMIT 30
");
$stmtHistory->execute([$delivery_id]);
$completed_orders = $stmtHistory->fetchAll();

$period_labels = [
    'today' => "Today's Earnings",
    'weekly' => "Last 7 Days Earnings",
    'monthly' => "Last 30 Days Earnings"
];
?>

<?php include 'includes/header.php'; ?>

<style>
    .earnings-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .btn-back-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #FFFFFF;
        border: 1px solid var(--border-color);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-text);
        text-decoration: none;
        font-size: 14px;
        transition: background 0.15s;
    }

    .btn-back-circle:active {
        background: var(--light-green);
    }

    .earnings-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--primary-text);
        letter-spacing: -0.3px;
    }

    /* Pill Tabs for Period selection */
    .period-tabs-wrap {
        display: flex;
        gap: 8px;
        background: #F1F5F9;
        padding: 5px;
        border-radius: var(--pill-radius);
        margin-bottom: 20px;
    }

    .period-tab-btn {
        flex: 1;
        padding: 9px 12px;
        border-radius: var(--pill-radius);
        font-size: 13px;
        font-weight: 700;
        text-align: center;
        color: var(--secondary-text);
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .period-tab-btn.active {
        background: #FFFFFF;
        color: var(--primary-green);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    /* Hero Summary Card */
    .earnings-hero-card {
        background: #FFFFFF;
        border-radius: var(--card-radius);
        border: 1px solid var(--border-color);
        box-shadow: var(--card-shadow);
        padding: 24px 20px;
        text-align: center;
        margin-bottom: 20px;
    }

    .hero-period-label {
        font-size: 12.5px;
        font-weight: 700;
        color: var(--secondary-text);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .hero-amount {
        font-size: 34px;
        font-weight: 800;
        color: var(--primary-text);
        letter-spacing: -0.8px;
        line-height: 1.1;
        margin-bottom: 6px;
    }

    .hero-currency {
        font-size: 24px;
        vertical-align: super;
        color: var(--primary-green);
    }

    .hero-completed-sub {
        font-size: 13px;
        color: var(--secondary-text);
        font-weight: 500;
        margin-bottom: 20px;
    }

    /* Mini Stats Grid */
    .stats-breakdown-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        text-align: left;
        border-top: 1px solid var(--divider-color);
        padding-top: 18px;
    }

    .stat-pill-box {
        background: #F8FAFC;
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 12px 14px;
    }

    .stat-pill-label {
        font-size: 11.5px;
        color: var(--secondary-text);
        font-weight: 600;
        margin-bottom: 3px;
    }

    .stat-pill-val {
        font-size: 16px;
        font-weight: 800;
        color: var(--primary-text);
    }

    /* Section Header */
    .section-title {
        font-size: 15.5px;
        font-weight: 800;
        color: var(--primary-text);
        margin-bottom: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .section-subtitle {
        font-size: 12px;
        font-weight: 600;
        color: var(--secondary-text);
    }

    /* Completed Deliveries Card */
    .history-order-card {
        background: #FFFFFF;
        border-radius: var(--card-radius);
        border: 1px solid var(--border-color);
        box-shadow: var(--card-shadow);
        padding: 14px 16px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        text-decoration: none;
        color: inherit;
        transition: transform 0.15s;
    }

    .history-order-card:active {
        transform: scale(0.98);
    }

    .history-card-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .history-badge-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #DCFCE7;
        color: #15803D;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .history-order-meta {
        min-width: 0;
    }

    .history-order-id {
        font-size: 14px;
        font-weight: 800;
        color: var(--primary-text);
        letter-spacing: -0.2px;
        margin-bottom: 2px;
    }

    .history-order-date {
        font-size: 11.5px;
        color: var(--secondary-text);
        font-weight: 500;
    }

    .history-card-right {
        text-align: right;
        flex-shrink: 0;
    }

    .history-order-amount {
        font-size: 15.5px;
        font-weight: 800;
        color: var(--primary-text);
    }

    .history-order-pm {
        font-size: 11px;
        font-weight: 700;
        color: var(--primary-green);
        text-transform: uppercase;
    }

    /* Empty State */
    .empty-card-state {
        text-align: center;
        padding: 40px 20px;
        background: #FFFFFF;
        border-radius: var(--card-radius);
        border: 1px dashed var(--border-color);
        margin: 16px 0;
    }
</style>

<div class="page-content">

    <div class="earnings-header">
        <a href="index.php" class="btn-back-circle">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
        <h2 class="earnings-title">My Earnings</h2>
    </div>

    <!-- Period Tabs (Today / Weekly / Monthly) -->
    <div class="period-tabs-wrap">
        <a href="earnings.php?period=today" class="period-tab-btn <?= $period === 'today' ? 'active' : '' ?>">Today</a>
        <a href="earnings.php?period=weekly" class="period-tab-btn <?= $period === 'weekly' ? 'active' : '' ?>">Weekly</a>
        <a href="earnings.php?period=monthly" class="period-tab-btn <?= $period === 'monthly' ? 'active' : '' ?>">Monthly</a>
    </div>

    <!-- Hero Earnings Summary Card -->
    <div class="earnings-hero-card">
        <div class="hero-period-label"><?= $period_labels[$period] ?></div>
        <div class="hero-amount">
            <span class="hero-currency">₹</span><?= number_format((float)$periodStats['total_earnings'], 0) ?>
        </div>
        <div class="hero-completed-sub">
            <i class="fa-solid fa-circle-check text-success me-1"></i>
            <?= (int)$periodStats['total_count'] ?> Completed Deliveries
        </div>

        <div class="stats-breakdown-grid">
            <div class="stat-pill-box">
                <div class="stat-pill-label">Cash on Delivery</div>
                <div class="stat-pill-val">₹<?= number_format((float)$periodStats['cod_cash'], 0) ?></div>
            </div>
            <div class="stat-pill-box">
                <div class="stat-pill-label">Paid Online</div>
                <div class="stat-pill-val">₹<?= number_format((float)$periodStats['online_cash'], 0) ?></div>
            </div>
            <div class="stat-pill-box">
                <div class="stat-pill-label">Total All-Time</div>
                <div class="stat-pill-val">₹<?= number_format((float)$allStats['all_time_earnings'], 0) ?></div>
            </div>
            <div class="stat-pill-box">
                <div class="stat-pill-label">Lifetime Deliveries</div>
                <div class="stat-pill-val"><?= (int)$allStats['all_time_deliveries'] ?></div>
            </div>
        </div>
    </div>

    <!-- Completed Deliveries List -->
    <div class="section-title">
        <span>Delivery History</span>
        <span class="section-subtitle">Showing <?= count($completed_orders) ?> orders</span>
    </div>

    <?php if (!empty($completed_orders)): ?>
        <?php foreach ($completed_orders as $order): ?>
            <?php
                $order_no = $order['order_number'] ?? $order['order_no'] ?? ('ORD-' . $order['id']);
                $cust_name = $order['user_name'] ?? 'Customer';
                $total = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0);
                $pm = $order['payment_method'] ?? 'COD';
                $is_cod = (strpos(strtolower($pm), 'cash') !== false || strpos(strtolower($pm), 'cod') !== false);
                $date_str = !empty($order['updated_at']) ? date('M d, Y • h:i A', strtotime($order['updated_at'])) : date('M d, Y', strtotime($order['created_at']));
            ?>
            <a href="view_order.php?id=<?= (int)$order['id'] ?>" class="history-order-card">
                <div class="history-card-left">
                    <div class="history-badge-icon">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div class="history-order-meta">
                        <div class="history-order-id">#<?= e($order_no) ?></div>
                        <div class="history-order-date"><?= e($date_str) ?> &bull; <?= e($cust_name) ?></div>
                    </div>
                </div>
                <div class="history-card-right">
                    <div class="history-order-amount">₹<?= number_format($total, 0) ?></div>
                    <div class="history-order-pm"><?= $is_cod ? 'COD' : 'Paid' ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-card-state">
            <div style="font-size: 28px; color: #94A3B8; margin-bottom: 8px;">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <h4 style="font-size: 15px; font-weight: 700; color: var(--primary-text); margin-bottom: 4px;">No Delivered Orders Yet</h4>
            <p style="font-size: 13px; color: var(--secondary-text); margin: 0;">Completed orders will appear here automatically.</p>
        </div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
