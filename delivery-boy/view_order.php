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
    .order-details-container {
        padding: 24px 20px;
    }
    
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }
    
    .back-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-dark);
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border: 1px solid #f1f5f9;
        font-size: 1.1rem;
    }
    
    .page-title {
        font-weight: 700;
        font-size: 1.25rem;
        margin: 0;
        color: var(--text-dark);
    }
    
    .status-badge {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 6px 12px;
        border-radius: 6px;
    }
    .status-badge.bg-success { background: #d1fae5 !important; color: #059669 !important; }
    .status-badge.bg-warning { background: #fef3c7 !important; color: #d97706 !important; }

    /* Map Preview Placeholder */
    .map-preview {
        background: #e2e8f0;
        border-radius: 20px;
        height: 200px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.05);
    }
    /* Simple CSS map graphic */
    .map-preview::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(#cbd5e1 2px, transparent 2px);
        background-size: 20px 20px;
        opacity: 0.5;
    }
    .route-line {
        position: absolute;
        top: 40%;
        left: 20%;
        width: 50%;
        height: 40px;
        border: 4px dashed var(--mandal-green);
        border-bottom: 0;
        border-left: 0;
        border-radius: 0 40px 0 0;
        transform: rotate(15deg);
    }
    .map-marker {
        position: absolute;
        width: 24px;
        height: 24px;
        background: white;
        border-radius: 50%;
        border: 6px solid;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        z-index: 2;
    }
    .map-pickup { border-color: var(--mandal-green); top: 35%; left: 15%; }
    .map-drop { border-color: #ef4444; top: 60%; left: 65%; }
    .map-btn-float {
        position: absolute;
        bottom: 15px;
        right: 15px;
        width: 44px;
        height: 44px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        color: var(--text-dark);
        z-index: 3;
    }

    .timeline {
        position: relative;
        padding-left: 24px;
        margin-bottom: 20px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 5px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: #e2e8f0;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 24px;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    .timeline-icon {
        position: absolute;
        left: -24px;
        top: 2px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: white;
        border: 3px solid;
    }
    .icon-pickup { border-color: var(--mandal-green); }
    .icon-drop { border-color: #ef4444; }
    
    .timeline-title {
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 4px;
    }
    .timeline-subtitle {
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--text-dark);
        margin-bottom: 4px;
    }
    .timeline-desc {
        font-size: 0.85rem;
        color: var(--text-muted);
        line-height: 1.4;
    }

    /* Info rows */
    .amount-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 0;
        border-top: 1px dashed #e2e8f0;
        border-bottom: 1px dashed #e2e8f0;
        margin: 20px 0;
    }

    /* Actions */
    .action-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-top: 20px;
    }
    .btn-action {
        border-radius: 12px;
        padding: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
        transition: transform 0.2s;
    }
    .btn-action:hover { transform: scale(0.98); }
    .btn-call { background: #ecfdf5; color: #10b981; border: 1px solid #d1fae5; }
    .btn-map { background: #eff6ff; color: #3b82f6; border: 1px solid #dbeafe; }

    /* Items */
    .item-list {
        display: flex;
        gap: 15px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .item-list:last-child { border-bottom: none; }
    .item-img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
    }

    #otpSection {
        display: none;
        background: var(--secondary-bg);
        border-radius: 16px;
        padding: 20px;
        margin-top: 15px;
    }
</style>

<div class="order-details-container">
    
    <div class="page-header">
        <a href="index.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i></a>
        <h4 class="page-title">My Tasks</h4>
        <div style="width: 40px;"></div> <!-- spacer -->
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4"><i class="fa-solid fa-check-circle me-2"></i><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4"><i class="fa-solid fa-circle-exclamation me-2"></i><?= e($error) ?></div>
    <?php endif; ?>

    <!-- Map Preview Placeholder (UI Only) -->
    <div class="map-preview">
        <div class="route-line"></div>
        <div class="map-marker map-pickup"></div>
        <div class="map-marker map-drop"></div>
        <a href="https://maps.google.com/?q=<?= urlencode($address . ' ' . $pincode) ?>" target="_blank" class="map-btn-float">
            <i class="fa-solid fa-crosshairs"></i>
        </a>
    </div>

    <!-- Main Order Details Card -->
    <div class="premium-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <span class="badge bg-light text-success border px-3 py-2" style="font-size:0.85rem;">#<?= e($order_no) ?></span>
            <span class="status-badge <?= $status === 'delivered' ? 'bg-success' : 'bg-warning' ?>">
                <?= str_replace('_', ' ', e($status)) ?>
            </span>
        </div>

        <div class="text-muted small mb-4">
            <?= !empty($order['created_at']) ? date('h:i A, M d', strtotime($order['created_at'])) : '' ?>
        </div>

        <!-- Delivery Timeline -->
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-icon icon-pickup"></div>
                <div class="timeline-title text-success">Pickup</div>
                <div class="timeline-subtitle">Mandal Variety Store</div>
                <div class="timeline-desc">Balarampur, Jalpaiguri, WB</div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-icon icon-drop"></div>
                <div class="timeline-title text-danger">Drop</div>
                <div class="timeline-subtitle"><?= e($customer_name) ?></div>
                <div class="timeline-desc">
                    <?= nl2br(e($address)) ?>
                    <?php if ($landmark): ?><br>Landmark: <?= e($landmark) ?><?php endif; ?>
                    <?php if ($pincode): ?><br>Pin: <?= e($pincode) ?><?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="action-grid">
            <a href="tel:<?= e($customer_phone) ?>" class="btn-action btn-call">
                <i class="fa-solid fa-phone"></i> Call
            </a>
            <a href="https://maps.google.com/?q=<?= urlencode($address . ' ' . $pincode) ?>" target="_blank" class="btn-action btn-map">
                <i class="fa-solid fa-map"></i> Map
            </a>
        </div>

        <div class="amount-row">
            <div class="d-flex align-items-center text-muted fw-600">
                <i class="fa-solid fa-indian-rupee-sign me-2" style="color: var(--mandal-green);"></i> Cash to Collect
            </div>
            <div class="fw-bold fs-4" style="color: var(--mandal-green);">
                <?php if ($payment_method === 'cod' && $payment_status !== 'paid'): ?>
                    ₹<?= number_format($grand_total, 2) ?>
                <?php else: ?>
                    <span class="fs-6 text-muted">Paid</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Update Status Section -->
        <?php if ($status !== 'delivered'): ?>
            <form method="POST" id="statusForm" class="mt-4">
                <input type="hidden" name="action" value="update_status">
                
                <label class="form-label fw-bold text-dark">Update Order Status</label>
                <select name="status" class="form-select form-select-lg mb-3 rounded-3" id="statusSelect" style="border: 2px solid #e2e8f0;">
                    <option value="out_for_delivery" <?= $status === 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                    <option value="delivered">Delivered (OTP Required)</option>
                </select>

                <div id="otpSection">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="fa-solid fa-shield-check text-primary"></i>
                        <h6 class="fw-bold m-0">Customer Verification</h6>
                    </div>
                    
                    <button type="button" class="btn btn-outline-primary w-100 fw-bold mb-3 rounded-3" id="btnSendOtp">
                        Send OTP to Customer
                    </button>
                    
                    <div class="text-center text-success fw-bold small mb-3 d-none" id="otpSentMsg">
                        <i class="fa-solid fa-check-circle me-1"></i> OTP Sent! Enter 6-digit code below.
                    </div>
                    
                    <input type="text" name="otp" class="form-control form-control-lg text-center fw-bold letter-spacing-lg rounded-3" placeholder="Enter 6-digit OTP" maxlength="6">
                </div>

                <button type="submit" class="btn-premium mt-3">
                    Update Order
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Order Items -->
    <div class="premium-card p-4">
        <h6 class="fw-bold mb-3">Order Items</h6>
        <div>
            <?php foreach ($items as $item): ?>
                <div class="item-list">
                    <img src="<?= e(getThumb($item['images'])) ?>" class="item-img" alt="Product">
                    <div class="w-100 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold text-dark lh-sm mb-1"><?= e($item['product_name'] ?? 'Product') ?></div>
                            <div class="text-muted small">Qty: <?= (int)($item['quantity'] ?? 1) ?></div>
                        </div>
                        <div class="fw-bold">₹<?= number_format((float)($item['price'] ?? 0), 2) ?></div>
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
