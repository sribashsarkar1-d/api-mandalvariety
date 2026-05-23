<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// Get ID from ANY source - super flexible
$id = $_GET['id'] ?? $_REQUEST['id'] ?? 1; // Default to 1 for testing

// Basic validation - NO strict checks
if (!is_numeric($id)) {
    echo json_encode(['success' => false, 'message' => 'Bad ID: ' . $id]);
    exit;
}

try {
    // Simple query for your mondal-vr schema
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Fix images JSON
        $product['images'] = json_decode($product['images'] ?? '[]');
        echo json_encode(['success' => true, 'data' => $product]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No product ID: ' . $id]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
