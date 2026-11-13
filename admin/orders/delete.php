<?php
require_once __DIR__ . '/../includes/config.php';

$id = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
} else {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
}

if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid order ID provided.';
    header('Location: list.php');
    exit;
}

try {
    // Check if order exists
    $stmtCheck = $conn->prepare("SELECT order_number FROM orders WHERE id = ?");
    $stmtCheck->execute([$id]);
    $order = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $_SESSION['flash_error'] = 'Order not found or already deleted.';
        header('Location: list.php');
        exit;
    }

    $orderNumber = !empty($order['order_number']) ? $order['order_number'] : ('#' . $id);

    $conn->beginTransaction();

    // 1. Delete order items
    $stmtItems = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmtItems->execute([$id]);

    // 2. Delete order
    $stmtOrder = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmtOrder->execute([$id]);

    $conn->commit();

    $_SESSION['flash_success'] = "Order {$orderNumber} deleted successfully.";
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $_SESSION['flash_error'] = 'Failed to delete order: ' . $e->getMessage();
}

header('Location: list.php');
exit;

