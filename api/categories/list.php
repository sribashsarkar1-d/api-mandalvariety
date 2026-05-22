<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$stmt = $pdo->query("SELECT id, name, slug, description, image, is_active FROM categories WHERE is_active = 1 ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'data' => $categories]);
?>
