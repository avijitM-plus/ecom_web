<?php
/**
 * Product Management - Delete Product
 * RoboMart E-commerce Platform
 */

require_once '../config.php';

// Check for product ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id) {
    // Delete product
    $result = delete_product($pdo, $id);
    if ($result['success']) {
        header('Location: index.php?success=' . urlencode($result['message']));
    } else {
        header('Location: index.php?error=' . urlencode($result['message']));
    }
} else {
    header('Location: index.php?error=' . urlencode('Invalid product ID'));
}
exit;
?>
