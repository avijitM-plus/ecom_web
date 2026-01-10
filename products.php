<?php
/**
 * All Products Page
 */
require_once 'config.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : '';
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : '';
$discount_filter = isset($_GET['discount']) && $_GET['discount'] == '1';
$per_page = 12;

// If discount filter is enabled, fetch only discounted products
if ($discount_filter) {
    $result = get_discounted_products($pdo, $page, $per_page);
} else {
    $result = get_all_products($pdo, $page, $per_page, $search, $category, $min_price, $max_price);
}
$products = $result['products'];
$total_pages = $result['total_pages'];
$current_page = $result['current_page'];

// Header handles session & cart
// $is_logged_in = is_logged_in();
// $user_name = $is_logged_in ? $_SESSION['full_name'] : '';
// $cart_count = get_cart_total_quantity();

// Fetch Categories for Sidebar
$stmt_cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$sidebar_categories = $stmt_cats->fetchAll();
?>
<?php
$page_title = $discount_filter ? "Flash Sale - Discounted Products" : "All Products";
include 'includes/header.php';
?>

    <main class="container mx-auto px-4 py-8">
        <?php if ($discount_filter): ?>
        <!-- Flash Sale Banner -->
        <div class="bg-gradient-to-r from-red-500 to-orange-500 text-white rounded-xl p-6 mb-8 shadow-lg">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="text-4xl animate-pulse"><i class="fas fa-bolt"></i></div>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold">⚡ Flash Sale!</h1>
                        <p class="opacity-90">Grab these amazing discounts before they're gone!</p>
                    </div>
                </div>
                <a href="products.php" class="bg-white text-red-500 px-6 py-2 rounded-lg font-semibold hover:bg-gray-100 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to All Products
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar Filters -->
            <aside class="w-full md:w-1/4 h-fit">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm">
                    <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-white"><i class="fas fa-filter mr-2"></i>Filters</h3>
                    
                    <!-- Categories -->
                    <div class="mb-6">
                        <h4 class="font-semibold mb-3 text-electric">Categories</h4>
                        <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                             <li>
                                <a href="products.php" class="<?php echo !isset($_GET['category']) ? 'text-electric font-bold' : 'hover:text-electric transition'; ?>">All Categories</a>
                            </li>
                            <?php foreach ($sidebar_categories as $cat): 
                                $is_active = isset($_GET['category']) && $_GET['category'] === $cat['slug'];
                            ?>
                            <li>
                                <a href="products.php?category=<?php echo htmlspecialchars($cat['slug']); ?>" class="<?php echo $is_active ? 'text-electric font-bold' : 'hover:text-electric transition'; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Price Filter -->
                    <form action="products.php" method="GET">
                        <?php if (isset($_GET['category'])) echo '<input type="hidden" name="category" value="' . htmlspecialchars($_GET['category']) . '">'; ?>
                        <?php if (isset($_GET['search'])) echo '<input type="hidden" name="search" value="' . htmlspecialchars($_GET['search']) . '">'; ?>
                        
                        <h4 class="font-semibold mb-3 text-electric">Price Range</h4>
                        <div class="flex items-center space-x-2 mb-3">
                            <input type="number" name="min_price" placeholder="Min" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>" class="w-full px-2 py-1 border rounded text-sm dark:bg-gray-700 dark:border-gray-600">
                            <span>-</span>
                            <input type="number" name="max_price" placeholder="Max" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>" class="w-full px-2 py-1 border rounded text-sm dark:bg-gray-700 dark:border-gray-600">
                        </div>
                        <button type="submit" class="w-full bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white py-2 rounded text-sm hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                            Apply
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Product Grid -->
            <div class="w-full md:w-3/4">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white">
                        <?php 
                        if (isset($_GET['category'])) echo htmlspecialchars($_GET['category']);
                        elseif (!empty($search)) echo 'Search: "' . htmlspecialchars($search) . '"';
                        else echo 'All Products';
                        ?>
                    </h1>
                </div>

                <?php if (empty($products)): ?>
                    <div class="text-center py-20 bg-white dark:bg-gray-800 rounded-xl shadow">
                        <div class="text-6xl text-gray-300 mb-4"><i class="fas fa-box-open"></i></div>
                        <h2 class="text-2xl font-bold mb-2">No products found</h2>
                        <p class="text-gray-500">Try adjusting your filters.</p>
                        <a href="products.php" class="mt-4 inline-block text-electric hover:underline">Reset Filters</a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($products as $product): ?>
                        <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-md hover-lift group transition duration-300">
                            <div class="relative h-48 bg-gray-100 dark:bg-gray-700 overflow-hidden">
                                 <?php if ($product['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-gray-400"><i class="fas fa-image text-4xl"></i></div>
                                <?php endif; ?>
                                
                                <div class="absolute top-2 right-2 flex space-x-1 opacity-0 group-hover:opacity-100 transition duration-300">
                                     <!-- Wishlist Button -->
                                     <form action="wishlist_action.php" method="POST">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                         <?php 
                                            $in_wishlist = $is_logged_in && is_in_wishlist($pdo, $_SESSION['user_id'], $product['id']);
                                         ?>
                                          <button class="bg-white dark:bg-gray-800 text-gray-600 dark:text-white p-2 rounded-full shadow hover:text-red-500 transition">
                                             <i class="<?php echo $in_wishlist ? 'fas text-red-500' : 'far'; ?> fa-heart"></i>
                                          </button>
                                     </form>
                                </div>
                                
                                <?php 
                                $discount = isset($product['discount_percent']) ? floatval($product['discount_percent']) : 0;
                                if ($discount > 0): 
                                ?>
                                    <div class="absolute top-4 left-4 bg-red-500 text-white px-2 py-1 rounded text-sm font-semibold">
                                        -<?php echo number_format($discount, 0); ?>%
                                    </div>
                                <?php elseif ($product['stock'] == 0): ?>
                                     <div class="absolute top-4 left-4 bg-gray-500 text-white px-2 py-1 rounded text-sm font-semibold">Out of Stock</div>
                                <?php endif; ?>
                            </div>
                            <div class="p-4">
                                <span class="text-xs text-electric font-semibold uppercase tracking-wider"><?php echo htmlspecialchars($product['category'] ?? 'General'); ?></span>
                                <div class="flex items-center mt-1 mb-2">
                                    <?php 
                                    $rating = get_avg_rating($pdo, $product['id']); 
                                    $stars = round($rating['avg']);
                                    ?>
                                    <div class="flex text-accent text-xs">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                            <?php if($i <= $stars): ?>
                                                <i class="fas fa-star"></i>
                                            <?php elseif($i - 0.5 <= $rating['avg']): ?>
                                                <i class="fas fa-star-half-alt"></i>
                                            <?php else: ?>
                                                <i class="far fa-star text-gray-300"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="text-xs text-gray-500 ml-1">(<?php echo $rating['count']; ?>)</span>
                                </div>
                                <h3 class="font-semibold text-lg mb-1 truncate">
                                    <a href="product-details.php?id=<?php echo $product['id']; ?>" class="hover:text-electric transition">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </a>
                                </h3>
                                <div class="flex justify-between items-center mt-3">
                                    <?php if ($discount > 0): 
                                        $final_price = get_discounted_price($product['price'], $discount);
                                    ?>
                                        <div class="flex items-center gap-2">
                                            <span class="text-lg font-bold text-red-500">৳<?php echo number_format($final_price, 2); ?></span>
                                            <span class="text-sm text-gray-400 line-through">৳<?php echo number_format($product['price'], 2); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-lg font-bold text-gray-900 dark:text-white">৳<?php echo number_format($product['price'], 2); ?></span>
                                    <?php endif; ?>
                                    <?php if ($product['stock'] > 0): ?>
                                        <form action="cart.php" method="POST" class="inline">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" class="bg-electric text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-tech transition shadow">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="flex justify-center mt-12 space-x-2">
                        <?php 
                        $query_params = $_GET;
                        unset($query_params['page']);
                        $qs = http_build_query($query_params);
                        ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>&<?php echo $qs; ?>" 
                            class="px-4 py-2 rounded-lg <?php echo $i === $current_page ? 'bg-electric text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
