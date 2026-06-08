<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = $_SESSION['delivery_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch order details
$stmt = $conn->prepare("
    SELECT o.*, u.name as customer_name, u.phone as customer_phone
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.assigned_delivery_id = ?
");
$stmt->execute([$order_id, $delivery_id]);
$order = $stmt->fetch();

if (!$order) {
    echo "Order not found or not assigned to you.";
    exit;
}

// Fetch order items
$stmt = $conn->prepare("
    SELECT oi.*, p.name, p.images
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

// Handle status updates
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $new_status = '';
    
    if ($action === 'out_for_delivery' && $order['status'] !== 'out_for_delivery') {
        $new_status = 'out_for_delivery';
    } elseif ($action === 'delivered' && $order['status'] === 'out_for_delivery') {
        $new_status = 'delivered';
    } else {
        $error = "Invalid status transition.";
    }

    if ($new_status !== '') {
        $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $updateStmt->execute([$new_status, $order_id]);
        $order['status'] = $new_status; // update local variable
        $success = "Order status updated successfully!";
    }
}
?>

<?php include 'includes/header.php'; ?>

<style>
    .order-container {
        padding: 20px;
        max-width: 800px;
        margin: 0 auto;
        padding-bottom: 80px; /* space for fixed bottom button */
    }

    .info-card {
        padding: 20px;
    }

    .icon-box {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #10b981;
        font-size: 18px;
    }

    .item-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 8px;
    }

    .bottom-actions {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: white;
        padding: 15px 20px;
        box-shadow: 0 -4px 15px rgba(0,0,0,0.05);
        display: flex;
        gap: 15px;
        z-index: 10;
    }

    .badge-status {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
    }
</style>

<div class="order-container">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-1">Order #<?= e($order['order_number']) ?></h5>
            <div class="text-muted small"><?= date('h:i A, M d, Y', strtotime($order['created_at'])) ?></div>
        </div>
        <div>
            <span class="badge-status bg-light border text-dark">
                <?= str_replace('_', ' ', e($order['status'])) ?>
            </span>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success rounded-3 small"><i class="fa-solid fa-check me-2"></i><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger rounded-3 small"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= e($error) ?></div>
    <?php endif; ?>

    <!-- Customer Details -->
    <div class="premium-card info-card mb-4">
        <h6 class="fw-bold mb-3 text-muted small">CUSTOMER DETAILS</h6>
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="icon-box"><i class="fa-solid fa-user"></i></div>
            <div>
                <div class="fw-bold"><?= e($order['customer_name'] ?? 'Unknown') ?></div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="icon-box"><i class="fa-solid fa-phone"></i></div>
            <div>
                <div class="fw-bold"><?= e($order['customer_phone'] ?? 'N/A') ?></div>
                <a href="tel:<?= e($order['customer_phone'] ?? '') ?>" class="text-success small fw-bold text-decoration-none">Call Customer</a>
            </div>
        </div>
        <div class="d-flex align-items-start gap-3">
            <div class="icon-box"><i class="fa-solid fa-location-dot"></i></div>
            <div>
                <div class="fw-bold text-dark mb-1">Delivery Address</div>
                <div class="small text-muted" style="line-height: 1.5;">
                    <?= nl2br(e($order['delivery_address'])) ?>
                    <br><strong>PIN: <?= e($order['pincode']) ?></strong>
                </div>
                <a href="https://maps.google.com/?q=<?= urlencode($order['delivery_address']) ?>" target="_blank" class="text-primary small fw-bold text-decoration-none mt-1 d-inline-block">
                    <i class="fa-solid fa-map-location-dot me-1"></i> View on Map
                </a>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="premium-card info-card mb-4">
        <h6 class="fw-bold mb-3 text-muted small">ORDER ITEMS (<?= count($items) ?>)</h6>
        
        <?php foreach($items as $item): ?>
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-light item-img d-flex align-items-center justify-content-center text-muted border">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size:0.95rem;"><?= e($item['name']) ?></div>
                        <div class="small text-muted">Qty: <?= (int)$item['quantity'] ?> × ₹<?= number_format((float)$item['price'], 2) ?></div>
                    </div>
                </div>
                <div class="fw-bold text-dark">
                    ₹<?= number_format($item['quantity'] * $item['price'], 2) ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="mt-3 text-end">
            <div class="small text-muted mb-1">Delivery Charge: ₹<?= number_format((float)$order['delivery_charge'], 2) ?></div>
            <div class="fw-bold text-success fs-5">Total: ₹<?= number_format((float)$order['grand_total'], 2) ?></div>
            <div class="small fw-bold mt-1 text-primary">Payment: <?= strtoupper(e($order['payment_status'])) ?></div>
        </div>
    </div>

</div>

<!-- Bottom Action Buttons -->
<?php if ($order['status'] !== 'delivered' && $order['status'] !== 'cancelled'): ?>
<div class="bottom-actions">
    <form method="POST" class="w-100 m-0">
        <?php if ($order['status'] === 'confirmed' || $order['status'] === 'preparing'): ?>
            <button type="submit" name="action" value="out_for_delivery" class="btn-premium">
                <i class="fa-solid fa-truck-fast me-2"></i> Start Delivery
            </button>
        <?php elseif ($order['status'] === 'out_for_delivery'): ?>
            <button type="submit" name="action" value="delivered" class="btn-premium" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                <i class="fa-solid fa-circle-check me-2"></i> Mark as Delivered
            </button>
        <?php endif; ?>
    </form>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
