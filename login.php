<?php
/**
 * User Login Page
 * RoboMart E-commerce Platform
 */

require_once 'config.php';

$error = '';

// Redirect if already logged in
if (is_logged_in()) {
    redirect('account.php');
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    // Validation
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } elseif (!validate_email($email)) {
        $error = "Please enter a valid email address.";
    } else {
        // Find user by email
        $user = get_user_by_email($pdo, $email);
        
        if ($user && verify_password($password, $user['password_hash'])) {
            // Successful login
            create_session($user['id'], $user['email'], $user['full_name']);
            
            // Handle "Remember Me" functionality
            if ($remember_me) {
                // Set cookie for 30 days
                $cookie_token = bin2hex(random_bytes(32));
                setcookie('remember_token', $cookie_token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                
                // Store token in database (optional enhancement)
                // You would need to add this to the user_sessions table
            }
            

            redirect('account.php');
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RoboMart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #06b6d4 0%, #8b5cf6 100%);
        }
        .form-input:focus {
            outline: none;
            border-color: #06b6d4;
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.1);
        }
        .btn-gradient {
            background: linear-gradient(135deg, #06b6d4 0%, #8b5cf6 100%);
            transition: all 0.3s ease;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(6, 182, 212, 0.3);
        }
        .dark-mode-toggle { 
            position: relative; 
            display: inline-block; 
            width: 60px; 
            height: 30px; 
        }
        .dark-mode-toggle input { 
            opacity: 0; 
            width: 0; 
            height: 0; 
        }
        .slider { 
            position: absolute; 
            cursor: pointer; 
            top: 0; 
            left: 0; 
            right: 0; 
            bottom: 0; 
            background-color: #ccc; 
            transition: 0.4s; 
            border-radius: 30px; 
        }
        .slider:before { 
            position: absolute; 
            content: ""; 
            height: 22px; 
            width: 22px; 
            left: 4px; 
            bottom: 4px; 
            background-color: white; 
            transition: 0.4s; 
            border-radius: 50%; 
        }
        input:checked + .slider { 
            background-color: #06b6d4; 
        }
        input:checked + .slider:before { 
            transform: translateX(30px); 
        }

    </style>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        electric: '#06b6d4',
                        tech: '#8b5cf6',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-950">
    <!-- Navigation -->
    <header class="sticky top-0 z-50 bg-white dark:bg-gray-900 shadow-sm dark:shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <a href="index.php" class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-full gradient-bg flex items-center justify-center">
                        <i class="fas fa-bolt text-white text-lg"></i>
                    </div>
                    <span class="text-xl font-bold text-gray-800 dark:text-white">RoboMart</span>
                </a>
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="text-gray-700 dark:text-gray-300 hover:text-electric">Back to Home</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Login Form -->
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <!-- Login Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Welcome Back</h1>
                    <p class="text-gray-600 dark:text-gray-400">Sign in to your RoboMart account</p>
                </div>

                <!-- Display Error Message -->
                <?php if ($error): ?>
                    <?php echo display_error($error); ?>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" action="login.php" id="loginForm" class="space-y-6">
                    <!-- Email Input -->
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Email Address</label>
                        <input type="email" name="email" placeholder="you@example.com" required
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white form-input">
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Password</label>
                        <input type="password" name="password" placeholder="••••••••" required
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white form-input">
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember_me" class="w-4 h-4 rounded">
                            <span class="ml-2 text-gray-600 dark:text-gray-400">Remember me</span>
                        </label>
                        <a href="#" class="text-electric hover:text-tech font-semibold">Forgot password?</a>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="w-full btn-gradient text-white py-3 rounded-lg font-semibold">
                        Sign In
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">Or continue with</span>
                    </div>
                </div>

                <!-- Social Login -->
                <div class="grid grid-cols-2 gap-4">
                    <button class="flex items-center justify-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <i class="fab fa-google text-lg text-red-500"></i>
                    </button>
                    <button class="flex items-center justify-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <i class="fab fa-github text-lg text-gray-800 dark:text-white"></i>
                    </button>
                </div>

                <!-- Sign Up Link -->
                <p class="text-center mt-8 text-gray-600 dark:text-gray-400">
                    Don't have an account? 
                    <a href="register.php" class="text-electric hover:text-tech font-semibold">Sign up here</a>
                </p>
            </div>

            <!-- Additional Info -->
            <div class="mt-8 p-6 bg-gradient-to-r from-electric/10 to-tech/10 dark:from-electric/20 dark:to-tech/20 rounded-xl border border-electric/30">
                <p class="text-gray-700 dark:text-gray-300 text-sm">
                    <i class="fas fa-shield-alt text-electric mr-2"></i>
                    Your login is secure. We never share your data.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Dark mode toggle for consistency
        const htmlElement = document.documentElement;
        const isDarkMode = localStorage.getItem('darkMode') === 'true';
        if (isDarkMode) {
            htmlElement.classList.add('dark');
        }
    </script>
</body>
</html>
