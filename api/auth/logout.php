<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

session_start();
session_destroy();

// Delete Bearer Token if provided
$headers = getallheaders();
if (isset($headers['Authorization']) && preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
    $auth_token = $matches[1];
    try {
        $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE auth_token = ?");
        $stmt->execute([$auth_token]);
    } catch (Exception $e) {}
}

echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
?>
