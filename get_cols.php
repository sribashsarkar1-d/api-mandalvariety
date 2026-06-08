<?php
require_once __DIR__ . '/admin/includes/config.php';
$stmt = $conn->query("DESCRIBE orders");
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($cols, JSON_PRETTY_PRINT);
