<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = $_SESSION['delivery_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

// Auto-add delivery_otp and delivery_boy_status columns
try {
    $conn->query("SELECT delivery_otp, delivery_boy_status FROM orders LIMIT 1");
} catch (\PDOException $e) {
    try {
        $conn->exec("ALTER TABLE orders ADD COLUMN delivery_otp VARCHAR(10) NULL DEFAULT NULL");
        $conn->exec("ALTER TABLE orders ADD COLUMN delivery_boy_status VARCHAR(50) DEFAULT 'assigned'");
    } catch (\PDOException $e2) {}
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
            $error = "Please resend OTP to customer.";
        } elseif ($entered_otp !== $order['delivery_otp']) {
            $error = "Invalid OTP. Try again.";
        } else {
            // Check if payment is needed
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

// Fetch items
$stmtItems = $conn->prepare("
    SELECT oi.*, p.name as product_name
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmtItems->execute([$order_id]);
$items = $stmtItems->fetchAll();

$order_no = $order['order_number'] ?? $order['order_no'] ?? 'N/A';
$customer_name = $order['user_name'] ?? $order['customer_name'] ?? $order['name'] ?? 'Customer';
$customer_phone = $order['user_phone'] ?? $order['customer_phone'] ?? $order['phone'] ?? 'N/A';
$address = $order['shipping_address'] ?? $order['delivery_address'] ?? $order['address'] ?? '';
$pincode = $order['shipping_pincode'] ?? $order['delivery_pincode'] ?? $order['pincode'] ?? '';
$grand_total = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0);

?>

<?php include 'includes/header.php'; ?>

<style>
    body { background-color: #ffffff; }
    .page-container { padding: 24px 20px; padding-bottom: 100px; display: flex; flex-direction: column; min-height: 100vh; }
    
    .page-header { display: flex; align-items: center; gap: 15px; margin-bottom: 24px; }
    .back-btn { color: var(--text-dark); text-decoration: none; font-size: 1.2rem; }
    .page-title { font-weight: 800; font-size: 1.25rem; margin: 0; color: var(--text-dark); }
    
    .btn-bottom {
        background: var(--mandal-green);
        color: white; border: none; border-radius: 12px; padding: 16px; width: 100%;
        font-size: 1rem; font-weight: 700; text-align: center; display: block;
        text-decoration: none; transition: transform 0.2s; cursor: pointer; margin-top: auto;
    }
    .btn-bottom:active { transform: scale(0.98); }
    
    .btn-reject { background: #fee2e2; color: #ef4444; }
    
    /* Assigned / Accepted State */
    .order-card { background: #ffffff; border-radius: 20px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 24px; border: 1px solid rgba(0,0,0,0.02); }
    .cod-badge { background: #f59e0b; color: white; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; }
    
    /* Illustration States */
    .state-illustration { text-align: center; margin-top: 40px; margin-bottom: 40px; }
    .state-icon-circle { width: 120px; height: 120px; border-radius: 50%; background: #e8f7f0; color: var(--mandal-green); display: flex; align-items: center; justify-content: center; font-size: 3rem; margin: 0 auto 20px; box-shadow: 0 10px 25px rgba(7, 161, 88, 0.2); }
    .state-title { font-size: 1.4rem; font-weight: 800; color: var(--text-dark); margin-bottom: 8px; }
    .state-subtitle { font-size: 0.95rem; color: var(--text-muted); font-weight: 500; margin-bottom: 20px; }

    /* OTP State */
    .otp-inputs { display: flex; gap: 10px; justify-content: center; margin: 30px 0; }
    .otp-box { width: 50px; height: 60px; border: 2px solid #e2e8f0; border-radius: 12px; text-align: center; font-size: 1.5rem; font-weight: 800; color: var(--text-dark); }
    .otp-box:focus { border-color: var(--mandal-green); outline: none; }

    /* Payment State */
    .payment-box { background: #f8fafc; border-radius: 16px; padding: 24px; text-align: center; margin-bottom: 30px; border: 1px solid #e2e8f0; }
    .payment-amount { font-size: 2.5rem; font-weight: 800; color: var(--text-dark); margin: 10px 0; }
    
    /* Proof State */
    .photo-placeholder { background: #f1f5f9; border: 2px dashed #cbd5e1; border-radius: 16px; height: 200px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #64748b; margin-bottom: 20px; cursor: pointer; }
    .photo-placeholder i { font-size: 2.5rem; margin-bottom: 10px; }

    /* Success State */
    .success-circle { width: 100px; height: 100px; background: var(--mandal-green); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; margin: 40px auto 20px; box-shadow: 0 10px 30px rgba(7, 161, 88, 0.3); }

</style>

<div class="page-container">

    <?php if ($error): ?>
        <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4"><i class="fa-solid fa-circle-exclamation me-2"></i><?= e($error) ?></div>
    <?php endif; ?>

    <!-- STATE: ASSIGNED or ACCEPTED (Order Details) -->
    <?php if ($state === 'assigned' || $state === 'accepted'): ?>
        <div class="page-header">
            <a href="index.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <h4 class="page-title"><?= $state === 'assigned' ? 'New Delivery Request' : 'Order Details' ?></h4>
        </div>
        
        <div class="order-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="fw-bold fs-5">#<?= e($order_no) ?></div>
                <div class="cod-badge"><?= $is_cod ? 'COD' : 'PAID' ?></div>
            </div>
            
            <div class="d-flex align-items-center mb-4">
                <div class="customer-avatar me-3" style="width: 40px; height: 40px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #94a3b8;"><i class="fa-solid fa-user"></i></div>
                <div class="flex-grow-1">
                    <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Customer</div>
                    <div class="fw-bold text-dark"><?= e($customer_name) ?></div>
                    <div style="font-size:0.85rem; color:var(--text-muted);"><?= e($customer_phone) ?></div>
                </div>
                <a href="tel:<?= e($customer_phone) ?>" style="width: 40px; height: 40px; border-radius: 50%; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; text-decoration: none;"><i class="fa-solid fa-phone"></i></a>
            </div>

            <div class="fw-bold mb-2">Items (<?= count($items) ?>)</div>
            <?php foreach ($items as $item): ?>
                <div class="d-flex justify-content-between text-muted small mb-2">
                    <div><i class="fa-solid fa-square me-2" style="color:#cbd5e1; font-size:10px;"></i><?= e($item['product_name']) ?></div>
                    <div style="width:20px; text-align:center;"><?= (int)$item['quantity'] ?></div>
                    <div style="width:50px; text-align:right;">₹<?= number_format((float)$item['price'], 0) ?></div>
                </div>
            <?php endforeach; ?>

            <div style="background:#f8fafc; border-radius:12px; padding:15px; margin-top:20px;">
                <div class="d-flex justify-content-between text-muted small mb-2"><span>Subtotal</span><span>₹<?= number_format($grand_total, 0) ?></span></div>
                <div class="d-flex justify-content-between text-muted small mb-3"><span>Delivery Charge</span><span>₹0</span></div>
                <div class="d-flex justify-content-between fw-bold text-dark pt-2" style="border-top:1px dashed #cbd5e1;"><span>Total Amount</span><span>₹<?= number_format($grand_total, 0) ?></span></div>
            </div>

            <div class="mt-4 pt-3" style="border-top:1px dashed #e2e8f0;">
                <div style="font-size:0.75rem; color:var(--text-muted); font-weight:700; text-transform:uppercase; margin-bottom:5px;">Delivery Address</div>
                <div class="fw-bold text-dark small" style="line-height:1.4;">
                    <?= nl2br(e($address)) ?>
                    <?php if ($pincode): ?> - <?= e($pincode) ?><?php endif; ?>
                </div>
            </div>
        </div>

        <form method="POST" class="mt-auto d-flex gap-3">
            <?php if ($state === 'assigned'): ?>
                <button type="submit" name="action" value="reject" class="btn-bottom btn-reject w-50">Reject</button>
                <button type="submit" name="action" value="accept" class="btn-bottom w-50">Accept</button>
            <?php else: ?>
                <button type="submit" name="action" value="confirm_pickup" class="btn-bottom">Confirm Pickup</button>
            <?php endif; ?>
        </form>

    <!-- STATE: PICKED UP (En route) -->
    <?php elseif ($state === 'picked_up'): ?>
        <div class="page-header">
            <a href="index.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <h4 class="page-title">Out for Delivery</h4>
        </div>
        <div class="state-illustration">
            <div class="state-icon-circle"><i class="fa-solid fa-motorcycle"></i></div>
            <div class="state-title">Order is Out for Delivery</div>
            <div class="state-subtitle">#<?= e($order_no) ?></div>
        </div>
        <form method="POST" class="mt-auto">
            <button type="submit" name="action" value="update_status" class="btn-bottom">Update Status</button>
        </form>

    <!-- STATE: OUT FOR DELIVERY (Arrived) -->
    <?php elseif ($state === 'out_for_delivery'): ?>
        <div class="page-header">
            <a href="index.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <h4 class="page-title">Arrived at Location</h4>
        </div>
        <div class="state-illustration">
            <div class="state-icon-circle" style="background:#eff6ff; color:#3b82f6;"><i class="fa-solid fa-location-dot"></i></div>
            <div class="state-title">You have arrived</div>
            <div class="state-subtitle">at the customer location</div>
        </div>
        <form method="POST" class="mt-auto">
            <button type="submit" name="action" value="confirm_arrival" class="btn-bottom">Confirm Arrival</button>
        </form>

    <!-- STATE: ARRIVED (Customer OTP) -->
    <?php elseif ($state === 'arrived'): ?>
        <div class="page-header">
            <a href="index.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <h4 class="page-title">Customer OTP</h4>
        </div>
        <div class="text-center mt-5">
            <div style="width:80px; height:80px; background:#f1f5f9; border-radius:20px; display:flex; align-items:center; justify-content:center; color:var(--mandal-green); font-size:2rem; margin:0 auto 20px;"><i class="fa-solid fa-shield-halved"></i></div>
            <div class="state-title">Enter Customer OTP</div>
            
            <button type="button" class="btn btn-outline-success fw-bold rounded-pill px-4 py-2 mt-3" id="btnSendOtp">
                Send OTP to Customer
            </button>
            <div class="text-success fw-bold small mt-2 d-none" id="otpSentMsg">OTP Sent Successfully!</div>

            <form method="POST" class="mt-4" id="otpForm">
                <input type="hidden" name="action" value="verify_otp">
                <div class="otp-inputs">
                    <input type="text" class="otp-box" maxlength="1" pattern="\d">
                    <input type="text" class="otp-box" maxlength="1" pattern="\d">
                    <input type="text" class="otp-box" maxlength="1" pattern="\d">
                    <input type="text" class="otp-box" maxlength="1" pattern="\d">
                    <input type="text" class="otp-box" maxlength="1" pattern="\d">
                    <input type="text" class="otp-box" maxlength="1" pattern="\d">
                    <input type="hidden" name="otp" id="hiddenOtp">
                </div>
                <button type="submit" class="btn-bottom" style="margin-top:40px;">Verify OTP</button>
            </form>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $('#btnSendOtp').click(function() {
                let btn = $(this); btn.prop('disabled', true).text('Sending...');
                $.ajax({
                    url: 'ajax_send_otp.php', type: 'POST', data: { order_id: <?= $order_id ?> }, dataType: 'json',
                    success: function(r) { if(r.success) { btn.hide(); $('#otpSentMsg').removeClass('d-none'); } else { alert(r.message); btn.prop('disabled', false).text('Send OTP'); } },
                    error: function() { alert("Error"); btn.prop('disabled', false).text('Send OTP'); }
                });
            });
            const inputs = $('.otp-box');
            inputs.on('keyup', function(e) {
                const val = $(this).val(); const index = inputs.index(this);
                if (val.length === 1 && index < inputs.length - 1) inputs.eq(index + 1).focus();
                if (e.key === 'Backspace' && index > 0 && val.length === 0) inputs.eq(index - 1).focus();
                let fullOtp = ''; inputs.each(function() { fullOtp += $(this).val(); }); $('#hiddenOtp').val(fullOtp);
            });
        </script>

    <!-- STATE: PAYMENT COLLECTED (COD ONLY) -->
    <?php elseif ($state === 'payment_pending'): ?>
        <div class="page-header">
            <a href="index.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <h4 class="page-title">Collect Payment</h4>
        </div>
        <div class="payment-box mt-4">
            <div class="fw-bold text-muted text-uppercase small">Order Amount</div>
            <div class="payment-amount">₹<?= number_format($grand_total, 0) ?></div>
            <div class="d-inline-flex align-items-center gap-2 bg-white px-3 py-2 rounded-pill shadow-sm mt-2 border">
                <i class="fa-solid fa-money-bill-wave text-success"></i>
                <span class="fw-bold text-dark small">Cash on Delivery (COD)</span>
            </div>
        </div>
        <form method="POST" class="mt-auto">
            <button type="submit" name="action" value="confirm_payment" class="btn-bottom">Confirm Payment</button>
        </form>

    <!-- STATE: PROOF SUBMITTED -->
    <?php elseif ($state === 'proof_pending'): ?>
        <div class="page-header">
            <a href="index.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
            <h4 class="page-title">Proof of Delivery</h4>
        </div>
        
        <div class="d-flex gap-2 mb-4">
            <div class="flex-grow-1 text-center pb-2 border-bottom border-success border-3 fw-bold text-success">Take Photo</div>
            <div class="flex-grow-1 text-center pb-2 border-bottom fw-bold text-muted">Signature</div>
        </div>

        <div class="photo-placeholder">
            <i class="fa-solid fa-camera"></i>
            <div class="fw-bold">Tap to capture photo</div>
        </div>

        <form method="POST" class="mt-auto">
            <button type="submit" name="action" value="submit_proof" class="btn-bottom">Use Photo</button>
        </form>

    <!-- STATE: DELIVERED -->
    <?php elseif ($state === 'delivered'): ?>
        <div class="text-center mt-5">
            <div class="success-circle"><i class="fa-solid fa-check"></i></div>
            <div class="state-title mt-4">Delivery Successful!</div>
            <div class="state-subtitle mb-4">Order #<?= e($order_no) ?></div>
            
            <?php if ($is_cod): ?>
                <div class="fs-2 fw-bold text-dark mb-5">₹<?= number_format($grand_total, 0) ?> Collected</div>
            <?php else: ?>
                <div class="fs-2 fw-bold text-dark mb-5">Paid Online</div>
            <?php endif; ?>

            <div class="fw-bold text-muted mb-2">Rate Customer (Optional)</div>
            <div class="d-flex justify-content-center gap-3 text-warning fs-1 mb-5">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star"></i>
            </div>
        </div>
        <a href="index.php" class="btn-bottom" style="margin-top:auto;">Done</a>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
