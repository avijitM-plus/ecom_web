<?php
require 'config.php';

try {
    // Check if column exists first to avoid error
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'coupon_code'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN coupon_code VARCHAR(50) DEFAULT NULL AFTER total_amount");
        $pdo->exec("ALTER TABLE orders ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00 AFTER coupon_code");
        echo "Orders table updated successfully.";
    } else {
        echo "Columns already exist.";
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
