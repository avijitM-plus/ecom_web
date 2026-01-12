<?php
/**
 * Checkout Page
 */
require_once 'config.php';

// Check login
if (!is_logged_in()) {
    $_SESSION['redirect_url'] = 'checkout.php';
    header('Location: login.php');
    exit;
}

// Get cart details
$cart_details = get_cart_details($pdo);
$cart_items = $cart_details['items'];
$subtotal = $cart_details['subtotal'];


// Get user details for auto-fill
$user = get_user_by_id($pdo, $_SESSION['user_id']);

// Calculate Discount
$discount = 0;
$coupon_code = null;
if (isset($_SESSION['coupon'])) {
    $coupon = $_SESSION['coupon'];
    $val = validate_coupon($coupon, $subtotal);
    if ($val['valid']) {
        $discount = $val['discount'];
        $coupon_code = $coupon['code'];
    } else {
        unset($_SESSION['coupon']);
    }
}
// Calculate Shipping Options
$cart_weight = get_cart_total_quantity() * 0.5; // Estimated 0.5kg per item
$shipping_options = get_shipping_zones_with_cost($pdo, $cart_weight);

// Determine selected shipping
$selected_zone_id = $_POST['shipping_zone_id'] ?? ($_SESSION['last_shipping_zone_id'] ?? null);
$shipping_cost = 0;

// Find cost for selected zone
if ($selected_zone_id) {
    foreach ($shipping_options as $option) {
        if ($option['id'] == $selected_zone_id) {
            $shipping_cost = $option['cost'];
            break;
        }
    }
} else {
    // Default to first option if exists
    if (!empty($shipping_options)) {
        $selected_zone_id = $shipping_options[0]['id'];
        $shipping_cost = $shipping_options[0]['cost'];
    }
}

// Calculate Tax
$taxable_amount = $subtotal - $discount;
$tax = calculate_tax($pdo, $taxable_amount);

// Free shipping for orders over ৳2000
$free_shipping_threshold = 2000;
if ($subtotal >= $free_shipping_threshold) {
    $shipping_cost = 0;
}

$total = $subtotal - $discount + $shipping_cost + $tax;

// Redirect if cart is empty
if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

// Handle Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) { // Added check for specific submit button
    $user_id = $_SESSION['user_id'];
    
    // Capture individual fields
    $phone = $_POST['phone'];
    $address_line = $_POST['address'];
    $city = $_POST['city'];
    $postal_code = $_POST['postal_code'];
    $country = $_POST['country'];
    
    // Store preference
    $_SESSION['last_shipping_zone_id'] = $selected_zone_id;
    
    // Re-verify cost (logic already above, $shipping_cost is set from POST)
    
    // Update user's address for next time
    update_user_address($pdo, $user_id, $phone, $address_line, $city, $postal_code, $country);
    
    $address = trim($address_line . ', ' . $city . ', ' . $country . ' ' . $postal_code);
    $payment_method = $_POST['payment_method'] ?? 'card';
    
    try {
        $pdo->beginTransaction();
        
        // 1. Create Order
        // Note: You might want to add a shipping_cost column to orders table later
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, coupon_code, discount_amount, status, shipping_address, payment_method) VALUES (?, ?, ?, ?, 'pending', ?, ?)");
        $stmt->execute([$user_id, $total, $coupon_code, $discount, $address, $payment_method]);
        $order_id = $pdo->lastInsertId();
        
        // 2. Insert Order Items and Update Stock
        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt_stock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
        
        foreach ($cart_items as $item) {
            // Insert item
            $stmt_item->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
            
            // Update stock
            $stmt_stock->execute([$item['quantity'], $item['id'], $item['quantity']]);
            
            if ($stmt_stock->rowCount() == 0) {
                // Rollback if stock insufficient (race condition)
                $pdo->rollBack();
                die("Error: Insufficient stock for " . $item['name']);
            }
        }
        
        // 3. Update Coupon Usage
        if ($coupon_code) {
            $stmt_coupon = $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?");
            $stmt_coupon->execute([$coupon_code]);
            unset($_SESSION['coupon']);
        }
        
        $pdo->commit();
        
        // 4. Send Invoice Email
        send_order_invoice($pdo, $order_id);
        
        clear_cart();
        header('Location: orders.php?success=1');
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Order failed: " . $e->getMessage();
    }
}

// $user_name = $_SESSION['full_name'];
?>
<?php
$page_title = "Checkout";
include 'includes/header.php';
?>

    <main class="container mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-6">Checkout</h1>
        
        <?php if (isset($error)): ?>
            <div class="bg-red-100 text-red-700 p-4 rounded mb-6"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <form method="POST" action="checkout.php" class="md:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-6 shadow-md">
                <h2 class="font-semibold mb-4 text-xl border-b pb-2">Shipping Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm mb-1">Full Name</label>
                        <input type="text" name="full_name" required value="<?php echo htmlspecialchars($user['full_name']); ?>" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Phone Number</label>
                        <input type="text" name="phone" required value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm mb-1">Address</label>
                        <input type="text" name="address" required placeholder="Street address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">City</label>
                        <input type="text" name="city" required value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Postal Code</label>
                        <input type="text" name="postal_code" required value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>" class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm mb-1">Country</label>
                        <select name="country" required class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600">
                            <option value="">Select Country</option>
                            <?php foreach (get_countries() as $code => $name): ?>
                                <option value="<?php echo $code; ?>" <?php echo (isset($user['country']) && $user['country'] == $code) || (isset($_POST['country']) && $_POST['country'] == $code) ? 'selected' : ''; ?>>
                                    <?php echo $name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <h2 class="font-semibold mt-8 mb-4 text-xl border-b pb-2">Shipping Method</h2>
                <div class="space-y-2 mb-6">
                    <?php if (empty($shipping_options)): ?>
                        <div class="p-3 border rounded bg-yellow-50 text-yellow-700">No shipping options available for your cart/location.</div>
                    <?php else: ?>
                        <?php foreach ($shipping_options as $option): ?>
                        <label class="flex items-center space-x-2 p-3 border rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 <?php echo $selected_zone_id == $option['id'] ? 'border-electric bg-blue-50 dark:bg-gray-600' : ''; ?>">
                            <input type="radio" name="shipping_zone_id" value="<?php echo $option['id']; ?>" 
                                   <?php echo $selected_zone_id == $option['id'] ? 'checked' : ''; ?>
                                   onchange="this.form.submit()">
                            <span><?php echo htmlspecialchars($option['zone_name']); ?></span>
                            <span class="ml-auto font-bold">৳<?php echo number_format($option['cost'], 2); ?></span>
                        </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <!-- Hidden input to treat onchange submits differently if needed, or just relying on value persistence -->

                <h2 class="font-semibold mt-8 mb-4 text-xl border-b pb-2">Payment Method</h2>
                <div class="space-y-2 mb-6">
                    <label class="flex items-center space-x-2 p-3 border rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                        <input type="radio" name="payment_method" value="card" checked>
                        <span>Credit/Debit Card</span>
                        <i class="fab fa-cc-visa ml-auto text-xl text-blue-600"></i>
                    </label>
                    <label class="flex items-center space-x-2 p-3 border rounded cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700">
                        <input type="radio" name="payment_method" value="cod">
                        <span>Cash on Delivery</span>
                        <i class="fas fa-money-bill-wave ml-auto text-xl text-green-600"></i>
                    </label>
                </div>

                <h2 class="font-semibold mb-4">Card Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 opacity-50 pointer-events-none">
                     <input type="text" placeholder="Cardholder Name" class="col-span-2 px-3 py-2 border rounded">
                     <input type="text" placeholder="Card Number" class="col-span-2 px-3 py-2 border rounded">
                     <input type="text" placeholder="MM/YY" class="px-3 py-2 border rounded">
                     <input type="text" placeholder="CVC" class="px-3 py-2 border rounded">
                </div>
                <p class="text-xs text-gray-500 mt-2">* Payment integration simulated for demo.</p>

                <button type="submit" name="place_order" class="mt-8 w-full bg-electric text-white px-6 py-4 rounded-lg font-bold hover:bg-tech transition duration-300 shadow-lg">
                    Confirm Order (৳<?php echo number_format($total, 2); ?>)
                </button>
            </form>

            <aside class="bg-white dark:bg-gray-800 rounded-xl p-6 h-fit shadow-md">
                <h3 class="font-semibold text-xl mb-4">Order Summary</h3>
                <div class="space-y-3 mb-6">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">
                            <?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?>
                        </span>
                        <span class="font-medium">৳<?php echo number_format($item['line_total'], 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="border-t pt-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                        <span class="font-bold">৳<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    
                    <?php if ($discount > 0): ?>
                    <div class="flex justify-between mb-2 text-green-600">
                        <span>Discount (<?php echo htmlspecialchars($coupon_code); ?>)</span>
                        <span>-৳<?php echo number_format($discount, 2); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="flex justify-between mb-4">
                        <span class="text-gray-600 dark:text-gray-400">Shipping</span>
                        <span class="font-bold <?php echo $shipping_cost == 0 ? 'text-green-500' : 'text-gray-800 dark:text-gray-200'; ?>">
                            <?php echo $shipping_cost == 0 ? 'Free' : '৳' . number_format($shipping_cost, 2); ?>
                        </span>
                    </div>
                    
                    <div class="flex justify-between mb-4">
                        <span class="text-gray-600 dark:text-gray-400">Tax</span>
                        <span class="font-bold">৳<?php echo number_format($tax, 2); ?></span>
                    </div>
                    <div class="flex justify-between text-lg font-bold">
                        <span>Total</span>
                        <span class="text-electric">৳<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>
            </aside>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
