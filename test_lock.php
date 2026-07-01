<?php
require_once 'api/config/database.php';
try {
    $pdo->query("SELECT GET_LOCK('test', 5)")->fetchAll();
    echo 'Lock successful';
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
