<?php
require_once '../includes/config.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        // Fetch to get image path for deletion
        $stmt = $conn->prepare("SELECT image FROM appbackimage WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Delete file if it's local
            if (!empty($row['image']) && !filter_var($row['image'], FILTER_VALIDATE_URL)) {
                $filePath = __DIR__ . '/../uploads/' . $row['image'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            // Delete from database
            $deleteStmt = $conn->prepare("DELETE FROM appbackimage WHERE id = ?");
            $deleteStmt->execute([$id]);
            echo "Deleted successfully";
        } else {
            http_response_code(404);
            echo "Image not found";
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
} else {
    http_response_code(400);
    echo "Invalid ID";
}
?>
