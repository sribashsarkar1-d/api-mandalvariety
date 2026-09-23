<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/admin/includes/fcm_helper.php';

// Allow OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Fallback to POST variables if not JSON
if (!$input) {
    $input = $_POST;
}

if (empty($input)) {
    echo json_encode(['success' => false, 'message' => 'No data provided. Please send a POST request with JSON body containing token, title, and body.']);
    exit;
}

$token = $input['token'] ?? null;
$title = $input['title'] ?? 'Test Notification';
$body = $input['body'] ?? 'This is a test notification from API.';
$data = $input['data'] ?? ['test_key' => 'test_value'];

if (!$token) {
    echo json_encode(['success' => false, 'message' => 'FCM token is required.']);
    exit;
}

$result = sendFCMNotification($token, $title, $body, null, $data);

// Formatting the response
$decodedResult = is_string($result) ? json_decode($result, true) : $result;

if ($decodedResult && !isset($decodedResult['error'])) {
    echo json_encode([
        'success' => true,
        'message' => 'Notification sent successfully!',
        'fcm_response' => $decodedResult
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send notification.',
        'fcm_response' => $decodedResult
    ]);
}
?>
