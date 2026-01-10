<?php
/**
 * User Orders Page
 */
require_once 'config.php';

// Check login
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
// $user_name = $_SESSION['full_name'];

// Fetch Orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<?php
$page_title = "My Orders";
include 'includes/header.php';
?>

    <main class="container mx-auto px-4 py-12">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">My Orders</h1>
            <?php if (isset($_GET['success'])): ?>
                <div class="text-green-500 font-semibold"><i class="fas fa-check-circle"></i> Order placed successfully!</div>
            <?php endif; ?>
        </div>
        
        <?php if (empty($orders)): ?>
            <div class="text-center py-20 bg-white dark:bg-gray-800 rounded-xl shadow">
                <h2 class="text-2xl font-bold mb-2">No orders yet</h2>
                <p class="text-gray-500 mb-8">Start shopping to see your orders here.</p>
                <a href="products.php" class="bg-electric text-white px-6 py-3 rounded-lg hover:bg-tech transition">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($orders as $order): ?>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm hover:shadow-md transition">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-4">
                        <div>
                            <h3 class="font-bold text-lg">Order #<?php echo $order['id']; ?></h3>
                            <p class="text-sm text-gray-500">Placed on: <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div class="text-right mt-2 md:mt-0">
                            <span class="block font-bold text-lg">৳<?php echo number_format($order['total_amount'], 2); ?></span>
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold 
                                <?php 
                                    echo match($order['status']) {
                                        'completed' => 'bg-green-100 text-green-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        default => 'bg-blue-100 text-blue-800'
                                    };
                                ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="border-t pt-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            <strong>Shipping to:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?>
                        </div>
                        
                        <!-- Order Items Preview -->
                         <?php
                            $stmt_items = $pdo->prepare("SELECT p.name, oi.quantity, oi.price FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                            $stmt_items->execute([$order['id']]);
                            $items = $stmt_items->fetchAll();
                        ?>
                        <div class="space-y-1">
                            <?php foreach ($items as $item): ?>
                                <div class="flex justify-between text-sm">
                                    <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                                    <span>৳<?php echo number_format($item['price'], 2); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    <?php include 'includes/footer.php'; ?>
