<?php
require_once 'config.php';

if (!is_logged_in()) {
    $_SESSION['redirect_url'] = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    $user_id = $_SESSION['user_id'];
    
    toggle_wishlist($pdo, $user_id, $product_id);
}

// Redirect back
$redirect = $_SERVER['HTTP_REFERER'] ?? 'products.php';
header("Location: $redirect");
exit;
?>
