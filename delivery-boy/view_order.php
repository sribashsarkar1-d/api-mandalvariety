<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = (int)$_SESSION['delivery_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

// Auto-add delivery_otp and delivery_boy_status columns if missing
try {
    $conn->query("SELECT delivery_otp FROM orders LIMIT 1");
} catch (\PDOException $e) {
    try { $conn->exec("ALTER TABLE orders ADD COLUMN delivery_otp VARCHAR(10) NULL DEFAULT NULL"); } catch (\PDOException $e2) {}
}

try {
    $conn->query("SELECT delivery_boy_status FROM orders LIMIT 1");
} catch (\PDOException $e) {
    try { $conn->exec("ALTER TABLE orders ADD COLUMN delivery_boy_status VARCHAR(50) DEFAULT 'assigned'"); } catch (\PDOException $e2) {}
}

// Fetch order
$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.assigned_delivery_id = ?
");
$stmt->execute([$order_id, $delivery_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: index.php");
    exit;
}

// Initialize state if empty
if (empty($order['delivery_boy_status'])) {
    $order['delivery_boy_status'] = 'assigned';
    $conn->exec("UPDATE orders SET delivery_boy_status = 'assigned' WHERE id = $order_id");
}

$state = $order['delivery_boy_status'];
$payment_method = $order['payment_method'] ?? $order['payment_type'] ?? 'N/A';
$is_cod = (strpos(strtolower($payment_method), 'cash') !== false || strpos(strtolower($payment_method), 'cod') !== false);

// Handle state transitions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $new_state = '';
    $main_status = '';

    if ($action === 'accept') {
        $new_state = 'accepted';
        $main_status = 'assigned';
    } elseif ($action === 'reject') {
        $conn->exec("UPDATE orders SET assigned_delivery_id = NULL, delivery_boy_status = NULL WHERE id = $order_id");
        header("Location: index.php");
        exit;
    } elseif ($action === 'confirm_pickup') {
        $new_state = 'picked_up';
        $main_status = 'shipped';
    } elseif ($action === 'update_status') {
        $new_state = 'out_for_delivery';
        $main_status = 'out_for_delivery';
    } elseif ($action === 'confirm_arrival') {
        $new_state = 'arrived';
    } elseif ($action === 'verify_otp') {
        $entered_otp = $_POST['otp'] ?? '';
        if (empty($order['delivery_otp'])) {
            $error = "Please click 'Send OTP to Customer' first.";
        } elseif ($entered_otp !== $order['delivery_otp']) {
            $error = "Invalid OTP. Please enter the correct code.";
        } else {
            if ($is_cod && $order['payment_status'] !== 'paid') {
                $new_state = 'payment_pending';
            } else {
                $new_state = 'proof_pending';
            }
        }
    } elseif ($action === 'confirm_payment') {
        $new_state = 'proof_pending';
    } elseif ($action === 'submit_proof') {
        $new_state = 'delivered';
        $main_status = 'delivered';
    }

    if ($new_state) {
        $stmt = $conn->prepare("UPDATE orders SET delivery_boy_status = ? WHERE id = ?");
        $stmt->execute([$new_state, $order_id]);
        
        if ($main_status) {
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$main_status, $order_id]);
            
            if ($main_status === 'delivered') {
                $conn->exec("UPDATE orders SET payment_status = 'paid' WHERE id = $order_id");
            }
        }
        
        header("Location: view_order.php?id=$order_id");
        exit;
    }
}

// Fetch ordered items
try {
    $stmtItems = $conn->prepare("
        SELECT oi.*, p.name as product_name, p.images
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmtItems->execute([$order_id]);
    $items = $stmtItems->fetchAll();
} catch (\PDOException $e) {
    $stmtItems = $conn->prepare("
        SELECT oi.*, p.name as product_name
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmtItems->execute([$order_id]);
    $items = $stmtItems->fetchAll();
}

$order_no = $order['order_number'] ?? $order['order_no'] ?? ('ORD-' . $order['id']);
$customer_name = $order['user_name'] ?? $order['customer_name'] ?? $order['name'] ?? 'Customer';
$customer_phone = $order['user_phone'] ?? $order['customer_phone'] ?? $order['phone'] ?? '';
$address = $order['shipping_address'] ?? $order['delivery_address'] ?? $order['address'] ?? '';
$pincode = $order['shipping_pincode'] ?? $order['delivery_pincode'] ?? $order['pincode'] ?? '';
$grand_total = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0);
$delivery_charge = (float)($order['delivery_charge'] ?? 0);
$items_subtotal = (float)($order['total_amount'] ?? ($grand_total - $delivery_charge));
if ($items_subtotal <= 0) $items_subtotal = $grand_total;

$maps_url = "https://www.google.com/maps/dir/?api=1&destination=" . urlencode($address . ' ' . $pincode) . "&travelmode=driving";
?>

<?php include 'includes/header.php'; ?>

<style>
    .order-details-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .header-left-title {
        display: flex;
        align-items: center;
        gap: 12px;
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

    .order-details-title {
        font-size: 18.5px;
        font-weight: 800;
        color: var(--primary-text);
        letter-spacing: -0.3px;
        line-height: 1.2;
    }

    .order-details-sub {
        font-size: 12px;
        color: var(--secondary-text);
        font-weight: 500;
    }

    /* Status Progress Tracker */
    .status-tracker-card {
        background: #FFFFFF;
        border-radius: var(--card-radius);
        border: 1px solid var(--border-color);
        box-shadow: var(--card-shadow);
        padding: 16px;
        margin-bottom: 16px;
    }

    .tracker-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
        padding: 6px 10px 0;
    }

    .tracker-steps::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 30px;
        right: 30px;
        height: 3px;
        background: #E5E7EB;
        z-index: 1;
    }

    .tracker-step {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }

    .step-circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #FFFFFF;
        border: 2px solid #CBD5E1;
        color: #94A3B8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        transition: all 0.2s ease;
    }

    .tracker-step.active .step-circle {
        background: var(--primary-green);
        border-color: var(--primary-green);
        color: #FFFFFF;
        box-shadow: 0 0 0 4px rgba(0, 155, 85, 0.18);
    }

    .tracker-step.completed .step-circle {
        background: var(--primary-green);
        border-color: var(--primary-green);
        color: #FFFFFF;
    }

    .step-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--secondary-text);
        text-align: center;
    }

    .tracker-step.active .step-label {
        color: var(--primary-green);
        font-weight: 700;
    }

    /* Section Card */
    .detail-card {
        background: #FFFFFF;
        border-radius: var(--card-radius);
        border: 1px solid var(--border-color);
        box-shadow: var(--card-shadow);
        padding: 18px;
        margin-bottom: 16px;
    }

    .detail-card-title {
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--secondary-text);
        letter-spacing: 0.5px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .detail-card-title i {
        color: var(--primary-green);
        font-size: 14px;
    }

    /* Customer Quick Actions */
    .customer-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .customer-meta {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .customer-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--light-green);
        color: var(--primary-green);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .customer-name {
        font-size: 15px;
        font-weight: 700;
        color: var(--primary-text);
        line-height: 1.2;
    }

    .customer-phone {
        font-size: 13px;
        color: var(--secondary-text);
        font-weight: 500;
        margin-top: 2px;
    }

    .quick-action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-quick-action {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        text-decoration: none;
        transition: transform 0.15s, background-color 0.15s;
        border: 1px solid var(--border-color);
    }

    .btn-quick-call {
        background: #EFF6FF;
        color: #2563EB;
        border-color: #DBEAFE;
    }

    .btn-quick-maps {
        background: var(--light-green);
        color: var(--primary-green);
        border-color: #C6F0DC;
    }

    .btn-quick-action:active {
        transform: scale(0.93);
    }

    /* Address Text */
    .address-box {
        background: #F8FAFC;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 13.5px;
        color: var(--primary-text);
        line-height: 1.45;
        font-weight: 500;
    }

    /* Product Item */
    .product-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid var(--divider-color);
    }
    .product-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .product-thumb {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        object-fit: cover;
        background: #F8FAFC;
        border: 1px solid var(--border-color);
        flex-shrink: 0;
    }

    .product-thumb-placeholder {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        background: var(--light-green);
        color: var(--primary-green);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .product-info {
        flex: 1;
        min-width: 0;
    }

    .product-title {
        font-size: 13.5px;
        font-weight: 700;
        color: var(--primary-text);
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .product-qty-badge {
        font-size: 12px;
        color: var(--secondary-text);
        margin-top: 2px;
        font-weight: 500;
    }

    .product-price {
        font-size: 14.5px;
        font-weight: 800;
        color: var(--primary-text);
        letter-spacing: -0.2px;
    }

    /* Bill Breakdown */
    .bill-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13.5px;
        color: var(--secondary-text);
        margin-bottom: 8px;
    }

    .bill-row.total-row {
        border-top: 1px dashed var(--border-color);
        padding-top: 12px;
        margin-top: 10px;
        margin-bottom: 0;
        font-size: 16px;
        font-weight: 800;
        color: var(--primary-text);
    }

    /* Fixed Bottom Action Bar */
    .fixed-action-bar {
        position: fixed;
        bottom: 68px; /* sits right above bottom nav */
        left: 50%;
        transform: translateX(-50%);
        width: 100%;
        max-width: 480px;
        background: #FFFFFF;
        padding: 12px 18px;
        border-top: 1px solid var(--border-color);
        box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.05);
        z-index: 990;
        display: flex;
        gap: 10px;
    }

    .btn-action-full {
        flex: 1;
        background: var(--primary-green);
        color: #FFFFFF;
        border: none;
        border-radius: var(--pill-radius);
        padding: 13px 20px;
        font-size: 14.5px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        text-decoration: none;
        transition: background-color 0.2s, transform 0.1s;
    }

    .btn-action-full:hover {
        background: var(--dark-green);
        color: #FFFFFF;
    }

    .btn-action-full:active {
        transform: scale(0.98);
    }

    .btn-action-danger {
        background: #FEE2E2;
        color: #DC2626;
    }

    .btn-action-danger:hover {
        background: #FCA5A5;
        color: #991B1B;
    }

    /* OTP Inputs */
    .otp-grid {
        display: flex;
        gap: 8px;
        justify-content: center;
        margin: 20px 0;
    }

    .otp-digit-input {
        width: 46px;
        height: 54px;
        border: 2px solid var(--border-color);
        border-radius: 12px;
        text-align: center;
        font-size: 22px;
        font-weight: 800;
        color: var(--primary-text);
        outline: none;
        transition: border-color 0.2s;
    }

    .otp-digit-input:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px rgba(0, 155, 85, 0.15);
    }

    /* Big Success Checkmark */
    .success-hero {
        text-align: center;
        padding: 30px 10px 10px;
    }

    .success-hero-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--primary-green);
        color: #FFFFFF;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 34px;
        margin-bottom: 16px;
        box-shadow: 0 8px 24px rgba(0, 155, 85, 0.3);
    }
</style>

<div class="page-content" style="padding-bottom: 90px;">

    <!-- 1. Header with Back Button and Order Number -->
    <div class="order-details-header">
        <div class="header-left-title">
            <a href="index.php" class="btn-back-circle">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <div>
                <h2 class="order-details-title">Order #<?= e($order_no) ?></h2>
                <div class="order-details-sub"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
            </div>
        </div>

        <div class="badge-status">
            <span class="status-dot"></span>
            <span><?= e(ucwords(str_replace('_', ' ', $state))) ?></span>
        </div>
    </div>

    <!-- Error Alert if any -->
    <?php if ($error): ?>
        <div class="detail-card" style="background:#FEF2F2; border-color:#FECACA; color:#B91C1C; padding:12px 16px; margin-bottom:14px;">
            <i class="fa-solid fa-circle-exclamation me-2"></i><?= e($error) ?>
        </div>
    <?php endif; ?>

    <!-- 2. Status Tracker Card -->
    <?php
        $steps = ['assigned', 'picked_up', 'out_for_delivery', 'delivered'];
        $step_labels = ['Assigned', 'Picked Up', 'Out for Delivery', 'Delivered'];
        $current_step_idx = 0;
        if ($state === 'picked_up') $current_step_idx = 1;
        elseif (in_array($state, ['out_for_delivery', 'arrived', 'payment_pending', 'proof_pending'])) $current_step_idx = 2;
        elseif ($state === 'delivered') $current_step_idx = 3;
    ?>
    <div class="status-tracker-card">
        <div class="tracker-steps">
            <?php foreach ($steps as $idx => $s): ?>
                <?php
                    $is_active = ($idx === $current_step_idx);
                    $is_completed = ($idx < $current_step_idx);
                ?>
                <div class="tracker-step <?= $is_active ? 'active' : '' ?> <?= $is_completed ? 'completed' : '' ?>">
                    <div class="step-circle">
                        <?php if ($is_completed): ?>
                            <i class="fa-solid fa-check"></i>
                        <?php else: ?>
                            <?= $idx + 1 ?>
                        <?php endif; ?>
                    </div>
                    <span class="step-label"><?= $step_labels[$idx] ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 3. Customer Info Card with Call & Map buttons -->
    <div class="detail-card">
        <div class="detail-card-title">
            <i class="fa-solid fa-user"></i>
            <span>Customer Details</span>
        </div>
        <div class="customer-row">
            <div class="customer-meta">
                <div class="customer-avatar">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <div class="customer-name"><?= e($customer_name) ?></div>
                    <div class="customer-phone"><?= e($customer_phone ?: 'No Phone Number') ?></div>
                </div>
            </div>

            <div class="quick-action-buttons">
                <?php if ($customer_phone): ?>
                    <a href="tel:<?= e($customer_phone) ?>" class="btn-quick-action btn-quick-call" title="Call Customer">
                        <i class="fa-solid fa-phone"></i>
                    </a>
                <?php endif; ?>
                <a href="<?= e($maps_url) ?>" target="_blank" class="btn-quick-action btn-quick-maps" title="Open in Maps">
                    <i class="fa-solid fa-location-arrow"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- 4. Complete Delivery Address Card -->
    <div class="detail-card">
        <div class="detail-card-title">
            <i class="fa-solid fa-location-dot"></i>
            <span>Delivery Address</span>
        </div>
        <div class="address-box">
            <?= nl2br(e($address)) ?>
            <?php if ($pincode): ?> - <?= e($pincode) ?><?php endif; ?>
        </div>
    </div>

    <!-- 5. Ordered Products Card -->
    <div class="detail-card">
        <div class="detail-card-title">
            <i class="fa-solid fa-bag-shopping"></i>
            <span>Ordered Products (<?= count($items) ?>)</span>
        </div>
        <div class="products-list">
            <?php foreach ($items as $item): ?>
                <?php
                    $imgUrl = getDeliveryProductImageUrl($item['images'] ?? '');
                    $fallbackUrl = !empty($imgUrl) && strpos($imgUrl, 'http') === 0 ? 'https://admin.mandal-variety.com/uploads/' . rawurlencode(basename($imgUrl)) : '';
                    $itemQty = (int)$item['quantity'];
                    $itemPrice = (float)$item['price'];
                ?>
                <div class="product-item">
                    <?php if (!empty($imgUrl)): ?>
                        <img src="<?= e($imgUrl) ?>" alt="Product" class="product-thumb" onerror="if (this.src.indexOf('admin.mandal-variety.com') === -1 && '<?= e($fallbackUrl) ?>') { this.src='<?= e($fallbackUrl) ?>'; } else { this.onerror=null; this.style.display='none'; }">
                    <?php else: ?>
                        <div class="product-thumb-placeholder">
                            <i class="fa-solid fa-box"></i>
                        </div>
                    <?php endif; ?>
                    <div class="product-info">
                        <div class="product-title"><?= e($item['product_name'] ?? 'Product') ?></div>
                        <div class="product-qty-badge">Qty: <?= $itemQty ?> &times; ₹<?= number_format($itemPrice, 0) ?></div>
                    </div>
                    <div class="product-price">₹<?= number_format($itemPrice * $itemQty, 0) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 6. Payment & Billing Details Card -->
    <div class="detail-card">
        <div class="detail-card-title">
            <i class="fa-solid fa-receipt"></i>
            <span>Payment Summary</span>
        </div>
        <div class="bill-row">
            <span>Items Subtotal</span>
            <span>₹<?= number_format($items_subtotal, 0) ?></span>
        </div>
        <div class="bill-row">
            <span>Delivery Charge</span>
            <span>₹<?= number_format($delivery_charge, 0) ?></span>
        </div>
        <div class="bill-row total-row">
            <span>Grand Total</span>
            <span style="color:var(--primary-green);">₹<?= number_format($grand_total, 0) ?></span>
        </div>
        <div style="margin-top: 14px; display: flex; gap: 8px;">
            <div class="badge-status" style="<?= $is_cod ? 'background:#FEF3C7; color:#B45309;' : 'background:#DCFCE7; color:#15803D;' ?>">
                <span class="status-dot" style="<?= $is_cod ? 'background:#B45309;' : 'background:#15803D;' ?>"></span>
                <span><?= $is_cod ? 'Cash on Delivery (COD)' : 'Paid Online' ?></span>
            </div>
            <div class="badge-status" style="background:#F1F5F9; color:#475569;">
                <span>Payment: <?= e(ucfirst($order['payment_status'] ?? 'Pending')) ?></span>
            </div>
        </div>
    </div>

    <!-- 7. Interactive State Actions & Modals -->
    <?php if ($state === 'arrived'): ?>
        <!-- Customer OTP Input Modal/Card -->
        <div class="detail-card" style="text-align: center; padding: 24px 18px;">
            <div style="width: 54px; height: 54px; border-radius: 50%; background: var(--light-green); color: var(--primary-green); display: inline-flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 12px;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h3 style="font-size: 17px; font-weight: 800; color: var(--primary-text); margin-bottom: 4px;">Enter Customer OTP</h3>
            <p style="font-size: 13px; color: var(--secondary-text); margin-bottom: 16px;">Ask the customer for the 6-digit delivery verification code.</p>

            <button type="button" class="btn-outline" id="btnSendCustomerOtp" onclick="sendOtpToCustomer()">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Send OTP to Customer</span>
            </button>
            <div id="otpSentBadge" style="display: none; margin-top: 8px; color: var(--primary-green); font-size: 12.5px; font-weight: 700;">
                <i class="fa-solid fa-circle-check me-1"></i> OTP sent successfully!
            </div>

            <form method="POST" id="otpSubmitForm" class="mt-3">
                <input type="hidden" name="action" value="verify_otp">
                <input type="hidden" name="otp" id="fullOtpHidden">
                <div class="otp-grid">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                        <input type="tel" class="otp-digit-input" maxlength="1" pattern="[0-9]*" inputmode="numeric" required>
                    <?php endfor; ?>
                </div>
            </form>
        </div>
    <?php elseif ($state === 'payment_pending'): ?>
        <!-- Cash Collection Card -->
        <div class="detail-card" style="text-align: center; padding: 24px 18px;">
            <div style="width: 54px; height: 54px; border-radius: 50%; background: #FEF3C7; color: #B45309; display: inline-flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 12px;">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <h3 style="font-size: 17px; font-weight: 800; color: var(--primary-text); margin-bottom: 4px;">Collect Cash on Delivery</h3>
            <div style="font-size: 28px; font-weight: 800; color: var(--primary-green); margin: 10px 0;">₹<?= number_format($grand_total, 0) ?></div>
            <p style="font-size: 13px; color: var(--secondary-text);">Please collect exact cash from customer before marking complete.</p>
        </div>
    <?php elseif ($state === 'delivered'): ?>
        <!-- Success Screen -->
        <div class="success-hero">
            <div class="success-hero-circle">
                <i class="fa-solid fa-check"></i>
            </div>
            <h2 style="font-size: 22px; font-weight: 800; color: var(--primary-text); margin-bottom: 4px;">Delivered Successfully!</h2>
            <p style="font-size: 14px; color: var(--secondary-text); margin-bottom: 20px;">This order has been completed and added to your delivery history.</p>
        </div>
    <?php endif; ?>

</div>

<!-- 8. Fixed Bottom Action Buttons -->
<div class="fixed-action-bar">
    <?php if ($state === 'assigned'): ?>
        <form method="POST" class="w-100 d-flex gap-2 m-0" style="display: flex; width: 100%; gap: 10px;">
            <button type="submit" name="action" value="reject" class="btn-action-full btn-action-danger">
                <i class="fa-solid fa-xmark"></i>
                <span>Reject</span>
            </button>
            <button type="submit" name="action" value="accept" class="btn-action-full">
                <i class="fa-solid fa-check"></i>
                <span>Accept Delivery</span>
            </button>
        </form>
    <?php elseif ($state === 'accepted'): ?>
        <form method="POST" class="w-100 m-0" style="width: 100%;">
            <button type="submit" name="action" value="confirm_pickup" class="btn-action-full w-100">
                <i class="fa-solid fa-box"></i>
                <span>Confirm Pickup & Start Delivery</span>
            </button>
        </form>
    <?php elseif ($state === 'picked_up'): ?>
        <form method="POST" class="w-100 m-0" style="width: 100%;">
            <button type="submit" name="action" value="update_status" class="btn-action-full w-100">
                <i class="fa-solid fa-motorcycle"></i>
                <span>Out for Delivery</span>
            </button>
        </form>
    <?php elseif ($state === 'out_for_delivery'): ?>
        <form method="POST" class="w-100 m-0" style="width: 100%;">
            <button type="submit" name="action" value="confirm_arrival" class="btn-action-full w-100">
                <i class="fa-solid fa-location-dot"></i>
                <span>Confirm Arrival</span>
            </button>
        </form>
    <?php elseif ($state === 'arrived'): ?>
        <button type="button" class="btn-action-full w-100" onclick="submitCustomerOtp()">
            <i class="fa-solid fa-check-double"></i>
            <span>Verify OTP & Proceed</span>
        </button>
    <?php elseif ($state === 'payment_pending'): ?>
        <form method="POST" class="w-100 m-0" style="width: 100%;">
            <button type="submit" name="action" value="confirm_payment" class="btn-action-full w-100">
                <i class="fa-solid fa-hand-holding-dollar"></i>
                <span>Cash Received & Continue</span>
            </button>
        </form>
    <?php elseif ($state === 'proof_pending'): ?>
        <form method="POST" class="w-100 m-0" style="width: 100%;">
            <button type="submit" name="action" value="submit_proof" class="btn-action-full w-100">
                <i class="fa-solid fa-circle-check"></i>
                <span>Mark as Delivered</span>
            </button>
        </form>
    <?php elseif ($state === 'delivered'): ?>
        <a href="index.php" class="btn-action-full w-100">
            <i class="fa-solid fa-house"></i>
            <span>Back to Home</span>
        </a>
    <?php endif; ?>
</div>

<script>
// OTP Auto-focus & digit handling
const otpInputs = Array.from(document.querySelectorAll('.otp-digit-input'));
const hiddenOtpInput = document.getElementById('fullOtpHidden');

if (otpInputs.length > 0) {
    otpInputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            const val = input.value.replace(/\D/g, '');
            input.value = val;
            if (val && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
            syncOtpHidden();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !input.value && index > 0) {
                otpInputs[index - 1].focus();
                otpInputs[index - 1].value = '';
                syncOtpHidden();
            }
        });

        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            for (let i = 0; i < text.length && i < otpInputs.length; i++) {
                otpInputs[i].value = text[i];
            }
            syncOtpHidden();
            if (text.length >= otpInputs.length) {
                otpInputs[otpInputs.length - 1].focus();
            }
        });
    });
}

function syncOtpHidden() {
    if (!hiddenOtpInput) return '';
    const full = otpInputs.map(i => i.value.trim()).join('');
    hiddenOtpInput.value = full;
    return full;
}

function submitCustomerOtp() {
    const otp = syncOtpHidden();
    if (otp.length !== 6) {
        showMvToast('Please enter the 6-digit OTP', false);
        return;
    }
    document.getElementById('otpSubmitForm').submit();
}

function sendOtpToCustomer() {
    const btn = document.getElementById('btnSendCustomerOtp');
    const badge = document.getElementById('otpSentBadge');
    if (!btn) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Sending...</span>';

    const formData = new FormData();
    formData.append('order_id', <?= $order_id ?>);

    fetch('ajax_send_otp.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>Resend OTP</span>';
        if (data.success) {
            badge.style.display = 'block';
            showMvToast('OTP sent to customer successfully', true);
            if (otpInputs.length > 0) otpInputs[0].focus();
        } else {
            showMvToast(data.message || 'Failed to send OTP', false);
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>Send OTP to Customer</span>';
        showMvToast('Network connection error', false);
    });
}
</script>

<?php include 'includes/footer.php'; ?>
