<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
/**
 * Utility Functions for RoboMart
 * Authentication and Helper Functions
 */

/**
 * Sanitize user input to prevent XSS attacks
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email address
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * Requirements: At least 8 characters, contains uppercase, lowercase, and number
 */
function validate_password($password) {
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return "Password must be at least " . PASSWORD_MIN_LENGTH . " characters long.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        return "Password must contain at least one lowercase letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        return "Password must contain at least one number.";
    }
    return true;
}

/**
 * Hash password securely using bcrypt
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password against hash
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Create user session after successful login
 */
function create_session($user_id, $email, $full_name, $role = 'user') {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['email'] = $email;
    $_SESSION['full_name'] = $full_name;
    $_SESSION['role'] = $role;
    $_SESSION['logged_in'] = true;
    $_SESSION['last_activity'] = time();
    
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        return false;
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
        destroy_session();
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Destroy user session (logout)
 */
function destroy_session() {
    $_SESSION = array();
    
    // Delete session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

/**
 * Redirect to a specific page
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if user is logged in, redirect to login if not
 */
function require_login() {
    if (!is_logged_in()) {
        redirect(SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

/**
 * Display error message
 */
function display_error($message) {
    return '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>' . htmlspecialchars($message) . '
            </div>';
}

/**
 * Display success message
 */
function display_success($message) {
    return '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4" role="alert">
                <i class="fas fa-check-circle mr-2"></i>' . htmlspecialchars($message) . '
            </div>';
}

/**
 * Get user data by ID
 */
function get_user_by_id($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT id, full_name, email, role, is_active, created_at, phone, address, city, postal_code, country FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Get user data by email
 */
function get_user_by_email($pdo, $email) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

/**
 * Check if current user is an admin
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require admin access - redirect if not admin
 */
function require_admin() {
    if (!is_logged_in()) {
        redirect(SITE_URL . '/login.php?error=Please login to access admin panel');
    }
    if (!is_admin()) {
        redirect(SITE_URL . '/login.php?error=Access denied. Admin privileges required.');
    }
}

/**
 * Get all users with pagination
 */
function get_all_users($pdo, $page = 1, $per_page = 10, $search = '', $role = '', $status = '') {
    $offset = ($page - 1) * $per_page;
    
    $where_clauses = [];
    $params = [];
    
    if (!empty($search)) {
        $where_clauses[] = "(full_name LIKE ? OR email LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    if (!empty($role)) {
        $where_clauses[] = "role = ?";
        $params[] = $role;
    }

    if ($status !== '') {
        $where_clauses[] = "is_active = ?";
        $params[] = $status;
    }
    
    $where = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    
    // Get total count
    $count_sql = "SELECT COUNT(*) FROM users {$where}";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total = $count_stmt->fetchColumn();
    
    // Get users
    $sql = "SELECT id, full_name, email, role, is_active, created_at, updated_at 
            FROM users {$where} 
            ORDER BY created_at DESC 
            LIMIT {$per_page} OFFSET {$offset}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    
    return [
        'users' => $users,
        'total' => $total,
        'pages' => ceil($total / $per_page),
        'current_page' => $page
    ];
}

/**
 * Create a new user
 */
function create_user($pdo, $full_name, $email, $password, $role = 'user') {
    // Check if email already exists
    if (get_user_by_email($pdo, $email)) {
        return ['success' => false, 'message' => 'Email already exists'];
    }
    
    $password_hash = hash_password($password);
    
    $stmt = $pdo->prepare(
        "INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)"
    );
    
    try {
        $stmt->execute([$full_name, $email, $password_hash, $role]);
        return ['success' => true, 'message' => 'User created successfully', 'id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to create user: ' . $e->getMessage()];
    }
}

/**
 * Update user details
 */
function update_user($pdo, $user_id, $full_name, $email, $role, $is_active, $password = null) {
    // Check if email exists for different user
    $existing = get_user_by_email($pdo, $email);
    if ($existing && $existing['id'] != $user_id) {
        return ['success' => false, 'message' => 'Email already in use by another user'];
    }
    
    try {
        if ($password) {
            $password_hash = hash_password($password);
            $stmt = $pdo->prepare(
                "UPDATE users SET full_name = ?, email = ?, password_hash = ?, role = ?, is_active = ? WHERE id = ?"
            );
            $stmt->execute([$full_name, $email, $password_hash, $role, $is_active, $user_id]);
        } else {
            $stmt = $pdo->prepare(
                "UPDATE users SET full_name = ?, email = ?, role = ?, is_active = ? WHERE id = ?"
            );
            $stmt->execute([$full_name, $email, $role, $is_active, $user_id]);
        }
        return ['success' => true, 'message' => 'User updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to update user: ' . $e->getMessage()];
    }
}

/**
 * Update user profile (Self-Service)
 */
function update_profile($pdo, $user_id, $full_name, $email, $password = null) {
    // Check email uniqueness
    $existing = get_user_by_email($pdo, $email);
    if ($existing && $existing['id'] != $user_id) {
        return ['success' => false, 'message' => 'Email already in use'];
    }

    try {
        if ($password) {
            $hash = hash_password($password);
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, password_hash = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $hash, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
            $stmt->execute([$full_name, $email, $user_id]);
        }
        return ['success' => true, 'message' => 'Profile updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Update failed: ' . $e->getMessage()];
    }
}

/**
 * Delete user
 */
function delete_user($pdo, $user_id) {
    // Prevent deleting yourself
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id) {
        return ['success' => false, 'message' => 'You cannot delete your own account'];
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return ['success' => true, 'message' => 'User deleted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to delete user: ' . $e->getMessage()];
    }
}

/**
 * Get dashboard statistics
 */
/**
 * Get dashboard statistics
 */
function get_dashboard_stats($pdo) {
    $stats = [];
    
    // Users
    $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['total_admins'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    $stats['active_users'] = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
    $stats['new_users_month'] = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
    
    // Orders & Sales
    $stats['total_orders'] = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $stats['pending_orders'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
    $stats['total_sales'] = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?: 0;
    
    return $stats;
}

/**
 * Get recent orders
 */
function get_recent_orders($pdo, $limit = 5) {
    $stmt = $pdo->prepare("
        SELECT o.*, u.full_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get recent users
 */
function get_recent_users($pdo, $limit = 5) {
    $stmt = $pdo->prepare(
        "SELECT id, full_name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT ?"
    );
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}
/**
 * Get all products with pagination
 */
/**
 * Get all products with pagination and filters
 */
function get_all_products($pdo, $page = 1, $per_page = 12, $search = '', $category_slug = '', $min_price = 0, $max_price = 0, $status = 'active', $stock_status = '') {
    $offset = ($page - 1) * $per_page;
    $params = [];
    
    $where_clauses = [];
    
    // Status Filter
    if ($status === 'active') {
        $where_clauses[] = "p.is_active = 1";
    } elseif ($status === 'draft') {
        $where_clauses[] = "p.is_active = 0";
    }
    
    // Search
    if ($search) {
        $where_clauses[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $joins = "";
    // Category Filter (Using Pivot Table)
    if ($category_slug) {
        $joins .= " JOIN product_categories pc ON p.id = pc.product_id 
                    JOIN categories c ON pc.category_id = c.id";
        $where_clauses[] = "c.slug = ?";
        $params[] = $category_slug;
    }
    
    // Price Filter
    if ($max_price > 0) {
        $where_clauses[] = "p.price BETWEEN ? AND ?";
        $params[] = $min_price;
        $params[] = $max_price;
    } elseif ($min_price > 0) {
        $where_clauses[] = "p.price >= ?";
        $params[] = $min_price;
    }

    // Stock Filter
    if ($stock_status === 'instock') {
        $where_clauses[] = "p.stock > 0";
    } elseif ($stock_status === 'outofstock') {
        $where_clauses[] = "p.stock = 0";
    } elseif ($stock_status === 'lowstock') {
        $where_clauses[] = "p.stock <= 5 AND p.stock > 0";
    }
    
    $where = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    
    // Count Query
    $count_sql = "SELECT COUNT(DISTINCT p.id) FROM products p $joins $where";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total = $count_stmt->fetchColumn();
    
    // Fetch Query
    $sql = "SELECT DISTINCT p.* FROM products p $joins $where ORDER BY p.created_at DESC LIMIT " . (int)$per_page . " OFFSET " . (int)$offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    return [
        'products' => $products,
        'total_pages' => ceil($total / $per_page),
        'current_page' => $page,
        'total_items' => $total
    ];
}

/**
 * Get product by ID
 */
function get_product_by_id($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Create new product
 */
function create_product($pdo, $name, $description, $price, $stock, $image_url, $is_active, $category = '', $is_featured = 0, $discount_percent = 0) {
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO products (name, description, price, discount_percent, stock, image_url, is_active, category, is_featured) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$name, $description, $price, $discount_percent, $stock, $image_url, $is_active, $category, $is_featured]);
        return ['success' => true, 'message' => 'Product created successfully', 'id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to create product: ' . $e->getMessage()];
    }
}

/**
 * Update product
 */
function update_product($pdo, $id, $name, $description, $price, $stock, $image_url, $is_active, $category = '', $is_featured = 0, $discount_percent = 0) {
    try {
        $stmt = $pdo->prepare(
            "UPDATE products 
             SET name = ?, description = ?, price = ?, discount_percent = ?, stock = ?, image_url = ?, is_active = ?, category = ?, is_featured = ?
             WHERE id = ?"
        );
        $stmt->execute([$name, $description, $price, $discount_percent, $stock, $image_url, $is_active, $category, $is_featured, $id]);
        return ['success' => true, 'message' => 'Product updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to update product: ' . $e->getMessage()];
    }
}

/**
 * Calculate discounted price
 */
function get_discounted_price($price, $discount_percent) {
    if ($discount_percent > 0 && $discount_percent <= 100) {
        return $price * (1 - ($discount_percent / 100));
    }
    return $price;
}

/**
 * Delete product
 */
function delete_product($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return ['success' => true, 'message' => 'Product deleted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to delete product: ' . $e->getMessage()];
    }
}
/**
 * Shopping Cart Functions
 */

// Initialize cart if not exists
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/**
 * Get total items in cart (number of unique products)
 */
function get_cart_item_count() {
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    return count($_SESSION['cart']);
}

/**
 * Get total quantity of all items in cart
 */
function get_cart_total_quantity() {
    if (!isset($_SESSION['cart'])) {
        return 0;
    }
    return array_sum($_SESSION['cart']);
}

/**
 * Get cart total price (with discounts applied)
 */
function get_cart_total($pdo) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    $total = 0;
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product = get_product_by_id($pdo, $product_id);
        if ($product) {
            $discount = isset($product['discount_percent']) ? $product['discount_percent'] : 0;
            $final_price = get_discounted_price($product['price'], $discount);
            $total += $final_price * $quantity;
        }
    }
    
    return $total;
}

function get_cart() {
    return $_SESSION['cart'];
}

function add_to_cart($product_id, $quantity = 1) {
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

function remove_from_cart($product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

function update_cart_quantity($product_id, $quantity) {
    if ($quantity > 0) {
        $_SESSION['cart'][$product_id] = $quantity;
    } else {
        remove_from_cart($product_id);
    }
}

function clear_cart() {
    $_SESSION['cart'] = [];
}

function get_cart_count() {
    return array_sum($_SESSION['cart']);
}

function get_cart_details($pdo) {
    $cart = get_cart();
    $details = ['items' => [], 'subtotal' => 0, 'savings' => 0];
    
    if (empty($cart)) {
        return $details;
    }
    
    $ids = array_keys($cart);
    // Create placeholders
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $product) {
        $qty = $cart[$product['id']];
        $discount = isset($product['discount_percent']) ? $product['discount_percent'] : 0;
        $original_price = $product['price'];
        $final_price = get_discounted_price($original_price, $discount);
        $line_total = $final_price * $qty;
        $original_total = $original_price * $qty;
        
        $product['quantity'] = $qty;
        $product['final_price'] = $final_price;
        $product['line_total'] = $line_total;
        $product['original_line_total'] = $original_total;
        
        $details['items'][] = $product;
        $details['subtotal'] += $line_total;
        $details['savings'] += ($original_total - $line_total);
    }
    
    return $details;
}

/**
 * Get Featured Products
 */
function get_featured_products($pdo, $limit = 4) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE is_featured = 1 ORDER BY RAND() LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get Discounted Products (products with discount_percent > 0)
 */
function get_discounted_products($pdo, $page = 1, $per_page = 12) {
    $offset = ($page - 1) * $per_page;
    
    try {
        // Count total discounted products
        $count_stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE discount_percent > 0 AND is_active = 1");
        $total = $count_stmt->fetchColumn();
        
        // Fetch discounted products
        $stmt = $pdo->prepare("SELECT * FROM products WHERE discount_percent > 0 AND is_active = 1 ORDER BY discount_percent DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, (int)$per_page, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();
        
        return [
            'products' => $products,
            'total_pages' => ceil($total / $per_page),
            'current_page' => $page,
            'total_items' => $total
        ];
    } catch (PDOException $e) {
        return [
            'products' => [],
            'total_pages' => 0,
            'current_page' => 1,
            'total_items' => 0
        ];
    }
}

/**
 * Blog Functions
 */
function get_recent_posts($pdo, $limit = 3) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) { return []; }
}

function get_post_by_slug($pdo, $slug) {
    $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/**
 * Wishlist Functions
 */
function toggle_wishlist($pdo, $user_id, $product_id) {
    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    if ($stmt->fetch()) {
        // Remove
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        return 'removed';
    } else {
        // Add
        $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $product_id]);
        return 'added';
    }
}

function is_in_wishlist($pdo, $user_id, $product_id) {
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    return (bool)$stmt->fetch();
}

function get_user_wishlist($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT p.* 
        FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        WHERE w.user_id = ? 
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}


/**
 * Check if user has purchased and completed an order for a product
 */
function has_purchased_product($pdo, $user_id, $product_id) {
    $stmt = $pdo->prepare("
        SELECT o.id 
        FROM orders o 
        JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? 
        AND oi.product_id = ? 
        AND o.status = 'completed' 
        LIMIT 1
    ");
    $stmt->execute([$user_id, $product_id]);
    return (bool)$stmt->fetch();
}

/**
 * Get total number of times a user has purchased a product (completed orders)
 */
function get_purchase_count($pdo, $user_id, $product_id) {
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(oi.quantity), 0) as total_purchases
        FROM orders o 
        JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? 
        AND oi.product_id = ? 
        AND o.status = 'completed'
    ");
    $stmt->execute([$user_id, $product_id]);
    return (int)$stmt->fetchColumn();
}

/**
 * Get number of reviews a user has submitted for a product
 */
function get_user_review_count($pdo, $user_id, $product_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE product_id = ? AND user_id = ?");
    $stmt->execute([$product_id, $user_id]);
    return (int)$stmt->fetchColumn();
}

/**
 * Check if user can still review a product (reviews < purchases)
 */
function can_review_product($pdo, $user_id, $product_id) {
    $purchases = get_purchase_count($pdo, $user_id, $product_id);
    $reviews = get_user_review_count($pdo, $user_id, $product_id);
    
    return [
        'can_review' => $purchases > $reviews,
        'purchases' => $purchases,
        'reviews' => $reviews,
        'remaining' => max(0, $purchases - $reviews)
    ];
}

/**
 * Ratings & Reviews System
 */
function add_review($pdo, $product_id, $user_id, $rating, $comment) {
    if ($rating < 1 || $rating > 5) return ['success' => false, 'message' => 'Invalid rating'];
    
    // Check if user can review (purchases > reviews submitted)
    $review_status = can_review_product($pdo, $user_id, $product_id);
    
    if ($review_status['purchases'] == 0) {
        return ['success' => false, 'message' => 'You must purchase this product before reviewing'];
    }
    
    if (!$review_status['can_review']) {
        return ['success' => false, 'message' => 'You have already reviewed this product for all your purchases'];
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$product_id, $user_id, $rating, $comment]);
        
        $remaining = $review_status['remaining'] - 1;
        $message = 'Review submitted successfully!';
        if ($remaining > 0) {
            $message .= " You can still leave {$remaining} more review(s).";
        }
        
        return ['success' => true, 'message' => $message];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function get_product_reviews($pdo, $product_id) {
    $stmt = $pdo->prepare("
        SELECT r.*, u.full_name 
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.product_id = ? 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll();
}

function get_avg_rating($pdo, $product_id) {
    $stmt = $pdo->prepare("SELECT AVG(rating) as avg, COUNT(*) as count FROM reviews WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    return [
        'avg' => $res['avg'] ? round($res['avg'], 1) : 0,
        'count' => $res['count']
    ];
}

/**
 * Coupon Functions
 */
function get_all_coupons($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM coupons ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) { return []; }
}

function get_coupon_by_code($pdo, $code) {
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ?");
    $stmt->execute([$code]);
    return $stmt->fetch();
}

function validate_coupon($coupon, $cart_total) {
    if (!$coupon) return ['valid' => false, 'message' => 'Invalid coupon code'];
    if (!$coupon['is_active']) return ['valid' => false, 'message' => 'Coupon is inactive'];
    if ($coupon['expiry_date'] && strtotime($coupon['expiry_date']) < time()) return ['valid' => false, 'message' => 'Coupon expired'];
    if ($coupon['usage_limit'] > 0 && $coupon['used_count'] >= $coupon['usage_limit']) return ['valid' => false, 'message' => 'Coupon usage limit reached'];
    if ($cart_total < $coupon['min_spend']) return ['valid' => false, 'message' => 'Minimum spend of $' . $coupon['min_spend'] . ' required'];
    
    $discount = 0;
    if ($coupon['type'] === 'percent') {
        $discount = $cart_total * ($coupon['value'] / 100);
    } else {
        $discount = $coupon['value'];
    }
    
    return ['valid' => true, 'discount' => min($discount, $cart_total), 'coupon' => $coupon];
}

function create_coupon($pdo, $code, $type, $value, $min_spend, $expiry_date, $usage_limit) {
    // Check duplication
    if (get_coupon_by_code($pdo, $code)) return ['success' => false, 'message' => 'Coupon code exists'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO coupons (code, type, value, min_spend, expiry_date, usage_limit) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$code, $type, $value, $min_spend, $expiry_date ?: null, $usage_limit]);
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function delete_coupon($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->execute([$id]);
        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Update user address details
 */
function update_user_address($pdo, $user_id, $phone, $address, $city, $postal_code, $country) {
    try {
        $stmt = $pdo->prepare(
            "UPDATE users SET phone = ?, address = ?, city = ?, postal_code = ?, country = ? WHERE id = ?"
        );
        $stmt->execute([$phone, $address, $city, $postal_code, $country, $user_id]);
        return ['success' => true, 'message' => 'Address updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to update address: ' . $e->getMessage()];
    }
}



/**
 * System Settings Functions
 */
function get_setting($pdo, $key) {
    static $settings_cache = [];
    
    if (isset($settings_cache[$key])) {
        return $settings_cache[$key];
    }
    
    $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    
    $settings_cache[$key] = $value;
    return $value;
}

function update_setting($pdo, $key, $value, $group = 'general') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO system_settings (setting_key, setting_value, group_name) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = ?, group_name = ?
        ");
        $stmt->execute([$key, $value, $group, $value, $group]);
        return ['success' => true, 'message' => 'Setting updated'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Check User Permission
 */
function check_permission($required_role) {
    if (!is_logged_in()) return false;
    
    $user_role = $_SESSION['role'];
    
    // Admin has access to everything
    if ($user_role === 'admin') return true;
    
    // Define role hierarchy or specific permissions
    $permissions = [
        'editor' => ['blog_management', 'banner_management'],
        'sales_manager' => ['order_management', 'reports_access'],
        'warehouse_manager' => ['inventory_management', 'shipping_management', 'returns_management']
    ];
    
    // For now simple role check
    return $user_role === $required_role;
}

/**
 * Inventory Management Functions
 */
function log_inventory_change($pdo, $product_id, $user_id, $change, $reason) {
    try {
        // Record log
        $stmt = $pdo->prepare("INSERT INTO inventory_logs (product_id, user_id, quantity_change, reason) VALUES (?, ?, ?, ?)");
        $stmt->execute([$product_id, $user_id, $change, $reason]);
        
        // Update Actual Product Stock
        $stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $stmt->execute([$change, $product_id]);
        
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function get_inventory_logs($pdo, $product_id = null, $limit = 20) {
    $sql = "
        SELECT l.*, p.name as product_name, u.full_name as user_name 
        FROM inventory_logs l 
        JOIN products p ON l.product_id = p.id 
        JOIN users u ON l.user_id = u.id 
    ";
    
    $params = [];
    if ($product_id) {
        $sql .= " WHERE l.product_id = ?";
        $params[] = $product_id;
    }
    
    $sql .= " ORDER BY l.created_at DESC LIMIT " . (int)$limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Calculate Shipping Cost
 */
function calculate_shipping_cost($pdo, $country_code, $weight = 0) {
    // 1. Check for specific zone match
    $stmt = $pdo->prepare("SELECT * FROM shipping_zones WHERE countries LIKE ?");
    $stmt->execute(['%"' . $country_code . '"%']);
    $zone = $stmt->fetch();
    
    if (!$zone) {
        // Check for 'Global' zone (empty countries list or explicit global flag if we had one)
        // For now, if no zone, maybe return flat rate from settings
        $flat_rate = get_setting($pdo, 'shipping_flat_rate');
        return $flat_rate ? (float)$flat_rate : 0.00;
    }
    
    // 2. Find matching rate in zone
    // Rates are often weight based. If weight is 0, we might just take the first rate or lowest.
    // Assuming simple flat rate per zone if weight is 0
    $stmt = $pdo->prepare("SELECT cost FROM shipping_rates WHERE zone_id = ? AND (? BETWEEN min_weight AND IFNULL(max_weight, 99999)) LIMIT 1");
    $stmt->execute([$zone['id'], $weight]);
    $rate = $stmt->fetchColumn();
    
    if ($rate !== false) {
        return (float)$rate;
    }
    
    // Fallback if no specific weight matches
    $flat_rate = get_setting($pdo, 'shipping_flat_rate');
    return $flat_rate ? (float)$flat_rate : 0.00;
}

/**
 * Calculate Tax
 */
function calculate_tax($pdo, $amount) {
    if ($amount <= 0) return 0;
    
    $tax_rate = get_setting($pdo, 'tax_rate');
    $tax_rate = $tax_rate ? (float)$tax_rate : 0;
    
    return $amount * ($tax_rate / 100);
}

/**
 * Get Country List
 */
function get_countries() {
    return [
        'BD' => 'Bangladesh',
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'CA' => 'Canada',
        'AU' => 'Australia',
        'IN' => 'India',
        'DE' => 'Germany',
        'FR' => 'France',
        'JP' => 'Japan',
        'CN' => 'China',
        'AE' => 'United Arab Emirates'
    ];
}

/**
 * Get Shipping Zones with Calculated Cost
 */
function get_shipping_zones_with_cost($pdo, $weight) {
    $zones = $pdo->query("SELECT * FROM shipping_zones ORDER BY zone_name")->fetchAll();
    $results = [];
    
    foreach ($zones as $zone) {
        // Find rate for weight
        // Added ORDER BY cost DESC to pick the most specific/highest rate in case of overlapped ranges
        $stmt = $pdo->prepare("SELECT cost FROM shipping_rates WHERE zone_id = ? AND (? BETWEEN min_weight AND IFNULL(max_weight, 99999)) ORDER BY min_weight DESC LIMIT 1");
        $stmt->execute([$zone['id'], $weight]);
        $cost = $stmt->fetchColumn();
        
        // If no rate found for this weight, check if 'flat rate' fallback is desired or skip
        // For now, we only include zones that have a valid rate for this cart
        if ($cost !== false) {
            $zone['cost'] = (float)$cost;
            $results[] = $zone;
        }
    }
    
    // Always add a "Standard" or "Flat Rate" option if defined in settings and no zones matched?
    // User requested "Flat Rate Shipping Cost", so if zones are empty, maybe fallback?
    // But user wants to SELECT zone. So likely zones exist.
    
    return $results;
}

/**
 * Send Order Invoice Email using PHPMailer
 */

function send_order_invoice($pdo, $order_id) {
    // Ensure Composer autoloader is loaded
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    }

    try {
        // 1. Fetch Order Details
        $stmt = $pdo->prepare("
            SELECT o.*, u.full_name, u.email 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
        
        if (!$order) return false;
        
        // 2. Fetch Order Items
        $stmt = $pdo->prepare("
            SELECT oi.*, p.name 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll();
        
        // 3. Construct Email Body
        $subject = "RoboMart Order Invoice #" . str_pad($order_id, 6, '0', STR_PAD_LEFT);
        
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px; }
                .header { background-color: #2563EB; color: #fff; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .order-info { margin: 20px 0; background: #f9f9f9; padding: 15px; border-radius: 5px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
                th { background-color: #f8f8f8; }
                .total-row td { font-weight: bold; font-size: 1.1em; }
                .footer { margin-top: 30px; text-align: center; font-size: 0.9em; color: #777; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Thank You for Your Order!</h1>
                </div>
                
                <p>Hi ' . htmlspecialchars(explode(' ', $order['full_name'])[0]) . ',</p>
                <p>Your order has been confirmed successfully. Here are your order details:</p>
                
                <div class="order-info">
                    <p><strong>Order ID:</strong> #' . str_pad($order_id, 6, '0', STR_PAD_LEFT) . '</p>
                    <p><strong>Order Date:</strong> ' . date('F j, Y, g:i a') . '</p>
                    <p><strong>Shipping Address:</strong><br>' . nl2br(htmlspecialchars($order['shipping_address'])) . '</p>
                    <p><strong>Payment Method:</strong> ' . ucfirst($order['payment_method']) . '</p>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        foreach ($items as $item) {
            $message .= '
            <tr>
                <td>' . htmlspecialchars($item['name']) . '</td>
                <td>' . $item['quantity'] . '</td>
                <td>' . number_format($item['price'], 2) . 'TK</td>
                <td>' . number_format($item['quantity'] * $item['price'], 2) . 'TK</td>
            </tr>';
        }
        
        $message .= '
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align:right"><strong>Subtotal:</strong></td>
                    <td>' . number_format($order['total_amount'] + $order['discount_amount'], 2) . 'TK</td>
                </tr>';
                
        if ($order['discount_amount'] > 0) {
            $message .= '
            <tr>
                <td colspan="3" style="text-align:right; color: green;"><strong>Discount:</strong></td>
                <td style="color: green;">-৳' . number_format($order['discount_amount'], 2) . '</td>
            </tr>';
        }
        
        $message .= '
                <tr class="total-row">
                    <td colspan="3" style="text-align:right">Total:</td>
                    <td style="color: #2563EB;">৳' . number_format($order['total_amount'], 2) . '</td>
                </tr>
            </tfoot>
        </table>
        
        <p style="margin-top: 30px;">We will notify you once your package is shipped.</p>
        
        <div class="footer">
            <p>&copy; ' . date('Y') . ' RoboMart. All rights reserved.</p>
            <p>Questions? Contact us at support@robomart.com</p>
        </div>
            </div>
        </body>
        </html>';
        
        // 4. Send Email via PHPMailer
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = defined('SMTP_USER') ? SMTP_USER : '';
        $mail->Password   = defined('SMTP_PASS') ? SMTP_PASS : '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = defined('SMTP_PORT') ? SMTP_PORT : 587;
        
        // Recipients
        $mail->setFrom(defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'noreply@robomart.com', defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'RoboMart');
        $mail->addAddress($order['email'], $order['full_name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        // Log error silently
        error_log("Failed to send invoice email (PHPMailer): " . $e->getMessage());
        return false;
    }
}

/**
 * Send email verification code to user
 */
function send_verification_email($email, $code, $full_name = 'User') {
    require_once __DIR__ . '/vendor/autoload.php';
    
    try {
        $subject = 'Verify Your RoboMart Account';
        
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
                .container { max-width: 500px; margin: 0 auto; background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { color: #06b6d4; margin: 0; }
                .code-box { background: linear-gradient(135deg, #06b6d4, #8b5cf6); color: #fff; font-size: 32px; letter-spacing: 8px; text-align: center; padding: 20px; border-radius: 10px; margin: 20px 0; font-weight: bold; }
                .footer { text-align: center; margin-top: 30px; color: #888; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🤖 RoboMart</h1>
                </div>
                <p>Hi ' . htmlspecialchars($full_name) . ',</p>
                <p>Thank you for registering! Please use the verification code below to complete your account setup:</p>
                <div class="code-box">' . $code . '</div>
                <p>This code will expire in <strong>15 minutes</strong>.</p>
                <p>If you did not request this, please ignore this email.</p>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' RoboMart. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';
        
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = defined('SMTP_USER') ? SMTP_USER : '';
        $mail->Password   = defined('SMTP_PASS') ? SMTP_PASS : '';
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = defined('SMTP_PORT') ? SMTP_PORT : 587;
        
        $mail->setFrom(defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'noreply@robomart.com', defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'RoboMart');
        $mail->addAddress($email, $full_name);
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = "Your RoboMart verification code is: $code. This code expires in 15 minutes.";
        
        $mail->send();
        return true;
        
    } catch (\Exception $e) {
        error_log("Failed to send verification email: " . $e->getMessage());
        return false;
    }
}

