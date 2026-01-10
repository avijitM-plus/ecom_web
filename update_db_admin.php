<?php
require_once 'config.php';

echo "<h2>Updating Database for Admin Modules...</h2>";

try {
    // 1. Add low_stock_threshold to products table
    echo "Checking products table for low_stock_threshold column...<br>";
    try {
        $pdo->query("SELECT low_stock_threshold FROM products LIMIT 1");
        echo " - Column already exists.<br>";
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE products ADD COLUMN low_stock_threshold INT DEFAULT 10 AFTER stock");
        echo " - Column added.<br>";
    }

    // 2. Create Inventory Logs Table
    echo "Creating inventory_logs table...<br>";
    $sql = "CREATE TABLE IF NOT EXISTS inventory_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        user_id INT NOT NULL,
        quantity_change INT NOT NULL,
        reason VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);
    echo " - Done.<br>";

    // 3. Create Shipping Zones Table
    echo "Creating shipping_zones table...<br>";
    $sql = "CREATE TABLE IF NOT EXISTS shipping_zones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        zone_name VARCHAR(100) NOT NULL,
        countries TEXT, 
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);
    echo " - Done.<br>";

    // 4. Create Shipping Rates Table
    echo "Creating shipping_rates table...<br>";
    $sql = "CREATE TABLE IF NOT EXISTS shipping_rates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        zone_id INT NOT NULL,
        min_weight DECIMAL(10,2) DEFAULT 0,
        max_weight DECIMAL(10,2) NULL,
        cost DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (zone_id) REFERENCES shipping_zones(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);
    echo " - Done.<br>";

    // 5. Create Returns Table
    echo "Creating returns table...<br>";
    $sql = "CREATE TABLE IF NOT EXISTS returns (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        user_id INT NOT NULL,
        product_id INT NOT NULL,
        reason VARCHAR(255) NOT NULL,
        status ENUM('pending', 'approved', 'rejected', 'refunded') DEFAULT 'pending',
        admin_notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (product_id) REFERENCES products(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);
    echo " - Done.<br>";

    // 6. Create System Settings Table
    echo "Creating system_settings table...<br>";
    $sql = "CREATE TABLE IF NOT EXISTS system_settings (
        setting_key VARCHAR(100) PRIMARY KEY,
        setting_value TEXT,
        group_name VARCHAR(50) DEFAULT 'general',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);
    echo " - Done.<br>";

    echo "<h3>All Updates Completed Successfully!</h3>";
    echo "<p><a href='index.php'>Go to Home</a></p>";

} catch (PDOException $e) {
    echo "<div style='color:red'><h3>Error: " . $e->getMessage() . "</h3></div>";
}
?>
