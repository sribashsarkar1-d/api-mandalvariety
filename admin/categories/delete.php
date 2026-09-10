<?php
include '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'parent';

if ($id > 0 && in_array($type, ['parent', 'child'])) {
    
    if ($type === 'parent') {
        // Prevent deletion if children exist
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM child_categories WHERE parent_category_id = ?");
        $checkStmt->execute([$id]);
        $childCount = $checkStmt->fetchColumn();

        if ($childCount > 0) {
            http_response_code(400);
            echo "This parent category contains child categories. Please remove or move the child categories before deleting.";
            exit;
        }
        
        $table = 'parent_categories';
    } else {
        $table = 'child_categories';
    }

    $stmt = $conn->prepare("SELECT image FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($category) {
        if (!empty($category['image'])) {
            $filePath = __DIR__ . '/../uploads/' . $category['image'];
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        try {
            // NOTE: We do not modify product category relationships as requested.
            $conn->prepare("DELETE FROM $table WHERE id = ?")->execute([$id]);
            
            echo "deleted";
        } catch (Exception $e) {
            http_response_code(500);
            echo "error";
        }
    } else {
        http_response_code(404);
        echo "not found";
    }
} else {
    http_response_code(400);
    echo "invalid parameter";
}
