<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$stmt = $pdo->query("SELECT * FROM offers ORDER BY priority DESC, created_at DESC");
$offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'data' => $offers]);
?>
