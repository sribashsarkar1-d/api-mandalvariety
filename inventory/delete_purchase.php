<?php
require_once 'includes/config.php';
if (!isset($_SESSION['inventory_user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $conn->prepare("DELETE FROM inventory_purchases WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: index.php");
exit;
?>
