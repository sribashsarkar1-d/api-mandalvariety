<?php
header('Content-Type: application/json');
require_once '../config/database.php'; 

try {
    $category_id = $_GET['category_id'] ?? null;
    $where = $category_id ? "WHERE p.category_id = ? AND p.is_active = 1" : "WHERE p.is_active = 1";
    $sql = "SELECT p.id, p.name, p.price, p.images, c.name as category_name 
            FROM products p LEFT JOIN categories c ON p.category_id = c.id $where";
    
    $stmt = $pdo->prepare($sql);
    if ($category_id) $stmt->execute([$category_id]);
    else $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $products]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error']);
}
?>
