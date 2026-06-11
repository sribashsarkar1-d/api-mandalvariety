<?php
require_once 'includes/config.php';

try {
    // Modify quantity column to support decimals (e.g., 1.5)
    $sql1 = "ALTER TABLE inventory_purchases MODIFY quantity DECIMAL(10,3) NOT NULL";
    $conn->exec($sql1);
    echo "Modified quantity column successfully.<br>";

    // Add unit column (e.g., kg, pcs)
    $sql2 = "ALTER TABLE inventory_purchases ADD COLUMN unit VARCHAR(20) NOT NULL DEFAULT 'pcs' AFTER quantity";
    $conn->exec($sql2);
    echo "Added unit column successfully.<br>";

    echo "<b>Database upgrade complete! You can now use kg, pcs, etc.</b>";

} catch(PDOException $e) {
    // If column already exists or error occurs
    echo "Update message (might already be applied): " . $e->getMessage();
}
?>
