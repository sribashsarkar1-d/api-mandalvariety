<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

function statusBadge($status)
{
    $status = strtolower((string)$status);

    $map = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'processing' => 'primary',
        'packed' => 'secondary',
        'shipped' => 'dark',
        'out_for_delivery' => 'info',
        'delivered' => 'success',
        'cancelled' => 'danger',
        'ordered' => 'secondary',
        'on_the_way' => 'primary',
        'paid' => 'success',
        'failed' => 'danger',
        'refunded' => 'dark'
    ];

    $class = $map[$status] ?? 'light';

    return '<span class="badge text-bg-' . $class . ' px-3 py-2 rounded-pill">' . e(formatStatus($status)) . '</span>';
}

function sendInvoiceMail($toEmail, $toName, $subject, $body, $pdfPath = null)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'roy338004@gmail.com'; // CHANGE
        $mail->Password   = 'npny pdiu brbj tlly'; // CHANGE
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('roy338004@gmail.com', 'Mandal Variety'); // CHANGE
        $mail->addAddress($toEmail, $toName);

        if (!empty($pdfPath) && file_exists($pdfPath)) {
            $mail->addAttachment($pdfPath);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], ["\n", "\n", "\n", "\n"], $body));

        $mail->send();

        return [
            'success' => true,
            'error'   => ''
        ];
    } catch (\Throwable $ex) {
        return [
            'success' => false,
            'error'   => $mail->ErrorInfo
        ];
    }
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
                background: linear-gradient(135deg, #0f172a, #1d4ed8);
                color: #fff;
                padding: 24px;
            }
            .topbar table {
                width: 100%;
                border-collapse: collapse;
            }
            .brand {
                font-size: 26px;
                font-weight: 700;
                letter-spacing: .3px;
            }
            .muted-white {
                color: rgba(255,255,255,.85);
                font-size: 12px;
            }
            .section {
                padding: 20px 24px;
            }
            .grid {
                width: 100%;
                border-collapse: collapse;
            }
            .grid td {
                vertical-align: top;
            }
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
                margin-top: 4px;
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
                        <td style="width: 60%;">
                            <div class="brand">Mandal Variety</div>
                            <div class="muted-white">Order Invoice / Bill</div>
                        </td>
                        <td style="width: 40%; text-align: right;">
                            <strong>Invoice:</strong> <?= e($order['invoice_no']) ?><br>
                            <strong>Order:</strong> <?= e($order['order_no']) ?><br>
                            <strong>Date:</strong> <?= e($order['order_date']) ?><br>
                            <strong>Order Status:</strong> <?= e($order['status']) ?>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <table class="grid">
                    <tr>
                        <td style="width: 50%; padding-right: 8px;">
                            <div class="box">
                                <div class="box-title">Customer Details</div>
                                <strong><?= e($order['customer_name']) ?></strong><br>
                                <?= e($order['customer_email']) ?><br>
                                <?= e($order['customer_phone']) ?>
                            </div>
                        </td>
                        <td style="width: 50%; padding-left: 8px;">
                            <div class="box">
                                <div class="box-title">Shipping Address</div>
                                <?= nl2br(e($order['shipping_address'])) ?>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="status-line">
                    <strong>Tracking Status:</strong> <?= e($order['tracking_status']) ?> |
                    <strong>Payment Status:</strong> <?= e($order['payment_status']) ?>
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
                            <th style="width: 6%;">#</th>
                            <th>Product</th>
                            <th style="width: 14%;">SKU</th>
                            <th style="width: 12%;" class="text-end">Price</th>
                            <th style="width: 10%;" class="text-end">Qty</th>
                            <th style="width: 16%;" class="text-end">Total</th>
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

function generateInvoicePdf($html, $savePath)
{
    $options = new Options();
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    file_put_contents($savePath, $dompdf->output());

    return file_exists($savePath);
}

include '../includes/header.php';
include '../includes/sidebar.php';

$errors = [];
$success = '';

$orderId = (int)($_GET['id'] ?? $_POST['order_id'] ?? 0);
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
$currentAssignedDeliveryId = val($order, ['assigned_delivery_id'], null);

$itemsSubtotal = 0;
$totalQty = 0;
$productIds = [];
$categoryIds = [];
$invoiceItems = [];

foreach ($orderItems as $item) {
    $qty = (int)val($item, ['quantity', 'qty'], 0);
    $price = (float)val($item, ['price', 'unit_price', 'product_discount_price', 'product_price'], 0);
    $subtotal = $price * $qty;

    $itemsSubtotal += $subtotal;
    $totalQty += $qty;

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
        'subtotal' => $subtotal
    ];
}

$productIds = array_values(array_unique(array_filter($productIds)));
$categoryIds = array_values(array_unique(array_filter($categoryIds)));

$offerDiscount = (float)val($order, ['discount_amount', 'discount'], 0);
$offerName = val($order, ['offer_name'], '');
$offerType = val($order, ['offer_type'], '');
$offerValue = (float)val($order, ['offer_value'], 0);

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
            $offerName = val($offer, ['offer_name'], 'Offer');
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

$invoiceHtml = buildInvoiceHtml($invoiceData);
$invoiceDir = __DIR__ . '/../invoices/';
if (!is_dir($invoiceDir)) {
    mkdir($invoiceDir, 0777, true);
}
$invoicePdfPath = $invoiceDir . $invoiceNo . '.pdf';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'download_pdf') {
    if (!generateInvoicePdf($invoiceHtml, $invoicePdfPath) || !file_exists($invoicePdfPath)) {
        die('PDF generation failed');
    }

    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($invoicePdfPath) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($invoicePdfPath));

    readfile($invoicePdfPath);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') !== 'download_pdf') {
    $action = $_POST['action'] ?? 'save';

    $newStatus = trim($_POST['status'] ?? $currentStatus);
    $newTrackingStatus = trim($_POST['tracking_status'] ?? $currentTrackingStatus);
    $newPaymentStatus = trim($_POST['payment_status'] ?? $currentPaymentStatus);
    $remark = trim($_POST['remark'] ?? '');

    if ($newStatus === '') $errors[] = 'Order status required';
    if ($newTrackingStatus === '') $errors[] = 'Tracking status required';
    if ($newPaymentStatus === '') $errors[] = 'Payment status required';

    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            $updateFields = [];
            $updateParams = [];

            if (array_key_exists('status', $order)) {
                $updateFields[] = "status = ?";
                $updateParams[] = $newStatus;
            }
            if (array_key_exists('tracking_status', $order)) {
                $updateFields[] = "tracking_status = ?";
                $updateParams[] = $newTrackingStatus;
            }
            if (array_key_exists('payment_status', $order)) {
                $updateFields[] = "payment_status = ?";
                $updateParams[] = $newPaymentStatus;
            }
            if (array_key_exists('admin_remark', $order)) {
                $updateFields[] = "admin_remark = ?";
                $updateParams[] = $remark;
            } elseif (array_key_exists('remark', $order)) {
                $updateFields[] = "remark = ?";
                $updateParams[] = $remark;
            }

            if (isset($_POST['assigned_delivery_id'])) {
                $assignedDeliveryId = $_POST['assigned_delivery_id'] === '' ? null : (int)$_POST['assigned_delivery_id'];
                $updateFields[] = "assigned_delivery_id = ?";
                $updateParams[] = $assignedDeliveryId;
            }

            if (array_key_exists('invoice_no', $order) && empty($order['invoice_no'])) {
                $updateFields[] = "invoice_no = ?";
                $updateParams[] = $invoiceNo;
            }
            if (array_key_exists('updated_at', $order)) {
                $updateFields[] = "updated_at = NOW()";
            }

            if (!empty($updateFields)) {
                $updateParams[] = $orderId;
                $sqlUpdate = "UPDATE orders SET " . implode(', ', $updateFields) . " WHERE id = ?";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                $conn->exec("SET FOREIGN_KEY_CHECKS=0;");
                $stmtUpdate->execute($updateParams);
                $conn->exec("SET FOREIGN_KEY_CHECKS=1;");
            }

            $currentStatus = $newStatus;
            $currentTrackingStatus = $newTrackingStatus;
            $currentPaymentStatus = $newPaymentStatus;
            $currentRemark = $remark;

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

            $invoiceHtml = buildInvoiceHtml($invoiceData);
            generateInvoicePdf($invoiceHtml, $invoicePdfPath);

            if ($action === 'save') {
                if (!empty($customerEmail)) {
                    $mailBody = "
                        <div style='font-family:Arial,sans-serif;font-size:14px;color:#222;line-height:1.6'>
                            <p>Dear " . e($customerName) . ",</p>
                            <p>Your order <strong>" . e($orderNumber) . "</strong> has been updated.</p>
                            <p><strong>Order Status:</strong> " . e(formatStatus($currentStatus)) . "<br>
                            <strong>Tracking Status:</strong> " . e(formatStatus($currentTrackingStatus)) . "<br>
                            <strong>Payment Status:</strong> " . e(formatStatus($currentPaymentStatus)) . "</p>
                            <p>Your invoice PDF is attached with this email.</p>
                            <p>Thank you for shopping with us.</p>
                        </div>
                    ";

                    $mailResult = sendInvoiceMail(
                        $customerEmail,
                        $customerName,
                        'Invoice for Order ' . $orderNumber,
                        $mailBody,
                        $invoicePdfPath
                    );

                    if (!$mailResult['success']) {
                        $errors[] = 'Email failed to send: ' . $mailResult['error'];
                        $success = 'Status updated and bill generated, but email was not sent.';
                    } else {
                        $success = 'Status updated, bill generated, and email sent successfully.';
                    }
                } else {
                    $success = 'Status updated and bill generated successfully (No email found to send).';
                }
            } else {
                $success = 'Status updated and bill generated successfully.';
            }
            $conn->commit();
        } catch (\Throwable $ex) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $errors[] = $ex->getMessage();
        }
    }
}
?>

<div class="w-100">
    <?php include '../includes/topbar.php'; ?>

    <style>
        .status-page {
            background: #f3f7fc;
            min-height: 100vh;
        }
        .status-page .page-card,
        .status-page .soft-card {
            background: #fff;
            border: 1px solid #e4ebf3;
            border-radius: 20px;
            box-shadow: 0 14px 35px rgba(15, 23, 42, 0.05);
        }
        .status-page .page-card {
            padding: 22px;
        }
        .status-page .soft-card {
            padding: 20px;
        }
        .status-page .heading-mini {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .1em;
            font-weight: 700;
            margin-bottom: 14px;
        }
        .status-page .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            background: #f8fbff;
            border: 1px solid #ebf1f7;
            border-radius: 14px;
            padding: 13px 14px;
            margin-bottom: 10px;
        }
        .status-page .btn-ui {
            border-radius: 12px;
            padding: 10px 16px;
            font-weight: 600;
        }
        .receipt-card {
            background: #fff;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            max-width: 380px;
            margin: 0 auto;
            color: #000;
            font-family: monospace;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .receipt-header h2 {
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 5px;
            color: #000;
            font-family: sans-serif;
        }
        .receipt-header p {
            font-size: 13px;
            margin-bottom: 2px;
            color: #000;
            font-family: sans-serif;
        }
        .receipt-details {
            font-size: 13px;
            margin-bottom: 15px;
            font-family: sans-serif;
        }
        .receipt-details table {
            width: 100%;
        }
        .receipt-details td {
            padding: 2px 0;
            color: #000;
            vertical-align: top;
        }
        .receipt-items {
            width: 100%;
            font-size: 13px;
            margin-bottom: 15px;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            font-family: sans-serif;
        }
        .receipt-items th {
            font-size: 12px;
            padding: 8px 5px;
            text-align: center;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
        }
        .receipt-items th:nth-child(2) { text-align: left; }
        .receipt-items td {
            padding: 8px 5px;
            color: #000;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        .receipt-items td:nth-child(2) { text-align: left; }
        .receipt-totals {
            font-size: 14px;
            font-family: sans-serif;
        }
        .receipt-totals .d-flex {
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }
        .receipt-totals .grand-total-row {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 10px 0;
            margin-top: 10px;
            font-weight: 800;
            font-size: 16px;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 20px;
            font-family: sans-serif;
        }
        .receipt-footer h5 {
            font-weight: 800;
            font-size: 20px;
            margin-bottom: 10px;
        }
        
        @media print {
            @page { 
                margin: 0; 
                size: 80mm auto; 
            }
            body { 
                margin: 0; 
                padding: 0;
                background: white; 
                color: black !important;
            }
            body * {
                visibility: hidden !important;
            }
            #printBillArea, #printBillArea * {
                visibility: visible !important;
            }
            #printBillArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background: #fff;
                margin: 0;
                padding: 4mm;
                border: none;
                box-shadow: none;
                max-width: 100%;
            }
            .receipt-card {
                border: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>

    <div class="container-fluid py-4 status-page">
        <div class="page-card mb-4 no-print">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="mb-1">Update Order Status</h4>
                    <small class="text-muted">Update status, create stylish bill, download PDF, print bill, and share invoice by email.</small>
                </div>
                <a href="view.php?id=<?= (int)$orderId ?>" class="btn btn-outline-secondary btn-ui">← Back to Order</a>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger no-print">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success no-print"><?= e($success) ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4 no-print">
                <div class="soft-card mb-4">
                    <div class="heading-mini">Order Summary</div>
                    <div class="summary-row"><strong>Order No</strong><strong><?= e($orderNumber) ?></strong></div>
                    <div class="summary-row"><strong>Invoice No</strong><strong><?= e($invoiceNo) ?></strong></div>
                    <div class="summary-row"><strong>Customer</strong><strong><?= e($customerName) ?></strong></div>
                    <div class="summary-row"><strong>Email</strong><strong><?= e($customerEmail ?: '-') ?></strong></div>
                    <div class="summary-row"><strong>Phone</strong><strong><?= e($customerPhone ?: '-') ?></strong></div>
                    <div class="summary-row"><strong>Order Status</strong><strong><?= statusBadge($currentStatus) ?></strong></div>
                    <div class="summary-row"><strong>Tracking</strong><strong><?= statusBadge($currentTrackingStatus) ?></strong></div>
                    <div class="summary-row"><strong>Payment</strong><strong><?= statusBadge($currentPaymentStatus) ?></strong></div>
                </div>

                <div class="soft-card">
                    <div class="heading-mini">Bill Totals</div>
                    <div class="summary-row"><strong>Total Qty</strong><strong><?= (int)$totalQty ?></strong></div>
                    <div class="summary-row"><strong>Items Subtotal</strong><strong>₹<?= number_format($itemsSubtotal, 2) ?></strong></div>
                    <div class="summary-row"><strong>Offer</strong><strong><?= e($offerName ?: 'No Offer') ?></strong></div>
                    <div class="summary-row"><strong>Discount</strong><strong class="text-success">- ₹<?= number_format($offerDiscount, 2) ?></strong></div>
                    <div class="summary-row"><strong>Delivery</strong><strong>₹<?= number_format($deliveryCharge, 2) ?></strong></div>
                    <div class="summary-row grand-total"><strong>Grand Total</strong><strong>₹<?= number_format($grandTotal, 2) ?></strong></div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="soft-card mb-4 no-print">
                    <div class="heading-mini">Update Status</div>

                    <form method="POST">
                        <input type="hidden" name="order_id" value="<?= (int)$orderId ?>">

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Order Status</label>
                                <select name="status" class="form-select" required>
                                    <?php
                                    $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'out_for_delivery', 'delivered', 'cancelled'];
                                    foreach ($statuses as $st):
                                    ?>
                                        <option value="<?= e($st) ?>" <?= $currentStatus === $st ? 'selected' : '' ?>>
                                            <?= e(formatStatus($st)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Tracking Status</label>
                                <select name="tracking_status" class="form-select" required>
                                    <?php
                                    $trackingStatuses = ['ordered', 'packed', 'shipped', 'on_the_way', 'out_for_delivery', 'delivered'];
                                    foreach ($trackingStatuses as $st):
                                    ?>
                                        <option value="<?= e($st) ?>" <?= $currentTrackingStatus === $st ? 'selected' : '' ?>>
                                            <?= e(formatStatus($st)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Payment Status</label>
                                <select name="payment_status" class="form-select" required>
                                    <?php
                                    $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];
                                    foreach ($paymentStatuses as $st):
                                    ?>
                                        <option value="<?= e($st) ?>" <?= $currentPaymentStatus === $st ? 'selected' : '' ?>>
                                            <?= e(formatStatus($st)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Assign Delivery</label>
                                <select name="assigned_delivery_id" class="form-select">
                                    <option value="">-- No Delivery Boy --</option>
                                    <?php
                                    $stmtBoys = $conn->query("SELECT id, name FROM delivery_boys WHERE is_active = 1 ORDER BY name ASC");
                                    $allBoys = $stmtBoys->fetchAll();
                                    foreach ($allBoys as $boy):
                                    ?>
                                        <option value="<?= $boy['id'] ?>" <?= $currentAssignedDeliveryId == $boy['id'] ? 'selected' : '' ?>>
                                            <?= e($boy['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Admin Remark</label>
                                <textarea name="remark" rows="4" class="form-control" placeholder="Enter admin note / delivery note / cancellation reason / payment note"><?= e($currentRemark) ?></textarea>
                            </div>

                            <div class="col-12">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" name="action" value="save" class="btn btn-primary btn-ui">Save</button>
                                    <a href="download_invoice.php?id=<?= (int)$orderId ?>" class="btn btn-outline-dark btn-ui">Download PDF</a>
                                    <button type="button" class="btn btn-dark btn-ui" onclick="printBillOnly()">Web Print</button>
                                    <button type="button" class="btn btn-success btn-ui" onclick="printRawBT()"><i class="fa-solid fa-bluetooth me-1"></i> Bluetooth Print (RawBT)</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="printBillArea">
                    <div class="receipt-card">
                        <div class="receipt-header">
                            <h2>Mandal Variety</h2>
                            <p>6JJ5+6M3, Balarampur, Jalpaiguri Division</p>
                            <p>West Bengal, 736134</p>
                            <p><strong>Contact: 8972541454</strong></p>
                        </div>
                        
                        <div class="receipt-details">
                            <table>
                                <tr>
                                    <td style="width: 25%;"><strong>Invoice</strong></td>
                                    <td style="width: 45%;">: <?= e($invoiceNo) ?></td>
                                    <td style="width: 12%;"><strong>Date</strong></td>
                                    <td style="width: 18%;">: <?= !empty($order['created_at']) ? e(date('d M Y', strtotime($order['created_at']))) : e(date('d M Y')) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Order</strong></td>
                                    <td>: <?= e($orderNumber) ?></td>
                                    <td><strong>Time</strong></td>
                                    <td>: <?= !empty($order['created_at']) ? e(date('h:i A', strtotime($order['created_at']))) : e(date('h:i A')) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status</strong></td>
                                    <td colspan="3">: <?= e(formatStatus($currentStatus)) ?></td>
                                </tr>
                            </table>
                        </div>

                        <div class="receipt-details border-bottom-0 pb-0">
                            <p class="mb-1" style="text-transform: uppercase;"><strong>CUSTOMER DETAILS</strong></p>
                            <table>
                                <tr>
                                    <td style="width: 25%;"><strong>Name</strong></td>
                                    <td style="width: 75%;">: <?= e($customerName) ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Contact</strong></td>
                                    <td>: <?= e($customerPhone ?: '-') ?></td>
                                </tr>
                            </table>
                        </div>

                        <div class="receipt-details border-bottom-0">
                            <p class="mb-1" style="text-transform: uppercase;"><strong>CUSTOMER ADDRESS</strong></p>
                            <p class="mb-0" style="line-height: 1.4;"><?= nl2br(e($shippingAddress ?: '-')) ?></p>
                        </div>

                        <table class="receipt-items" cellpadding="0" cellspacing="0">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">SL</th>
                                    <th style="width: 45%;">PRODUCT</th>
                                    <th style="width: 10%;">QTY</th>
                                    <th style="width: 15%;">PRICE</th>
                                    <th style="width: 20%; text-align: right;">SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($invoiceItems)): ?>
                                    <?php foreach ($invoiceItems as $index => $item): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><?= e($item['product_name']) ?></td>
                                            <td><?= (int)$item['quantity'] ?></td>
                                            <td>₹<?= number_format($item['price'], 2) ?></td>
                                            <td style="text-align: right;">₹<?= number_format($item['subtotal'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <div class="receipt-totals">
                            <div class="d-flex">
                                <strong>Items Subtotal</strong>
                                <strong>₹<?= number_format($itemsSubtotal, 2) ?></strong>
                            </div>
                            <?php if ($offerName): ?>
                            <div class="d-flex">
                                <strong>Offer Name</strong>
                                <span><?= e($offerName) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if ($offerDiscount > 0): ?>
                            <div class="d-flex">
                                <strong>Discount</strong>
                                <span>- ₹<?= number_format($offerDiscount, 2) ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex">
                                <strong>Delivery Charge</strong>
                                <span>₹<?= number_format($deliveryCharge, 2) ?></span>
                            </div>
                            
                            <div class="d-flex grand-total-row">
                                <span>Grand Total</span>
                                <span>₹<?= number_format($grandTotal, 2) ?></span>
                            </div>
                        </div>

                        <div class="receipt-footer">
                            <p style="font-family: 'Brush Script MT', cursive; font-size: 22px; margin-bottom: 5px;">Thank you for shopping with</p>
                            <h5>Mandal Variety</h5>
                            <p style="letter-spacing: 2px; text-transform: uppercase; font-size: 11px;">Visit Again !</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function printBillOnly() {
    window.print();
}

function printRawBT() {
    var printContent = document.getElementById('printBillArea').innerHTML;
    
    // Create a complete HTML document for RawBT to parse correctly
    var fullHtml = `
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body { margin: 0; padding: 2mm; font-family: monospace; color: #000; background: #fff; font-size: 12px; }
            .receipt-card { width: 100%; }
            .receipt-header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
            .receipt-header h2 { font-size: 22px; margin: 0 0 5px 0; font-family: sans-serif; }
            .receipt-header p { font-size: 12px; margin: 0 0 2px 0; }
            .receipt-details { margin-bottom: 10px; font-size: 12px; }
            .receipt-details table { width: 100%; }
            .receipt-details td { padding: 2px 0; vertical-align: top; }
            .receipt-items { width: 100%; font-size: 12px; margin-bottom: 10px; border-top: 1px dashed #000; border-bottom: 1px dashed #000; border-collapse: collapse; }
            .receipt-items th { font-size: 11px; padding: 5px 2px; text-align: center; border-bottom: 1px solid #000; text-transform: uppercase; }
            .receipt-items th:nth-child(2) { text-align: left; }
            .receipt-items td { padding: 5px 2px; text-align: center; border-bottom: 1px solid #eee; }
            .receipt-items td:nth-child(2) { text-align: left; }
            .receipt-totals { font-size: 12px; }
            .receipt-totals .d-flex { display: flex; justify-content: space-between; margin-bottom: 5px; }
            .receipt-totals .grand-total-row { border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 8px 0; margin-top: 8px; font-weight: bold; font-size: 14px; }
            .receipt-footer { text-align: center; margin-top: 15px; }
            .receipt-footer h5 { font-size: 18px; margin: 5px 0 10px 0; }
            .pb-0 { padding-bottom: 0 !important; }
            .border-bottom-0 { border-bottom: 0 !important; }
            .mb-0 { margin-bottom: 0 !important; }
            .mb-1 { margin-bottom: 5px !important; }
            strong { font-weight: bold; }
        </style>
    </head>
    <body>
        ${printContent}
    </body>
    </html>
    `;

    // Convert to base64 for data URI (handles unicode properly)
    var b64 = btoa(unescape(encodeURIComponent(fullHtml)));
    var rawBtUrl = "rawbt:data:text/html;base64," + b64;
    window.location.href = rawBtUrl;
}
</script>

<?php include '../includes/footer.php'; ?>