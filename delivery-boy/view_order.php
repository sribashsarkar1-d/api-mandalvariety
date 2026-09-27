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

// Auto-add delivery_otp column if it doesn't exist
try {
    $conn->query("SELECT delivery_otp FROM orders LIMIT 1");
} catch (\PDOException $e) {
    try {
        $conn->exec("ALTER TABLE orders ADD COLUMN delivery_otp VARCHAR(10) NULL DEFAULT NULL");
    } catch (\PDOException $e2) {
        // Ignore
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_status') {
        $new_status = $_POST['status'] ?? '';
        $entered_otp = $_POST['otp'] ?? '';
        
        // Fetch order to verify
        $stmt = $conn->prepare("SELECT status, delivery_otp FROM orders WHERE id = ? AND assigned_delivery_id = ?");
        $stmt->execute([$order_id, $delivery_id]);
        $order_verify = $stmt->fetch();
        
        if ($order_verify) {
            if ($new_status === 'delivered') {
                // Verify OTP
                if (empty($order_verify['delivery_otp'])) {
                    $error = "Please send the OTP to the customer first.";
                } elseif ($entered_otp !== $order_verify['delivery_otp']) {
                    $error = "Invalid OTP entered. Please try again.";
                } else {
                    // OTP is valid! Mark as delivered
                    $stmt = $conn->prepare("
                        UPDATE orders 
                        SET status = 'delivered', tracking_status = 'delivered', payment_status = 'paid' 
                        WHERE id = ?
                    ");
                    $stmt->execute([$order_id]);
                    $success = "Order successfully delivered!";
                }
            } else {
                // Just update status (e.g. out_for_delivery)
                $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $order_id]);
                $success = "Status updated successfully.";
            }
        } else {
            $error = "Order not found or not assigned to you.";
        }
    }
}

// Fetch order details
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

// Fetch items
$stmtItems = $conn->prepare("
    SELECT oi.*, p.name as product_name, p.images
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmtItems->execute([$order_id]);
$items = $stmtItems->fetchAll();

// Handle data fields
$order_no = $order['order_number'] ?? $order['order_no'] ?? 'N/A';
$customer_name = $order['user_name'] ?? $order['customer_name'] ?? $order['name'] ?? 'Customer';
$customer_phone = $order['user_phone'] ?? $order['customer_phone'] ?? $order['phone'] ?? 'No phone';
$address = $order['shipping_address'] ?? $order['delivery_address'] ?? $order['address'] ?? 'No address provided';
$landmark = $order['shipping_landmark'] ?? $order['delivery_landmark'] ?? '';
$pincode = $order['shipping_pincode'] ?? $order['delivery_pincode'] ?? $order['pincode'] ?? '';

$grand_total = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0);
$payment_method = $order['payment_method'] ?? $order['payment_type'] ?? 'N/A';
$payment_status = $order['payment_status'] ?? 'pending';
$status = $order['status'] ?? 'unknown';

function getThumb($imagesJson) {
    if (!$imagesJson) return '../assets/images/placeholder.png';
    $images = json_decode($imagesJson, true);
    if (is_array($images) && !empty($images[0])) {
        return 'https://mandal-variety.com/admin/uploads/' . $images[0];
    }
    return '../assets/images/placeholder.png';
}

?>

<?php include 'includes/header.php'; ?>

<style>
    body {
        background-color: #f8fafc;
    }
    .order-details-container {
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
    
    .order-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        margin-bottom: 24px;
        border: 1px solid rgba(0,0,0,0.02);
    }

    .order-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .order-number {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--text-dark);
    }

    .cod-badge {
        background: #f59e0b;
        color: white;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 700;
    }
    .paid-badge {
        background: #10b981;
        color: white;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .customer-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .customer-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .customer-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        font-size: 1.2rem;
    }

    .customer-details {
        display: flex;
        flex-direction: column;
    }

    .customer-name {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-dark);
    }

    .customer-phone {
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .call-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #eff6ff;
        color: #3b82f6;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 1.1rem;
    }

    .items-section {
        margin-bottom: 20px;
    }

    .items-header {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 12px;
    }

    .item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 8px;
    }
    .item-row.total-row {
        color: var(--text-dark);
        font-weight: 800;
        font-size: 1rem;
        border-top: 1px dashed #e2e8f0;
        padding-top: 12px;
        margin-top: 12px;
    }

    .item-name {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .item-icon {
        color: #cbd5e1;
        font-size: 0.7rem;
    }

    .item-qty {
        width: 30px;
        text-align: right;
    }

    .item-price {
        width: 60px;
        text-align: right;
    }

    .totals-section {
        background: #f8fafc;
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 24px;
    }

    .total-line {
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 8px;
    }
    .total-line:last-child {
        margin-bottom: 0;
    }

    .btn-bottom {
        background: var(--mandal-green);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 16px;
        width: 100%;
        font-size: 1rem;
        font-weight: 700;
        text-align: center;
        display: block;
        text-decoration: none;
        transition: transform 0.2s;
        cursor: pointer;
    }
    .btn-bottom:active {
        transform: scale(0.98);
    }

    .status-form {
        background: #ffffff;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.02);
    }

    .otp-section {
        display: none;
        background: #f8fafc;
        border-radius: 12px;
        padding: 16px;
        margin-top: 16px;
        border: 1px solid #e2e8f0;
    }
    
    .address-box {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px dashed #e2e8f0;
    }
    
    .otp-inputs {
        display: flex;
        gap: 8px;
        justify-content: center;
        margin-bottom: 12px;
    }
    
    .otp-box {
        width: 40px;
        height: 50px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        text-align: center;
        font-size: 1.2rem;
        font-weight: 700;
    }
    .otp-box:focus {
        border-color: var(--mandal-green);
        outline: none;
    }
</style>

<div class="order-details-container">
    
    <div class="page-header">
        <a href="index.php" class="back-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <h4 class="page-title">Order Details</h4>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success border-0 rounded-3 shadow-sm mb-4"><i class="fa-solid fa-check-circle me-2"></i><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4"><i class="fa-solid fa-circle-exclamation me-2"></i><?= e($error) ?></div>
    <?php endif; ?>

    <div class="order-card">
        <div class="order-header-row">
            <div class="order-number">#<?= e($order_no) ?></div>
            <?php if (strpos(strtolower($payment_method), 'cash') !== false || strpos(strtolower($payment_method), 'cod') !== false): ?>
                <div class="cod-badge">COD</div>
            <?php else: ?>
                <div class="paid-badge">PAID</div>
            <?php endif; ?>
        </div>

        <div class="customer-info">
            <div class="customer-left">
                <div class="customer-avatar">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="customer-details">
                    <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Customer</div>
                    <div class="customer-name"><?= e($customer_name) ?></div>
                    <div class="customer-phone"><?= e($customer_phone) ?></div>
                </div>
            </div>
            <a href="tel:<?= e($customer_phone) ?>" class="call-btn">
                <i class="fa-solid fa-comment-dots"></i>
            </a>
        </div>

        <div class="items-section">
            <div class="items-header">Items (<?= count($items) ?>)</div>
            <?php foreach ($items as $item): ?>
                <div class="item-row">
                    <div class="item-name">
                        <i class="fa-solid fa-square item-icon"></i>
                        <?= e($item['product_name'] ?? 'Product') ?>
                    </div>
                    <div class="item-qty"><?= (int)($item['quantity'] ?? 1) ?></div>
                    <div class="item-price">₹<?= number_format((float)($item['price'] ?? 0), 0) ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="totals-section">
            <div class="total-line">
                <span>Subtotal</span>
                <span>₹<?= number_format($grand_total, 0) ?></span>
            </div>
            <div class="total-line">
                <span>Delivery Charge</span>
                <span>₹0</span>
            </div>
            <div class="item-row total-row" style="margin-bottom:0;">
                <span>Total Amount</span>
                <span>₹<?= number_format($grand_total, 0) ?></span>
            </div>
        </div>
        
        <div class="address-box">
            <div class="customer-details mb-2">
                <div style="font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Delivery Address</div>
                <div class="customer-name mt-1" style="font-weight: 500; font-size: 0.85rem; line-height: 1.4;">
                    <?= nl2br(e($address)) ?>
                    <?php if ($landmark): ?><br><?= e($landmark) ?><?php endif; ?>
                    <?php if ($pincode): ?> - <?= e($pincode) ?><?php endif; ?>
                </div>
            </div>
            <a href="https://maps.google.com/?q=<?= urlencode($address . ' ' . $pincode) ?>" target="_blank" class="btn btn-outline-primary w-100 rounded-3 mt-3 fw-bold" style="font-size: 0.9rem;">
                <i class="fa-solid fa-map-location-dot me-2"></i> Open in Google Maps
            </a>
        </div>
    </div>

    <?php if ($status !== 'delivered'): ?>
        <form method="POST" id="statusForm" class="status-form">
            <input type="hidden" name="action" value="update_status">
            
            <div class="fw-bold mb-2 text-dark">Update Status</div>
            <select name="status" class="form-select mb-3 rounded-3" id="statusSelect" style="border-color: #cbd5e1; height: 50px;">
                <option value="out_for_delivery" <?= $status === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                <option value="delivered">Delivered (OTP Required)</option>
            </select>

            <div id="otpSection" class="otp-section">
                <div class="fw-bold mb-3 text-center text-dark" style="font-size: 1rem;">Enter Customer OTP</div>
                
                <button type="button" class="btn btn-outline-success w-100 mb-3 fw-bold rounded-3" id="btnSendOtp" style="height: 48px;">
                    Send OTP to Customer
                </button>
                
                <div class="text-center text-success fw-bold small mb-2 d-none" id="otpSentMsg">
                    OTP Sent! Enter code below.
                </div>
                
                <div class="otp-inputs">
                    <input type="text" class="otp-box" maxlength="1" pattern="\d">
                    <input type="text" class="otp-box" maxlength="1" pattern="\d">
                    <input type="text" class="otp-box" maxlength="1" pattern="\d">
                    <input type="text" class="otp-box" maxlength="1" pattern="\d">
                    <input type="text" class="otp-box" maxlength="1" pattern="\d">
                    <input type="text" class="otp-box" maxlength="1" pattern="\d">
                    <input type="hidden" name="otp" id="hiddenOtp">
                </div>
            </div>

            <button type="submit" class="btn-bottom mt-3">
                Update Status
            </button>
        </form>
    <?php endif; ?>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#statusSelect').change(function() {
            if ($(this).val() === 'delivered') {
                $('#otpSection').slideDown();
            } else {
                $('#otpSection').slideUp();
            }
        });
        
        if ($('#statusSelect').val() === 'delivered') {
            $('#otpSection').show();
        }

        $('#btnSendOtp').click(function() {
            let btn = $(this);
            btn.prop('disabled', true).text('Sending...');
            
            $.ajax({
                url: 'ajax_send_otp.php',
                type: 'POST',
                data: { order_id: <?= $order_id ?> },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        btn.hide();
                        $('#otpSentMsg').removeClass('d-none');
                    } else {
                        alert("Error: " + response.message);
                        btn.prop('disabled', false).text('Send OTP to Customer');
                    }
                },
                error: function() {
                    alert("Network error occurred.");
                    btn.prop('disabled', false).text('Send OTP to Customer');
                }
            });
        });
        
        // OTP Inputs logic
        const inputs = $('.otp-box');
        inputs.on('keyup', function(e) {
            const val = $(this).val();
            const index = inputs.index(this);
            
            if (val.length === 1 && index < inputs.length - 1) {
                inputs.eq(index + 1).focus();
            }
            if (e.key === 'Backspace' && index > 0 && val.length === 0) {
                inputs.eq(index - 1).focus();
            }
            updateHiddenOtp();
        });
        
        inputs.on('paste', function(e) {
            e.preventDefault();
            const text = (e.originalEvent || e).clipboardData.getData('text').slice(0, 6);
            if (/^\d+$/.test(text)) {
                text.split('').forEach((char, i) => {
                    if (inputs[i]) {
                        inputs.eq(i).val(char);
                    }
                });
                inputs.eq(Math.min(text.length, inputs.length - 1)).focus();
                updateHiddenOtp();
            }
        });
        
        function updateHiddenOtp() {
            let fullOtp = '';
            inputs.each(function() { fullOtp += $(this).val(); });
            $('#hiddenOtp').val(fullOtp);
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
