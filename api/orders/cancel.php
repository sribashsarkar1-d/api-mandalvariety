<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/database.php';

$rawInput = file_get_contents('php://input');
if (empty($rawInput) && php_sapi_name() === 'cli') {
    $rawInput = file_get_contents('php://stdin');
}
$input = json_decode($rawInput, true);
if (!is_array($input)) {
    $input = !empty($_POST) ? $_POST : [];
}

$user_id = (int)($input['user_id'] ?? $_GET['user_id'] ?? $_POST['user_id'] ?? 0);
$order_id = (int)($input['order_id'] ?? $_GET['order_id'] ?? $_POST['order_id'] ?? 0);

if (!$order_id) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Order ID is required to cancel an order.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo->beginTransaction();

    // Check order existence and status with lock
    $sql = "SELECT id, status FROM orders WHERE id = ? " . ($user_id > 0 ? "AND user_id = ?" : "") . " FOR UPDATE";
    $stmt = $pdo->prepare($sql);
    $params = $user_id > 0 ? [$order_id, $user_id] : [$order_id];
    $stmt->execute($params);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => "Order #$order_id not found."
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($order['status'] !== 'pending') {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Only pending orders can be cancelled. Current status: ' . $order['status']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Fetch items belonging to this order to restore stock
    $stmtItems = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $stmtItems->execute([$order_id]);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    $restore_by_pid = [];
    foreach ($items as $item) {
        $qty = (int)$item['quantity'];
        $pid = (int)$item['product_id'];
        if ($qty > 0 && $pid > 0) {
            $restore_by_pid[$pid] = ($restore_by_pid[$pid] ?? 0) + $qty;
        }
    }

    if (!empty($restore_by_pid)) {
        $pids = array_keys($restore_by_pid);
        $placeholders = implode(',', array_fill(0, count($pids), '?'));

        $stmtP = $pdo->prepare("SELECT id, stock_quantity, stock FROM products WHERE id IN ($placeholders) FOR UPDATE");
        $stmtP->execute($pids);
        $currentProducts = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        $stmtRestore = $pdo->prepare("
            UPDATE products 
            SET stock_quantity = ?,
                stock = ?
            WHERE id = ?
        ");

        foreach ($currentProducts as $cp) {
            $pid = (int)$cp['id'];
            $addQty = $restore_by_pid[$pid] ?? 0;
            $cur = max(0, max((int)($cp['stock_quantity'] ?? 0), (int)($cp['stock'] ?? 0)));
            $restoredStock = $cur + $addQty;
            $stmtRestore->execute([$restoredStock, $restoredStock, $pid]);
        }
    }

    // Update status to cancelled
    $stmtUpdate = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $stmtUpdate->execute([$order_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => "Order #$order_id cancelled successfully and product stock restored."
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to cancel order: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>