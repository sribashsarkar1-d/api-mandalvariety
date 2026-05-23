<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$user_id = $_GET['user_id'] ?? null;
$query = "SELECT * FROM age_verifications";
$params = [];

if ($user_id) {
    $query .= " WHERE user_id = ?";
    $params[] = $user_id;
}

$query .= " ORDER BY id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$verifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'data' => $verifications]);
?>
