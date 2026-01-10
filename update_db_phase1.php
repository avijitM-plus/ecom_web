<?php
require_once 'config.php';

try {
    // 1. Create system_settings table
    $sql_settings = "
        CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(50) NOT NULL UNIQUE,
            setting_value TEXT,
            group_name VARCHAR(50) NOT NULL DEFAULT 'general',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql_settings);
    echo "Created system_settings table.\n";

    // Insert default settings
    $default_settings = [
        ['site_name', 'RoboMart', 'general'],
        ['currency_symbol', '$', 'general'],
        ['tax_rate', '0', 'payment'],
        ['shipping_flat_rate', '10.00', 'shipping']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value, group_name) VALUES (?, ?, ?)");
    foreach ($default_settings as $setting) {
        $stmt->execute($setting);
    }
    echo "Inserted default settings.\n";

    // 2. Update users table role ENUM
    // Note: We cannot easily ALTER ENUM in a single portable SQL command without knowing current state,
    // but in MySQL we can redefine the column.
    $sql_alter_role = "
        ALTER TABLE users 
        MODIFY COLUMN role ENUM('user', 'admin', 'editor', 'sales_manager', 'warehouse_manager') NOT NULL DEFAULT 'user';
    ";
    $pdo->exec($sql_alter_role);
    echo "Updated users table role ENUM.\n";
    
    echo "Database schema updated successfully.\n";

} catch (PDOException $e) {
    die("Error updating database: " . $e->getMessage());
}
?>
