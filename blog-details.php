<?php
require_once 'config.php';

$slug = isset($_GET['slug']) ? $_GET['slug'] : '';
$post = get_post_by_slug($pdo, $slug);

if (!$post) {
    header("HTTP/1.0 404 Not Found");
    die("Post not found");
}

$is_logged_in = is_logged_in();
$user_name = $is_logged_in ? $_SESSION['full_name'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo htmlspecialchars($post['title']); ?> - RoboMart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                     colors: {
                        primary: '#3b82f6',
                        electric: '#06b6d4',
                        tech: '#8b5cf6',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="font-sans bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 transition duration-300">
     <style>
        .dark-mode-toggle { position: relative; display: inline-block; width: 60px; height: 30px; }
        .dark-mode-toggle input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: 0.4s; border-radius: 30px; }
        .slider:before { position: absolute; content: ""; height: 22px; width: 22px; left: 4px; bottom: 4px; background-color: white; transition: 0.4s; border-radius: 50%; }
        input:checked + .slider { background-color: #06b6d4; }
        input:checked + .slider:before { transform: translateX(30px); }
    </style>

    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white dark:bg-gray-900 shadow-sm dark:shadow-lg transition duration-300">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <a href="index.php" class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-electric to-tech flex items-center justify-center">
                        <i class="fas fa-bolt text-white text-lg"></i>
                    </div>
                    <span class="text-xl font-bold text-gray-800 dark:text-white">RoboMart</span>
                </a>

                 <div class="flex items-center space-x-6">
                    <label class="dark-mode-toggle">
                        <input type="checkbox" id="darkModeToggle">
                        <span class="slider"></span>
                    </label>
                    <div class="hidden md:flex items-center space-x-1 text-gray-700 hover:text-electric cursor-pointer dark:text-gray-300">
                        <i class="far fa-user text-lg"></i>
                        <?php if ($is_logged_in): ?>
                            <a href="account.php" class="font-medium">Hi, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?></a>
                        <?php else: ?>
                            <a href="login.php" class="font-medium">Account</a>
                        <?php endif; ?>
                    </div>
                     <a href="wishlist.php" class="hidden md:flex items-center space-x-1 text-gray-700 hover:text-electric cursor-pointer dark:text-gray-300">
                        <i class="far fa-heart text-lg"></i>
                        <span class="font-medium">Wishlist</span>
                    </a>
                    <a href="cart.php" class="flex items-center space-x-1 text-gray-700 hover:text-electric relative dark:text-gray-300">
                        <i class="fas fa-shopping-cart text-lg"></i>
                        <span class="font-medium">Cart</span>
                    </a>
                </div>
            </div>
             <nav class="hidden md:flex py-3 border-t border-gray-200 dark:border-gray-700">
                <div class="flex space-x-8">
                     <a href="index.php" class="text-gray-700 dark:text-gray-300 hover:text-electric font-medium">Home</a>
                     <a href="products.php" class="text-gray-700 dark:text-gray-300 hover:text-electric font-medium">Shop</a>
                     <a href="blog.php" class="text-electric font-bold">Blog</a>
                </div>
            </nav>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <article class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
            <div class="h-64 md:h-96 w-full relative">
                <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full h-full object-cover">
                <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/80 to-transparent p-8">
                    <?php if (!empty($post['category'])): ?>
                        <span class="inline-block bg-electric text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide mb-3">
                            <?php echo htmlspecialchars($post['category']); ?>
                        </span>
                    <?php endif; ?>
                    <h1 class="text-3xl md:text-5xl font-bold text-white mb-2"><?php echo htmlspecialchars($post['title']); ?></h1>
                    <div class="text-gray-300 text-sm">
                        <i class="far fa-calendar-alt mr-2"></i> <?php echo date('F d, Y', strtotime($post['created_at'])); ?>
                    </div>
                </div>
            </div>
            
            <div class="p-8 md:p-12 text-lg text-gray-700 dark:text-gray-300 leading-relaxed prose dark:prose-invert max-w-none">
                <?php echo $post['content']; // Outputting raw HTML from TinyMCE ?>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-900 p-8 border-t border-gray-200 dark:border-gray-700">
                 <a href="blog.php" class="text-electric font-semibold hover:underline">&larr; Back to Blog</a>
            </div>
        </article>
    </main>
    <script src="script.js"></script>
</body>
</html>
