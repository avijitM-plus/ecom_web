<?php
require_once 'config.php';

try {
    // Add status column
    $pdo->exec("ALTER TABLE blog_posts ADD COLUMN status ENUM('published', 'draft') DEFAULT 'published' AFTER slug");
    echo "Added status column.\n";
} catch (PDOException $e) {
    echo "Status column might already exist: " . $e->getMessage() . "\n";
}

try {
    // Add category column
    $pdo->exec("ALTER TABLE blog_posts ADD COLUMN category VARCHAR(100) AFTER status");
    echo "Added category column.\n";
} catch (PDOException $e) {
    echo "Category column might already exist: " . $e->getMessage() . "\n";
}

try {
    // Add excerpt column
    $pdo->exec("ALTER TABLE blog_posts ADD COLUMN excerpt TEXT AFTER category");
    echo "Added excerpt column.\n";
} catch (PDOException $e) {
    echo "Excerpt column might already exist: " . $e->getMessage() . "\n";
}

echo "Database schema update completed.";
?>
