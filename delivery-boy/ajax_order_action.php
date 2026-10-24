<?php
require_once 'includes/config.php';

header('Content-Type: application/json');

// Ensure delivery boy is logged in
if (!isset($_SESSION['delivery_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in again.']);
    exit;
}

$delivery_id = (int)$_SESSION['delivery_id'];
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$action = isset($_POST['action']) ? trim($_POST['action']) : '';

if ($order_id <= 0 || empty($action)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid order ID or action.']);
    exit;
}

// Auto-add delivery_boy_status column if needed
try {
    $conn->query("SELECT delivery_boy_status FROM orders LIMIT 1");
} catch (\PDOException $e) {
    try {
        $conn->exec("ALTER TABLE orders ADD COLUMN delivery_boy_status VARCHAR(50) DEFAULT 'assigned'");
    } catch (\PDOException $e2) {}
}

try {
    if ($action === 'accept') {
        // Atomic update: only succeeds if unassigned OR already assigned to this delivery boy, and not completed
        $stmt = $conn->prepare("
            UPDATE orders 
            SET assigned_delivery_id = ?, 
                delivery_boy_status = 'accepted',
                updated_at = NOW()
            WHERE id = ? 
              AND (assigned_delivery_id IS NULL OR assigned_delivery_id = ?) 
              AND status NOT IN ('delivered', 'cancelled', 'returned')
        ");
        $stmt->execute([$delivery_id, $order_id, $delivery_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Order accepted successfully!',
                'order_id' => $order_id
            ]);
        } else {
            // Check why it wasn't updated
            $chk = $conn->prepare("SELECT assigned_delivery_id, status FROM orders WHERE id = ?");
            $chk->execute([$order_id]);
            $existing = $chk->fetch();

            if (!$existing) {
                echo json_encode(['success' => false, 'message' => 'Order not found.']);
            } elseif ($existing['assigned_delivery_id'] && (int)$existing['assigned_delivery_id'] !== $delivery_id) {
                echo json_encode([
                    'success' => false,
                    'code' => 'already_accepted',
                    'message' => 'This order has already been accepted by another delivery partner.'
                ]);
            } elseif (in_array($existing['status'], ['delivered', 'cancelled', 'returned'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'This order is already ' . $existing['status'] . '.'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Order could not be accepted. Please refresh and try again.'
                ]);
            }
        }
        exit;

    } elseif ($action === 'pickup') {
        // Confirm pickup from shop/restaurant
        $stmt = $conn->prepare("
            UPDATE orders 
            SET delivery_boy_status = 'picked_up', 
                status = 'shipped',
                updated_at = NOW()
            WHERE id = ? 
              AND assigned_delivery_id = ? 
              AND delivery_boy_status = 'accepted'
        ");
        $stmt->execute([$order_id, $delivery_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Order marked as picked up!',
                'order_id' => $order_id
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Unable to update status. Please check order details.'
            ]);
        }
        exit;

    } elseif ($action === 'out_for_delivery') {
        // Mark as Out for Delivery
        $stmt = $conn->prepare("
            UPDATE orders 
            SET delivery_boy_status = 'out_for_delivery', 
                status = 'out_for_delivery',
                updated_at = NOW()
            WHERE id = ? 
              AND assigned_delivery_id = ? 
              AND delivery_boy_status = 'picked_up'
        ");
        $stmt->execute([$order_id, $delivery_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Order is now out for delivery!',
                'order_id' => $order_id
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Unable to update to out for delivery.'
            ]);
        }
        exit;

    } elseif ($action === 'confirm_arrival') {
        // Confirm arrived at customer location
        $stmt = $conn->prepare("
            UPDATE orders 
            SET delivery_boy_status = 'arrived',
                updated_at = NOW()
            WHERE id = ? 
              AND assigned_delivery_id = ? 
              AND delivery_boy_status = 'out_for_delivery'
        ");
        $stmt->execute([$order_id, $delivery_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Arrival confirmed at delivery location!',
                'order_id' => $order_id
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Unable to confirm arrival.'
            ]);
        }
        exit;

    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        exit;
    }

} catch (\Exception $ex) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $ex->getMessage()]);
    exit;
}
