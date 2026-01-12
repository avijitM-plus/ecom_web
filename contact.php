<?php
$page_title = "Contact Us";
require_once 'includes/header.php';
?>
<main class="container mx-auto px-4 py-12 transition-all duration-300">
    <h1 class="text-3xl font-bold mb-4 dark:text-white">Contact Us</h1>
    <p class="text-gray-600 dark:text-gray-400 mb-6">Have a question or need help? Send us a message and we'll get back to you.</p>

    <form id="contactForm" class="max-w-lg space-y-4">
        <div>
            <label class="block text-gray-700 dark:text-gray-300 mb-2">Name</label>
            <input type="text" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-electric focus:border-transparent outline-none transition" required>
        </div>
        <div>
            <label class="block text-gray-700 dark:text-gray-300 mb-2">Email</label>
            <input type="email" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-electric focus:border-transparent outline-none transition" required>
        </div>
        <div>
            <label class="block text-gray-700 dark:text-gray-300 mb-2">Message</label>
            <textarea class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-electric focus:border-transparent outline-none transition" rows="4" required></textarea>
        </div>
        <button type="submit" class="bg-electric text-white px-6 py-2 rounded hover:bg-opacity-90 transition transform hover:scale-105">Send Message</button>
    </form>
</main>

<script>
    document.getElementById('contactForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        alert('Thank you — your message has been received (demo).');
        this.reset();
    });
</script>
<?php require_once 'includes/footer.php'; ?>
