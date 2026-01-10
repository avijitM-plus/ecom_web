<?php
require_once 'config.php';

$password = password_hash('password123', PASSWORD_DEFAULT);

// Admin
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute(['admin@robomart.com']);
if (!$stmt->fetch()) {
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
    $stmt->execute(['Admin User', 'admin@robomart.com', $password, 'admin']);
    echo "Created admin: admin@robomart.com / password123\n";
} else {
    echo "Admin already exists.\n";
}

// User
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute(['user@test.com']);
if (!$stmt->fetch()) {
    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
    $stmt->execute(['Test User', 'user@test.com', $password, 'user']);
    echo "Created user: user@test.com / password123\n";
} else {
    echo "User already exists.\n";
}
?>
