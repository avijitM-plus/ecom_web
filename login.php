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
            // Check if user is active
            if (isset($user['is_active']) && $user['is_active'] == 0) {
                $error = "Your account has been deactivated. Please contact support.";
            } else {
                // Successful login - include role in session
                $role = isset($user['role']) ? $user['role'] : 'user';
                create_session($user['id'], $user['email'], $user['full_name'], $role);
                
                // Handle "Remember Me" functionality
                if ($remember_me) {
                    // Set cookie for 30 days
                    $cookie_token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $cookie_token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                }
                
                // Redirect based on role
                if ($role === 'admin') {
                    redirect('backend/index.php');
                } else {
                    redirect('account.php');
                }
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<?php
$page_title = "Login";
$hide_nav = true;
include 'includes/header.php';
?>
    <style>
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
    </style>

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

    <?php include 'includes/footer.php'; ?>
