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
    body {
        background-color: #f8fafc;
    }
    .earnings-container {
        padding: 24px 20px;
        padding-bottom: 100px;
    }
    
    .page-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 24px;
    }
    
    .back-btn {
        color: var(--text-dark);
        text-decoration: none;
        font-size: 1.2rem;
    }
    
    .page-title {
        font-weight: 800;
        font-size: 1.25rem;
        margin: 0;
        color: var(--text-dark);
    }

    .earnings-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 24px 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        margin-bottom: 24px;
        border: 1px solid rgba(0,0,0,0.02);
        text-align: center;
    }

    .tabs {
        display: flex;
        background: #f1f5f9;
        border-radius: 12px;
        padding: 4px;
        margin-bottom: 24px;
    }

    .tab {
        flex: 1;
        text-align: center;
        padding: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-muted);
        border-radius: 8px;
        cursor: pointer;
    }
    .tab.active {
        background: var(--mandal-green);
        color: white;
    }

    .today-badge {
        display: inline-block;
        background: #e8f7f0;
        color: var(--mandal-green);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        margin-bottom: 12px;
    }

    .amount {
        font-size: 3rem;
        font-weight: 800;
        color: var(--text-dark);
        line-height: 1;
        margin-bottom: 8px;
    }

    .completed-text {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 500;
        margin-bottom: 24px;
    }

    .breakdown {
        text-align: left;
    }

    .breakdown-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
    }
    .breakdown-row:last-child {
        border-bottom: none;
    }

    .bd-label {
        color: var(--text-dark);
        font-weight: 500;
    }

    .bd-value {
        color: var(--text-dark);
        font-weight: 700;
    }

    .btn-details {
        background: var(--mandal-green);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 14px;
        width: 100%;
        font-size: 0.95rem;
        font-weight: 700;
        margin-top: 20px;
        display: block;
        text-decoration: none;
        transition: transform 0.2s;
    }
    .btn-details:active {
        transform: scale(0.98);
    }

    .section-title {
        font-weight: 800;
        font-size: 1.1rem;
        margin-bottom: 16px;
        color: var(--text-dark);
    }

    .history-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
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
        font-weight: 800;
        font-size: 0.95rem;
        color: var(--text-dark);
        margin-bottom: 2px;
    }
    
    .history-date {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .history-amount {
        font-weight: 800;
        font-size: 1.1rem;
        color: var(--text-dark);
        text-align: right;
    }
    .history-pm {
        font-size: 0.7rem;
        color: var(--mandal-green);
        text-transform: uppercase;
        font-weight: 700;
        text-align: right;
    }
</style>

<div class="earnings-container">
    <div class="page-header">
        <a href="index.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <h4 class="page-title">My Earnings</h4>
    </div>

    <div class="earnings-card">
        <div class="tabs">
            <div class="tab active">Daily</div>
            <div class="tab">Weekly</div>
            <div class="tab">Monthly</div>
        </div>
        
        <div class="today-badge">Today</div>
        
        <div class="amount">₹<?= number_format($total_cash_collected, 2) ?></div>
        <div class="completed-text">Completed Deliveries: <?= $total_delivered ?></div>
        
        <div class="breakdown">
            <div class="breakdown-row">
                <span class="bd-label">COD Cash Collected</span>
                <span class="bd-value">₹<?= number_format($total_cash_collected, 2) ?></span>
            </div>
            <!-- Simulating breakdown structure as per mockup -->
            <div class="breakdown-row">
                <span class="bd-label">Base Delivery Charge</span>
                <span class="bd-value">₹0</span>
            </div>
            <div class="breakdown-row">
                <span class="bd-label">Incentives</span>
                <span class="bd-value">₹0</span>
            </div>
        </div>
        
        <a href="#" class="btn-details">View Details</a>
    </div>

    <h5 class="section-title">Delivery History</h5>

    <?php if (empty($completed_orders)): ?>
        <div class="text-center py-4 text-muted">
            <i class="fa-solid fa-receipt fa-2x mb-3" style="color: #cbd5e1;"></i>
            <p class="fw-bold mb-0" style="font-size: 0.9rem;">No delivered orders yet.</p>
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
                    <div class="history-amount">₹<?= number_format($amount, 0) ?></div>
                    <div class="history-pm"><?= e($pm) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
