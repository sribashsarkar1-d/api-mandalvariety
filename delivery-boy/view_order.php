<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = $_SESSION['delivery_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    header("Location: index.php");
    exit;
}

// Fetch order
$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name, u.phone as user_phone
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

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_status') {
        $new_status = $_POST['status'] ?? '';
        if ($new_status) {
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);
            // Refresh order
            $order['status'] = $new_status;
        }
        $next_step = $_POST['next_step'] ?? '';
        if ($next_step) {
            header("Location: view_order.php?id=$order_id&step=$next_step");
            exit;
        }
    }
}

// Items
$stmtItems = $conn->prepare("
    SELECT oi.*, p.name as product_name
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmtItems->execute([$order_id]);
$items = $stmtItems->fetchAll();

$customer_name = $order['user_name'] ?? $order['customer_name'] ?? 'Customer';
$customer_phone = $order['user_phone'] ?? $order['phone'] ?? '';
$order_no = $order['order_number'] ?? $order['order_no'] ?? $order['id'];
$amount = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0);
$pm = strtoupper($order['payment_method'] ?? 'COD');
$is_cod = ($pm === 'COD' || strpos(strtolower($pm), 'cash') !== false);

$step = isset($_GET['step']) ? $_GET['step'] : 'details';
if ($order['status'] === 'assigned' && $step === 'details') {
    $step = 'accept_reject';
}

// Helper to render layout
?>
<?php include 'includes/header.php'; ?>

<style>
    body { background-color: #f8fafc; }
    .page-header {
        display: flex;
        align-items: center;
        padding: 20px;
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .back-btn { color: var(--text-dark); font-size: 1.2rem; margin-right: 15px; text-decoration: none; }
    .page-title { font-size: 18px; font-weight: 700; margin: 0; flex: 1; }
    
    .screen-container {
        padding: 20px;
        padding-bottom: 100px;
        min-height: calc(100vh - 70px);
        display: flex;
        flex-direction: column;
    }
    
    .bottom-action-bar {
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100%;
        max-width: 480px;
        background: #fff;
        padding: 15px 20px;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.05);
        display: flex;
        gap: 15px;
        z-index: 100;
    }
    
    .btn-green-full {
        background: var(--mandal-green);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 16px;
        font-size: 16px;
        font-weight: 700;
        width: 100%;
        text-align: center;
        text-decoration: none;
        display: block;
    }
    .btn-green-full:active { background: #058547; color: white; }
    
    .btn-red-full {
        background: #ef4444;
        color: white;
        border: none;
        border-radius: 12px;
        padding: 16px;
        font-size: 16px;
        font-weight: 700;
        width: 100%;
        text-align: center;
        text-decoration: none;
        display: block;
    }

    .btn-outline-green {
        background: transparent;
        color: var(--mandal-green);
        border: 2px solid var(--mandal-green);
        border-radius: 12px;
        padding: 14px;
        font-size: 16px;
        font-weight: 700;
        width: 100%;
        text-align: center;
        text-decoration: none;
        display: block;
    }

    /* Screen Specific Styles */
    .illustration-box {
        text-align: center;
        margin: 40px 0;
    }
    .illustration-box img {
        width: 200px;
        height: auto;
    }
    .illustration-text {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dark);
        margin-top: 20px;
    }
    .illustration-sub {
        font-size: 14px;
        color: #64748b;
        margin-top: 8px;
    }

    .order-info-card {
        background: #fff;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        margin-bottom: 20px;
    }
    .order-info-card .row-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }
    .order-info-card .row-flex:last-child {
        margin-bottom: 0;
    }
    
    .cod-badge {
        background: #f59e0b;
        color: white;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
    }

    /* Map Box */
    .map-box {
        background: #e2e8f0;
        border-radius: 16px;
        height: 400px;
        width: 100%;
        position: relative;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .map-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .map-overlay-card {
        position: absolute;
        bottom: 20px;
        right: 20px;
        background: #fff;
        padding: 10px 15px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        text-align: center;
        font-weight: 700;
    }
    
    /* OTP inputs */
    .otp-grid {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin: 30px 0;
    }
    .otp-input {
        width: 50px;
        height: 60px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        font-size: 24px;
        font-weight: 700;
        text-align: center;
        color: var(--text-dark);
    }
    .otp-input:focus {
        border-color: var(--mandal-green);
        outline: none;
    }

    /* Success Screen */
    .success-bg {
        background: var(--mandal-green);
        color: white;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 20px;
    }
    .check-circle {
        width: 100px;
        height: 100px;
        background: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--mandal-green);
        font-size: 50px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .stars {
        color: #fcd34d;
        font-size: 30px;
        margin: 20px 0 40px 0;
        letter-spacing: 5px;
    }

</style>

<?php
// Function to render forms easily
function renderStepForm($actionStatus, $nextStep, $btnText, $btnClass = 'btn-green-full', $isForm = true) {
    global $order_id;
    if (!$isForm) return "<a href='view_order.php?id=$order_id&step=$nextStep' class='$btnClass'>$btnText</a>";
    return "
    <form method='POST' style='width:100%;'>
        <input type='hidden' name='action' value='update_status'>
        <input type='hidden' name='status' value='$actionStatus'>
        <input type='hidden' name='next_step' value='$nextStep'>
        <button type='submit' class='$btnClass'>$btnText</button>
    </form>";
}
?>

<?php if ($step === 'accept_reject'): ?>
    <div class="page-header">
        <a href="index.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <h1 class="page-title">New Delivery Request</h1>
    </div>
    <div class="screen-container">
        <div class="order-info-card">
            <div class="row-flex mb-3">
                <div>
                    <div style="font-size:14px; color:#64748b; margin-bottom:4px;">Order #<?= $order_no ?></div>
                    <div style="font-size:24px; font-weight:800;">₹<?= number_format($amount, 0) ?> <span class="cod-badge" style="vertical-align: middle;"><?= $pm ?></span></div>
                </div>
            </div>
            
            <div class="row-flex" style="justify-content: flex-start; gap: 15px; margin-bottom: 20px;">
                <div style="width: 40px; height: 40px; background: #e2e8f0; border-radius: 50%; display: flex; align-items:center; justify-content:center;">
                    <i class="fa-solid fa-user text-muted"></i>
                </div>
                <div>
                    <div style="font-weight:700; color:var(--text-dark);"><?= e($customer_name) ?></div>
                    <div style="font-size:13px; color:#64748b;"><?= e($customer_phone) ?></div>
                </div>
            </div>
            
            <div class="row-flex" style="justify-content: flex-start; gap: 15px; align-items: flex-start;">
                <div style="color:var(--mandal-green); font-size:18px; margin-top:2px;"><i class="fa-solid fa-location-dot"></i></div>
                <div>
                    <div style="font-weight:600; font-size:14px; color:var(--text-dark);">Delivery Address</div>
                    <div style="font-size:13px; color:#64748b; margin-top:4px; line-height:1.4;">
                        <?= e($order['shipping_address'] ?? 'No Address') ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bottom-action-bar">
            <form method="POST" style="flex:1;">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="status" value="cancelled">
                <input type="hidden" name="next_step" value="details">
                <button type="submit" class="btn-red-full">Reject</button>
            </form>
            <form method="POST" style="flex:1;">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="status" value="accepted">
                <input type="hidden" name="next_step" value="details">
                <button type="submit" class="btn-green-full">Accept</button>
            </form>
        </div>
    </div>

<?php elseif ($step === 'details'): ?>
    <div class="page-header">
        <a href="index.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <h1 class="page-title">Order Details</h1>
        <?php if ($pm === 'COD'): ?><span class="cod-badge">COD</span><?php endif; ?>
    </div>
    <div class="screen-container">
        <div class="order-info-card">
            <div style="font-size:14px; font-weight:700; margin-bottom:4px;">#<?= $order_no ?></div>
            <div class="row-flex" style="justify-content:flex-start; gap:10px; margin-bottom:15px;">
                <i class="fa-solid fa-user text-muted"></i>
                <span style="font-weight:600;"><?= e($customer_name) ?></span>
                <a href="tel:<?= e($customer_phone) ?>" style="margin-left:auto; color:#3b82f6;"><i class="fa-solid fa-phone"></i></a>
            </div>
        </div>
        
        <div class="order-info-card">
            <h3 style="font-size:15px; font-weight:700; margin-bottom:15px;">Items (<?= count($items) ?>)</h3>
            <?php foreach ($items as $item): ?>
                <div class="row-flex" style="margin-bottom:10px; font-size:14px;">
                    <div style="flex:2; font-weight:500; color:var(--text-dark);"><i class="fa-solid fa-square-xs text-muted me-2"></i><?= e($item['product_name']) ?></div>
                    <div style="flex:1; text-align:center; color:#64748b;"><?= $item['quantity'] ?></div>
                    <div style="flex:1; text-align:right; font-weight:600;">₹<?= number_format($item['price'], 0) ?></div>
                </div>
            <?php endforeach; ?>
            <hr style="border-color:#f1f5f9; margin:15px 0;">
            <div class="row-flex" style="font-size:14px; margin-bottom:8px;">
                <span style="color:#64748b;">Subtotal</span>
                <span style="font-weight:600;">₹<?= number_format($amount, 0) ?></span>
            </div>
            <div class="row-flex" style="font-size:14px; margin-bottom:15px;">
                <span style="color:#64748b;">Delivery Charge</span>
                <span style="font-weight:600;">₹0</span>
            </div>
            <div class="row-flex" style="font-size:16px; font-weight:800;">
                <span>Total Amount</span>
                <span>₹<?= number_format($amount, 0) ?></span>
            </div>
        </div>
        
        <div class="bottom-action-bar">
            <?= renderStepForm('', 'nav', 'Start Delivery', 'btn-green-full', false) ?>
        </div>
    </div>

<?php elseif ($step === 'nav'): ?>
    <div class="page-header">
        <a href="view_order.php?id=<?= $order_id ?>&step=details" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <h1 class="page-title">Navigate to Customer</h1>
    </div>
    <div class="screen-container" style="padding:0;">
        <div class="map-box" style="height: 100%; border-radius:0; margin:0; flex:1;">
            <!-- Simulating map with a static background pattern -->
            <div style="width:100%; height:100%; background: #e2e8f0; background-image: radial-gradient(#cbd5e1 2px, transparent 2px); background-size: 20px 20px; position:relative;">
                
                <svg width="100%" height="100%" style="position:absolute; top:0; left:0;">
                    <path d="M 100 300 Q 150 200 200 150 T 300 100" fill="none" stroke="#3b82f6" stroke-width="6" stroke-dasharray="8 8"/>
                </svg>
                <div style="position:absolute; top:280px; left:80px; font-size:30px; color:#3b82f6;"><i class="fa-solid fa-circle-dot"></i></div>
                <div style="position:absolute; top:70px; left:280px; font-size:36px; color:#ef4444;"><i class="fa-solid fa-location-dot"></i></div>

                <div class="map-overlay-card">
                    <div style="font-size:18px;">2.4 km</div>
                    <div style="font-size:13px; color:#64748b;">8 mins</div>
                </div>
            </div>
        </div>
        
        <div class="bottom-action-bar" style="flex-direction:column; padding-bottom: 20px;">
            <a href="https://maps.google.com/?q=<?= urlencode($order['shipping_address'] ?? '') ?>" target="_blank" class="btn-outline-green" style="margin-bottom:10px;">
                <i class="fa-solid fa-location-arrow me-2"></i> Open in Google Maps
            </a>
            <?= renderStepForm('', 'pickup', 'Arrived at Store', 'btn-green-full', false) ?>
        </div>
    </div>

<?php elseif ($step === 'pickup'): ?>
    <div class="page-header">
        <a href="view_order.php?id=<?= $order_id ?>&step=nav" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <h1 class="page-title">Picking Up Order</h1>
    </div>
    <div class="screen-container">
        <div class="illustration-box">
            <i class="fa-solid fa-boxes-packing" style="font-size:100px; color:var(--mandal-green);"></i>
            <div class="illustration-text">Mark Order as Picked Up?</div>
            <div class="illustration-sub">Order #<?= $order_no ?></div>
        </div>
        
        <div class="bottom-action-bar">
            <?= renderStepForm('out_for_delivery', 'out_for_delivery', 'Confirm Pickup') ?>
        </div>
    </div>

<?php elseif ($step === 'out_for_delivery'): ?>
    <div class="page-header">
        <a href="view_order.php?id=<?= $order_id ?>&step=pickup" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <h1 class="page-title">Out for Delivery</h1>
    </div>
    <div class="screen-container">
        <div class="illustration-box">
            <i class="fa-solid fa-motorcycle" style="font-size:100px; color:var(--mandal-green);"></i>
            <div class="illustration-text">Order is Out for Delivery</div>
            <div class="illustration-sub">#<?= $order_no ?></div>
        </div>
        
        <div class="bottom-action-bar">
            <?= renderStepForm('', 'arrived', 'Update Status (Arrived)', 'btn-green-full', false) ?>
        </div>
    </div>

<?php elseif ($step === 'arrived'): ?>
    <div class="page-header">
        <a href="view_order.php?id=<?= $order_id ?>&step=out_for_delivery" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <h1 class="page-title">Arrived at Location</h1>
    </div>
    <div class="screen-container">
        <div class="illustration-box">
            <div style="width:120px; height:120px; background:#e8f7f0; border-radius:50%; margin:0 auto; display:flex; align-items:center; justify-content:center;">
                <i class="fa-solid fa-location-dot" style="font-size:50px; color:var(--mandal-green);"></i>
            </div>
            <div class="illustration-text">You have arrived<br>at the customer location</div>
        </div>
        
        <div class="bottom-action-bar">
            <?= renderStepForm('', 'otp', 'Confirm Arrival', 'btn-green-full', false) ?>
        </div>
    </div>

<?php elseif ($step === 'otp'): ?>
    <div class="page-header">
        <a href="view_order.php?id=<?= $order_id ?>&step=arrived" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <h1 class="page-title">Complete Delivery</h1>
    </div>
    <div class="screen-container">
        <div class="illustration-box" style="margin-bottom:10px;">
            <i class="fa-solid fa-mobile-screen-button" style="font-size:60px; color:var(--mandal-green);"></i>
            <div class="illustration-text">Enter Customer OTP</div>
        </div>
        
        <div class="otp-grid">
            <input type="text" class="otp-input" maxlength="1" value="2" readonly>
            <input type="text" class="otp-input" maxlength="1" value="4" readonly>
            <input type="text" class="otp-input" maxlength="1" value="7" readonly>
            <input type="text" class="otp-input" maxlength="1" value="1" readonly>
        </div>
        
        <div style="text-align:center; margin-top:10px;">
            <a href="#" style="color:#3b82f6; text-decoration:underline; font-size:14px; font-weight:600;">Customer didn't get OTP?</a>
        </div>
        
        <div class="bottom-action-bar">
            <?php $next = $is_cod ? 'cod' : 'proof'; ?>
            <?= renderStepForm('', $next, 'Verify OTP', 'btn-green-full', false) ?>
        </div>
    </div>

<?php elseif ($step === 'cod'): ?>
    <div class="page-header">
        <a href="view_order.php?id=<?= $order_id ?>&step=otp" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <h1 class="page-title">Collect Payment</h1>
    </div>
    <div class="screen-container">
        <div style="font-size:14px; color:#64748b; margin-bottom:5px;">Order Amount</div>
        <div style="font-size:32px; font-weight:800; color:var(--text-dark); margin-bottom:30px;">₹<?= number_format($amount, 0) ?></div>
        
        <div class="order-info-card" style="display:flex; align-items:center; gap:15px; margin-bottom:30px;">
            <i class="fa-solid fa-money-bill-wave text-success" style="font-size:24px;"></i>
            <div>
                <div style="font-size:12px; color:#64748b;">Payment Type</div>
                <div style="font-size:16px; font-weight:700;">Cash on Delivery (COD)</div>
            </div>
        </div>
        
        <div style="font-size:14px; color:#64748b; margin-bottom:10px; font-weight:600;">Amount Received</div>
        <input type="text" value="₹<?= number_format($amount, 0) ?>" readonly style="width:100%; padding:15px; border-radius:12px; border:1px solid #cbd5e1; font-size:18px; font-weight:700; background:#f1f5f9;">
        
        <div class="bottom-action-bar">
            <?= renderStepForm('', 'proof', 'Confirm Payment', 'btn-green-full', false) ?>
        </div>
    </div>

<?php elseif ($step === 'proof'): ?>
    <div class="page-header">
        <a href="view_order.php?id=<?= $order_id ?>&step=<?= $is_cod ? 'cod' : 'otp' ?>" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <h1 class="page-title">Proof of Delivery</h1>
    </div>
    <div class="screen-container">
        <div style="display:flex; background:#e2e8f0; border-radius:30px; padding:4px; margin-bottom:20px;">
            <div style="flex:1; background:var(--mandal-green); color:white; text-align:center; padding:10px; border-radius:30px; font-weight:600; font-size:14px;">Take Photo</div>
            <div style="flex:1; color:#64748b; text-align:center; padding:10px; font-weight:600; font-size:14px;">Customer Signature</div>
        </div>
        
        <div style="background:#e2e8f0; border-radius:16px; height:300px; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative;">
            <!-- Simulating photo view -->
            <div style="font-size:50px; color:#cbd5e1;"><i class="fa-solid fa-camera"></i></div>
            
            <div style="position:absolute; bottom:15px; left:15px; width:60px; height:60px; background:#fff; border-radius:8px; border:2px solid white; overflow:hidden;">
                <div style="width:100%; height:100%; background:#cbd5e1;"></div>
            </div>
        </div>
        
        <div class="bottom-action-bar">
            <?= renderStepForm('delivered', 'success', 'Use Photo') ?>
        </div>
    </div>

<?php elseif ($step === 'success'): ?>
    <!-- No header, full screen green -->
    <div class="success-bg">
        <div class="check-circle">
            <i class="fa-solid fa-check"></i>
        </div>
        
        <h1 style="font-size:28px; font-weight:800; margin-bottom:10px;">Delivery Successful!</h1>
        <div style="font-size:16px; opacity:0.9; margin-bottom:15px;">Order #<?= $order_no ?></div>
        
        <?php if ($is_cod): ?>
            <div style="font-size:24px; font-weight:800; margin-bottom:40px;">₹<?= number_format($amount, 0) ?> Collected</div>
        <?php else: ?>
            <div style="margin-bottom:40px;"></div>
        <?php endif; ?>
        
        <div style="font-size:14px; font-weight:600; opacity:0.9;">Rate Customer (Optional)</div>
        <div class="stars">
            ★★★★★
        </div>
        
        <a href="index.php" style="background:#ffffff; color:var(--mandal-green); border-radius:12px; padding:16px; font-size:18px; font-weight:800; width:100%; max-width:300px; text-decoration:none; display:block; margin:0 auto;">Done</a>
    </div>

<?php endif; ?>

<?php include 'includes/footer.php'; ?>
