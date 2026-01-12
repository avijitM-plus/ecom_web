<?php
$page_title = "404 - Not Found";
require_once 'includes/header.php';
?>
<div class="container mx-auto px-4 py-20 text-center min-h-[60vh] flex flex-col items-center justify-center">
    <h1 class="text-9xl font-black mb-4 text-gray-200 dark:text-gray-800">404</h1>
    <div class="absolute">
        <p class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Page Not Found</p>
        <p class="text-gray-600 dark:text-gray-400 mb-6">The content you're looking for does not exist.</p>
        <a href="index.php" class="bg-electric text-white px-8 py-3 rounded-xl font-semibold hover:bg-opacity-90 transition shadow-lg hover:shadow-electric/50">
            <i class="fas fa-home mr-2"></i>Return Home
        </a>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
