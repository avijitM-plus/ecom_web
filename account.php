<?php
/**
 * User Account Dashboard
 * RoboMart E-commerce Platform
 * Protected Page - Requires Login
 */

require_once 'config.php';

// Require user to be logged in
require_login();

// Get user data
$user = get_user_by_id($pdo, $_SESSION['user_id']);

if (!$user) {
    destroy_session();
    redirect('login.php');
}

// Redirect admin to dashboard
if ($user['role'] === 'admin') {
    redirect('backend/index.php');
}
?>
<?php
$page_title = "Account";
include 'includes/header.php';
?>

    <main class="container mx-auto px-4 py-12">
        <h1 class="text-3xl font-bold mb-4">Account Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <aside class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg">
                <h2 class="font-semibold mb-4 text-xl">Profile</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Name</p>
                        <p class="font-medium"><?php echo htmlspecialchars($user['full_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                        <p class="font-medium"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Member Since</p>
                        <p class="font-medium"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>
                <a href="logout.php" class="mt-6 inline-block w-full text-center bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg transition">
                    <i class="fas fa-sign-out-alt mr-2"></i>Sign Out
                </a>
            </aside>

            <section class="md:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg">
                <h2 class="font-semibold mb-4 text-xl">Quick Actions</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="orders.php" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-electric transition">
                        <i class="fas fa-box text-electric text-2xl mb-2"></i>
                        <h3 class="font-semibold">My Orders</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Track and manage your orders</p>
                    </a>
                    <a href="cart.php" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-electric transition">
                        <i class="fas fa-shopping-cart text-electric text-2xl mb-2"></i>
                        <h3 class="font-semibold">Shopping Cart</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">View items in your cart</p>
                    </a>
                    <a href="#" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-electric transition">
                        <i class="fas fa-heart text-electric text-2xl mb-2"></i>
                        <h3 class="font-semibold">Wishlist</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Manage your favorite items</p>
                    </a>
                    <a href="account-edit.php" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-electric transition">
                        <i class="fas fa-user-edit text-electric text-2xl mb-2"></i>
                        <h3 class="font-semibold">Edit Profile</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Update your account details</p>
                    </a>
                </div>
            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
