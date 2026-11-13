<?php
require_once __DIR__ . '/../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_error'] = 'Invalid request method.';
    header('Location: list.php');
    exit;
}

try {
    $conn->beginTransaction();

    // 1. Delete all order items
    $conn->exec("DELETE FROM order_items");

    // 2. Delete all orders
    $conn->exec("DELETE FROM orders");

    // 3. Reset auto-increment counter
    try {
        $conn->exec("ALTER TABLE order_items AUTO_INCREMENT = 1");
        $conn->exec("ALTER TABLE orders AUTO_INCREMENT = 1");
    } catch (Exception $e) {
        // Non-critical if DB user lacks ALTER privilege
    }

    $conn->commit();

    $_SESSION['flash_success'] = 'All orders and order items have been cleared successfully.';
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $_SESSION['flash_error'] = 'Failed to clear orders: ' . $e->getMessage();
}

header('Location: list.php');
exit;

