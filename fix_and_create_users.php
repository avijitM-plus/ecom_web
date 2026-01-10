<?php
require_once 'config.php';

try {
    // 1. Fix Users Table - Check if password column exists
    $col_check = $pdo->query("SHOW COLUMNS FROM users LIKE 'password'");
    if (!$col_check->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN password VARCHAR(255) NOT NULL AFTER email");
        echo "Added 'password' column to users table.\n";
    } else {
        echo "'password' column already exists.\n";
    }

    // 2. Create Users
    $password = password_hash('password123', PASSWORD_DEFAULT);

    // Admin
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@robomart.com']);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute(['Admin User', 'admin@robomart.com', $password, 'admin']);
        echo "Created admin: admin@robomart.com\n";
    }

    // User
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['user@test.com']);
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute(['Test User', 'user@test.com', $password, 'user']);
        echo "Created user: user@test.com\n";
    }

    echo "Fixes applied successfully.\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
