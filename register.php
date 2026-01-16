<?php
/**
 * User Registration Page
 * RoboMart E-commerce Platform
 */

require_once 'config.php';
require_once 'includes/google-config.php';

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
            // Create new user with verification code
            try {
                $password_hash = hash_password($password);
                $verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $verification_expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, is_active, verification_code, verification_expires_at) VALUES (?, ?, ?, 0, ?, ?)");
                $stmt->execute([$full_name, $email, $password_hash, $verification_code, $verification_expires]);
                
                // Send verification email
                send_verification_email($email, $verification_code, $full_name);
                
                // Store email in session for verification page
                $_SESSION['pending_verification_email'] = $email;
                
                redirect('verify.php');
                
            } catch (PDOException $e) {
                error_log("Registration Error: " . $e->getMessage());
                $error = "An error occurred during registration. Please try again.";
            }
        }
    }
}
?>
<?php
$page_title = "Sign Up";
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
    </style>

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
                        <div class="relative">
                            <input type="password" name="password" id="password" placeholder="••••••••" required
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white form-input pr-12">
                            <button type="button" onclick="togglePassword('password', 'toggleIcon1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                                <i id="toggleIcon1" class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="mt-2 flex space-x-1">
                            <div class="password-strength" id="passwordStrength"></div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2" id="passwordHint">At least 8 characters with uppercase, lowercase, and numbers</p>
                    </div>

                    <!-- Confirm Password Input -->
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Confirm Password</label>
                        <div class="relative">
                            <input type="password" name="confirm_password" id="confirm_password" placeholder="••••••••" required
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white form-input pr-12">
                            <button type="button" onclick="togglePassword('confirm_password', 'toggleIcon2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                                <i id="toggleIcon2" class="fas fa-eye"></i>
                            </button>
                        </div>
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
                <div class="grid grid-cols-1 gap-4">
                    <a href="<?php echo $google_client->createAuthUrl(); ?>" class="flex items-center justify-center px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <i class="fab fa-google text-lg text-red-500"></i>
                    </a>
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

        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

<?php include 'includes/footer.php'; ?>
