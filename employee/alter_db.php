<?php
require_once 'config/database.php';

try {
    $pdo->exec("ALTER TABLE employee_users ADD COLUMN reset_otp VARCHAR(10) NULL AFTER password");
    $pdo->exec("ALTER TABLE employee_users ADD COLUMN reset_otp_expiry DATETIME NULL AFTER reset_otp");
    echo "Columns added successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Columns already exist.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
