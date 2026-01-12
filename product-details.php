<?php
/**
 * Product Details Page
 */
require_once 'config.php';

// Get product ID
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = get_product_by_id($pdo, $product_id);

// Header handles session
// $is_logged_in = is_logged_in();
// $user_name = $is_logged_in ? $_SESSION['full_name'] : '';

// Handle Review Submission
$review_error = '';
$review_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!is_logged_in()) {
        $review_error = 'You must be logged in to review.';
    } else {
        $rating = (int)$_POST['rating'];
        $comment = sanitize_input($_POST['comment']);
        // Assuming add_review function is available in functions.php
        $res = add_review($pdo, $product_id, $_SESSION['user_id'], $rating, $comment);
        if ($res['success']) {
            $review_success = $res['message'];
        } else {
            $review_error = $res['message'];
        }
    }
}

// Get Reviews and Avg Rating
$reviews = get_product_reviews($pdo, $product_id);
$avg_stats = get_avg_rating($pdo, $product_id);
?>
<?php
$page_title = $product ? htmlspecialchars($product['name']) : 'Product Not Found';
include 'includes/header.php';
?>

    <main class="container mx-auto px-4 py-6 md:py-12">
        <?php if ($product): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden p-4 flex items-center justify-center">
                 <?php if ($product['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="max-w-full max-h-96 object-contain">
                <?php else: ?>
                    <div class="w-full h-96 bg-gray-200 flex items-center justify-center text-gray-400">
                        <i class="fas fa-image text-6xl"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <!-- Rating Summary -->
                <div class="flex items-center mb-4">
                    <div class="flex text-accent text-sm">
                        <?php for($i=1; $i<=5; $i++): ?>
                            <?php if($i <= round($avg_stats['avg'])): ?>
                                <i class="fas fa-star text-yellow-400"></i>
                            <?php else: ?>
                                <i class="far fa-star text-gray-400"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <span class="ml-2 text-gray-500 text-sm"><?php echo $avg_stats['avg']; ?> (<?php echo $avg_stats['count']; ?> reviews)</span>
                </div>
                
                <div class="mb-4">
                    <span class="text-3xl font-extrabold text-gray-900 dark:text-white">৳<?php echo number_format($product['price'], 2); ?></span>
                </div>
                
                 <p class="text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
                     <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                 </p>

                <?php if ($product['stock'] > 0): ?>
                    <div class="flex flex-col gap-4">
                        <form action="cart.php" method="POST" class="w-full">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                                <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="w-24 mt-2 px-3 py-2 border border-gray-300 rounded dark:bg-gray-800 dark:border-gray-600 dark:text-white" />
                                <span class="text-sm text-gray-500 ml-2"><?php echo $product['stock']; ?> available</span>
                            </div>

                            <div class="flex items-center gap-4">
                                <button type="submit" class="bg-electric text-white px-6 py-3 rounded-lg hover:bg-tech transition duration-300 font-semibold shadow-lg hover:shadow-xl flex-1">
                                    <i class="fas fa-cart-plus mr-2"></i> Add to Cart
                                </button>
                            </div>
                        </form>
                        
                        <!-- Wishlist Form -->
                        <form action="wishlist_action.php" method="POST" class="w-full">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                             <?php 
                                $in_wishlist = $is_logged_in && is_in_wishlist($pdo, $_SESSION['user_id'], $product['id']);
                             ?>
                             <button type="submit" class="w-full border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 px-6 py-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition font-semibold">
                                <i class="<?php echo $in_wishlist ? 'fas text-red-500' : 'far'; ?> fa-heart mr-2"></i> <?php echo $in_wishlist ? 'In Wishlist' : 'Add to Wishlist'; ?>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg inline-block">
                        <i class="fas fa-exclamation-circle mr-2"></i> Out of Stock
                    </div>
                <?php endif; ?>

                <section class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-8">
                    <h3 class="font-semibold mb-2">Safe Checkout</h3>
                    <div class="flex space-x-4 opacity-70">
                        <i class="fab fa-cc-visa text-2xl"></i>
                        <i class="fab fa-cc-mastercard text-2xl"></i>
                        <i class="fab fa-cc-paypal text-2xl"></i>
                        <i class="fab fa-cc-stripe text-2xl"></i>
                    </div>
                </section>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="mt-16 bg-white dark:bg-gray-800 rounded-xl p-8 shadow-sm">
            <h2 class="text-2xl font-bold mb-6">Customer Reviews</h2>
            
            <?php if ($review_success): ?>
                <div class="bg-green-100 text-green-700 p-4 rounded mb-6"><?php echo $review_success; ?></div>
            <?php endif; ?>
            <?php if ($review_error): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded mb-6"><?php echo $review_error; ?></div>
            <?php endif; ?>

            <!-- Review Form -->
            <?php 
            $can_review = false;
            $review_status = null;
            if ($is_logged_in) {
                // Check how many reviews user can still submit
                $review_status = can_review_product($pdo, $_SESSION['user_id'], $product['id']);
                $can_review = $review_status['can_review'];
            }
            ?>

            <?php if ($can_review): ?>
                <form method="POST" class="mb-10 bg-gray-50 dark:bg-gray-900 p-6 rounded-lg">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold">Write a Review</h3>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            <i class="fas fa-info-circle mr-1"></i>
                            You can leave <?php echo $review_status['remaining']; ?> more review(s) 
                            (<?php echo $review_status['reviews']; ?>/<?php echo $review_status['purchases']; ?> submitted)
                        </span>
                    </div>
                    <input type="hidden" name="submit_review" value="1">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Rating</label>
                        <select name="rating" required class="w-full md:w-48 px-4 py-2 border rounded dark:bg-gray-700 dark:border-gray-600">
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Good</option>
                            <option value="3">3 - Average</option>
                            <option value="2">2 - Fair</option>
                            <option value="1">1 - Poor</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Comment</label>
                        <textarea name="comment" rows="3" required class="w-full px-4 py-2 border rounded dark:bg-gray-700 dark:border-gray-600" placeholder="Share your experience..."></textarea>
                    </div>
                    
                    <button type="submit" class="bg-electric text-white px-6 py-2 rounded hover:bg-tech transition">Submit Review</button>
                </form>
            <?php elseif ($is_logged_in): ?>
                <div class="mb-8 p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg flex items-center">
                    <i class="bi bi-info-circle mr-2"></i>
                    <span>Only customers who have purchased this product can write a review.</span>
                </div>
            <?php else: ?>
                <div class="mb-8 p-4 bg-gray-100 dark:bg-gray-700 rounded text-center">
                    <p>Please <a href="login.php" class="text-electric font-semibold">Log In</a> to write a review.</p>
                </div>
            <?php endif; ?>

            <!-- Reviews List -->
            <div class="space-y-6">
                <?php if (empty($reviews)): ?>
                    <p class="text-gray-500 italic">No reviews yet. Be the first to review!</p>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                    <div class="border-b border-gray-100 dark:border-gray-700 pb-6 last:border-0">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white"><?php echo htmlspecialchars($review['full_name']); ?></h4>
                                <div class="text-accent text-sm mt-1">
                                    <?php for($i=1; $i<=5; $i++) echo $i <= $review['rating'] ? '<i class="fas fa-star text-yellow-400"></i>' : '<i class="far fa-star text-gray-400"></i>'; ?>
                                </div>
                            </div>
                            <span class="text-xs text-gray-500"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                        </div>
                        <p class="text-gray-600 dark:text-gray-300"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
            <div class="text-center py-20">
                <div class="text-6xl text-gray-300 mb-4"><i class="fas fa-box-open"></i></div>
                <h1 class="text-3xl font-bold mb-4">Product Not Found</h1>
                <p class="text-gray-500 mb-8">The product you are looking for does not exist or has been removed.</p>
                <a href="index.php" class="bg-electric text-white px-6 py-3 rounded-lg hover:bg-tech transition">Return to Home</a>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>
