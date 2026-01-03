<?php
/**
 * Toggle Product Featured Status
 */
require_once '../config.php';

// Check if admin
require_admin();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Get current status
    $product = get_product_by_id($pdo, $id);
    
    if ($product) {
        $new_status = $product['is_featured'] ? 0 : 1;
        
        try {
            $stmt = $pdo->prepare("UPDATE products SET is_featured = ? WHERE id = ?");
            $stmt->execute([$new_status, $id]);
            
            $msg = $new_status ? "Product marked as featured" : "Product removed from featured";
            redirect('index.php?success=' . urlencode($msg));
        } catch (PDOException $e) {
            redirect('index.php?error=' . urlencode("Database error: " . $e->getMessage()));
        }
    } else {
        redirect('index.php?error=' . urlencode("Product not found"));
    }
} else {
    redirect('index.php?error=' . urlencode("Invalid product ID"));
}
