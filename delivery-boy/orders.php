<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = $_SESSION['delivery_id'];

// Handle tabs
$tab = $_GET['tab'] ?? 'ongoing'; // all, ongoing, delivered, failed

$status_condition = "";
if ($tab === 'ongoing') {
    $status_condition = "AND status NOT IN ('delivered', 'cancelled', 'returned')";
} elseif ($tab === 'delivered') {
    $status_condition = "AND status = 'delivered'";
} elseif ($tab === 'failed') {
    $status_condition = "AND status IN ('cancelled', 'returned', 'failed')";
}

// Fetch orders
$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name 
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.assigned_delivery_id = ? $status_condition
    ORDER BY o.created_at DESC
");
$stmt->execute([$delivery_id]);
$orders = $stmt->fetchAll();

?>

<?php include 'includes/header.php'; ?>

<style>
    body {
        background-color: #f8fafc;
    }
    .orders-container {
        padding: 24px 20px;
        padding-bottom: 100px;
    }
    
    .page-header {
        display: flex;
        align-items: center;
        margin-bottom: 24px;
        gap: 15px;
    }
    
    .page-title {
        font-weight: 800;
        font-size: 1.25rem;
        margin: 0;
        color: var(--text-dark);
    }
    
    .back-btn {
        color: var(--text-dark);
        text-decoration: none;
        font-size: 1.2rem;
    }

    .tabs-wrapper {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding-bottom: 10px;
        margin-bottom: 20px;
        scrollbar-width: none; /* Firefox */
    }
    .tabs-wrapper::-webkit-scrollbar {
        display: none; /* Chrome */
    }

    .tab-pill {
        padding: 8px 16px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-muted);
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.2s;
    }
    .tab-pill.active {
        background: var(--mandal-green);
        color: white;
        border-color: var(--mandal-green);
        box-shadow: 0 4px 10px rgba(7, 161, 88, 0.2);
    }

    .order-card {
        background: white;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.02);
        display: block;
        text-decoration: none;
        color: inherit;
    }
    .order-card:hover {
        color: inherit;
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }

    .order-info {
        display: flex;
        gap: 12px;
    }

    .order-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .order-icon.delivered { background: #dcfce7; color: #10b981; }
    .order-icon.ongoing { background: #eff6ff; color: #3b82f6; }
    .order-icon.failed { background: #fee2e2; color: #ef4444; }

    .order-details-col {
        display: flex;
        flex-direction: column;
    }

    .order-id {
        font-weight: 800;
        font-size: 0.95rem;
        color: var(--text-dark);
        margin-bottom: 2px;
    }

    .order-customer {
        font-size: 0.85rem;
        color: var(--text-dark);
        font-weight: 600;
    }

    .order-amount-row {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .order-amount {
        font-weight: 700;
        color: var(--text-dark);
    }

    .order-status-col {
        text-align: right;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .status-badge.delivered { background: #10b981; color: white; }
    .status-badge.ongoing { background: #f59e0b; color: white; }
    .status-badge.failed { background: #ef4444; color: white; }
    
    .status-time {
        font-size: 0.75rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .divider {
        height: 1px;
        background: #f1f5f9;
        margin: 12px 0;
    }

    .order-footer {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.8rem;
        color: var(--text-muted);
    }
    .order-footer i {
        color: #ef4444;
    }
</style>

<div class="orders-container">
    <div class="page-header">
        <a href="index.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <h4 class="page-title">My Orders</h4>
    </div>

    <div class="tabs-wrapper">
        <a href="?tab=all" class="tab-pill <?= $tab === 'all' ? 'active' : '' ?>">All</a>
        <a href="?tab=ongoing" class="tab-pill <?= $tab === 'ongoing' ? 'active' : '' ?>">Ongoing</a>
        <a href="?tab=delivered" class="tab-pill <?= $tab === 'delivered' ? 'active' : '' ?>">Delivered</a>
        <a href="?tab=failed" class="tab-pill <?= $tab === 'failed' ? 'active' : '' ?>">Failed</a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-box-open fa-3x mb-3" style="color: #cbd5e1;"></i>
            <p class="fw-bold mb-0">No orders found.</p>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <?php 
                $amount = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0); 
                $pm = $order['payment_method'] ?? 'unknown';
                $status = $order['status'] ?? 'pending';
                
                $status_class = 'ongoing';
                $icon_class = 'ongoing';
                $icon_html = '<i class="fa-solid fa-motorcycle"></i>';
                
                if ($status === 'delivered') {
                    $status_class = 'delivered';
                    $icon_class = 'delivered';
                    $icon_html = '<i class="fa-solid fa-check"></i>';
                } elseif (in_array($status, ['cancelled', 'returned', 'failed'])) {
                    $status_class = 'failed';
                    $icon_class = 'failed';
                    $icon_html = '<i class="fa-solid fa-xmark"></i>';
                }
            ?>
            <a href="view_order.php?id=<?= (int)$order['id'] ?>" class="order-card">
                <div class="order-header">
                    <div class="order-info">
                        <div class="order-icon <?= $icon_class ?>">
                            <?= $icon_html ?>
                        </div>
                        <div class="order-details-col">
                            <div class="order-id">#<?= e($order['order_number'] ?? $order['order_no'] ?? 'N/A') ?></div>
                            <div class="order-customer"><?= e($order['user_name'] ?? $order['customer_name'] ?? 'Customer') ?></div>
                            <div class="order-amount-row">
                                <span class="order-amount">₹<?= number_format($amount, 0) ?></span>
                                <span>•</span>
                                <span style="text-transform: uppercase; font-weight: 700; color: var(--mandal-green);"><?= e($pm) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="order-status-col">
                        <div class="status-badge <?= $status_class ?>"><?= str_replace('_', ' ', e($status)) ?></div>
                        <div class="status-time"><?= !empty($order['created_at']) ? date('h:i A', strtotime($order['created_at'])) : '' ?></div>
                    </div>
                </div>
                
                <?php if ($status_class === 'ongoing' || $status_class === 'failed'): ?>
                    <div class="divider"></div>
                    <div class="order-footer">
                        <i class="fa-solid fa-location-dot"></i>
                        <span><?= e($order['shipping_address'] ?? $order['address'] ?? 'Delivery Address') ?></span>
                    </div>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
