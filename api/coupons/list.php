<?php
header('Content-Type: application/json');
require '../config/database.php';

$stmt = $pdo->query("SELECT * FROM coupons ORDER BY id DESC");
$coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'data' => $coupons]);
?>
