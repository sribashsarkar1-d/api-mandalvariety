<?php include '../includes/header.php'; ?>
<?php include '../includes/sidebar.php'; ?>

<?php
if (!function_exists('e')) {
    function e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

function val(array $row, array $keys, $default = 'N/A')
{
    foreach ($keys as $key) {
        if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
            return $row[$key];
        }
    }
    return $default;
}

function orderStatusBadge($status)
{
    $status = strtolower(trim((string)$status));

    return match ($status) {
        'pending' => '<span class="badge rounded-pill text-bg-warning px-3 py-2">Pending</span>',
        'confirmed' => '<span class="badge rounded-pill text-bg-info px-3 py-2">Confirmed</span>',
        'processing' => '<span class="badge rounded-pill text-bg-primary px-3 py-2">Processing</span>',
        'shipped' => '<span class="badge rounded-pill text-bg-secondary px-3 py-2">Shipped</span>',
        'out_for_delivery' => '<span class="badge rounded-pill text-bg-dark px-3 py-2">Out for Delivery</span>',
        'delivered' => '<span class="badge rounded-pill text-bg-success px-3 py-2">Delivered</span>',
        'cancelled' => '<span class="badge rounded-pill text-bg-danger px-3 py-2">Cancelled</span>',
        default => '<span class="badge rounded-pill bg-light text-dark border px-3 py-2">' . e(ucwords(str_replace('_', ' ', $status ?: 'unknown'))) . '</span>',
    };
}

function paymentStatusBadge($status)
{
    $status = strtolower(trim((string)$status));

    return match ($status) {
        'paid' => '<span class="badge rounded-pill text-bg-success px-3 py-2">Paid</span>',
        'pending' => '<span class="badge rounded-pill text-bg-warning px-3 py-2">Pending</span>',
        'failed' => '<span class="badge rounded-pill text-bg-danger px-3 py-2">Failed</span>',
        'refunded' => '<span class="badge rounded-pill text-bg-info px-3 py-2">Refunded</span>',
        default => '<span class="badge rounded-pill bg-light text-dark border px-3 py-2">' . e(ucwords($status ?: 'unknown')) . '</span>',
    };
}

function trackingBadge($status)
{
    $status = strtolower(trim((string)$status));

    return match ($status) {
        'ordered' => '<span class="badge rounded-pill text-bg-secondary px-3 py-2">Ordered</span>',
        'packed' => '<span class="badge rounded-pill text-bg-info px-3 py-2">Packed</span>',
        'shipped' => '<span class="badge rounded-pill text-bg-primary px-3 py-2">Shipped</span>',
        'on_the_way' => '<span class="badge rounded-pill text-bg-warning px-3 py-2">On The Way</span>',
        'delivered' => '<span class="badge rounded-pill text-bg-success px-3 py-2">Delivered</span>',
        default => '<span class="badge rounded-pill bg-light text-dark border px-3 py-2">' . e(ucwords(str_replace('_', ' ', $status ?: 'n/a'))) . '</span>',
    };
}

function productThumb($imagesJson)
{
    if (!$imagesJson) return '';

    $images = json_decode($imagesJson, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($images) && !empty($images[0])) {
        return '../uploads/' . $images[0];
    }

    return '';
}

$orderId = (int)($_GET['id'] ?? 0);

if ($orderId <= 0) {
    die('Invalid order ID');
}

$stmt = $conn->prepare("
    SELECT 
        o.*,
        u.name AS user_name,
        u.email AS user_email,
        u.phone AS user_phone
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
    LIMIT 1
");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die('Order not found');
}

$stmtItems = $conn->prepare("
    SELECT
        oi.*,
        p.id AS product_db_id,
        p.name AS product_name,
        p.slug AS product_slug,
        p.sku AS product_sku,
        p.description AS product_description,
        p.price AS product_base_price,
        p.discount_price AS product_discount_price,
        p.stock_quantity AS product_stock_quantity,
        p.stock AS product_stock,
        p.weight AS product_weight,
        p.images AS product_images,
        p.is_active AS product_is_active,
        p.category_id AS product_category_id,
        c.name AS category_name
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE oi.order_id = ?
    ORDER BY oi.id ASC
");
$stmtItems->execute([$orderId]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

$totalItems = count($items);
$totalQty = 0;
$itemsSubtotal = 0;

$productIds = [];
$categoryIds = [];

foreach ($items as $item) {
    $qty = (int)($item['quantity'] ?? 0);
    $price = (float)($item['price'] ?? $item['unit_price'] ?? 0);
    $lineSubtotal = $price * $qty;

    $totalQty += $qty;
    $itemsSubtotal += $lineSubtotal;

    if (!empty($item['product_id'])) {
        $productIds[] = (int)$item['product_id'];
    }

    if (!empty($item['product_category_id'])) {
        $categoryIds[] = (int)$item['product_category_id'];
    }
}

$productIds = array_values(array_unique(array_filter($productIds)));
$categoryIds = array_values(array_unique(array_filter($categoryIds)));

$orderNumber = val($order, ['order_number', 'order_no', 'invoice_no', 'invoice_number'], 'N/A');
$orderStatus = val($order, ['status'], '');
$paymentStatus = val($order, ['payment_status'], '');
$trackingStatus = val($order, ['tracking_status'], '');

$userName = val($order, ['user_name', 'customer_name', 'name'], 'N/A');
$userEmail = val($order, ['user_email', 'email'], 'N/A');
$userPhone = val($order, ['user_phone', 'phone', 'mobile'], 'N/A');

$deliveryAddress = val($order, ['delivery_address', 'shipping_address', 'address', 'full_address'], 'N/A');
$deliveryLandmark = val($order, ['delivery_landmark', 'shipping_landmark', 'landmark', 'area'], 'N/A');
$deliveryCity = val($order, ['delivery_city', 'shipping_city', 'city'], 'N/A');
$deliveryState = val($order, ['delivery_state', 'shipping_state', 'state'], 'N/A');
$deliveryCountry = val($order, ['delivery_country', 'shipping_country', 'country'], 'N/A');
$deliveryPincode = val($order, ['delivery_pincode', 'shipping_pincode', 'pincode', 'postal_code', 'zip', 'zip_code'], 'N/A');
$deliveryId = val($order, ['delivery_id', 'delivery_boy_id', 'assigned_delivery_id', 'rider_id'], 'N/A');

$etaRaw = val($order, ['delivery_eta', 'estimated_delivery_date', 'delivery_date', 'eta'], '');
$deliveryEta = ($etaRaw !== 'N/A' && $etaRaw !== '') ? date('d M Y', strtotime($etaRaw)) : 'N/A';

$paymentMethod = val($order, ['payment_method', 'payment_type', 'method'], 'N/A');
$transactionId = val($order, ['transaction_id', 'txn_id', 'payment_id'], 'N/A');
$notes = val($order, ['notes', 'order_note', 'customer_note'], 'N/A');

$createdAt = !empty($order['created_at']) ? date('d M Y, h:i A', strtotime($order['created_at'])) : 'N/A';
$updatedAt = !empty($order['updated_at']) ? date('d M Y, h:i A', strtotime($order['updated_at'])) : 'N/A';

$offerName = val($order, ['offer_name'], '');
$offerType = val($order, ['offer_type'], '');
$offerValue = (float)($order['offer_value'] ?? 0);
$offerDiscount = (float)($order['discount_amount'] ?? $order['discount'] ?? 0);

if (($offerName === '' || $offerType === '' || $offerDiscount <= 0) && (!empty($productIds) || !empty($categoryIds))) {
    $offerWhere = [];
    $offerParams = [];

    if (!empty($productIds)) {
        $productPlaceholders = implode(',', array_fill(0, count($productIds), '?'));
        $offerWhere[] = "product_id IN ($productPlaceholders)";
        $offerParams = array_merge($offerParams, $productIds);
    }

    if (!empty($categoryIds)) {
        $categoryPlaceholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $offerWhere[] = "category_id IN ($categoryPlaceholders)";
        $offerParams = array_merge($offerParams, $categoryIds);
    }

    if (!empty($offerWhere)) {
        $offerSql = "
            SELECT *
            FROM offers
            WHERE (" . implode(' OR ', $offerWhere) . ")
            AND (start_date IS NULL OR start_date <= CURDATE())
            AND (end_date IS NULL OR end_date >= CURDATE())
            ORDER BY priority DESC, id DESC
            LIMIT 1
        ";

        $stmtOffer = $conn->prepare($offerSql);
        $stmtOffer->execute($offerParams);
        $offer = $stmtOffer->fetch(PDO::FETCH_ASSOC);

        if ($offer) {
            $offerName = $offer['offer_name'] ?? 'Offer';
            $offerType = $offer['offer_type'] ?? '';
            $offerValue = (float)($offer['offer_value'] ?? 0);

            if ($offerType === 'percent') {
                $offerDiscount = ($itemsSubtotal * $offerValue) / 100;
            } elseif ($offerType === 'flat') {
                $offerDiscount = $offerValue;
            }
        }
    }
}

if ($offerDiscount > $itemsSubtotal) {
    $offerDiscount = $itemsSubtotal;
}

$deliveryCharge = 10.00;
$grandTotal = $itemsSubtotal - $offerDiscount + $deliveryCharge;
?>

<div class="w-100 orders-page">
    <?php include '../includes/topbar.php'; ?>

    <style>
        .orders-page {
            background: linear-gradient(180deg, #f8fbff 0%, #f1f5f9 100%);
            min-height: 100vh;
            padding-bottom: 30px;
        }

        .orders-page .page-header {
            background: #ffffff;
            border-radius: 20px;
            padding: 22px 24px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            border: 1px solid #eaf0f6;
        }

        .orders-page .page-title {
            font-weight: 800;
            font-size: 1.8rem;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .orders-page .page-subtitle {
            color: #64748b;
            margin: 0;
            font-size: .95rem;
        }

        .orders-page .header-actions .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 10px 16px;
        }

        .orders-page .btn-update {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            border: none;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
        }

        .orders-page .btn-update:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            color: #fff;
        }

        .orders-page .stats-card,
        .orders-page .detail-card,
        .orders-page .table-card {
            border: 1px solid #e9eef5;
            border-radius: 20px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
            background: #fff;
            overflow: hidden;
        }

        .orders-page .stats-card .card-body,
        .orders-page .detail-card .card-body {
            padding: 20px;
        }

        .orders-page .stats-label,
        .orders-page .section-label {
            font-size: .76rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .orders-page .stats-value {
            font-size: 1.45rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }

        .orders-page .stats-meta {
            margin-top: 8px;
            color: #64748b;
            font-size: .88rem;
        }

        .orders-page .info-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .orders-page .info-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;
            padding: 12px 14px;
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 14px;
        }

        .orders-page .info-key {
            color: #64748b;
            font-size: .9rem;
            min-width: 110px;
        }

        .orders-page .info-value {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
            word-break: break-word;
        }

        .orders-page .table-card-header {
            padding: 20px;
            border-bottom: 1px solid #eef2f7;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .orders-page .table-title {
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
            font-size: 1.1rem;
        }

        .orders-page .table-note {
            color: #64748b;
            font-size: .9rem;
        }

        .orders-page .table-scroll-area {
            width: 100%;
            overflow-x: auto;
        }

        .orders-page .table {
            margin-bottom: 0;
        }

        .orders-page .table thead th {
            background: #0f172a !important;
            color: #fff !important;
            border: none !important;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            white-space: nowrap;
            padding: 14px 12px;
        }

        .orders-page .table tbody td {
            vertical-align: middle;
            padding: 14px 12px;
            border-top: 1px solid #eef2f7;
        }

        .orders-page .table tbody tr:hover td {
            background: #f8fbff;
        }

        .orders-page .product-thumb {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            background: #fff;
        }

        .orders-page .product-thumb-placeholder {
            width: 64px;
            height: 64px;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            color: #6b7280;
            font-size: 12px;
            font-weight: 600;
        }

        .orders-page .product-name {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .orders-page .product-meta {
            color: #64748b;
            font-size: .84rem;
            line-height: 1.5;
        }

        .orders-page .summary-box {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .orders-page .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 12px 14px;
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 14px;
        }

        .orders-page .summary-key {
            color: #64748b;
            font-size: .92rem;
        }

        .orders-page .summary-value {
            font-weight: 800;
            color: #0f172a;
        }

        .orders-page .summary-value.discount {
            color: #16a34a;
        }

        .orders-page .summary-value.total {
            color: #111827;
            font-size: 1.1rem;
        }

        .orders-page .note-box {
            background: #fff7ed;
            color: #9a3412;
            border: 1px solid #fed7aa;
            border-radius: 14px;
            padding: 14px 16px;
            font-size: .92rem;
        }

        .orders-page .btn-view-product {
            border-radius: 10px;
            font-weight: 600;
            padding: 8px 14px;
        }

        .orders-page .modal-content {
            border: 0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.16);
        }

        .orders-page .modal-header {
            background: linear-gradient(135deg, #eff6ff, #f8fafc);
            border-bottom: 1px solid #e5e7eb;
            padding: 18px 22px;
        }

        .orders-page .modal-title {
            font-weight: 800;
            color: #0f172a;
        }

        .orders-page .modal-body {
            padding: 22px;
        }

        .orders-page .modal-product-image {
            width: 100%;
            max-height: 260px;
            object-fit: cover;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            background: #fff;
        }

        .orders-page .modal-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .orders-page .modal-info-item {
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 14px;
            padding: 12px 14px;
        }

        .orders-page .modal-info-item .label {
            font-size: .75rem;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .orders-page .modal-info-item .value {
            font-size: .95rem;
            color: #0f172a;
            font-weight: 700;
            word-break: break-word;
        }

        .orders-page .modal-description {
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 14px;
            padding: 14px;
            color: #334155;
            line-height: 1.7;
            white-space: pre-wrap;
        }

        @media (max-width: 991.98px) {
            .orders-page .modal-info-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .orders-page .page-header {
                padding: 18px;
                border-radius: 16px;
            }

            .orders-page .page-title {
                font-size: 1.35rem;
            }

            .orders-page .stats-card .card-body,
            .orders-page .detail-card .card-body,
            .orders-page .table-card-header,
            .orders-page .modal-body {
                padding: 16px;
            }

            .orders-page .info-item,
            .orders-page .summary-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .orders-page .info-value {
                text-align: left;
            }

            .orders-page .header-actions {
                width: 100%;
            }

            .orders-page .header-actions .btn {
                width: 100%;
            }
        }
    </style>

    <div class="container-fluid mt-4">
        <div class="page-header mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="page-title mb-1">Order Details</h4>
                    <p class="page-subtitle">View complete order, delivery, billing and product information.</p>
                </div>

                <div class="d-flex flex-wrap gap-2 header-actions">
                    <a href="list.php" class="btn btn-outline-secondary">← Back to List</a>
                    <a href="update_status.php?id=<?= $orderId ?>" class="btn btn-update">Update Status</a>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="stats-card h-100">
                    <div class="card-body">
                        <div class="stats-label">Order No</div>
                        <div class="stats-value"><?= e($orderNumber) ?></div>
                        <div class="stats-meta"><?= e($createdAt) ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="stats-card h-100">
                    <div class="card-body">
                        <div class="stats-label">Order Status</div>
                        <div><?= orderStatusBadge($orderStatus) ?></div>
                        <div class="stats-meta mt-2">Tracking: <?= trackingBadge($trackingStatus) ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="stats-card h-100">
                    <div class="card-body">
                        <div class="stats-label">Payment</div>
                        <div><?= paymentStatusBadge($paymentStatus) ?></div>
                        <div class="stats-meta mt-2"><?= e($paymentMethod) ?></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="stats-card h-100">
                    <div class="card-body">
                        <div class="stats-label">Final Payable</div>
                        <div class="stats-value">₹<?= number_format($grandTotal, 2) ?></div>
                        <div class="stats-meta"><?= $totalItems ?> item(s), Qty <?= $totalQty ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="detail-card mb-4">
                    <div class="card-body">
                        <div class="section-label">Customer Details</div>
                        <div class="info-list">
                            <div class="info-item">
                                <div class="info-key">Name</div>
                                <div class="info-value"><?= e($userName) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-key">Email</div>
                                <div class="info-value"><?= e($userEmail) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-key">Phone</div>
                                <div class="info-value"><?= e($userPhone) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-key">Transaction ID</div>
                                <div class="info-value"><?= e($transactionId) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-key">Updated</div>
                                <div class="info-value"><?= e($updatedAt) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail-card mb-4">
                    <div class="card-body">
                        <div class="section-label">Delivery Details</div>
                        <div class="info-list">
                            <div class="info-item">
                                <div class="info-key">Address</div>
                                <div class="info-value"><?= e($deliveryAddress) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-key">Landmark</div>
                                <div class="info-value"><?= e($deliveryLandmark) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-key">City</div>
                                <div class="info-value"><?= e($deliveryCity) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-key">State</div>
                                <div class="info-value"><?= e($deliveryState) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-key">Country</div>
                                <div class="info-value"><?= e($deliveryCountry) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-key">Pincode</div>
                                <div class="info-value"><?= e($deliveryPincode) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-key">Delivery ID</div>
                                <div class="info-value"><?= e($deliveryId) ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-key">ETA</div>
                                <div class="info-value"><?= e($deliveryEta) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail-card mb-4">
                    <div class="card-body">
                        <div class="section-label">Billing Summary</div>
                        <div class="summary-box">
                            <div class="summary-row">
                                <div class="summary-key">Items Subtotal</div>
                                <div class="summary-value">₹<?= number_format($itemsSubtotal, 2) ?></div>
                            </div>
                            <div class="summary-row">
                                <div class="summary-key">Offer Discount</div>
                                <div class="summary-value discount">- ₹<?= number_format($offerDiscount, 2) ?></div>
                            </div>
                            <div class="summary-row">
                                <div class="summary-key">Delivery Charge</div>
                                <div class="summary-value">₹<?= number_format($deliveryCharge, 2) ?></div>
                            </div>
                            <div class="summary-row">
                                <div class="summary-key">Grand Total</div>
                                <div class="summary-value total">₹<?= number_format($grandTotal, 2) ?></div>
                            </div>
                            <div class="summary-row">
                                <div class="summary-key">Offer Name</div>
                                <div class="summary-value"><?= e($offerName ?: 'No Offer') ?></div>
                            </div>
                            <div class="summary-row">
                                <div class="summary-key">Offer Type</div>
                                <div class="summary-value"><?= e($offerType ?: 'N/A') ?></div>
                            </div>
                            <div class="summary-row">
                                <div class="summary-key">Offer Value</div>
                                <div class="summary-value">
                                    <?php if ($offerType === 'percent'): ?>
                                        <?= number_format($offerValue, 2) ?>%
                                    <?php elseif ($offerType === 'flat'): ?>
                                        ₹<?= number_format($offerValue, 2) ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($notes !== 'N/A'): ?>
                    <div class="note-box">
                        <strong>Customer Note:</strong><br>
                        <?= nl2br(e($notes)) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-8">
                <div class="table-card">
                    <div class="table-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="table-title">Order Items</div>
                            <div class="table-note"><?= $totalItems ?> item(s), total quantity <?= $totalQty ?></div>
                        </div>
                        <a href="update_status.php?id=<?= $orderId ?>" class="btn btn-sm btn-update">Update Status</a>
                    </div>

                    <div class="table-scroll-area">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($items)): ?>
                                    <?php foreach ($items as $i => $item): ?>
                                        <?php
                                            $productId = (int)($item['product_db_id'] ?? $item['product_id'] ?? 0);
                                            $productName = val($item, ['product_name', 'name'], 'N/A');
                                            $productSku = val($item, ['product_sku', 'sku'], 'N/A');
                                            $categoryName = val($item, ['category_name'], 'N/A');
                                            $productSlug = val($item, ['product_slug'], 'N/A');
                                            $productDescription = val($item, ['product_description'], 'N/A');
                                            $productBasePrice = (float)($item['product_base_price'] ?? 0);
                                            $productDiscountPrice = ($item['product_discount_price'] !== null && $item['product_discount_price'] !== '') ? (float)$item['product_discount_price'] : null;
                                            $productStockQty = (int)($item['product_stock_quantity'] ?? 0);
                                            $productStock = (int)($item['product_stock'] ?? 0);
                                            $productWeight = ($item['product_weight'] !== null && $item['product_weight'] !== '') ? (float)$item['product_weight'] : null;
                                            $productStatus = (int)($item['product_is_active'] ?? 0) === 1 ? 'Active' : 'Inactive';

                                            $price = (float)($item['price'] ?? $item['unit_price'] ?? 0);
                                            $qty = (int)($item['quantity'] ?? 0);
                                            $subtotal = $price * $qty;
                                            $thumb = productThumb($item['product_images'] ?? '');
                                        ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td>
                                                <?php if ($thumb): ?>
                                                    <img src="<?= e($thumb) ?>" alt="<?= e($productName) ?>" class="product-thumb">
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center justify-content-center product-thumb-placeholder">No Image</div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="product-name"><?= e($productName) ?></div>
                                                <div class="product-meta">Product ID: <?= $productId ?></div>
                                                <div class="product-meta">Category: <?= e($categoryName) ?></div>
                                            </td>
                                            <td><?= e($productSku) ?></td>
                                            <td>₹<?= number_format($price, 2) ?></td>
                                            <td><?= $qty ?></td>
                                            <td><strong>₹<?= number_format($subtotal, 2) ?></strong></td>
                                            <td class="text-center">
                                                <button
                                                    type="button"
                                                    class="btn btn-primary btn-sm btn-view-product view-product-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#productDetailsModal"
                                                    data-id="<?= $productId ?>"
                                                    data-name="<?= e($productName) ?>"
                                                    data-sku="<?= e($productSku) ?>"
                                                    data-slug="<?= e($productSlug) ?>"
                                                    data-category="<?= e($categoryName) ?>"
                                                    data-description="<?= e($productDescription) ?>"
                                                    data-price="<?= number_format($productBasePrice, 2, '.', '') ?>"
                                                    data-discount_price="<?= $productDiscountPrice !== null ? number_format($productDiscountPrice, 2, '.', '') : '' ?>"
                                                    data-stock_quantity="<?= $productStockQty ?>"
                                                    data-stock="<?= $productStock ?>"
                                                    data-weight="<?= $productWeight !== null ? $productWeight : '' ?>"
                                                    data-status="<?= e($productStatus) ?>"
                                                    data-image="<?= e($thumb) ?>"
                                                >
                                                    View Product
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No order items found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="productDetailsModal" tabindex="-1" aria-labelledby="productDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productDetailsModalLabel">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <div class="col-md-5">
                        <img src="" alt="Product Image" id="modalProductImage" class="modal-product-image">
                    </div>

                    <div class="col-md-7">
                        <div class="modal-info-grid">
                            <div class="modal-info-item">
                                <div class="label">Product ID</div>
                                <div class="value" id="modalProductId">N/A</div>
                            </div>
                            <div class="modal-info-item">
                                <div class="label">Name</div>
                                <div class="value" id="modalProductName">N/A</div>
                            </div>
                            <div class="modal-info-item">
                                <div class="label">SKU</div>
                                <div class="value" id="modalProductSku">N/A</div>
                            </div>
                            <div class="modal-info-item">
                                <div class="label">Slug</div>
                                <div class="value" id="modalProductSlug">N/A</div>
                            </div>
                            <div class="modal-info-item">
                                <div class="label">Category</div>
                                <div class="value" id="modalProductCategory">N/A</div>
                            </div>
                            <div class="modal-info-item">
                                <div class="label">Status</div>
                                <div class="value" id="modalProductStatus">N/A</div>
                            </div>
                            <div class="modal-info-item">
                                <div class="label">Price</div>
                                <div class="value" id="modalProductPrice">N/A</div>
                            </div>
                            <div class="modal-info-item">
                                <div class="label">Discount Price</div>
                                <div class="value" id="modalProductDiscountPrice">N/A</div>
                            </div>
                            <div class="modal-info-item">
                                <div class="label">Stock Quantity</div>
                                <div class="value" id="modalProductStockQty">N/A</div>
                            </div>
                            <div class="modal-info-item">
                                <div class="label">Stock</div>
                                <div class="value" id="modalProductStock">N/A</div>
                            </div>
                            <div class="modal-info-item">
                                <div class="label">Weight</div>
                                <div class="value" id="modalProductWeight">N/A</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="label mb-2" style="font-size:.78rem; text-transform:uppercase; color:#64748b; font-weight:700;">Description</div>
                        <div class="modal-description" id="modalProductDescription">N/A</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.view-product-btn').forEach(button => {
    button.addEventListener('click', function () {
        const image = this.getAttribute('data-image') || '';
        document.getElementById('modalProductImage').src = image || '../assets/no-image.png';
        document.getElementById('modalProductId').textContent = this.getAttribute('data-id') || 'N/A';
        document.getElementById('modalProductName').textContent = this.getAttribute('data-name') || 'N/A';
        document.getElementById('modalProductSku').textContent = this.getAttribute('data-sku') || 'N/A';
        document.getElementById('modalProductSlug').textContent = this.getAttribute('data-slug') || 'N/A';
        document.getElementById('modalProductCategory').textContent = this.getAttribute('data-category') || 'N/A';
        document.getElementById('modalProductStatus').textContent = this.getAttribute('data-status') || 'N/A';
        document.getElementById('modalProductPrice').textContent = '₹' + (this.getAttribute('data-price') || '0.00');
        document.getElementById('modalProductDiscountPrice').textContent = this.getAttribute('data-discount_price') ? '₹' + this.getAttribute('data-discount_price') : 'N/A';
        document.getElementById('modalProductStockQty').textContent = this.getAttribute('data-stock_quantity') || '0';
        document.getElementById('modalProductStock').textContent = this.getAttribute('data-stock') || '0';
        document.getElementById('modalProductWeight').textContent = this.getAttribute('data-weight') ? this.getAttribute('data-weight') + ' kg' : 'N/A';
        document.getElementById('modalProductDescription').textContent = this.getAttribute('data-description') || 'N/A';
    });
});
</script>

<?php include '../includes/footer.php'; ?>