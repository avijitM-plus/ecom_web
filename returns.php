<?php
/**
 * User Return Request
 * RoboMart E-commerce Platform
 */

require_once 'config.php';
require_login();

$page_title = 'Request Return';
$user_id = $_SESSION['user_id'];

// Handle Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)$_POST['order_id'];
    $product_id = (int)$_POST['product_id'];
    $reason = sanitize_input($_POST['reason']);
    
    // Verify ownership
    if (has_purchased_product($pdo, $user_id, $product_id)) {
        $stmt = $pdo->prepare("INSERT INTO returns (order_id, user_id, product_id, reason) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$order_id, $user_id, $product_id, $reason])) {
            $success = "Return request submitted successfully.";
        } else {
            $error = "Failed to submit request.";
        }
    } else {
        $error = "Invalid order or product.";
    }
}

// Fetch Eligible Items (Completed orders in last 30 days)
$stmt = $pdo->prepare("
    SELECT o.id as order_id, o.created_at, p.id as product_id, p.name as product_name, p.image_url 
    FROM orders o 
    JOIN order_items oi ON o.id = oi.order_id 
    JOIN products p ON oi.product_id = p.id 
    WHERE o.user_id = ? 
    AND o.status = 'completed' 
    AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    AND NOT EXISTS (SELECT 1 FROM returns r WHERE r.order_id = o.id AND r.product_id = p.id)
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$eligible_items = $stmt->fetchAll();

// Fetch History
$stmt = $pdo->prepare("
    SELECT r.*, p.name as product_name 
    FROM returns r 
    JOIN products p ON r.product_id = p.id 
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-12">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">Return Center</h2>
        <p class="text-gray-600 dark:text-gray-400">Request returns for items purchased in the last 30 days.</p>
    </div>
    
    <?php if (isset($success)): ?>
    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
    </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Request Form -->
        <div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700 transition duration-300 hover:shadow-xl">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <h5 class="text-lg font-semibold text-gray-800 dark:text-white">Start a Return</h5>
                </div>
                <div class="p-6">
                    <?php if (empty($eligible_items)): ?>
                        <div class="text-center py-8">
                            <div class="bg-gray-100 dark:bg-gray-700 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-box-open text-gray-400 dark:text-gray-500 text-2xl"></i>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400">No eligible items for return.</p>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <div class="mb-4">
                                <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Select Item</label>
                                <select name="product_id_select" id="itemSelect" onchange="updateOrderInput(this)"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-electric focus:border-transparent">
                                    <option value="">Choose an item...</option>
                                    <?php foreach ($eligible_items as $item): ?>
                                        <option value="<?php echo $item['product_id']; ?>" data-order="<?php echo $item['order_id']; ?>">
                                            <?php echo htmlspecialchars($item['product_name']); ?> (Order #<?php echo $item['order_id']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="product_id" id="productIdInput">
                                <input type="hidden" name="order_id" id="orderIdInput">
                            </div>
                            
                            <div class="mb-6">
                                <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Reason for Return</label>
                                <textarea name="reason" rows="3" required placeholder="Describe why you want to return this item..."
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-electric focus:border-transparent"></textarea>
                            </div>
                            
                            <button type="submit" class="w-full bg-electric hover:bg-cyan-600 text-white font-bold py-3 rounded-lg transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                Submit Request
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- History -->
        <div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-100 dark:border-gray-700 transition duration-300 hover:shadow-xl">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <h5 class="text-lg font-semibold text-gray-800 dark:text-white">Return History</h5>
                </div>
                <div class="p-0">
                    <?php if (empty($history)): ?>
                         <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No past return requests.</p>
                        </div>
                    <?php else: ?>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <?php foreach ($history as $return): ?>
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-750 transition">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h6 class="font-semibold text-gray-800 dark:text-gray-200 mb-1"><?php echo htmlspecialchars($return['product_name']); ?></h6>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Reason: <?php echo htmlspecialchars($return['reason']); ?></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-500"><?php echo date('M d, Y', strtotime($return['created_at'])); ?></div>
                                    </div>
                                    <?php
                                    $statusColor = match($return['status']) {
                                        'refunded' => 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800',
                                        'rejected' => 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800',
                                        'approved' => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800',
                                        default => 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-300 dark:border-yellow-800'
                                    };
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium border <?php echo $statusColor; ?>">
                                        <?php echo ucfirst($return['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateOrderInput(select) {
    const option = select.options[select.selectedIndex];
    if (option.value) {
        document.getElementById('productIdInput').value = option.value;
        document.getElementById('orderIdInput').value = option.getAttribute('data-order');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
