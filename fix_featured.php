<?php
require_once 'config.php';

echo "Fixing featured products...\n";

// Randomly set 4 products as featured
$pdo->exec("UPDATE products SET is_featured = 1 WHERE id IN (1, 3, 5, 7)"); 
// Or just random if IDs differ:
$pdo->exec("UPDATE products SET is_featured=1 ORDER BY RAND() LIMIT 4");

echo "Fixed. Featured count:\n";
$stmt = $pdo->query("SELECT count(*) FROM products WHERE is_featured=1");
echo $stmt->fetchColumn() . "\n";
?>
