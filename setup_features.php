<?php
require_once 'config.php';

echo "Setting up features...\n";

// 1. Add Columns (Ignore if exists via try-catch)
try {
    $pdo->exec("ALTER TABLE products ADD COLUMN category VARCHAR(50)");
    echo "Added category column.\n";
} catch (Exception $e) { echo "Category column likely exists.\n"; }

try {
    $pdo->exec("ALTER TABLE products ADD COLUMN is_featured BOOLEAN DEFAULT 0");
    echo "Added is_featured column.\n";
} catch (Exception $e) { echo "is_featured column likely exists.\n"; }

// 2. Create Tables
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS wishlist (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_wishlist (user_id, product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Wishlist table checked.\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS blog_posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            content TEXT NOT NULL,
            image_url VARCHAR(255),
            author_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Blog Posts table checked.\n";
} catch (Exception $e) { echo "Table creation error: " . $e->getMessage() . "\n"; }

// 3. Demo Data
// Products
$products = [
    ['Arduino Uno R3', 'Microcontroller board based on the ATmega328P.', 25.00, 50, 'https://m.media-amazon.com/images/I/515M1M5kCgL.jpg', 'Microcontrollers', 1],
    ['Raspberry Pi 4 Model B', 'High-performance single-board computer.', 55.00, 30, 'https://m.media-amazon.com/images/I/51I3UjD-Q1L._SX522_.jpg', 'Microcontrollers', 1],
    ['SG90 Micro Servo', 'Tiny and lightweight with high output power.', 2.50, 200, 'https://m.media-amazon.com/images/I/51v+v-qIqkL._AC_SX679_.jpg', 'Robotics', 0],
    ['HC-SR04 Ultrasonic Sensor', 'Provides 2cm - 400cm non-contact measurement function.', 3.00, 150, 'https://m.media-amazon.com/images/I/61s7s+7x+ZL._AC_SX679_.jpg', 'Sensors', 0],
    ['ESP8266 WiFi Module', 'Low cost Wi-Fi microchip.', 5.00, 100, 'https://m.media-amazon.com/images/I/61X-2y2l9+L._AC_SX679_.jpg', 'IoT Devices', 0],
    ['L298N Motor Driver', 'Dual H-Bridge motor driver module.', 4.50, 80, 'https://m.media-amazon.com/images/I/71X8g+M+6+L._AC_SX679_.jpg', 'Robotics', 0],
    ['Jumper Wires 120pcs', 'Multicolored dupont wire 40pin M-M M-F F-F.', 7.00, 100, 'https://m.media-amazon.com/images/I/61A8-x+G+L._AC_SX679_.jpg', 'Components', 1],
    ['Robot Car Kit', 'Smart Robot Car Kit for Arduino with Tutorial.', 45.00, 20, 'https://m.media-amazon.com/images/I/71v+v-qIqkL._AC_SX679_.jpg', 'Kits & Tools', 1],
    ['DHT11 Temp Sensor', 'Digital temperature and humidity sensor.', 3.50, 120, 'https://m.media-amazon.com/images/I/61s7s+7x+ZL._AC_SX679_.jpg', 'Sensors', 0],
    ['OLED Display 0.96"', '128x64 Pixel I2C OLED Display Module.', 6.00, 60, 'https://m.media-amazon.com/images/I/61X-2y2l9+L._AC_SX679_.jpg', 'Components', 0]
];

echo "Inserting products...\n";
$stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, image_url, category, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
foreach ($products as $p) {
    try {
        // Check if name exists to avoid dupes (rough check)
        $chk = $pdo->prepare("SELECT id FROM products WHERE name = ?");
        $chk->execute([$p[0]]);
        if (!$chk->fetch()) {
            $stmt->execute($p);
            echo "Inserted: {$p[0]}\n";
        }
    } catch (Exception $e) { echo "Failed to insert {$p[0]}: " . $e->getMessage() . "\n"; }
}

// Blog Posts
$admin_id = 1; // Ensure this user exists
$posts = [
    ['Getting Started with Arduino', 'arduino-start', 'Arduino is an open-source electronics platform based on easy-to-use hardware and software. Use it to build interactive projects...', 'https://images.unsplash.com/photo-1555664424-778a69f45c94?auto=format&fit=crop&w=1000&q=80'],
    ['Top 5 IoT Trends in 2026', 'iot-trends-2026', 'The Internet of Things is rapidly evolving. Here are the top trends defining the connected world this year...', 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1000&q=80'],
    ['Building Your First Robot', 'first-robot', 'Robotics is fun! Follow this guide to build a simple line-following robot using basic components...', 'https://images.unsplash.com/photo-1535378437346-b5e0046c8021?auto=format&fit=crop&w=1000&q=80']
];

echo "Inserting posts...\n";
$stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, image_url, author_id) VALUES (?, ?, ?, ?, ?)");
foreach ($posts as $post) {
    try {
        $stmt->execute([...$post, $admin_id]);
        echo "Inserted post: {$post[0]}\n";
    } catch (Exception $e) { echo "Failed to insert post {$post[0]} (might exist)\n"; }
}

echo "Done.\n";
?>
