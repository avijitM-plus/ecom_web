<?php
/**
 * Shopping Cart Page
 */
require_once 'config.php';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    
    if ($action === 'add' && $product_id > 0) {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        add_to_cart($product_id, $quantity);
    } elseif ($action === 'update' && $product_id > 0) {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        update_cart_quantity($product_id, $quantity);
    } elseif ($action === 'remove' && $product_id > 0) {
        remove_from_cart($product_id);
    }
    
    // Redirect to prevent form resubmission
    header('Location: cart.php');
    exit;
}

// Get cart details
$cart_details = get_cart_details($pdo);
$cart_items = $cart_details['items'];
$subtotal = $cart_details['subtotal'];
$discount = 0;
$total = $subtotal;

// Calculate totals with coupon
if (isset($_SESSION['coupon'])) {
    $val = validate_coupon($_SESSION['coupon'], $subtotal);
    if ($val['valid']) {
        $discount = $val['discount'];
        $total = $subtotal - $discount;
    } else {
        unset($_SESSION['coupon']); // Remove invalid coupon
    }
}

// Header handles session
// $is_logged_in = is_logged_in();
// $user_name = $is_logged_in ? $_SESSION['full_name'] : '';
?>
<?php
$page_title = "Cart";
include 'includes/header.php';
?>

    <main class="container mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-6">Your Cart</h1>
        
        <?php if (empty($cart_items)): ?>
            <div class="text-center py-20 bg-white dark:bg-gray-800 rounded-xl shadow">
                <div class="text-6xl text-gray-300 mb-4"><i class="fas fa-shopping-cart"></i></div>
                <h2 class="text-2xl font-bold mb-2">Your cart is empty</h2>
                <p class="text-gray-500 mb-8">Looks like you haven't added anything to your cart yet.</p>
                <a href="products.php" class="bg-electric text-white px-6 py-3 rounded-lg hover:bg-tech transition">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2">
                    <?php foreach ($cart_items as $item): ?>
                    <!-- Cart item -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-4 mb-4 flex items-center gap-4 shadow-sm">
                        <?php if ($item['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-28 h-20 object-cover rounded">
                        <?php else: ?>
                        <div class="w-28 h-20 bg-gray-200 flex items-center justify-center rounded text-gray-400"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                        
                        <div class="flex-1">
                            <h3 class="font-semibold text-lg text-gray-900 dark:text-white">
                                <a href="product-details.php?id=<?php echo $item['id']; ?>" class="hover:underline">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </a>
                            </h3>
                            <form action="cart.php" method="POST" class="mt-2">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <div class="flex items-center">
                                    <label class="text-gray-600 dark:text-gray-400 text-sm mr-2">Qty:</label>
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" 
                                           class="w-16 px-2 py-1 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                           onchange="this.form.submit()">
                                </div>
                            </form>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-gray-900 dark:text-white">$<?php echo number_format($item['line_total'], 2); ?></div>
                            <form action="cart.php" method="POST" class="inline">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm mt-2 transition">Remove</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <aside class="bg-white dark:bg-gray-800 rounded-xl p-6 h-fit shadow-md">
                    <h3 class="font-semibold text-xl mb-4">Order Summary</h3>
                    
                    <!-- Coupon Messages -->
                    <?php if (isset($coupon_error)): ?>
                        <div class="mb-3 text-red-500 text-sm"><?php echo htmlspecialchars($coupon_error); ?></div>
                    <?php endif; ?>
                    <?php if (isset($coupon_success)): ?>
                        <div class="mb-3 text-green-500 text-sm"><?php echo htmlspecialchars($coupon_success); ?></div>
                    <?php endif; ?>

                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                        <span class="font-bold text-gray-900 dark:text-white">$<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <?php if ($discount > 0): ?>
                    <div class="flex justify-between mb-2 text-green-600">
                        <span>Discount (<?php echo $_SESSION['coupon']['code']; ?>)</span>
                        <span>-$<?php echo number_format($discount, 2); ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="flex justify-between mb-4">
                        <span class="text-gray-600 dark:text-gray-400">Shipping</span>
                        <span class="font-bold text-green-500">Free</span>
                    </div>
                    
                    <!-- Coupon Input -->
                    <?php if (!isset($_SESSION['coupon'])): ?>
                    <form action="cart.php" method="POST" class="mb-4">
                        <input type="hidden" name="action" value="apply_coupon">
                        <div class="flex gap-2">
                            <input type="text" name="code" placeholder="Coupon Code" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 text-sm">
                            <button type="submit" class="bg-gray-200 dark:bg-gray-700 px-3 py-2 rounded hover:bg-gray-300 transition text-sm">Apply</button>
                        </div>
                    </form>
                    <?php else: ?>
                    <form action="cart.php" method="POST" class="mb-4">
                        <input type="hidden" name="action" value="remove_coupon">
                        <button type="submit" class="text-red-500 text-sm hover:underline">Remove Coupon</button>
                    </form>
                    <?php endif; ?>
                    
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700 mb-6">
                        <div class="flex justify-between">
                            <span class="font-bold text-lg text-gray-900 dark:text-white">Total</span>
                            <span class="font-bold text-lg text-electric">$<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                    <a href="checkout.php" class="block text-center bg-electric text-white py-3 rounded-lg hover:bg-tech transition font-semibold shadow-lg hover:shadow-xl transform hover:scale-105">
                        Proceed to Checkout
                    </a>
                </aside>
            </div>
        <?php endif; ?>
    </main>
    <?php include 'includes/footer.php'; ?>
