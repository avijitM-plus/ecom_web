<?php
require 'config.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        type ENUM('percent', 'fixed') NOT NULL DEFAULT 'percent',
        value DECIMAL(10,2) NOT NULL,
        min_spend DECIMAL(10,2) DEFAULT 0,
        expiry_date DATETIME,
        usage_limit INT DEFAULT 0,
        used_count INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_code (code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $pdo->exec($sql);
    echo "Successfully created 'coupons' table.<br>";

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
