<?php
require_once 'config.php';

try {
    $pdo->beginTransaction();

    // 1. Update Products Table
    try {
        $pdo->exec("ALTER TABLE products ADD COLUMN category VARCHAR(50)");
    } catch (PDOException $e) { /* Ignore if exists */ }
    
    try {
        $pdo->exec("ALTER TABLE products ADD COLUMN is_featured BOOLEAN DEFAULT 0");
    } catch (PDOException $e) { /* Ignore if exists */ }

    // 2. Create Wishlist Table
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

    // 3. Create Blog Posts Table
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

    // 4. Insert Demo Products
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

    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, image_url, category, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($products as $p) {
        $stmt->execute($p);
    }
    
    // 5. Insert Demo Blog Posts
    // Get Admin ID (Assuming ID 1)
    $admin_id = 1;
    
    $posts = [
        ['Getting Started with Arduino', 'arduino-start', 'Arduino is an open-source electronics platform based on easy-to-use hardware and software...', 'https://images.unsplash.com/photo-1555664424-778a69f45c94?auto=format&fit=crop&w=1000&q=80'],
        ['Top 5 IoT Trends in 2026', 'iot-trends-2026', 'The Internet of Things is rapidly evolving. Here are the top trends...', 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1000&q=80'],
        ['Building Your First Robot', 'first-robot', 'Robotics is fun! Follow this guide to build a simple line-following robot...', 'https://images.unsplash.com/photo-1535378437346-b5e0046c8021?auto=format&fit=crop&w=1000&q=80']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, image_url, author_id) VALUES (?, ?, ?, ?, ?)");
    foreach ($posts as $post) {
        $stmt->execute([...$post, $admin_id]);
    }

    $pdo->commit();
    echo "Database updated and demo data inserted successfully.";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage();
}
?>
