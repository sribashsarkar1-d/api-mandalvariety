<?php
require_once __DIR__ . '/config/database.php';

try {
    // 1. Fix order status enum
    $sql1 = "ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'processing', 'shipped', 'out_for_delivery', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending'";
    $pdo->exec($sql1);
    echo "✅ Successfully updated 'status' column!<br>";

    // 2. Fix tracking status enum (if it exists)
    try {
        $sql2 = "ALTER TABLE orders MODIFY COLUMN tracking_status ENUM('ordered', 'packed', 'shipped', 'on_the_way', 'out_for_delivery', 'delivered') NOT NULL DEFAULT 'ordered'";
        $pdo->exec($sql2);
        echo "✅ Successfully updated 'tracking_status' column!<br>";
    } catch (Exception $e) {
        echo "⚠️ Could not update tracking_status (it might not exist): " . $e->getMessage() . "<br>";
    }

    // 3. Fix payment status enum
    try {
        $sql3 = "ALTER TABLE orders MODIFY COLUMN payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending'";
        $pdo->exec($sql3);
        echo "✅ Successfully updated 'payment_status' column!<br>";
    } catch (Exception $e) {
        echo "⚠️ Could not update payment_status (it might not exist): " . $e->getMessage() . "<br>";
    }

    echo "<br><b>🎉 ALL FIXED! You can now delete this file and use your Admin Panel normally.</b>";

} catch (Exception $e) {
    echo "❌ Error fixing database: " . $e->getMessage();
}
?>
