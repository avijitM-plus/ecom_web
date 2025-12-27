<?php
/**
 * User Registration Page
 * RoboMart E-commerce Platform
 */

require_once 'config.php';

$error = '';
$success = '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms_accepted = isset($_POST['terms']);
    
    // Validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif (!validate_email($email)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (($password_validation = validate_password($password)) !== true) {
        $error = $password_validation;
    } elseif (!$terms_accepted) {
        $error = "You must accept the Terms of Service and Privacy Policy.";
    } else {
        // Check if email already exists
        $existing_user = get_user_by_email($pdo, $email);
        
        if ($existing_user) {
            $error = "An account with this email already exists.";
        } else {
            // Create new user
            try {
                $password_hash = hash_password($password);
                
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)");
                $stmt->execute([$full_name, $email, $password_hash]);
                
                $success = "Account created successfully! You can now log in.";
                
                // Optional: Auto-login after registration
                // $user_id = $pdo->lastInsertId();
                // create_session($user_id, $email, $full_name);
                // redirect('account.php');
                
            } catch (PDOException $e) {
                error_log("Registration Error: " . $e->getMessage());
                $error = "An error occurred during registration. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - RoboMart</title>
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
        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s;
        }
        .strength-weak {
            background-color: #ef4444;
            width: 33%;
        }
        .strength-medium {
            background-color: #f59e0b;
            width: 66%;
        }
        .strength-strong {
            background-color: #10b981;
            width: 100%;
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
    <!-- Standard Header with Dark Toggle -->
    <header class="sticky top-0 z-50 bg-white dark:bg-gray-900 shadow-sm dark:shadow-lg transition duration-300">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <a href="index.html" class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-full gradient-bg flex items-center justify-center">
                        <i class="fas fa-bolt text-white text-lg"></i>
                    </div>
                    <span class="text-xl font-bold text-gray-800 dark:text-white">RoboMart</span>
                </a>

                <div class="hidden md:flex flex-1 max-w-2xl mx-8">
                    <div class="relative w-full">
                        <input type="text" placeholder="Search robotics, electronics, IoT devices..." 
                               class="w-full pl-4 pr-10 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-electric focus:border-transparent dark:placeholder-gray-400">
                        <button class="absolute right-0 top-0 h-full px-4 text-gray-500 dark:text-gray-400 hover:text-electric dark:hover:text-electric">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center space-x-6">
                    <label class="dark-mode-toggle">
                        <input type="checkbox" id="darkModeToggle">
                        <span class="slider"></span>
                    </label>
                    <div class="hidden md:flex items-center space-x-1 text-gray-700 hover:text-electric cursor-pointer dark:text-gray-300 dark:hover:text-electric">
                        <i class="far fa-user text-lg"></i>
                        <span class="font-medium"><a href="login.php">Account</a></span>
                    </div>
                    <div class="flex items-center space-x-1 text-gray-700 hover:text-electric cursor-pointer relative dark:text-gray-300 dark:hover:text-electric">
                        <i class="fas fa-shopping-cart text-lg"></i>
                        <span class="font-medium">Cart</span>
                        <span class="absolute -top-2 -right-2 bg-accent text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
                    </div>
                    <button id="mobileMenuButton" class="md:hidden text-gray-700 dark:text-gray-300" aria-controls="mobileMenu" aria-expanded="false" aria-label="Toggle menu">
                        <i class="fas fa-bars text-xl" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <nav class="hidden md:flex py-3 border-t border-gray-200 dark:border-gray-700">
                <div class="flex space-x-8">
                    <a href="index.html" class="text-gray-700 dark:text-gray-300 hover:text-electric dark:hover:text-electric font-medium transition">Home</a>
                    <a href="products.html" class="text-gray-700 dark:text-gray-300 hover:text-electric dark:hover:text-electric font-medium transition">Robotics</a>
                    <a href="products.html" class="text-gray-700 dark:text-gray-300 hover:text-electric dark:hover:text-electric font-medium transition">Microcontrollers</a>
                    <a href="products.html" class="text-gray-700 dark:text-gray-300 hover:text-electric dark:hover:text-electric font-medium transition">IoT Devices</a>
                    <a href="products.html" class="text-gray-700 dark:text-gray-300 hover:text-electric dark:hover:text-electric font-medium transition">AI & ML</a>
                    <a href="products.html" class="text-electric dark:text-electric font-medium">Flash Sale</a>
                </div>
            </nav>

            <div id="mobileMenu" class="md:hidden hidden border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                <div class="px-4 pt-4 pb-6 space-y-4">
                    <a href="index.html" class="block text-gray-700 dark:text-gray-300 hover:text-electric">Home</a>
                    <a href="products.html" class="block text-gray-700 dark:text-gray-300 hover:text-electric">Robotics</a>
                    <a href="products.html" class="block text-gray-700 dark:text-gray-300 hover:text-electric">Microcontrollers</a>
                    <a href="products.html" class="block text-electric dark:text-electric font-medium">Flash Sale</a>
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                        <a href="login.php" class="block text-gray-700 dark:text-gray-300 py-2">Sign In</a>
                        <a href="account.html" class="block text-gray-700 dark:text-gray-300 py-2">Account</a>
                        <a href="cart.html" class="block text-gray-700 dark:text-gray-300 py-2">Cart</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Signup Form -->
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <!-- Signup Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Create Account</h1>
                    <p class="text-gray-600 dark:text-gray-400">Join RoboMart and explore amazing robotics products</p>
                </div>

                <!-- Display Messages -->
                <?php if ($error): ?>
                    <?php echo display_error($error); ?>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <?php echo display_success($success); ?>
                    <div class="text-center mb-4">
                        <a href="login.php" class="text-electric hover:text-tech font-semibold">Click here to login</a>
                    </div>
                <?php endif; ?>

                <!-- Signup Form -->
                <form method="POST" action="register.php" id="signupForm" class="space-y-5">
                    <!-- Full Name Input -->
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Full Name</label>
                        <input type="text" name="full_name" placeholder="John Doe" required
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white form-input">
                    </div>

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
                        <input type="password" name="password" id="password" placeholder="••••••••" required
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white form-input">
                        <div class="mt-2 flex space-x-1">
                            <div class="password-strength" id="passwordStrength"></div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2" id="passwordHint">At least 8 characters with uppercase, lowercase, and numbers</p>
                    </div>

                    <!-- Confirm Password Input -->
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Confirm Password</label>
                        <input type="password" name="confirm_password" placeholder="••••••••" required
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white form-input">
                    </div>

                    <!-- Terms & Privacy -->
                    <label class="flex items-start">
                        <input type="checkbox" name="terms" class="w-4 h-4 rounded mt-1" required>
                        <span class="ml-3 text-gray-600 dark:text-gray-400 text-sm">
                            I agree to the <a href="terms.html" class="text-electric hover:text-tech font-semibold">Terms of Service</a> and 
                            <a href="privacy.html" class="text-electric hover:text-tech font-semibold">Privacy Policy</a>
                        </span>
                    </label>

                    <!-- Subscribe to Newsletter -->
                    <label class="flex items-start">
                        <input type="checkbox" name="newsletter" class="w-4 h-4 rounded mt-1">
                        <span class="ml-3 text-gray-600 dark:text-gray-400 text-sm">
                            Subscribe to our newsletter for exclusive deals and product launches
                        </span>
                    </label>

                    <!-- Signup Button -->
                    <button type="submit" class="w-full btn-gradient text-white py-3 rounded-lg font-semibold">
                        Create Account
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">Or sign up with</span>
                    </div>
                </div>

                <!-- Social Signup -->
                <div class="grid grid-cols-2 gap-4">
                    <button class="flex items-center justify-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <i class="fab fa-google text-lg text-red-500"></i>
                    </button>
                    <button class="flex items-center justify-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <i class="fab fa-github text-lg text-gray-800 dark:text-white"></i>
                    </button>
                </div>

                <!-- Login Link -->
                <p class="text-center mt-8 text-gray-600 dark:text-gray-400">
                    Already have an account? 
                    <a href="login.php" class="text-electric hover:text-tech font-semibold">Sign in here</a>
                </p>
            </div>

            <!-- Security Info -->
            <div class="mt-8 p-6 bg-gradient-to-r from-electric/10 to-tech/10 dark:from-electric/20 dark:to-tech/20 rounded-xl border border-electric/30">
                <p class="text-gray-700 dark:text-gray-300 text-sm">
                    <i class="fas fa-lock text-electric mr-2"></i>
                    Your data is protected with industry-standard encryption.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('passwordStrength');
        const strengthHint = document.getElementById('passwordHint');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            let hint = '';

            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            strengthBar.className = 'password-strength';
            if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
                hint = 'Weak password';
            } else if (strength <= 3) {
                strengthBar.classList.add('strength-medium');
                hint = 'Medium strength';
            } else {
                strengthBar.classList.add('strength-strong');
                hint = 'Strong password';
            }

            strengthHint.textContent = hint;
        });

        // Dark mode + mobile menu handling
        (function(){
            const darkModeToggle = document.getElementById('darkModeToggle');
            const htmlElement = document.documentElement;
            const isDark = localStorage.getItem('darkMode') === 'true' || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches && localStorage.getItem('darkMode') !== 'false');
            if (isDark) { htmlElement.classList.add('dark'); if (darkModeToggle) darkModeToggle.checked = true; }
            if (darkModeToggle) {
                darkModeToggle.addEventListener('change', function(){
                    if (this.checked) { htmlElement.classList.add('dark'); localStorage.setItem('darkMode','true'); }
                    else { htmlElement.classList.remove('dark'); localStorage.setItem('darkMode','false'); }
                });
            }

            const mobileMenuButton = document.getElementById('mobileMenuButton');
            const mobileMenu = document.getElementById('mobileMenu');
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    const expanded = this.getAttribute('aria-expanded') === 'true';
                    this.setAttribute('aria-expanded', String(!expanded));
                    mobileMenu.classList.toggle('hidden');
                    const icon = this.querySelector('i.fas');
                    if (icon) { if (!expanded) { icon.classList.remove('fa-bars'); icon.classList.add('fa-times'); } else { icon.classList.remove('fa-times'); icon.classList.add('fa-bars'); } }
                });
            }
        })();
    </script>
    <script src="script.js"></script>
</body>
</html>
