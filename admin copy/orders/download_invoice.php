<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/config.php'; // adjust if your DB file path is different

use Dompdf\Dompdf;
use Dompdf\Options;

if (!function_exists('e')) {
    function e($string)
    {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

function val($array, $keys, $default = '')
{
    foreach ($keys as $key) {
        if (isset($array[$key]) && $array[$key] !== '' && $array[$key] !== null) {
            return $array[$key];
        }
    }
    return $default;
}

function formatStatus($status)
{
    return ucwords(str_replace('_', ' ', (string)$status));
}

function buildInvoiceHtml($data)
{
    $order = $data['order'];
    $items = $data['items'];
    $summary = $data['summary'];

    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Invoice <?= e($order['invoice_no']) ?></title>
        <style>
            * { box-sizing: border-box; }
            body {
                margin: 0;
                padding: 28px;
                font-family: DejaVu Sans, sans-serif;
                color: #1f2937;
                background: #ffffff;
                font-size: 13px;
                line-height: 1.5;
            }
            .invoice-wrap {
                border: 1px solid #dbe4ee;
                border-radius: 14px;
                overflow: hidden;
            }
            .topbar {
                background: #0f172a;
                color: #fff;
                padding: 24px;
            }
            .topbar table { width: 100%; border-collapse: collapse; }
            .brand { font-size: 26px; font-weight: 700; }
            .muted-white { color: rgba(255,255,255,.85); font-size: 12px; }
            .section { padding: 20px 24px; }
            .grid { width: 100%; border-collapse: collapse; }
            .grid td { vertical-align: top; }
            .box {
                border: 1px solid #dbe4ee;
                border-radius: 12px;
                padding: 14px;
                min-height: 110px;
                background: #f8fbff;
            }
            .box-title {
                font-size: 12px;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: .08em;
                font-weight: 700;
                margin-bottom: 8px;
            }
            .items-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 8px;
            }
            .items-table thead th {
                background: #0f172a;
                color: #fff;
                padding: 11px 10px;
                text-align: left;
                font-size: 12px;
            }
            .items-table tbody td {
                padding: 11px 10px;
                border-bottom: 1px solid #e5edf5;
                font-size: 12px;
            }
            .text-end { text-align: right; }
            .totals {
                width: 340px;
                margin-left: auto;
                margin-top: 18px;
                border-collapse: collapse;
            }
            .totals td {
                border: 1px solid #dbe4ee;
                padding: 10px 12px;
                font-size: 12px;
            }
            .totals tr:last-child td {
                background: #eff6ff;
                font-weight: 700;
                color: #0f172a;
            }
            .status-line {
                margin-top: 8px;
                font-size: 12px;
                color: #475569;
            }
            .remark {
                margin-top: 16px;
                border: 1px dashed #cbd5e1;
                background: #f8fafc;
                border-radius: 10px;
                padding: 12px;
            }
            .footer {
                padding: 16px 24px 24px;
                text-align: center;
                color: #64748b;
                font-size: 11px;
            }
        </style>
    </head>
    <body>
        <div class="invoice-wrap">
            <div class="topbar">
                <table>
                    <tr>
                        <td style="width:60%;">
                            <div class="brand">Mandal Variety</div>
                            <div class="muted-white">Order Invoice / Bill</div>
                        </td>
                        <td style="width:40%; text-align:right;">
                            <strong>Invoice:</strong> <?= e($order['invoice_no']) ?><br>
                            <strong>Order:</strong> <?= e($order['order_no']) ?><br>
                            <strong>Date:</strong> <?= e($order['order_date']) ?><br>
                            <strong>Status:</strong> <?= e($order['status']) ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <table class="grid">
                    <tr>
                        <td style="width:50%; padding-right:8px;">
                            <div class="box">
                                <div class="box-title">Customer Details</div>
                                <strong><?= e($order['customer_name']) ?></strong><br>
                                <?= e($order['customer_email']) ?><br>
                                <?= e($order['customer_phone']) ?>
                            </div>
                        </td>
                        <td style="width:50%; padding-left:8px;">
                            <div class="box">
                                <div class="box-title">Shipping Address</div>
                                <?= nl2br(e($order['shipping_address'])) ?>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="status-line">
                    <strong>Tracking:</strong> <?= e($order['tracking_status']) ?> |
                    <strong>Payment:</strong> <?= e($order['payment_status']) ?>
                </div>

                <?php if (!empty($order['admin_remark'])): ?>
                    <div class="remark">
                        <strong>Admin Remark:</strong><br>
                        <?= nl2br(e($order['admin_remark'])) ?>
                    </div>
                <?php endif; ?>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width:6%;">#</th>
                            <th>Product</th>
                            <th style="width:14%;">SKU</th>
                            <th style="width:12%;" class="text-end">Price</th>
                            <th style="width:10%;" class="text-end">Qty</th>
                            <th style="width:16%;" class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= e($item['product_name']) ?></td>
                                <td><?= e($item['sku']) ?></td>
                                <td class="text-end">₹<?= number_format($item['price'], 2) ?></td>
                                <td class="text-end"><?= (int)$item['quantity'] ?></td>
                                <td class="text-end">₹<?= number_format($item['subtotal'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <table class="totals">
                    <tr>
                        <td>Items Subtotal</td>
                        <td class="text-end">₹<?= number_format($summary['itemsSubtotal'], 2) ?></td>
                    </tr>
                    <tr>
                        <td>Offer Discount</td>
                        <td class="text-end">- ₹<?= number_format($summary['offerDiscount'], 2) ?></td>
                    </tr>
                    <tr>
                        <td>Delivery Charge</td>
                        <td class="text-end">₹<?= number_format($summary['deliveryCharge'], 2) ?></td>
                    </tr>
                    <tr>
                        <td>Grand Total</td>
                        <td class="text-end">₹<?= number_format($summary['grandTotal'], 2) ?></td>
                    </tr>
                </table>
            </div>

            <div class="footer">
                Thank you for shopping with us.
            </div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) {
    exit('Invalid order ID');
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
    exit('Order not found');
}

$stmtItems = $conn->prepare("
    SELECT 
        oi.*,
        p.name AS product_name,
        p.sku AS product_sku,
        p.price AS product_price,
        p.discount_price AS product_discount_price,
        p.category_id AS product_category_id
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
    ORDER BY oi.id ASC
");
$stmtItems->execute([$orderId]);
$orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

$orderNumber = val($order, ['order_number', 'order_no', 'invoice_number'], 'ORD-' . $orderId);
$invoiceNo = val($order, ['invoice_no'], 'INV-' . str_pad($orderId, 6, '0', STR_PAD_LEFT));

$customerName = val($order, ['customer_name', 'user_name', 'name'], 'Customer');
$customerEmail = val($order, ['customer_email', 'user_email', 'email'], '');
$customerPhone = val($order, ['customer_phone', 'user_phone', 'phone'], '');

$shippingAddress = trim(
    val($order, ['shipping_address', 'address', 'delivery_address'], '') . "\n" .
    val($order, ['shipping_landmark', 'delivery_landmark'], '') . "\n" .
    val($order, ['shipping_city', 'delivery_city', 'city'], '') . ', ' .
    val($order, ['shipping_state', 'delivery_state', 'state'], '') . ' - ' .
    val($order, ['shipping_pincode', 'delivery_pincode', 'pincode', 'zip'], '') . "\n" .
    val($order, ['shipping_country', 'delivery_country', 'country'], 'India')
);

$currentStatus = val($order, ['status'], 'pending');
$currentTrackingStatus = val($order, ['tracking_status'], 'ordered');
$currentPaymentStatus = val($order, ['payment_status'], 'pending');
$currentRemark = val($order, ['admin_remark', 'remark'], '');

$itemsSubtotal = 0;
$invoiceItems = [];
$productIds = [];
$categoryIds = [];

foreach ($orderItems as $item) {
    $qty = (int)val($item, ['quantity', 'qty'], 0);
    $price = (float)val($item, ['price', 'unit_price', 'product_discount_price', 'product_price'], 0);
    $subtotal = $qty * $price;

    $itemsSubtotal += $subtotal;

    if (!empty($item['product_id'])) {
        $productIds[] = (int)$item['product_id'];
    }
    if (!empty($item['product_category_id'])) {
        $categoryIds[] = (int)$item['product_category_id'];
    }

    $invoiceItems[] = [
        'product_name' => val($item, ['product_name'], 'Product'),
        'sku' => val($item, ['product_sku', 'sku'], '-'),
        'price' => $price,
        'quantity' => $qty,
        'subtotal' => $subtotal,
    ];
}

$productIds = array_values(array_unique(array_filter($productIds)));
$categoryIds = array_values(array_unique(array_filter($categoryIds)));

$offerDiscount = (float)val($order, ['discount_amount', 'discount'], 0);

if ($offerDiscount <= 0) {
    $whereParts = [];
    $params = [];

    if (!empty($productIds)) {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $whereParts[] = "product_id IN ($placeholders)";
        $params = array_merge($params, $productIds);
    }

    if (!empty($categoryIds)) {
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $whereParts[] = "category_id IN ($placeholders)";
        $params = array_merge($params, $categoryIds);
    }

    if (!empty($whereParts)) {
        $sqlOffer = "
            SELECT *
            FROM offers
            WHERE (" . implode(' OR ', $whereParts) . ")
            AND (start_date IS NULL OR start_date <= CURDATE())
            AND (end_date IS NULL OR end_date >= CURDATE())
            ORDER BY priority DESC, id DESC
            LIMIT 1
        ";
        $stmtOffer = $conn->prepare($sqlOffer);
        $stmtOffer->execute($params);
        $offer = $stmtOffer->fetch(PDO::FETCH_ASSOC);

        if ($offer) {
            $offerType = val($offer, ['offer_type'], '');
            $offerValue = (float)val($offer, ['offer_value'], 0);

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

$deliveryCharge = (float)val($order, ['delivery_charge', 'shipping_charge'], 0);
$grandTotal = $itemsSubtotal - $offerDiscount + $deliveryCharge;

$invoiceData = [
    'order' => [
        'invoice_no' => $invoiceNo,
        'order_no' => $orderNumber,
        'order_date' => !empty($order['created_at']) ? date('d M Y', strtotime($order['created_at'])) : date('d M Y'),
        'status' => formatStatus($currentStatus),
        'tracking_status' => formatStatus($currentTrackingStatus),
        'payment_status' => formatStatus($currentPaymentStatus),
        'admin_remark' => $currentRemark,
        'customer_name' => $customerName,
        'customer_email' => $customerEmail,
        'customer_phone' => $customerPhone,
        'shipping_address' => $shippingAddress
    ],
    'items' => $invoiceItems,
    'summary' => [
        'itemsSubtotal' => $itemsSubtotal,
        'offerDiscount' => $offerDiscount,
        'deliveryCharge' => $deliveryCharge,
        'grandTotal' => $grandTotal
    ]
];

$html = buildInvoiceHtml($invoiceData);

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$pdfOutput = $dompdf->output();
$fileName = $invoiceNo . '.pdf';

while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($pdfOutput));
header('Cache-Control: private, must-revalidate, post-check=0, pre-check=0, max-age=0');
header('Pragma: public');
header('Expires: 0');

echo $pdfOutput;
exit;