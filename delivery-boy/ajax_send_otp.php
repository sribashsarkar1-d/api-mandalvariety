<?php
require_once 'includes/config.php';
require_once '../admin/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if (!isset($_SESSION['delivery_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
if ($order_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Order ID']);
    exit;
}

$delivery_id = $_SESSION['delivery_id'];

// Verify this order belongs to this delivery boy and is active
$stmt = $conn->prepare("
    SELECT o.*, u.email as user_email, u.name as user_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.assigned_delivery_id = ?
");
$stmt->execute([$order_id, $delivery_id]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found or not assigned to you']);
    exit;
}

// Generate OTP
$otp = sprintf("%06d", mt_rand(1, 999999));

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

// Save OTP to DB
try {
    $stmt = $conn->prepare("UPDATE orders SET delivery_otp = ? WHERE id = ?");
    $stmt->execute([$otp, $order_id]);
} catch (\Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error saving OTP: ' . $e->getMessage()]);
    exit;
}

$customerEmail = $order['user_email'] ?? $order['customer_email'] ?? $order['email'] ?? '';
$customerName = $order['user_name'] ?? $order['customer_name'] ?? $order['name'] ?? 'Customer';

if (empty($customerEmail)) {
    echo json_encode(['success' => false, 'message' => 'Customer email not found for this order.']);
    exit;
}

$mailBody = "
<div style='font-family:Arial,sans-serif;font-size:16px;color:#333;line-height:1.6;max-width:600px;margin:0 auto;border:1px solid #eee;border-radius:10px;padding:20px;'>
    <div style='text-align:center;margin-bottom:20px;'>
        <h2 style='color:#10b981;margin:0;'>Delivery Verification</h2>
    </div>
    <p>Dear " . htmlspecialchars($customerName) . ",</p>
    <p>Your delivery partner has arrived with your order <strong>#" . htmlspecialchars($order['order_number'] ?? $order['order_no'] ?? $order_id) . "</strong>.</p>
    <p>To securely complete this delivery, please share the following OTP with your delivery partner:</p>
    <div style='text-align:center;margin:30px 0;'>
        <span style='font-size:32px;font-weight:bold;letter-spacing:5px;color:#1d4ed8;background:#eff6ff;padding:15px 30px;border-radius:8px;border:1px dashed #93c5fd;'>
            {$otp}
        </span>
    </div>
    <p>If you did not expect this, please contact support.</p>
    <p style='color:#666;font-size:14px;'>Thank you for shopping with Mandal Variety!</p>
</div>
";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'roy338004@gmail.com';
    $mail->Password   = 'npny pdiu brbj tlly';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom('roy338004@gmail.com', 'Mandal Variety Delivery');
    $mail->addAddress($customerEmail, $customerName);

    $mail->isHTML(true);
    $mail->Subject = 'Your Delivery OTP Code - Mandal Variety';
    $mail->Body    = $mailBody;
    $mail->AltBody = "Your Delivery OTP is: {$otp}";

    $mail->send();

    echo json_encode(['success' => true, 'message' => 'OTP Sent successfully.']);
} catch (Exception $ex) {
    echo json_encode(['success' => false, 'message' => 'Mailer Error: ' . $mail->ErrorInfo]);
}
