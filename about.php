<?php
$page_title = "About Us";
require_once 'includes/header.php';
?>
<main class="container mx-auto px-4 py-12 transition-all duration-300">
    <h1 class="text-3xl font-bold mb-4 dark:text-white">About RoboMart</h1>
    <p class="text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
        RoboMart is a student-led project showcasing an e-commerce front-end for robotics, electronics, and IoT devices. 
        It demonstrates modern UI techniques, dark mode support, and interactive components like carousels and product grids.
    </p>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700">
            <div class="w-12 h-12 bg-electric/10 rounded-full flex items-center justify-center mb-4 text-electric text-xl"><i class="fas fa-rocket"></i></div>
            <h3 class="text-xl font-bold mb-2 dark:text-white">Innovation</h3>
            <p class="text-gray-600 dark:text-gray-400">Bringing the latest tech to your doorstep.</p>
        </div>
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700">
            <div class="w-12 h-12 bg-tech/10 rounded-full flex items-center justify-center mb-4 text-tech text-xl"><i class="fas fa-users"></i></div>
            <h3 class="text-xl font-bold mb-2 dark:text-white">Community</h3>
            <p class="text-gray-600 dark:text-gray-400">Built by students, for students and hobbyists.</p>
        </div>
        <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700">
            <div class="w-12 h-12 bg-accent/10 rounded-full flex items-center justify-center mb-4 text-accent text-xl"><i class="fas fa-check-circle"></i></div>
            <h3 class="text-xl font-bold mb-2 dark:text-white">Quality</h3>
            <p class="text-gray-600 dark:text-gray-400">Verified components and reliable resources.</p>
        </div>
    </div>
</main>
<?php require_once 'includes/footer.php'; ?>
