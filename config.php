<?php
/**
 * Database Configuration File
 * RoboMart E-commerce Platform
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'robomart_db');

// Application configuration
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost:8000');
define('SITE_NAME', 'RoboMart');

// Security settings
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_LIFETIME', 3600); // 1 hour in seconds

// Create database connection using PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    die("Database connection failed. Please contact the administrator.");
}

// Include utility functions
require_once __DIR__ . '/functions.php';

