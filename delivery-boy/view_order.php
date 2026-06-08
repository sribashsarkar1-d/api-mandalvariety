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
        return '../uploads/' . $images[0];
    }
    return '../assets/images/placeholder.png';
}

?>

<?php include 'includes/header.php'; ?>

<style>
    .dashboard-container {
        padding: 20px;
        max-width: 800px;
        margin: 0 auto;
        padding-bottom: 80px;
    }

    .premium-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.05);
        padding: 20px;
        margin-bottom: 20px;
    }

    .info-group {
        margin-bottom: 15px;
    }

    .info-label {
        font-size: 0.8rem;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .info-value {
        font-weight: 600;
        color: #1e293b;
        font-size: 1.05rem;
        word-break: break-word;
    }

    .call-btn {
        background: #ecfdf5;
        color: #10b981;
        border: none;
        border-radius: 12px;
        padding: 12px;
        font-weight: bold;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    
    .map-btn {
        background: #eff6ff;
        color: #3b82f6;
        border: none;
        border-radius: 12px;
        padding: 12px;
        font-weight: bold;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .item-list {
        display: flex;
        gap: 15px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .item-list:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .item-img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 12px;
        background: #f8fafc;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 99px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .status-badge.bg-success { background: #d1fae5 !important; color: #059669 !important; }
    .status-badge.bg-warning { background: #fef3c7 !important; color: #d97706 !important; }

    #otpSection {
        display: none;
        background: #f8fafc;
        border-radius: 16px;
        padding: 20px;
        margin-top: 15px;
        border: 1px dashed #cbd5e1;
    }
</style>

<div class="dashboard-container">
    <div class="d-flex align-items-center mb-4">
        <a href="index.php" class="text-dark me-3"><i class="fa-solid fa-arrow-left fa-lg"></i></a>
        <h4 class="fw-bold mb-0">Order #<?= e($order_no) ?></h4>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success rounded-3"><i class="fa-solid fa-check-circle me-2"></i><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger rounded-3"><i class="fa-solid fa-circle-exclamation me-2"></i><?= e($error) ?></div>
    <?php endif; ?>

    <!-- Status Card -->
    <div class="premium-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="info-label mb-0">Current Status</span>
            <span class="status-badge <?= $status === 'delivered' ? 'bg-success' : 'bg-warning' ?>">
                <?= str_replace('_', ' ', e($status)) ?>
            </span>
        </div>
        
        <?php if ($status !== 'delivered'): ?>
            <form method="POST" id="statusForm">
                <input type="hidden" name="action" value="update_status">
                
                <label class="form-label fw-bold">Update Status</label>
                <select name="status" class="form-select form-select-lg mb-3" id="statusSelect">
                    <option value="out_for_delivery" <?= $status === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                    <option value="delivered">Delivered (Requires OTP)</option>
                </select>

                <div id="otpSection">
                    <h6 class="fw-bold text-center mb-3">Customer Verification</h6>
                    <button type="button" class="btn btn-outline-primary w-100 fw-bold mb-3 rounded-pill" id="btnSendOtp">
                        <i class="fa-solid fa-paper-plane me-2"></i> Send OTP to Customer
                    </button>
                    
                    <div class="text-center text-success fw-bold small mb-3 d-none" id="otpSentMsg">
                        <i class="fa-solid fa-check-circle me-1"></i> OTP Sent! Ask customer for the 6-digit code.
                    </div>
                    
                    <input type="text" name="otp" class="form-control form-control-lg text-center fw-bold letter-spacing-lg mb-3" placeholder="Enter 6-digit OTP" maxlength="6">
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold rounded-pill">
                    Update Order
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Customer & Delivery Info -->
    <div class="premium-card">
        <h5 class="fw-bold mb-3">Delivery Details</h5>
        
        <div class="info-group">
            <div class="info-label">Customer Name</div>
            <div class="info-value"><?= e($customer_name) ?></div>
        </div>

        <div class="info-group">
            <div class="info-label">Phone Number</div>
            <div class="info-value"><?= e($customer_phone) ?></div>
        </div>

        <div class="info-group">
            <div class="info-label">Delivery Address</div>
            <div class="info-value">
                <?= nl2br(e($address)) ?>
                <?php if ($landmark): ?><br><small class="text-muted">Landmark: <?= e($landmark) ?></small><?php endif; ?>
                <?php if ($pincode): ?><br><small class="text-muted">Pin: <?= e($pincode) ?></small><?php endif; ?>
            </div>
        </div>

        <div class="row g-2 mt-3">
            <div class="col-6">
                <a href="tel:<?= e($customer_phone) ?>" class="call-btn">
                    <i class="fa-solid fa-phone"></i> Call
                </a>
            </div>
            <div class="col-6">
                <a href="https://maps.google.com/?q=<?= urlencode($address . ' ' . $pincode) ?>" target="_blank" class="map-btn">
                    <i class="fa-solid fa-map-location-dot"></i> Map
                </a>
            </div>
        </div>
    </div>

    <!-- Items & Payment Info -->
    <div class="premium-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Order Items</h5>
            <div class="text-success fw-bold fs-5">₹<?= number_format($grand_total, 2) ?></div>
        </div>
        
        <div class="info-group mb-4">
            <span class="badge bg-light text-dark border px-3 py-2">
                <?= e(strtoupper($payment_method)) ?> - <?= e(ucwords($payment_status)) ?>
            </span>
            <?php if ($payment_method === 'cod' && $payment_status !== 'paid' && $status !== 'delivered'): ?>
                <div class="text-danger small fw-bold mt-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> Collect ₹<?= number_format($grand_total, 2) ?> from customer!</div>
            <?php endif; ?>
        </div>

        <div>
            <?php foreach ($items as $item): ?>
                <div class="item-list">
                    <img src="<?= e(getThumb($item['images'])) ?>" class="item-img" alt="Product">
                    <div>
                        <div class="fw-bold text-dark lh-sm mb-1"><?= e($item['product_name'] ?? 'Product') ?></div>
                        <div class="text-muted small">Qty: <?= (int)($item['quantity'] ?? 1) ?> × ₹<?= number_format((float)($item['price'] ?? 0), 2) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
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
        
        // Trigger on load
        if ($('#statusSelect').val() === 'delivered') {
            $('#otpSection').show();
        }

        $('#btnSendOtp').click(function() {
            let btn = $(this);
            btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i> Sending...');
            
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
                        btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-2"></i> Send OTP to Customer');
                    }
                },
                error: function() {
                    alert("Network error occurred.");
                    btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane me-2"></i> Send OTP to Customer');
                }
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
