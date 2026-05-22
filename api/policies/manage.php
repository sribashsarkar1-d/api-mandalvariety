<?php
header('Content-Type: application/json');
require '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$data = json_decode(file_get_contents("php://input"), true);

if ($method === 'POST') {
    $title = trim($data['title'] ?? '');
    $slug = trim($data['slug'] ?? strtolower(str_replace(' ', '-', $title)));
    $type = $data['type'] ?? 'custom';
    $short_description = $data['short_description'] ?? null;
    $content = $data['content'] ?? null;
    $status = $data['status'] ?? 'draft';
    $visibility = $data['visibility'] ?? 'public';
    $is_featured = $data['is_featured'] ?? 0;
    $display_order = $data['display_order'] ?? 0;

    if (empty($title)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Title is required']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO policies (title, slug, type, short_description, content, status, visibility, is_featured, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$title, $slug, $type, $short_description, $content, $status, $visibility, $is_featured, $display_order])) {
        echo json_encode(['success' => true, 'message' => 'Policy created successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create policy']);
    }
} elseif ($method === 'PUT') {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID required']);
        exit;
    }
    $updates = [];
    $params = [];
    foreach (['title', 'slug', 'type', 'short_description', 'content', 'status', 'visibility', 'is_featured', 'display_order'] as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    
    if (empty($updates)) {
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        exit;
    }
    $params[] = $id;
    
    $stmt = $pdo->prepare("UPDATE policies SET " . implode(", ", $updates) . " WHERE id = ?");
    if ($stmt->execute($params)) {
        echo json_encode(['success' => true, 'message' => 'Policy updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update policy']);
    }
} elseif ($method === 'DELETE') {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID required']);
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM policies WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['success' => true, 'message' => 'Policy deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete policy']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
