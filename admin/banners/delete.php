<?php
include '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // 1. Confirm banner exists
    $stmt = $conn->prepare("SELECT image FROM home_banners WHERE id = ?");
    $stmt->execute([$id]);
    $banner = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($banner) {
        $imagePath = $banner['image'];

        try {
            // 2. Safely delete only that banner
            $conn->prepare("DELETE FROM home_banners WHERE id = ?")->execute([$id]);
            
            // 3. Check if image is used by another banner before deleting it from server
            if (!empty($imagePath)) {
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM home_banners WHERE image = ?");
                $checkStmt->execute([$imagePath]);
                $usageCount = $checkStmt->fetchColumn();

                if ($usageCount == 0) {
                    $filePath = __DIR__ . '/../uploads/' . $imagePath;
                    if (is_file($filePath)) {
                        @unlink($filePath);
                    }
                }
            }

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
