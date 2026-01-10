<?php
require_once 'config.php';

try {
    // 1. Inventory Logs
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS inventory_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            user_id INT NOT NULL,
            quantity_change INT NOT NULL,
            reason VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Created inventory_logs table.\n";

    // 2. Shipping Zones
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS shipping_zones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            zone_name VARCHAR(100) NOT NULL,
            countries TEXT,
            is_active TINYINT(1) DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Created shipping_zones table.\n";

    // 3. Shipping Rates
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS shipping_rates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            zone_id INT NOT NULL,
            min_weight DECIMAL(10,2) DEFAULT 0,
            max_weight DECIMAL(10,2),
            cost DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (zone_id) REFERENCES shipping_zones(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Created shipping_rates table.\n";

    // 4. Returns (RMA)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS returns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            reason TEXT NOT NULL,
            status ENUM('pending', 'approved', 'rejected', 'refunded') DEFAULT 'pending',
            admin_notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (product_id) REFERENCES products(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Created returns table.\n";

    // 5. Update Products table for low stock alert
    // Check if column exists first to avoid error
    $col_check = $pdo->query("SHOW COLUMNS FROM products LIKE 'low_stock_threshold'");
    if (!$col_check->fetch()) {
        $pdo->exec("ALTER TABLE products ADD COLUMN low_stock_threshold INT DEFAULT 5 AFTER stock");
        echo "Added low_stock_threshold to products table.\n";
    }

    echo "Phase 2 database updates completed successfully.\n";

} catch (PDOException $e) {
    die("Error updating database: " . $e->getMessage());
}
?>
