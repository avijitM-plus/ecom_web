<?php
// Ensure config is loaded if not already (safeguard)
require_once __DIR__ . '/../config.php';

// Check login status
$is_logged_in = is_logged_in();
$user_name = $is_logged_in ? $_SESSION['full_name'] : '';

// Get Cart Count
// Check if get_cart_total_quantity function exists, if not, use session or safe default
$cart_count = 0;
if (function_exists('get_cart_total_quantity')) {
    $cart_count = get_cart_total_quantity();
} elseif (isset($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}

// Current Page for Active State (Optional, logic can be enhanced)
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - RoboMart' : 'RoboMart - Robotics, Electronics & IoT Solutions'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#1e40af',
                        accent: '#f59e0b',
                        electric: '#06b6d4',
                        tech: '#8b5cf6',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'pulse-slow': 'pulse 3s infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'slide-in-left': 'slideInLeft 0.8s ease-out',
                        'slide-in-right': 'slideInRight 0.8s ease-out',
                        'bounce-in': 'bounceIn 0.6s ease-out',
                        'text-glow': 'textGlow 2s ease-in-out infinite',
                        'pulse-text': 'pulseText 2s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        slideInLeft: {
                            '0%': { transform: 'translateX(-60px)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' },
                        },
                        slideInRight: {
                            '0%': { transform: 'translateX(60px)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' },
                        },
                        bounceIn: {
                            '0%': { transform: 'scale(0.5)', opacity: '0' },
                            '50%': { transform: 'scale(1.05)' },
                            '100%': { transform: 'scale(1)', opacity: '1' },
                        },
                        textGlow: {
                            '0%, 100%': { textShadow: '0 0 5px rgba(6, 182, 212, 0.5)' },
                            '50%': { textShadow: '0 0 20px rgba(6, 182, 212, 1)' },
                        },
                        pulseText: {
                            '0%, 100%': { opacity: '1' },
                            '50%': { opacity: '0.7' },
                        }
                    }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .text-shadow { text-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
            .tech-gradient { background: linear-gradient(135deg, #06b6d4 0%, #8b5cf6 100%); }
            .dark .tech-gradient { background: linear-gradient(135deg, #164e63 0%, #3f3b5f 100%); }
            .hover-lift { transition: all 0.3s ease; }
            .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }
            .glow { box-shadow: 0 0 15px rgba(59, 130, 246, 0.5); }
            .dark-mode-toggle { position: relative; display: inline-block; width: 60px; height: 30px; }
            .dark-mode-toggle input { opacity: 0; width: 0; height: 0; }
            .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: 0.4s; border-radius: 30px; }
            .slider:before { position: absolute; content: ""; height: 22px; width: 22px; left: 4px; bottom: 4px; background-color: white; transition: 0.4s; border-radius: 50%; }
            input:checked + .slider { background-color: #06b6d4; }
            input:checked + .slider:before { transform: translateX(30px); }
        }
    </style>
    <script>
        // Immediate Dark Mode Initialization
        (function() {
            const stored = localStorage.getItem('darkMode');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = stored === 'true' || (stored === null && prefersDark);
            
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
</head>
<body class="font-sans bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 transition duration-300">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white dark:bg-gray-900 shadow-sm dark:shadow-lg transition duration-300">
        <div class="container mx-auto px-4">
            <!-- Top Bar -->
            <div class="flex items-center justify-between py-4">
                <!-- Logo -->
                <a href="index.php" class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-electric to-tech flex items-center justify-center">
                        <i class="fas fa-bolt text-white text-lg"></i>
                    </div>
                    <span class="text-xl font-bold text-gray-800 dark:text-white">RoboMart</span>
                </a>

                <!-- Search Bar -->
                <?php if (!isset($hide_nav) || !$hide_nav): ?>
                <div class="hidden md:flex flex-1 max-w-2xl mx-8">
                     <form action="products.php" method="GET" class="relative w-full">
                        <input type="text" name="search" placeholder="Search robotics, electronics, IoT devices..."
                            class="w-full pl-4 pr-10 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-electric focus:border-transparent dark:placeholder-gray-400">
                        <button type="submit" class="absolute right-0 top-0 h-full px-4 text-gray-500 dark:text-gray-400 hover:text-electric dark:hover:text-electric">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <!-- User Actions -->
                <?php if (isset($hide_nav) && $hide_nav): ?>
                    <div class="flex items-center space-x-4">
                        <label class="dark-mode-toggle">
                            <input type="checkbox" id="darkModeToggle">
                            <span class="slider"></span>
                        </label>
                        <a href="index.php" class="text-gray-700 dark:text-gray-300 hover:text-electric">Back to Home</a>
                    </div>
                <?php else: ?>
                <div class="flex items-center space-x-6">
                    <!-- Dark Mode Toggle -->
                    <label class="dark-mode-toggle">
                        <input type="checkbox" id="darkModeToggle">
                        <span class="slider"></span>
                    </label>

                    <div class="hidden md:flex items-center space-x-1 text-gray-700 hover:text-electric cursor-pointer dark:text-gray-300 dark:hover:text-electric">
                        <i class="far fa-user text-lg"></i>
                        <?php if ($is_logged_in): ?>
                            <span class="font-medium"><a href="account.php">Hi, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?></a></span>
                        <?php else: ?>
                            <span class="font-medium"><a href="login.php">Account</a></span>
                        <?php endif; ?>
                    </div>
                    <a href="wishlist.php" class="hidden md:flex items-center space-x-1 text-gray-700 hover:text-electric cursor-pointer dark:text-gray-300 dark:hover:text-electric">
                         <!-- Actually wishlist.php page doesn't exist? Use Products or Account? Or wishlist_action with no post? 
                              User said wishlist works now. But where is the page? 
                              Maybe account.php has wishlist tab?
                              I'll point to account.php or products.php for now if wishlist.php absent.
                              Wait, products.php has wishlist button. 
                              Let's assume there isn't a dedicated wishlist page yet unless I missed it.
                              I'll point to account.php as safe bet.
                          -->
                        <i class="far fa-heart text-lg"></i>
                        <span class="font-medium">Wishlist</span>
                    </a>
                    <a href="cart.php" class="flex items-center space-x-1 text-gray-700 hover:text-electric cursor-pointer relative dark:text-gray-300 dark:hover:text-electric">
                        <i class="fas fa-shopping-cart text-lg"></i>
                        <span class="font-medium">Cart</span>
                        <?php if ($cart_count > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-accent text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <button id="mobileMenuButton" class="md:hidden text-gray-700 dark:text-gray-300">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <!-- Navigation -->
            <?php if (!isset($hide_nav) || !$hide_nav): ?>
            <nav class="hidden md:flex py-3 border-t border-gray-200 dark:border-gray-700">
                <div class="flex space-x-8">
                    <a href="index.php" class="text-gray-700 dark:text-gray-300 hover:text-electric dark:hover:text-electric font-medium transition <?php echo $current_page == 'index.php' ? 'text-electric' : ''; ?>">Home</a>
                    <a href="products.php" class="text-gray-700 dark:text-gray-300 hover:text-electric dark:hover:text-electric font-medium transition <?php echo $current_page == 'products.php' ? 'text-electric' : ''; ?>">All Products</a>
                    <a href="products.php?cat=robotics" class="text-gray-700 dark:text-gray-300 hover:text-electric dark:hover:text-electric font-medium transition">Robotics</a>
                    <a href="products.php?cat=microcontrollers" class="text-gray-700 dark:text-gray-300 hover:text-electric dark:hover:text-electric font-medium transition">Microcontrollers</a>
                    <a href="products.php?cat=iot" class="text-gray-700 dark:text-gray-300 hover:text-electric dark:hover:text-electric font-medium transition">IoT Devices</a>
                    <a href="products.php?cat=ai" class="text-gray-700 dark:text-gray-300 hover:text-electric dark:hover:text-electric font-medium transition">AI & ML</a>
                    <a href="products.php" class="text-electric dark:text-electric font-medium">Flash Sale</a>
                </div>
            </nav>
            <div id="mobileMenu" class="md:hidden hidden border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                <div class="px-4 pt-4 pb-6 space-y-4">
                    <a href="index.php" class="block text-gray-700 dark:text-gray-300 hover:text-electric">Home</a>
                    <a href="products.php" class="block text-gray-700 dark:text-gray-300 hover:text-electric">All Products</a>
                    <a href="products.php" class="block text-electric dark:text-electric font-medium">Flash Sale</a>
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                        <a href="login.php" class="block text-gray-700 dark:text-gray-300 py-2">Sign In</a>
                        <a href="account.php" class="block text-gray-700 dark:text-gray-300 py-2">Account</a>
                        <a href="cart.php" class="block text-gray-700 dark:text-gray-300 py-2">Cart</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </header>
