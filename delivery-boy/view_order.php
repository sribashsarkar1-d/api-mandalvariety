<?php
require_once 'includes/config.php';
checkDeliveryLogin();

$delivery_id = (int)$_SESSION['delivery_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    header("Location: orders.php");
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

// Fetch order verifying assignment (including user address fields as fallback)
$stmt = $conn->prepare("
    SELECT o.*, 
           u.name as user_name, u.email as user_email, u.phone as user_phone,
           u.address as user_address, u.city as user_city, u.state as user_state, u.pincode as user_pincode
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.assigned_delivery_id = ?
");
$stmt->execute([$order_id, $delivery_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php");
    exit;
}

// Normalize delivery_boy_status
if (empty($order['delivery_boy_status'])) {
    $order['delivery_boy_status'] = 'assigned';
    $conn->exec("UPDATE orders SET delivery_boy_status = 'assigned' WHERE id = $order_id");
}

$state = strtolower(trim((string)$order['delivery_boy_status']));
$main_order_status = strtolower(trim((string)($order['status'] ?? 'pending')));

// Determine 4-step progress: 1 = Assigned, 2 = Picked Up, 3 = Out for Delivery, 4 = Delivered
$current_step = 1;
if ($state === 'delivered' || $main_order_status === 'delivered') {
    $current_step = 4;
} elseif (in_array($state, ['out_for_delivery', 'arrived', 'payment_pending', 'proof_pending']) || $main_order_status === 'out_for_delivery') {
    $current_step = 3;
} elseif ($state === 'picked_up' || $main_order_status === 'shipped') {
    $current_step = 2;
} else {
    $current_step = 1;
}

$payment_method = $order['payment_method'] ?? $order['payment_type'] ?? 'Cash on Delivery';
$is_cod = (strpos(strtolower($payment_method), 'cash') !== false || strpos(strtolower($payment_method), 'cod') !== false);

// Handle state transitions
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $new_state = '';
    $new_main_status = '';

    if ($action === 'confirm_pickup') {
        $new_state = 'picked_up';
        $new_main_status = 'shipped';
    } elseif ($action === 'out_for_delivery' || $action === 'update_status') {
        $new_state = 'out_for_delivery';
        $new_main_status = 'out_for_delivery';
    } elseif ($action === 'verify_otp' || $action === 'complete_delivery') {
        $entered_otp = trim((string)($_POST['otp'] ?? ''));
        
        // Re-fetch latest delivery_otp from database
        $otpCheckStmt = $conn->prepare("SELECT delivery_otp FROM orders WHERE id = ?");
        $otpCheckStmt->execute([$order_id]);
        $latestOtp = trim((string)$otpCheckStmt->fetchColumn());

        if (empty($latestOtp)) {
            $error = "Please click 'Send OTP to Customer' first to generate the delivery code.";
        } elseif ($entered_otp === '' || strlen($entered_otp) !== 6) {
            $error = "Please enter the complete 6-digit OTP provided by the customer.";
        } elseif ($entered_otp !== $latestOtp) {
            $error = "Invalid OTP code. Please ask the customer and try again.";
        } else {
            $new_state = 'delivered';
            $new_main_status = 'delivered';
        }
    }

    if ($new_state) {
        try {
            if ($new_state === 'delivered') {
                // When OTP is matched successfully:
                // 1. Order Status (status) -> 'delivered'
                // 2. Tracking Status (tracking_status) -> 'delivered'
                // 3. Payment Status (payment_status) -> 'paid'
                // 4. Delivery Partner Status (delivery_boy_status) -> 'delivered'
                // 5. Updated Timestamp -> NOW()
                $stmt = $conn->prepare("
                    UPDATE orders 
                    SET status = 'delivered',
                        tracking_status = 'delivered',
                        payment_status = 'paid',
                        delivery_boy_status = 'delivered',
                        updated_at = NOW()
                    WHERE id = ? AND (assigned_delivery_id = ? OR assigned_delivery_id IS NULL OR assigned_delivery_id = 0)
                ");
                $stmt->execute([$order_id, $delivery_id]);

                if ($stmt->rowCount() === 0) {
                    $conn->prepare("
                        UPDATE orders 
                        SET status = 'delivered',
                            tracking_status = 'delivered',
                            payment_status = 'paid',
                            delivery_boy_status = 'delivered',
                            updated_at = NOW()
                        WHERE id = ?
                    ")->execute([$order_id]);
                }
            } elseif ($new_state === 'picked_up') {
                $stmt = $conn->prepare("
                    UPDATE orders 
                    SET status = 'shipped',
                        tracking_status = 'shipped',
                        delivery_boy_status = 'picked_up',
                        updated_at = NOW()
                    WHERE id = ? AND (assigned_delivery_id = ? OR assigned_delivery_id IS NULL OR assigned_delivery_id = 0)
                ");
                $stmt->execute([$order_id, $delivery_id]);

                if ($stmt->rowCount() === 0) {
                    $conn->prepare("
                        UPDATE orders 
                        SET status = 'shipped',
                            tracking_status = 'shipped',
                            delivery_boy_status = 'picked_up',
                            updated_at = NOW()
                        WHERE id = ?
                    ")->execute([$order_id]);
                }
            } elseif ($new_state === 'out_for_delivery') {
                $stmt = $conn->prepare("
                    UPDATE orders 
                    SET status = 'out_for_delivery',
                        tracking_status = 'on_the_way',
                        delivery_boy_status = 'out_for_delivery',
                        updated_at = NOW()
                    WHERE id = ? AND (assigned_delivery_id = ? OR assigned_delivery_id IS NULL OR assigned_delivery_id = 0)
                ");
                $stmt->execute([$order_id, $delivery_id]);

                if ($stmt->rowCount() === 0) {
                    $conn->prepare("
                        UPDATE orders 
                        SET status = 'out_for_delivery',
                            tracking_status = 'on_the_way',
                            delivery_boy_status = 'out_for_delivery',
                            updated_at = NOW()
                        WHERE id = ?
                    ")->execute([$order_id]);
                }
            }

            header("Location: view_order.php?id=$order_id&updated=1");
            exit;
        } catch (\Exception $ex) {
            $error = "Failed to update order status: " . $ex->getMessage();
        }
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
$order_date_str = !empty($order['created_at']) ? date('d M Y, h:i A', strtotime($order['created_at'])) : date('d M Y, h:i A');

$customer_name = $order['user_name'] ?? $order['customer_name'] ?? 'Customer';
$customer_phone = $order['user_phone'] ?? $order['customer_phone'] ?? '';
$customer_email = $order['user_email'] ?? $order['customer_email'] ?? '';

// ------------------------------------------------------------------
// 1. Separate Customer Delivery Coordinates, Address and PIN Code
// ------------------------------------------------------------------
$customer_lat = null;
$customer_lng = null;

// Inspect coordinates in dedicated orders columns
if (isset($order['latitude']) && isset($order['longitude'])) {
    $raw_lat = trim((string)$order['latitude']);
    $raw_lng = trim((string)$order['longitude']);
    if (is_numeric($raw_lat) && is_numeric($raw_lng)) {
        $f_lat = (float)$raw_lat;
        $f_lng = (float)$raw_lng;
        // Validate coordinates (-90 <= lat <= 90, -180 <= lng <= 180, and non-zero)
        if ($f_lat != 0.0 && $f_lng != 0.0 && $f_lat >= -90.0 && $f_lat <= 90.0 && $f_lng >= -180.0 && $f_lng <= 180.0) {
            $customer_lat = $f_lat;
            $customer_lng = $f_lng;
        }
    }
}

// Inspect raw delivery address
$raw_address = trim((string)($order['delivery_address'] ?? $order['shipping_address'] ?? $order['address'] ?? ''));

// If coordinates are missing from columns, check if embedded in raw text (e.g. legacy "Live Location: lat, lng" or "?q=lat,lng")
if ($customer_lat === null || $customer_lng === null) {
    if (preg_match('/(?:Live Location:\s*|maps\.google\.com\/(?:maps)?\?q=)([+-]?\d+(?:\.\d+)?)\s*,\s*([+-]?\d+(?:\.\d+)?)/iu', $raw_address, $matchCoords)) {
        $f_lat = (float)$matchCoords[1];
        $f_lng = (float)$matchCoords[2];
        if ($f_lat != 0.0 && $f_lng != 0.0 && $f_lat >= -90.0 && $f_lat <= 90.0 && $f_lng >= -180.0 && $f_lng <= 180.0) {
            $customer_lat = $f_lat;
            $customer_lng = $f_lng;
        }
    }
}

// 2. Clean Delivery Address: Do not concatenate or display Live Location text, Google Maps URLs or metadata
$clean_address = $raw_address;
$clean_address = preg_replace('/[📍\x{1F4CD}]?\s*Live Location:.*?(?=(\r?\n|$))/ui', '', $clean_address);
$clean_address = preg_replace('/https?:\/\/[^\s)]+/i', '', $clean_address);
$clean_address = preg_replace('/\(\s*Map:\s*[^)]*\)/i', '', $clean_address);
$clean_address = preg_replace('/\(\s*Map:\s*\)/i', '', $clean_address);
$clean_address = preg_replace('/[ \t]+/', ' ', $clean_address);
$clean_address = preg_replace('/(\r?\n)+/', "\n", $clean_address);
$clean_address = preg_replace('/\s*,\s*,+/', ', ', $clean_address);
$clean_address = trim($clean_address, " \t\n\r\0\x0B,");

if (empty($clean_address) && !empty($order['user_address'])) {
    $clean_address = trim((string)$order['user_address']);
}

// 3. Clean & Validate PIN Code: Remove placeholder PIN codes such as '000000', '111111', etc.
$raw_pin = trim((string)($order['pincode'] ?? $order['shipping_pincode'] ?? $order['delivery_pincode'] ?? $order['user_pincode'] ?? ''));
$clean_pincode = '';
$digits_pin = preg_replace('/\D/', '', $raw_pin);
if (strlen($digits_pin) === 6 && $digits_pin !== '000000' && $digits_pin !== '111111' && $digits_pin !== '999999' && $digits_pin[0] !== '0') {
    $clean_pincode = $digits_pin;
}

$display_pincode = '';
if (!empty($clean_pincode)) {
    if (!preg_match('/\b' . preg_quote($clean_pincode, '/') . '\b/', $clean_address)) {
        $display_pincode = $clean_pincode;
    }
}

// 4. Generate Google Maps Directions API URL:
// Format: https://www.google.com/maps/dir/?api=1&destination=LATITUDE,LONGITUDE&travelmode=driving
// Omitting origin= lets Google Maps automatically detect the Delivery Boy's current live location.
$maps_url = '';
$has_valid_destination = false;

if ($customer_lat !== null && $customer_lng !== null) {
    // Priority: Use customer's actual delivery coordinates as destination
    $dest_coords = sprintf('%.8f,%.8f', $customer_lat, $customer_lng);
    $maps_url = "https://www.google.com/maps/dir/?api=1&destination=" . $dest_coords . "&travelmode=driving";
    $has_valid_destination = true;
} elseif (!empty($clean_address) && !in_array(strtolower($clean_address), ['n/a', 'na', 'none', 'customer delivery address'])) {
    // Fallback: Use clean, properly encoded delivery address
    $dest_str = $clean_address;
    if (!empty($display_pincode)) {
        $dest_str .= ", " . $display_pincode;
    }
    if (!empty($order['user_city']) && stripos($dest_str, $order['user_city']) === false) {
        $dest_str .= ", " . $order['user_city'];
    }
    if (!empty($order['user_state']) && stripos($dest_str, $order['user_state']) === false) {
        $dest_str .= ", " . $order['user_state'];
    }

    $maps_url = "https://www.google.com/maps/dir/?api=1&destination=" . rawurlencode($dest_str) . "&travelmode=driving";
    $has_valid_destination = true;
}

$clean_phone = preg_replace('/[^0-9]/', '', $customer_phone);
$whatsapp_url = "https://wa.me/91" . $clean_phone . "?text=" . urlencode("Hello $customer_name, I am your Mandal Variety delivery partner regarding order #$order_no.");

$grand_total = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0);
$delivery_charge = (float)($order['delivery_charge'] ?? 0);
$items_subtotal = (float)($order['total_amount'] ?? ($grand_total - $delivery_charge));
if ($items_subtotal <= 0) $items_subtotal = $grand_total;

// Status label and badge
$status_badge_labels = [
    1 => 'Assigned',
    2 => 'Picked Up',
    3 => 'Out for Delivery',
    4 => 'Delivered'
];
$current_badge_label = $status_badge_labels[$current_step] ?? 'Assigned';
?>

<?php include 'includes/header.php'; ?>

<style>
    /* Top Bar */
    .view-order-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 16px;
    }

    .topbar-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .btn-back-arrow {
        color: var(--primary-text);
        font-size: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        padding: 4px;
        transition: transform 0.15s;
    }

    .btn-back-arrow:active {
        transform: translateX(-3px);
    }

    .view-order-title {
        font-size: 19px;
        font-weight: 800;
        color: var(--primary-text);
        letter-spacing: -0.3px;
        margin: 0;
    }

    /* Brand Logo in Header */
    .brand-logo-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .brand-names {
        display: flex;
        flex-direction: column;
        line-height: 1.05;
    }

    .brand-main {
        font-size: 14px;
        font-weight: 800;
        color: var(--dark-green);
        letter-spacing: -0.3px;
    }

    .brand-sub {
        font-size: 12.5px;
        font-weight: 800;
        color: var(--primary-green);
        letter-spacing: -0.3px;
    }

    /* Reusable White Card */
    .order-view-card {
        background: #FFFFFF;
        border-radius: var(--card-radius);
        border: 1px solid var(--border-color);
        box-shadow: var(--card-shadow);
        padding: 18px;
        margin-bottom: 14px;
    }

    /* Card Section Titles */
    .card-heading-row {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14.5px;
        font-weight: 800;
        color: var(--primary-text);
        margin-bottom: 14px;
    }

    .card-heading-icon {
        color: var(--primary-green);
        font-size: 15px;
    }

    /* SECTION 1: Header Card Top Row */
    .order-head-info {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 18px;
    }

    .order-head-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .order-bag-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background-color: var(--light-green);
        color: var(--primary-green);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .order-id-main {
        font-size: 16.5px;
        font-weight: 800;
        color: var(--primary-text);
        letter-spacing: -0.3px;
        margin-bottom: 2px;
    }

    .order-date-text {
        font-size: 12px;
        color: var(--secondary-text);
        font-weight: 500;
    }

    /* SECTION 2: Horizontal 4-Step Progress */
    .progress-track-wrapper {
        position: relative;
        padding: 10px 6px 0;
    }

    .progress-line-bg {
        position: absolute;
        top: 24px;
        left: 28px;
        right: 28px;
        height: 2px;
        background-color: #E5E7EB;
        z-index: 1;
    }

    .progress-line-fill {
        position: absolute;
        top: 24px;
        left: 28px;
        height: 2px;
        background-color: var(--primary-green);
        z-index: 1;
        transition: width 0.3s ease;
    }

    .progress-nodes {
        display: flex;
        justify-content: space-between;
        position: relative;
        z-index: 2;
    }

    .progress-node {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        width: 72px;
    }

    .node-circle {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background-color: #FFFFFF;
        border: 2px solid #CBD5E1;
        color: #94A3B8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12.5px;
        font-weight: 700;
        transition: all 0.2s ease;
    }

    .node-label {
        font-size: 11.5px;
        font-weight: 600;
        color: var(--secondary-text);
        text-align: center;
        white-space: nowrap;
    }

    /* Node Completed & Active States */
    .progress-node.completed .node-circle {
        background-color: var(--primary-green);
        border-color: var(--primary-green);
        color: #FFFFFF;
    }

    .progress-node.active .node-circle {
        background-color: var(--primary-green);
        border-color: var(--primary-green);
        color: #FFFFFF;
        box-shadow: 0 0 0 4px rgba(0, 155, 85, 0.2);
    }

    .progress-node.active .node-label,
    .progress-node.completed .node-label {
        color: var(--primary-green);
        font-weight: 700;
    }

    /* SECTION 3: Customer Details */
    .customer-content-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .customer-left-box {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .customer-avatar-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background-color: var(--light-green);
        color: var(--primary-green);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .customer-name-text {
        font-size: 15px;
        font-weight: 800;
        color: var(--primary-text);
        line-height: 1.2;
    }

    .customer-phone-text {
        font-size: 13px;
        color: var(--secondary-text);
        font-weight: 500;
        margin-top: 3px;
    }

    .customer-action-circles {
        display: flex;
        gap: 8px;
    }

    .btn-action-round {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        text-decoration: none;
        transition: transform 0.15s;
    }

    .btn-action-round:active {
        transform: scale(0.92);
    }

    .btn-round-call {
        background-color: #EFF6FF;
        color: #2563EB;
        border: 1px solid #DBEAFE;
    }

    .btn-round-wa {
        background-color: #E8F7EF;
        color: var(--primary-green);
        border: 1px solid #C6F0DC;
    }

    /* SECTION 4: Delivery Address */
    .address-content-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
    }

    .address-text-left {
        font-size: 13px;
        color: var(--secondary-text);
        line-height: 1.45;
        font-weight: 500;
        flex: 1;
    }

    .btn-open-map-pill {
        background: var(--light-green);
        color: var(--primary-green);
        border: 1px solid #C6F0DC;
        border-radius: var(--pill-radius);
        padding: 8px 14px;
        font-size: 12.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        white-space: nowrap;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }

    .btn-open-map-pill:active {
        transform: scale(0.95);
        background: #DCFCE7;
    }

    .btn-open-map-pill.btn-map-disabled {
        background: #F1F5F9;
        color: #94A3B8;
        border-color: #E2E8F0;
        cursor: not-allowed;
    }

    .btn-open-map-pill.btn-map-disabled:active {
        transform: none;
        background: #F1F5F9;
    }

    /* SECTION 5: Ordered Products */
    .product-row-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid var(--divider-color);
    }

    .product-row-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .product-img-box {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        border: 1px solid var(--border-color);
        background-color: #F8FAFC;
        overflow: hidden;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .product-img-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .product-meta-col {
        flex: 1;
        min-width: 0;
    }

    .product-name-title {
        font-size: 13.5px;
        font-weight: 700;
        color: var(--primary-text);
        line-height: 1.3;
        margin-bottom: 3px;
    }

    .product-qty-sub {
        font-size: 12.5px;
        color: var(--secondary-text);
        font-weight: 500;
    }

    .product-price-total {
        font-size: 15.5px;
        font-weight: 800;
        color: var(--primary-text);
        text-align: right;
    }

    /* SECTION 6: Payment Summary */
    .payment-line-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13.5px;
        color: var(--secondary-text);
        margin-bottom: 8px;
    }

    .payment-line-item.total-line {
        border-top: 1px solid var(--divider-color);
        padding-top: 10px;
        margin-top: 10px;
        margin-bottom: 14px;
        font-size: 16px;
        font-weight: 800;
        color: var(--primary-text);
    }

    .payment-line-item.total-line .grand-amount {
        color: var(--primary-green);
        font-size: 18px;
    }

    .payment-badges-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    /* SECTION 7: Customer OTP Card */
    .otp-card-container {
        text-align: center;
        padding: 22px 18px;
    }

    .otp-card-header {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 16.5px;
        font-weight: 800;
        color: var(--primary-text);
        margin-bottom: 4px;
    }

    .otp-card-header i {
        color: var(--primary-green);
        font-size: 18px;
    }

    .otp-card-subtitle {
        font-size: 12.5px;
        color: var(--secondary-text);
        font-weight: 500;
        margin-bottom: 18px;
    }

    .otp-boxes-flex {
        display: flex;
        gap: 8px;
        justify-content: center;
        margin-bottom: 14px;
    }

    .otp-box-cell {
        width: 44px;
        height: 50px;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        background: #FFFFFF;
        text-align: center;
        font-size: 20px;
        font-weight: 800;
        color: var(--primary-text);
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .otp-box-cell:focus {
        border-color: var(--primary-green);
        box-shadow: 0 0 0 3px rgba(0, 155, 85, 0.12);
    }

    .otp-send-link-wrap {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
    }

    .btn-send-otp-link {
        color: var(--primary-green);
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px 8px;
        font-size: 13px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-send-otp-link:hover {
        text-decoration: underline;
    }

    .otp-feedback-badge {
        display: none;
        margin-top: 8px;
        color: var(--primary-green);
        font-size: 12.5px;
        font-weight: 700;
    }

    /* SECTION 8: Fixed Bottom Action Bar */
    .fixed-bottom-action-bar {
        position: fixed;
        bottom: 68px; /* positioned right above fixed bottom navigation */
        left: 50%;
        transform: translateX(-50%);
        width: 100%;
        max-width: 480px;
        background: #FFFFFF;
        padding: 12px 18px;
        border-top: 1px solid var(--border-color);
        box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.04);
        z-index: 990;
    }

    .btn-primary-action {
        width: 100%;
        background-color: var(--primary-green);
        color: #FFFFFF;
        border: none;
        border-radius: var(--pill-radius);
        padding: 14px 20px;
        font-size: 15px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: background-color 0.2s, transform 0.1s;
        text-decoration: none;
    }

    .btn-primary-action:hover {
        background-color: var(--dark-green);
        color: #FFFFFF;
    }

    .btn-primary-action:active {
        transform: scale(0.98);
    }

    .btn-primary-action.btn-completed {
        background-color: #059669;
        cursor: default;
    }

    /* Padding for bottom areas */
    .details-page-content {
        padding: 16px 18px <?= $current_step === 4 ? '90px' : '150px' ?>; /* ample space so bottom actions never overlap */
    }
</style>

<div class="details-page-content">

    <!-- Top Navigation Header -->
    <header class="view-order-topbar">
        <div class="topbar-left">
            <a href="orders.php" class="btn-back-arrow" title="Back to Orders">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h1 class="view-order-title">Order Details</h1>
        </div>

        <div class="brand-logo-wrap">
            <svg width="28" height="28" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M5 24C8 13 13 6 18 5C22 4 23 8 21 15C19 22 23 27 28 20C32 14 35 8 36 7" stroke="#009B55" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="35" cy="8" r="2.8" fill="#009B55"/>
            </svg>
            <div class="brand-names">
                <span class="brand-main">Mandal</span>
                <span class="brand-sub">Variety</span>
            </div>
        </div>
    </header>

    <!-- Error Banner if any -->
    <?php if ($error): ?>
        <div class="order-view-card" style="background:#FEF2F2; border-color:#FECACA; color:#B91C1C; padding:12px 16px;">
            <i class="fa-solid fa-circle-exclamation me-2"></i><?= e($error) ?>
        </div>
    <?php endif; ?>

    <!-- SECTION 1 & 2: Order Header & 4-Step Progress Tracker Card -->
    <div class="order-view-card">
        <div class="order-head-info">
            <div class="order-head-left">
                <div class="order-bag-circle" style="<?= $current_step === 4 ? 'background:#DCFCE7; color:#15803D;' : '' ?>">
                    <i class="fa-solid <?= $current_step === 4 ? 'fa-circle-check' : 'fa-bag-shopping' ?>"></i>
                </div>
                <div>
                    <h2 class="order-id-main">#<?= e($order_no) ?></h2>
                    <div class="order-date-text"><?= e($order_date_str) ?></div>
                </div>
            </div>

            <div class="badge-status" style="<?= $current_step === 4 ? 'background:#DCFCE7; color:#15803D;' : '' ?>">
                <span class="status-dot" style="<?= $current_step === 4 ? 'background-color:#15803D;' : '' ?>"></span>
                <span><?= e($current_badge_label) ?></span>
            </div>
        </div>

        <!-- Horizontal 4-Step Progress Tracker -->
        <div class="progress-track-wrapper">
            <div class="progress-line-bg"></div>
            <?php
                // Calculate percentage for progress line fill
                $lineWidthPercent = match($current_step) {
                    2 => '33.3%',
                    3 => '66.6%',
                    4 => '100%',
                    default => '0%'
                };
            ?>
            <div class="progress-line-fill" style="width: <?= $lineWidthPercent ?>;"></div>

            <div class="progress-nodes">
                <!-- Step 1: Assigned -->
                <div class="progress-node <?= $current_step >= 1 ? ($current_step === 1 ? 'active' : 'completed') : '' ?>">
                    <div class="node-circle">
                        <?php if ($current_step >= 1): ?>
                            <i class="fa-solid fa-check"></i>
                        <?php else: ?>
                            1
                        <?php endif; ?>
                    </div>
                    <span class="node-label">Assigned</span>
                </div>

                <!-- Step 2: Picked Up -->
                <div class="progress-node <?= $current_step >= 2 ? ($current_step === 2 ? 'active' : 'completed') : '' ?>">
                    <div class="node-circle">
                        <?php if ($current_step >= 2): ?>
                            <i class="fa-solid fa-check"></i>
                        <?php else: ?>
                            2
                        <?php endif; ?>
                    </div>
                    <span class="node-label">Picked Up</span>
                </div>

                <!-- Step 3: Out for Delivery -->
                <div class="progress-node <?= $current_step >= 3 ? ($current_step === 3 ? 'active' : 'completed') : '' ?>">
                    <div class="node-circle">
                        <?php if ($current_step >= 3): ?>
                            <i class="fa-solid fa-check"></i>
                        <?php else: ?>
                            3
                        <?php endif; ?>
                    </div>
                    <span class="node-label">Out for Delivery</span>
                </div>

                <!-- Step 4: Delivered -->
                <div class="progress-node <?= $current_step >= 4 ? 'active completed' : '' ?>">
                    <div class="node-circle">
                        <?php if ($current_step >= 4): ?>
                            <i class="fa-solid fa-check"></i>
                        <?php else: ?>
                            4
                        <?php endif; ?>
                    </div>
                    <span class="node-label">Delivered</span>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 3: Customer Details Card -->
    <div class="order-view-card">
        <div class="card-heading-row">
            <i class="fa-solid fa-user card-heading-icon"></i>
            <span>Customer Details</span>
        </div>

        <div class="customer-content-row">
            <div class="customer-left-box">
                <div class="customer-avatar-circle">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <div class="customer-name-text"><?= e($customer_name) ?></div>
                    <div class="customer-phone-text"><?= e($customer_phone ?: 'No phone provided') ?></div>
                </div>
            </div>

            <div class="customer-action-circles">
                <?php if ($customer_phone): ?>
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $customer_phone) ?>" class="btn-action-round btn-round-call" title="Call Customer">
                        <i class="fa-solid fa-phone"></i>
                    </a>
                    <?php if ($clean_phone): ?>
                        <a href="<?= e($whatsapp_url) ?>" target="_blank" class="btn-action-round btn-round-wa" title="WhatsApp Customer">
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SECTION 4: Delivery Address Card -->
    <div class="order-view-card">
        <div class="card-heading-row">
            <i class="fa-solid fa-location-dot card-heading-icon"></i>
            <span>Delivery Address</span>
        </div>

        <div class="address-content-row">
            <div class="address-text-left">
                <?php if (!empty($clean_address)): ?>
                    <?= nl2br(e($clean_address)) ?><?php if (!empty($display_pincode)): ?>, <?= e($display_pincode) ?><?php endif; ?>
                <?php else: ?>
                    <span style="color: #94A3B8; font-style: italic;">No specific delivery address recorded</span>
                <?php endif; ?>

                <?php if ($customer_lat !== null && $customer_lng !== null): ?>
                    <div style="font-size: 11.5px; color: var(--primary-green); font-weight: 600; margin-top: 5px; display: flex; align-items: center; gap: 4px;">
                        <i class="fa-solid fa-crosshairs"></i>
                        <span>Coordinates: <?= sprintf('%.5f, %.5f', $customer_lat, $customer_lng) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($has_valid_destination): ?>
                <a href="<?= e($maps_url) ?>" target="_blank" rel="noopener noreferrer" class="btn-open-map-pill" title="Open Google Maps Navigation">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Open in Map</span>
                </a>
            <?php else: ?>
                <button type="button" class="btn-open-map-pill btn-map-disabled" onclick="showMvToast('No valid customer delivery location or address found for this order.', false)" title="No valid address or coordinates">
                    <i class="fa-solid fa-location-slash"></i>
                    <span>No Location</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- SECTION 5: Ordered Products Card -->
    <div class="order-view-card">
        <div class="card-heading-row">
            <i class="fa-solid fa-bag-shopping card-heading-icon"></i>
            <span>Ordered Products (<?= count($items) ?>)</span>
        </div>

        <div class="products-stack">
            <?php foreach ($items as $item): ?>
                <?php
                    $imgUrl = getDeliveryProductImageUrl($item['images'] ?? '');
                    $fallbackUrl = !empty($imgUrl) && strpos($imgUrl, 'http') === 0 ? 'https://admin.mandal-variety.com/uploads/' . rawurlencode(basename($imgUrl)) : '';
                    $itemQty = (int)$item['quantity'];
                    $itemPrice = (float)$item['price'];
                    $itemLineTotal = $itemPrice * $itemQty;
                ?>
                <div class="product-row-item">
                    <div class="product-img-box">
                        <?php if (!empty($imgUrl)): ?>
                            <img src="<?= e($imgUrl) ?>" alt="<?= e($item['product_name']) ?>" onerror="if (this.src.indexOf('admin.mandal-variety.com') === -1 && '<?= e($fallbackUrl) ?>') { this.src='<?= e($fallbackUrl) ?>'; } else { this.onerror=null; this.parentElement.innerHTML='<i class=\"fa-solid fa-box text-secondary\"></i>'; }">
                        <?php else: ?>
                            <i class="fa-solid fa-box" style="color: #94A3B8; font-size: 18px;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="product-meta-col">
                        <div class="product-name-title"><?= e($item['product_name'] ?? 'Product') ?></div>
                        <div class="product-qty-sub">Qty: <?= $itemQty ?> &times; ₹<?= number_format($itemPrice, 0) ?></div>
                    </div>
                    <div class="product-price-total">₹<?= number_format($itemLineTotal, 0) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- SECTION 6: Payment Summary Card -->
    <div class="order-view-card">
        <div class="card-heading-row">
            <i class="fa-solid fa-receipt card-heading-icon"></i>
            <span>Payment Summary</span>
        </div>

        <div class="payment-line-item">
            <span>Items Subtotal</span>
            <span>₹<?= number_format($items_subtotal, 0) ?></span>
        </div>

        <div class="payment-line-item">
            <span>Delivery Charge</span>
            <span>₹<?= number_format($delivery_charge, 0) ?></span>
        </div>

        <div class="payment-line-item total-line">
            <span>Grand Total</span>
            <span class="grand-amount">₹<?= number_format($grand_total, 0) ?></span>
        </div>

        <?php
            $curr_pay_status = strtolower(trim((string)($order['payment_status'] ?? 'pending')));
            $is_paid_status = ($curr_pay_status === 'paid');
            $pay_badge_style = $is_paid_status ? 'background:#DCFCE7; color:#15803D;' : 'background:#FEF3C7; color:#B45309;';
            $pay_dot_style = $is_paid_status ? 'background-color:#15803D;' : 'background-color:#B45309;';
        ?>
        <div class="payment-badges-row">
            <div class="badge-status" style="<?= $is_cod ? 'background:#FEF3C7; color:#B45309;' : 'background:#DCFCE7; color:#15803D;' ?>">
                <span class="status-dot" style="<?= $is_cod ? 'background-color:#B45309;' : 'background-color:#15803D;' ?>"></span>
                <span><?= $is_cod ? 'Cash on Delivery (COD)' : 'Paid Online' ?></span>
            </div>

            <div class="badge-status" style="<?= $pay_badge_style ?>">
                <span class="status-dot" style="<?= $pay_dot_style ?>"></span>
                <span>Payment: <?= ucfirst(e($order['payment_status'] ?? 'Pending')) ?></span>
            </div>
        </div>
    </div>

    <!-- SECTION 7: Customer OTP Card (Only when Out for Delivery) -->
    <?php if ($current_step === 3): ?>
    <div class="order-view-card otp-card-container">
        <div class="otp-card-header">
            <i class="fa-solid fa-circle-check"></i>
            <span>Enter Customer OTP</span>
        </div>
        <p class="otp-card-subtitle">Ask the customer for the 6-digit delivery code.</p>

        <!-- 6 OTP Input Boxes -->
        <div class="otp-boxes-flex">
            <?php for ($i = 0; $i < 6; $i++): ?>
                <input type="tel" 
                       class="otp-box-cell" 
                       maxlength="1" 
                       pattern="[0-9]*" 
                       inputmode="numeric" 
                       autocomplete="off"
                       data-index="<?= $i ?>">
            <?php endfor; ?>
        </div>

        <!-- Send / Resend OTP Action -->
        <div class="otp-send-link-wrap">
            <button type="button" class="btn-send-otp-link" id="btnSendOtpLink" onclick="triggerSendOtp(false)">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Send OTP to Customer</span>
            </button>
        </div>
        <div id="otpSentAlertBadge" class="otp-feedback-badge">
            <i class="fa-solid fa-circle-check me-1"></i> OTP sent to customer successfully!
        </div>
    </div>
    <?php endif; ?>

</div>

<?php if ($current_step < 4): ?>
<!-- SECTION 8: Fixed Bottom Action Area (above bottom nav) -->
<div class="fixed-bottom-action-bar">
    <?php if ($current_step === 1): // Status = Assigned ?>
        <form method="POST" class="w-100 m-0">
            <input type="hidden" name="action" value="confirm_pickup">
            <button type="submit" class="btn-primary-action">
                <i class="fa-solid fa-box"></i>
                <span>Confirm Pickup & Start Delivery</span>
            </button>
        </form>
    <?php elseif ($current_step === 2): // Status = Picked Up ?>
        <form method="POST" class="w-100 m-0">
            <input type="hidden" name="action" value="out_for_delivery">
            <button type="submit" class="btn-primary-action">
                <i class="fa-solid fa-motorcycle"></i>
                <span>Start Delivery / Update Status</span>
            </button>
        </form>
    <?php elseif ($current_step === 3): // Status = Out for Delivery ?>
        <form method="POST" id="completeDeliveryForm" class="w-100 m-0" onsubmit="return handleOtpCompletionSubmit(event)">
            <input type="hidden" name="action" value="verify_otp">
            <input type="hidden" name="otp" id="hiddenOtpField">
            <button type="submit" class="btn-primary-action" id="btnSendOtpAndComplete">
                <i class="fa-solid fa-paper-plane"></i>
                <span>Send OTP & Complete Delivery</span>
            </button>
        </form>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
// OTP Digits Handling: Auto-advance, backspace, paste
const otpCells = Array.from(document.querySelectorAll('.otp-box-cell'));
const hiddenOtpField = document.getElementById('hiddenOtpField');

if (otpCells.length > 0) {
    otpCells.forEach((cell, idx) => {
        cell.addEventListener('input', (e) => {
            const val = cell.value.replace(/\D/g, '');
            cell.value = val;
            if (val && idx < otpCells.length - 1) {
                otpCells[idx + 1].focus();
            }
            syncOtpValue();
        });

        cell.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !cell.value && idx > 0) {
                otpCells[idx - 1].focus();
                otpCells[idx - 1].value = '';
                syncOtpValue();
            }
        });

        cell.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            for (let i = 0; i < pasted.length && idx + i < otpCells.length; i++) {
                otpCells[idx + i].value = pasted[i];
            }
            syncOtpValue();
            const nextEmptyIdx = otpCells.findIndex(c => !c.value);
            if (nextEmptyIdx !== -1) {
                otpCells[nextEmptyIdx].focus();
            } else {
                otpCells[otpCells.length - 1].focus();
            }
        });
    });
}

function syncOtpValue() {
    if (!hiddenOtpField) return '';
    const full = otpCells.map(c => c.value.trim()).join('');
    hiddenOtpField.value = full;
    return full;
}

// Trigger Send OTP to customer via AJAX
let isSendingOtp = false;
function triggerSendOtp(fromMainButton = false) {
    if (isSendingOtp) return;
    isSendingOtp = true;

    const linkBtn = document.getElementById('btnSendOtpLink');
    const badge = document.getElementById('otpSentAlertBadge');
    if (linkBtn) {
        linkBtn.disabled = true;
        linkBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Sending OTP...</span>';
    }

    const formData = new FormData();
    formData.append('order_id', <?= $order_id ?>);

    fetch('ajax_send_otp.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        isSendingOtp = false;
        if (linkBtn) {
            linkBtn.disabled = false;
            linkBtn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> <span>Resend OTP</span>';
        }

        if (data.success) {
            if (badge) badge.style.display = 'block';
            showMvToast('OTP sent to customer successfully!', true);
            // Focus first digit
            if (otpCells.length > 0) otpCells[0].focus();
        } else {
            showMvToast(data.message || 'Failed to send OTP to customer', false);
        }
    })
    .catch(() => {
        isSendingOtp = false;
        if (linkBtn) {
            linkBtn.disabled = false;
            linkBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>Send OTP to Customer</span>';
        }
        showMvToast('Network connection error sending OTP', false);
    });
}

// Handle Form Submission for Out for Delivery status
function handleOtpCompletionSubmit(e) {
    const otp = syncOtpValue();
    if (otp.length !== 6) {
        e.preventDefault();
        // If OTP is empty, trigger OTP sending first
        triggerSendOtp(true);
        showMvToast('Please enter the 6-digit OTP code to complete delivery', false);
        return false;
    }
    return true;
}
</script>

<?php include 'includes/footer.php'; ?>
