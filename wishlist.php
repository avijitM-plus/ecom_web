<?php
require_once 'config.php';

if (!is_logged_in()) {
    $_SESSION['redirect_url'] = 'wishlist.php';
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$products = get_user_wishlist($pdo, $user_id);
// $user_name = $_SESSION['full_name'];
?>
<?php
$page_title = "My Wishlist";
include 'includes/header.php';
?>

    <main class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">My Wishlist</h1>

        <?php if (empty($products)): ?>
            <div class="text-center py-20 bg-white dark:bg-gray-800 rounded-xl shadow">
                <div class="text-6xl text-gray-300 mb-4"><i class="far fa-heart"></i></div>
                <h2 class="text-2xl font-bold mb-2">Your wishlist is empty</h2>
                <p class="text-gray-500 mb-6">Save items you love to find them easily later.</p>
                <a href="products.php" class="bg-electric text-white px-6 py-3 rounded-lg hover:bg-tech transition">Explore Products</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($products as $product): ?>
                <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-md hover:shadow-lg transition duration-300">
                    <div class="relative h-56 bg-gray-100 dark:bg-gray-700 overflow-hidden">
                         <?php if ($product['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-400"><i class="fas fa-image text-4xl"></i></div>
                        <?php endif; ?>
                        
                         <!-- Remove form -->
                         <form action="wishlist_action.php" method="POST" class="absolute top-2 right-2">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                             <button type="submit" class="bg-red-500 text-white p-2 rounded-full shadow hover:bg-red-600 transition" title="Remove">
                                <i class="fas fa-times"></i>
                            </button>
                         </form>
                    </div>
                    <div class="p-4">
                        <h3 class="font-semibold text-lg mb-1 truncate">
                            <a href="product-details.php?id=<?php echo $product['id']; ?>" class="hover:text-electric transition">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h3>
                        <div class="flex justify-between items-center mt-3">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">৳<?php echo number_format($product['price'], 2); ?></span>
                            <?php if ($product['stock'] > 0): ?>
                                <form action="cart.php" method="POST" class="inline">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="bg-electric text-white px-4 py-2 rounded-lg text-sm hover:bg-tech transition">
                                        Add to Cart
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    <?php include 'includes/footer.php'; ?>
