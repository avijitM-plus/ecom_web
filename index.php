<?php
/**
 * Home Page - RoboMart E-commerce Platform
 */
require_once 'config.php';
$page_title = "Home";
include 'includes/header.php';
?>

    <!-- Hero Section -->
    <section
        class="relative tech-gradient dark:bg-gradient-to-r dark:from-gray-900 dark:via-blue-900 dark:to-purple-900 text-white py-20 md:py-32 overflow-hidden transition duration-300">
        <!-- Modern Background Animation -->
        <div class="absolute inset-0 overflow-hidden">
            <div
                class="absolute w-96 h-96 bg-electric/20 dark:bg-electric/10 rounded-full -top-40 -left-40 blur-3xl animate-pulse">
            </div>
            <div class="absolute w-96 h-96 bg-tech/20 dark:bg-tech/10 rounded-full -bottom-40 -right-40 blur-3xl animate-pulse"
                style="animation-delay: 2s;"></div>
            <div class="absolute inset-0 bg-black/20 dark:bg-black/40"></div>
        </div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="md:w-1/2 mb-10 md:mb-0">
                    <h1
                        class="text-6xl md:text-7xl font-black mb-6 text-shadow text-white animate-slide-in-left leading-tight">
                        <span class="animate-text-glow block">Next-Gen</span>
                        <span class="text-accent animate-bounce-in block"
                            style="animation-delay: 0.2s;">Electronics</span>
                        <span class="animate-pulse-text block" style="animation-delay: 0.4s;">& Robotics</span>
                    </h1>
                    <p class="text-xl md:text-2xl mb-8 opacity-95 animate-slide-up leading-relaxed font-light"
                        style="animation-delay: 0.3s;">
                        Transform your world with cutting-edge robotics kits, AI-powered electronics, and innovative IoT
                        solutions designed for tomorrow.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 animate-slide-up" style="animation-delay: 0.5s;">
                        <button
                            class="bg-gradient-to-r from-electric to-tech text-white px-8 py-4 rounded-xl font-bold hover:shadow-2xl hover:shadow-electric/50 transition duration-300 transform hover:scale-105">
                            Explore Collection
                        </button>
                        <button
                            class="border-2 border-white text-white px-8 py-4 rounded-xl font-bold hover:bg-white/10 backdrop-blur-sm transition duration-300 transform hover:scale-105">
                            Watch Demo
                        </button>
                    </div>
                </div>
                <div class="md:w-1/2 flex justify-center animate-float">
                    <div class="relative w-full max-w-md">
                        <!-- Ultra-Modern Carousel with Advanced Glass Effect -->
                        <div
                            class="carousel-container relative h-80 rounded-3xl shadow-2xl overflow-hidden backdrop-blur-xl border border-white/30 bg-gradient-to-br from-white/10 to-white/5 group">
                            <!-- Animated Background Gradient Overlay -->
                            <div
                                class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-black/20 z-5">
                            </div>

                            <div class="carousel-wrapper relative w-full h-full">
                                <!-- Slide 1 -->
                                <img src="images/1.jpg" alt="Robotic Arm"
                                    class="carousel-slide absolute w-full h-full object-cover transition-all duration-1000 ease-out scale-100 group-hover:scale-105"
                                    style="opacity: 1;">
                                <!-- Slide 2 -->
                                <img src="images/2.jpg" alt="Arduino Kit"
                                    class="carousel-slide absolute w-full h-full object-cover transition-all duration-1000 ease-out scale-100 group-hover:scale-105"
                                    style="opacity: 0;">
                                <!-- Slide 3 -->
                                <img src="images/3.jpg" alt="Drone"
                                    class="carousel-slide absolute w-full h-full object-cover transition-all duration-1000 ease-out scale-100 group-hover:scale-105"
                                    style="opacity: 0;">
                                <!-- Slide 4 -->
                                <img src="images/4.jpg" alt="AI Robot"
                                    class="carousel-slide absolute w-full h-full object-cover transition-all duration-1000 ease-out scale-100 group-hover:scale-105"
                                    style="opacity: 0;">
                            </div>

                            <!-- Premium Indicators - Rectangular Active State -->
                            <div
                                class="carousel-indicators absolute bottom-6 left-1/2 transform -translate-x-1/2 flex space-x-2 z-10 bg-white/15 backdrop-blur-xl px-5 py-3 rounded-full border border-white/25 shadow-lg">
                                <button
                                    class="indicator w-8 h-2 rounded-full bg-white/50 hover:bg-white/70 active:bg-white active:w-16 active:rounded-lg transition-all duration-300 hover:scale-110"
                                    data-index="0"></button>
                                <button
                                    class="indicator w-8 h-2 rounded-full bg-white/50 hover:bg-white/70 active:bg-white active:w-16 active:rounded-lg transition-all duration-300 hover:scale-110"
                                    data-index="1"></button>
                                <button
                                    class="indicator w-8 h-2 rounded-full bg-white/50 hover:bg-white/70 active:bg-white active:w-16 active:rounded-lg transition-all duration-300 hover:scale-110"
                                    data-index="2"></button>
                                <button
                                    class="indicator w-8 h-2 rounded-full bg-white/50 hover:bg-white/70 active:bg-white active:w-16 active:rounded-lg transition-all duration-300 hover:scale-110"
                                    data-index="3"></button>
                            </div>
                        </div>

                        <!-- Premium Discount Badge with Enhanced Effects -->
                        <div
                            class="absolute -bottom-6 -right-6 bg-gradient-to-r from-accent via-yellow-400 to-amber-500 text-white px-6 py-3 rounded-2xl shadow-2xl animate-pulse backdrop-blur-xl border border-white/40 hover:scale-110 transition-transform duration-300 cursor-pointer group/badge">
                            <div class="flex flex-col items-center">
                                <span class="font-black text-lg group-hover/badge:scale-110 transition-transform">30%
                                    OFF</span>
                                <span class="text-xs opacity-90 group-hover/badge:opacity-100">Limited Time</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Categories -->
    <section class="py-16 bg-white dark:bg-gray-900 transition duration-300">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800 dark:text-white animate-slide-in-left">Shop By
                Category</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
                <!-- Category 1 -->
                <div
                    class="bg-gradient-to-br from-electric/10 to-tech/10 rounded-xl p-6 text-center hover-lift cursor-pointer group border border-electric/30 transform hover:scale-105 transition duration-300">
                    <div
                        class="w-16 h-16 bg-electric/10 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-electric/30 transition duration-300 animate-bounce-in">
                        <i class="fas fa-robot text-electric text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 dark:text-white">Robotics</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">AI-Powered Robots</p>
                </div>

                <!-- Category 2 -->
                <div class="bg-gradient-to-br from-electric/10 to-tech/10 dark:from-electric/20 dark:to-tech/20 rounded-xl p-6 text-center hover-lift cursor-pointer group border border-electric/30 dark:border-electric/50 transform hover:scale-105 transition duration-300"
                    style="animation-delay: 0.1s;">
                    <div class="w-16 h-16 bg-electric/10 dark:bg-electric/20 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-electric/30 dark:group-hover:bg-electric/40 transition duration-300 animate-bounce-in"
                        style="animation-delay: 0.1s;">
                        <i class="fas fa-microchip text-electric text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 dark:text-white">Microcontrollers</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Arduino & Embedded</p>
                </div>

                <!-- Category 3 -->
                <div class="bg-gradient-to-br from-electric/10 to-tech/10 dark:from-electric/20 dark:to-tech/20 rounded-xl p-6 text-center hover-lift cursor-pointer group border border-electric/30 dark:border-electric/50 transform hover:scale-105 transition duration-300"
                    style="animation-delay: 0.2s;">
                    <div class="w-16 h-16 bg-electric/10 dark:bg-electric/20 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-electric/30 dark:group-hover:bg-electric/40 transition duration-300 animate-bounce-in"
                        style="animation-delay: 0.2s;">
                        <i class="fas fa-mobile-alt text-electric text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 dark:text-white">IoT Devices</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Smart Connected</p>
                </div>

                <!-- Category 4 -->
                <div class="bg-gradient-to-br from-electric/10 to-tech/10 dark:from-electric/20 dark:to-tech/20 rounded-xl p-6 text-center hover-lift cursor-pointer group border border-electric/30 dark:border-electric/50 transform hover:scale-105 transition duration-300"
                    style="animation-delay: 0.3s;">
                    <div class="w-16 h-16 bg-electric/10 dark:bg-electric/20 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-electric/30 dark:group-hover:bg-electric/40 transition duration-300 animate-bounce-in"
                        style="animation-delay: 0.3s;">
                        <i class="fas fa-server text-electric text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 dark:text-white">Components</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Sensors & Motors</p>
                </div>

                <!-- Category 5 -->
                <div class="bg-gradient-to-br from-electric/10 to-tech/10 dark:from-electric/20 dark:to-tech/20 rounded-xl p-6 text-center hover-lift cursor-pointer group border border-electric/30 dark:border-electric/50 transform hover:scale-105 transition duration-300"
                    style="animation-delay: 0.4s;">
                    <div class="w-16 h-16 bg-electric/10 dark:bg-electric/20 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-electric/30 dark:group-hover:bg-electric/40 transition duration-300 animate-bounce-in"
                        style="animation-delay: 0.4s;">
                        <i class="fas fa-cpu text-electric text-2xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 dark:text-white">AI & ML Kits</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Machine Learning</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Best Sellers -->
    <section class="py-16 bg-gray-50 dark:bg-gray-800 transition duration-300">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-10">
                <h2 class="text-3xl font-bold text-gray-800 dark:text-white animate-slide-in-left">Best Selling Products
                </h2>
                <a href="products.php"
                    class="text-electric font-semibold flex items-center hover:translate-x-1 transition duration-300">
                    View All
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php
                // Fetch featured products for Best Sellers
                $products = get_featured_products($pdo, 8);

                if (empty($products)):
                ?>
                    <div class="col-span-full text-center text-gray-500 dark:text-gray-400 py-10">
                        <p class="text-xl">No products available at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <!-- Product Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-md dark:shadow-xl dark:shadow-black/30 hover-lift group transform hover:scale-105 transition duration-300">
                        <div class="relative overflow-hidden h-56 bg-gradient-to-br from-electric/10 to-tech/10 dark:from-electric/20 dark:to-tech/20">
                            <?php if ($product['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"
                                class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                            <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                <i class="fas fa-image text-4xl"></i>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($product['stock'] < 5 && $product['stock'] > 0): ?>
                            <div class="absolute top-4 right-4 bg-orange-500 text-white px-2 py-1 rounded text-sm font-semibold animate-pulse">
                                Low Stock
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($product['stock'] == 0): ?>
                            <div class="absolute top-4 right-4 bg-red-500 text-white px-2 py-1 rounded text-sm font-semibold">
                                Out of Stock
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-lg mb-1 text-gray-900 dark:text-white truncate">
                                <a href="product-details.php?id=<?php echo $product['id']; ?>" class="hover:text-electric transition">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-2 line-clamp-2">
                                <?php echo htmlspecialchars($product['description']); ?>
                            </p>
                            <div class="flex items-center mb-2">
                                <div class="flex text-yellow-400 text-sm">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <span class="text-gray-500 dark:text-gray-400 text-xs ml-2">(New)</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-lg font-bold text-gray-900 dark:text-white">$<?php echo number_format($product['price'], 2); ?></span>
                                </div>
                                <?php if ($product['stock'] > 0): ?>
                                <form action="cart.php" method="POST" class="inline">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit"
                                        class="bg-electric text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-tech transition duration-300 add-to-cart">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                <button disabled class="bg-gray-300 dark:bg-gray-700 text-gray-500 w-10 h-10 rounded-full flex items-center justify-center cursor-not-allowed">
                                    <i class="fas fa-ban"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Latest Tech News (Blog) -->
    <section class="py-16 bg-white dark:bg-gray-900 transition duration-300">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-4 text-gray-800 dark:text-white animate-slide-in-left">Latest Tech News</h2>
            <p class="text-gray-600 dark:text-gray-400 text-center mb-10 max-w-2xl mx-auto animate-slide-up">Stay updated with the latest trends, tutorials, and insights in robotics and IoT.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php
                $recent_posts = get_recent_posts($pdo, 3);
                foreach ($recent_posts as $post):
                ?>
                <div class="relative rounded-xl overflow-hidden group border border-electric/30 transform hover:scale-105 transition duration-300 bg-white dark:bg-gray-800 shadow-lg">
                    <div class="h-56 overflow-hidden bg-gray-200">
                         <?php if ($post['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($post['image_url']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-gray-400"><i class="fas fa-image text-4xl"></i></div>
                        <?php endif; ?>
                    </div>
                    <div class="p-6">
                        <div class="text-xs text-electric font-semibold mb-2"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></div>
                        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2 line-clamp-2">
                            <a href="blog-details.php?slug=<?php echo $post['slug']; ?>" class="hover:text-electric transition">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4 line-clamp-2"><?php echo strip_tags($post['content']); ?></p>
                        <a href="blog-details.php?slug=<?php echo $post['slug']; ?>"
                            class="inline-block text-electric font-semibold hover:underline">
                            Read Article &rarr;
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Deals & Promotions -->
    <section class="py-16 tech-gradient text-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-2 animate-text-glow">Tech Deals & Promotions</h2>
            <p class="text-center mb-10 opacity-90 animate-slide-up">Exclusive offers on robotics kits, AI modules, and
                electronic components. Limited time!</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Deal 1 -->
                <div
                    class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center border border-white/20 transform hover:scale-105 transition duration-300 hover:shadow-lg hover:shadow-electric/50">
                    <div
                        class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce-in">
                        <i class="fas fa-bolt text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Robotics Flash Sale</h3>
                    <p class="mb-4 opacity-90">Up to 50% off on robotics kits. Ends in:</p>
                    <div class="flex justify-center space-x-2 mb-4">
                        <div class="bg-white/20 rounded-lg p-2 text-center w-12">
                            <span class="font-bold text-lg" id="days">02</span>
                            <span class="block text-xs">Days</span>
                        </div>
                        <div class="bg-white/20 rounded-lg p-2 text-center w-12">
                            <span class="font-bold text-lg" id="hours">12</span>
                            <span class="block text-xs">Hours</span>
                        </div>
                        <div class="bg-white/20 rounded-lg p-2 text-center w-12">
                            <span class="font-bold text-lg" id="minutes">45</span>
                            <span class="block text-xs">Mins</span>
                        </div>
                    </div>
                    <a href="products.php"
                        class="bg-white text-electric px-6 py-2 rounded-lg font-semibold hover:bg-gray-100 transition duration-300 transform hover:scale-105">
                        Shop Now
                    </a>
                </div>

                <!-- Deal 2 -->
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center border border-white/20 transform hover:scale-105 transition duration-300 hover:shadow-lg hover:shadow-tech/50"
                    style="animation-delay: 0.1s;">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce-in"
                        style="animation-delay: 0.1s;">
                        <i class="fas fa-shipping-fast text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Free Shipping</h3>
                    <p class="mb-4 opacity-90">On all electronics & components over $199. No code needed.</p>
                    <button
                        class="bg-white text-electric px-6 py-2 rounded-lg font-semibold hover:bg-gray-100 transition duration-300 transform hover:scale-105">
                        Learn More
                    </button>
                </div>

                <!-- Deal 3 -->
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center border border-white/20 transform hover:scale-105 transition duration-300 hover:shadow-lg hover:shadow-accent/50"
                    style="animation-delay: 0.2s;">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 animate-bounce-in"
                        style="animation-delay: 0.2s;">
                        <i class="fas fa-graduation-cap text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Student Program</h3>
                    <p class="mb-4 opacity-90">15% off for students & educators on robotics kits.</p>
                    <button
                        class="bg-white text-electric px-6 py-2 rounded-lg font-semibold hover:bg-gray-100 transition duration-300 transform hover:scale-105">
                        Get Discount
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Value Propositions -->
    <section class="py-16 bg-white dark:bg-gray-900 transition duration-300">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800 dark:text-white animate-slide-in-left">Why
                Choose RoboMart</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Value 1 -->
                <div class="text-center transform hover:scale-110 transition duration-300 animate-bounce-in">
                    <div class="w-16 h-16 bg-electric/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-cube text-electric text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Premium Components</h3>
                    <p class="text-gray-600 dark:text-gray-400">Authentic & verified electronics</p>
                </div>

                <!-- Value 2 -->
                <div class="text-center transform hover:scale-110 transition duration-300 animate-bounce-in"
                    style="animation-delay: 0.1s;">
                    <div class="w-16 h-16 bg-electric/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shipping-fast text-electric text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Fast Shipping</h3>
                    <p class="text-gray-600 dark:text-gray-400">Worldwide delivery available</p>
                </div>

                <!-- Value 3 -->
                <div class="text-center transform hover:scale-110 transition duration-300 animate-bounce-in"
                    style="animation-delay: 0.2s;">
                    <div class="w-16 h-16 bg-electric/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-book text-electric text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Tech Support</h3>
                    <p class="text-gray-600 dark:text-gray-400">Expert documentation & guides</p>
                </div>

                <!-- Value 4 -->
                <div class="text-center transform hover:scale-110 transition duration-300 animate-bounce-in"
                    style="animation-delay: 0.3s;">
                    <div class="w-16 h-16 bg-electric/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-undo-alt text-electric text-2xl"></i>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Easy Returns</h3>
                    <p class="text-gray-600 dark:text-gray-400">30-day money-back guarantee</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-16 bg-gray-50 dark:bg-gray-800 transition duration-300">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800 dark:text-white animate-text-glow">What Tech
                Experts Say</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-md dark:shadow-xl dark:shadow-black/30 transform hover:scale-105 transition duration-300 border-l-4 border-electric">
                    <div class="flex items-center mb-4">
                        <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80"
                            alt="Expert" class="w-12 h-12 rounded-full object-cover mr-4">
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-white">Dr. Alex Chen</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Robotics Engineer</p>
                        </div>
                    </div>
                    <div class="flex text-yellow-400 mb-2">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">"The robotics kits from RoboMart are exceptional! The
                        quality components and detailed documentation make it perfect for both students and
                        professionals. Highly recommend!"</p>
                </div>

                <!-- Testimonial 2 -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-md dark:shadow-xl dark:shadow-black/30 transform hover:scale-105 transition duration-300 border-l-4 border-tech"
                    style="animation-delay: 0.1s;">
                    <div class="flex items-center mb-4">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80"
                            alt="Expert" class="w-12 h-12 rounded-full object-cover mr-4">
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-white">Prof. Maya Patel</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">AI Researcher</p>
                        </div>
                    </div>
                    <div class="flex text-yellow-400 mb-2">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">"RoboMart's AI development kits have revolutionized my
                        research lab. Fast shipping, excellent customer support, and cutting-edge technology. Best
                        supplier in the market!"</p>
                </div>

                <!-- Testimonial 3 -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-md dark:shadow-xl dark:shadow-black/30 transform hover:scale-105 transition duration-300 border-l-4 border-accent"
                    style="animation-delay: 0.2s;">
                    <div class="flex items-center mb-4">
                        <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80"
                            alt="Expert" class="w-12 h-12 rounded-full object-cover mr-4">
                        <div>
                            <h4 class="font-semibold text-gray-900 dark:text-white">James Rivera</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Tech Entrepreneur</p>
                        </div>
                    </div>
                    <div class="flex text-yellow-400 mb-2">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">"I've been sourcing components from RoboMart for 3
                        years. Their electronics selection is unmatched, prices are competitive, and the technical
                        support is outstanding!"</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="py-12 bg-gradient-to-r from-electric to-tech text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-2xl font-bold mb-2 animate-text-glow">Stay Updated with Tech Innovations</h2>
            <p class="mb-6 max-w-2xl mx-auto animate-slide-up">Subscribe to our newsletter and be the first to know
                about new robotics kits, AI modules, exclusive deals, and tech insights.</p>
            <div class="max-w-md mx-auto flex transform hover:scale-105 transition duration-300">
                <input type="email" placeholder="Your email address"
                    class="flex-1 px-4 py-3 rounded-l-lg text-gray-800 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-accent">
                <button
                    class="bg-accent text-white px-6 py-3 rounded-r-lg font-semibold hover:bg-yellow-500 transition duration-300 transform hover:scale-105">
                    Subscribe
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
