<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$query = "SELECT id, title, slug, type, short_description, status, visibility, is_featured, display_order FROM policies";
$params = [];

if (isset($_GET['status'])) {
    $query .= " WHERE status = ?";
    $params[] = $_GET['status'];
}

$query .= " ORDER BY display_order ASC, id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$policies = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'data' => $policies]);
?>
