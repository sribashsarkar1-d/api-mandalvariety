<?php
require_once __DIR__ . '/includes/config.php';
checkDeliveryLogin();

$delivery_id = (int)$_SESSION['delivery_id'];
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
          || (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == 1);

try {
    // Get current status
    $stmt = $conn->prepare("SELECT is_available FROM delivery_boys WHERE id = ?");
    $stmt->execute([$delivery_id]);
    $current = (int)$stmt->fetchColumn();

    $new_status = isset($_REQUEST['status']) ? (int)$_REQUEST['status'] : ($current === 1 ? 0 : 1);

    $update = $conn->prepare("UPDATE delivery_boys SET is_available = ? WHERE id = ?");
    $update->execute([$new_status, $delivery_id]);

    $message = $new_status === 1 ? 'You are now Online' : 'You are now Offline';

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'is_available' => $new_status,
            'message' => $message
        ]);
        exit;
    }

    header('Location: index.php');
    exit;

} catch (Exception $e) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update status: ' . $e->getMessage()
        ]);
        exit;
    }

    header('Location: index.php');
    exit;
}
