<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = $_SESSION['delivery_id'];

// Fetch delivered orders
$stmt = $conn->prepare("
    SELECT *
    FROM orders 
    WHERE assigned_delivery_id = ? AND status = 'delivered'
    ORDER BY created_at DESC
");
$stmt->execute([$delivery_id]);
$completed_orders = $stmt->fetchAll();

$total_delivered = count($completed_orders);
$total_cash_collected = 0;

foreach ($completed_orders as $order) {
    $pm = strtolower($order['payment_method'] ?? '');
    if ($pm === 'cod') {
        $total_cash_collected += (float)($order['grand_total'] ?? $order['total_amount'] ?? 0);
    }
}
?>

<?php include 'includes/header.php'; ?>

<style>
    .earnings-container {
        padding: 24px 20px;
    }
    
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }
    
    .page-title {
        font-weight: 700;
        font-size: 1.25rem;
        margin: 0;
        color: var(--text-dark);
    }

    .earnings-hero {
        background: var(--mandal-green-gradient);
        border-radius: 24px;
        padding: 30px 24px;
        color: white;
        text-align: center;
        margin-bottom: 24px;
        box-shadow: 0 10px 25px rgba(7, 161, 88, 0.25);
    }

    .earnings-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
        opacity: 0.9;
        margin-bottom: 8px;
    }

    .earnings-amount {
        font-size: 2.8rem;
        font-weight: 800;
        margin: 0;
        line-height: 1.2;
    }

    .stats-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(0,0,0,0.02);
    }
    
    .stat-icon {
        color: var(--mandal-green);
        font-size: 1.5rem;
        margin-bottom: 10px;
    }

    .stat-value {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 2px;
    }
    
    .stat-label {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 600;
    }

    .section-title {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 16px;
        color: var(--text-dark);
    }

    .history-card {
        background: white;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 12px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(0,0,0,0.02);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .history-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: #f0fdf4;
        color: var(--mandal-green);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
        margin-right: 15px;
    }

    .history-details {
        flex: 1;
    }
    
    .history-id {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-dark);
        margin-bottom: 2px;
    }
    
    .history-date {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .history-amount {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--mandal-green);
        text-align: right;
    }
    .history-pm {
        font-size: 0.7rem;
        color: var(--text-muted);
        text-transform: uppercase;
        font-weight: 600;
    }
</style>

<div class="earnings-container">
    <div class="page-header">
        <h4 class="page-title">Earnings & Cash</h4>
    </div>

    <div class="earnings-hero">
        <div class="earnings-label">COD Cash Collected</div>
        <h1 class="earnings-amount">₹<?= number_format($total_cash_collected, 2) ?></h1>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-box-open"></i></div>
            <div class="stat-value"><?= $total_delivered ?></div>
            <div class="stat-label">Total Delivered</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-credit-card" style="color: #3b82f6;"></i></div>
            <div class="stat-value text-primary">Admin</div>
            <div class="stat-label">Earnings Logic</div>
        </div>
    </div>

    <h5 class="section-title">Delivery History</h5>

    <?php if (empty($completed_orders)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-receipt fa-3x mb-3" style="color: #cbd5e1;"></i>
            <p class="fw-bold mb-0">No delivered orders yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($completed_orders as $order): ?>
            <?php 
                $amount = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0); 
                $pm = $order['payment_method'] ?? 'unknown';
            ?>
            <div class="history-card">
                <div class="history-icon">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div class="history-details">
                    <div class="history-id">#<?= e($order['order_number'] ?? $order['order_no'] ?? 'N/A') ?></div>
                    <div class="history-date"><?= !empty($order['created_at']) ? date('M d, Y • h:i A', strtotime($order['created_at'])) : '' ?></div>
                </div>
                <div>
                    <div class="history-amount">₹<?= number_format($amount, 2) ?></div>
                    <div class="history-pm"><?= e($pm) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
