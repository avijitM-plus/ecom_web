<?php
/**
 * Set a test discount on the first product
 */
require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT id, name FROM products LIMIT 1");
    $product = $stmt->fetch();
    
    if ($product) {
        $pdo->exec("UPDATE products SET discount_percent = 20 WHERE id = " . $product['id']);
        echo "Discount set to 20% on product: " . $product['name'] . " (ID: " . $product['id'] . ")\n";
    } else {
        echo "No products found in database.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
