<?php
require_once 'config.php';

echo "Setting up categories...\n";

// 1. Create Tables
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            slug VARCHAR(50) NOT NULL UNIQUE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Categories table created.\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS product_categories (
            product_id INT NOT NULL,
            category_id INT NOT NULL,
            PRIMARY KEY (product_id, category_id),
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Product Categories pivot created.\n";
} catch (Exception $e) {
    echo "Table error: " . $e->getMessage() . "\n";
}

// 2. Migrate Data
echo "Migrating data...\n";
$stmt = $pdo->query("SELECT id, category FROM products WHERE category IS NOT NULL AND category != ''");
$products = $stmt->fetchAll();

foreach ($products as $p) {
    $cats = explode(',', $p['category']); // Just in case comma separated, though currently single
    foreach ($cats as $c_name) {
        $c_name = trim($c_name);
        if (empty($c_name)) continue;
        
        // Generate slug
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $c_name));
        
        // Check/Insert Category
        $cat_id = 0;
        $check = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
        $check->execute([$slug]);
        if ($row = $check->fetch()) {
            $cat_id = $row['id'];
        } else {
            $ins = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
            $ins->execute([$c_name, $slug]);
            $cat_id = $pdo->lastInsertId();
            echo "Created Category: $c_name\n";
        }
        
        // Link
        try {
            $link = $pdo->prepare("INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)");
            $link->execute([$p['id'], $cat_id]);
            echo "Linked Product {$p['id']} to Category $cat_id\n";
        } catch (Exception $e) {
            // Likely duplicate
        }
    }
}

echo "Done.\n";
?>
