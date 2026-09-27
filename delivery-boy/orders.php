<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = $_SESSION['delivery_id'];

// Determine which tab to show. Default to 'all'.
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';

// Build query based on tab
$where_clause = "WHERE assigned_delivery_id = ?";
if ($tab === 'ongoing') {
    $where_clause .= " AND status NOT IN ('delivered', 'cancelled', 'returned')";
} elseif ($tab === 'delivered') {
    $where_clause .= " AND status = 'delivered'";
} elseif ($tab === 'failed') {
    $where_clause .= " AND status IN ('cancelled', 'returned')";
}

$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    $where_clause
    ORDER BY o.created_at DESC
");
$stmt->execute([$delivery_id]);
$orders = $stmt->fetchAll();

// Helper to format date
function formatOrderDate($dateString) {
    if (!$dateString) return '';
    $timestamp = strtotime($dateString);
    $today = strtotime('today');
    $yesterday = strtotime('yesterday');
    
    if ($timestamp >= $today) {
        return 'Today ' . date('h:i A', $timestamp);
    } elseif ($timestamp >= $yesterday && $timestamp < $today) {
        return 'Yesterday ' . date('h:i A', $timestamp);
    } else {
        return date('M d, Y', $timestamp);
    }
}
?>
<?php include 'includes/header.php'; ?>

<style>
    body {
        background-color: #f8fafc;
    }
    
    .page-header {
        display: flex;
        align-items: center;
        padding: 20px 20px 10px 20px;
        background: #fff;
    }
    
    .back-btn {
        color: var(--text-dark);
        font-size: 1.2rem;
        margin-right: 15px;
    }
    
    .page-title {
        font-size: 18px;
        font-weight: 700;
        margin: 0;
    }

    .orders-container {
        padding: 20px;
    }

    /* Tabs */
    .tabs-wrapper {
        background: #f1f5f9;
        border-radius: 30px;
        display: flex;
        padding: 4px;
        margin-bottom: 24px;
    }

    .tab-btn {
        flex: 1;
        text-align: center;
        padding: 10px 0;
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        border-radius: 30px;
        text-decoration: none;
    }
    
    .tab-btn.active {
        background: var(--mandal-green);
        color: white;
        box-shadow: 0 4px 10px rgba(7, 161, 88, 0.2);
    }

    /* Order Cards */
    .order-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 12px;
        display: flex;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.02);
        text-decoration: none;
        color: inherit;
        position: relative;
        overflow: hidden;
    }

    .pin-icon {
        width: 16px;
        margin-right: 12px;
        display: flex;
        align-items: flex-start;
        padding-top: 4px;
    }

    .pin-icon i {
        font-size: 16px;
    }
    .pin-green { color: var(--mandal-green); }
    .pin-red { color: #ef4444; }
    .pin-orange { color: #f59e0b; }

    .order-details {
        flex: 1;
    }

    .order-id {
        font-size: 14px;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 4px;
    }

    .customer-name {
        font-size: 13px;
        color: #4b5563;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .order-meta {
        font-size: 12px;
        color: var(--text-dark);
        font-weight: 700;
    }
    .order-meta span {
        color: #94a3b8;
        font-weight: 500;
    }

    .order-status {
        text-align: right;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: flex-start;
    }

    .badge-status {
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .badge-delivered { background: #dcfce7; color: #166534; }
    .badge-failed { background: #fee2e2; color: #991b1b; }
    .badge-ongoing { background: #fef3c7; color: #92400e; }

    .order-time {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 500;
    }
</style>

<div class="page-header">
    <a href="index.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
    <h1 class="page-title">My Orders</h1>
</div>

<div class="orders-container">
    <div class="tabs-wrapper">
        <a href="orders.php?tab=all" class="tab-btn <?= $tab === 'all' ? 'active' : '' ?>">All</a>
        <a href="orders.php?tab=ongoing" class="tab-btn <?= $tab === 'ongoing' ? 'active' : '' ?>">Ongoing</a>
        <a href="orders.php?tab=delivered" class="tab-btn <?= $tab === 'delivered' ? 'active' : '' ?>">Delivered</a>
        <a href="orders.php?tab=failed" class="tab-btn <?= $tab === 'failed' ? 'active' : '' ?>">Failed</a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-box-open fa-3x mb-3" style="color: #cbd5e1;"></i>
            <p class="fw-bold mb-0">No orders found.</p>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order): ?>
            <?php 
                $status = $order['status'] ?? 'unknown';
                $badgeClass = 'badge-ongoing';
                $pinClass = 'pin-orange';
                $statusLabel = 'Ongoing';
                
                if ($status === 'delivered') {
                    $badgeClass = 'badge-delivered';
                    $pinClass = 'pin-green';
                    $statusLabel = 'Delivered';
                } elseif (in_array($status, ['cancelled', 'returned'])) {
                    $badgeClass = 'badge-failed';
                    $pinClass = 'pin-red';
                    $statusLabel = 'Failed';
                }

                $amount = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0);
                $pm = strtoupper($order['payment_method'] ?? 'COD');
                $customer = $order['user_name'] ?? $order['customer_name'] ?? 'Customer';
                $orderId = $order['order_number'] ?? $order['order_no'] ?? $order['id'];
            ?>
            <a href="view_order.php?id=<?= $order['id'] ?>" class="order-card">
                <div class="pin-icon <?= $pinClass ?>">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
                <div class="order-details">
                    <div class="order-id">#<?= e($orderId) ?></div>
                    <div class="customer-name"><?= e($customer) ?></div>
                    <div class="order-meta">₹<?= number_format($amount, 0) ?> <span>• <?= e($pm) ?></span></div>
                </div>
                <div class="order-status">
                    <div class="badge-status <?= $badgeClass ?>"><?= $statusLabel ?></div>
                    <div class="order-time"><?= formatOrderDate($order['created_at'] ?? null) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
