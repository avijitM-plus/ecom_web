<?php
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
        redirect('login.php');
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
        redirect('../login.php?error=Please login to access admin panel');
    }
    if (!is_admin()) {
        redirect('../login.php?error=Access denied. Admin privileges required.');
    }
}

/**
 * Get all users with pagination
 */
function get_all_users($pdo, $page = 1, $per_page = 10, $search = '') {
    $offset = ($page - 1) * $per_page;
    
    $where = '';
    $params = [];
    
    if (!empty($search)) {
        $where = "WHERE full_name LIKE ? OR email LIKE ?";
        $search_term = "%{$search}%";
        $params = [$search_term, $search_term];
    }
    
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
function get_all_products($pdo, $page = 1, $per_page = 12, $search = '', $category_slug = '', $min_price = 0, $max_price = 0) {
    $offset = ($page - 1) * $per_page;
    $params = [];
    
    // Base Condition
    $where = "WHERE p.is_active = 1";
    $joins = "";
    
    // Search
    if ($search) {
        $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    // Category Filter (Using Pivot Table)
    if ($category_slug) {
        $joins .= " JOIN product_categories pc ON p.id = pc.product_id 
                    JOIN categories c ON pc.category_id = c.id";
        $where .= " AND c.slug = ?";
        $params[] = $category_slug;
    }
    
    // Price Filter
    if ($max_price > 0) {
        $where .= " AND p.price BETWEEN ? AND ?";
        $params[] = $min_price;
        $params[] = $max_price;
    } elseif ($min_price > 0) {
        $where .= " AND p.price >= ?";
        $params[] = $min_price;
    }
    
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
function create_product($pdo, $name, $description, $price, $stock, $image_url, $is_active, $category = '', $is_featured = 0) {
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO products (name, description, price, stock, image_url, is_active, category, is_featured) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$name, $description, $price, $stock, $image_url, $is_active, $category, $is_featured]);
        return ['success' => true, 'message' => 'Product created successfully', 'id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to create product: ' . $e->getMessage()];
    }
}

/**
 * Update product
 */
function update_product($pdo, $id, $name, $description, $price, $stock, $image_url, $is_active, $category = '', $is_featured = 0) {
    try {
        $stmt = $pdo->prepare(
            "UPDATE products 
             SET name = ?, description = ?, price = ?, stock = ?, image_url = ?, is_active = ?, category = ?, is_featured = ?
             WHERE id = ?"
        );
        $stmt->execute([$name, $description, $price, $stock, $image_url, $is_active, $category, $is_featured, $id]);
        return ['success' => true, 'message' => 'Product updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Failed to update product: ' . $e->getMessage()];
    }
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
 * Get cart total price
 */
function get_cart_total($pdo) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    
    $total = 0;
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product = get_product_by_id($pdo, $product_id);
        if ($product) {
            $total += $product['price'] * $quantity;
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
    $details = ['items' => [], 'subtotal' => 0];
    
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
        $total = $product['price'] * $qty;
        
        $product['quantity'] = $qty;
        $product['line_total'] = $total;
        
        $details['items'][] = $product;
        $details['subtotal'] += $total;
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
 * Blog Functions
 */
function get_recent_posts($pdo, $limit = 3) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT ?");
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
 * Ratings & Reviews System
 */
function add_review($pdo, $product_id, $user_id, $rating, $comment) {
    if ($rating < 1 || $rating > 5) return ['success' => false, 'message' => 'Invalid rating'];
    
    // Check duplication
    $stmt = $pdo->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
    $stmt->execute([$product_id, $user_id]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'You have already reviewed this product'];
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->execute([$product_id, $user_id, $rating, $comment]);
        return ['success' => true, 'message' => 'Review submitted successfully'];
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


