<?php
/**
 * Email Verification Page
 * RoboMart E-commerce Platform
 */

require_once 'config.php';
require_once 'includes/google-config.php';

$error = '';
$success = '';
$email = $_SESSION['pending_verification_email'] ?? '';

// If no pending email, redirect to register
if (empty($email) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('register.php');
}

// Process verification form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? $email);
    $code = sanitize_input($_POST['code'] ?? '');
    
    if (empty($email) || empty($code)) {
        $error = "Please enter both email and verification code.";
    } else {
        // Get user by email
        $user = get_user_by_email($pdo, $email);
        
        if (!$user) {
            $error = "No account found with this email.";
        } elseif ($user['is_active'] == 1) {
            $error = "This account is already verified.";
        } elseif ($user['verification_code'] !== $code) {
            $error = "Invalid verification code.";
        } elseif (strtotime($user['verification_expires_at']) < time()) {
            $error = "Verification code has expired. Please register again.";
        } else {
            // Activate user
            try {
                $stmt = $pdo->prepare("UPDATE users SET is_active = 1, verification_code = NULL, verification_expires_at = NULL WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                // Clear pending email from session
                unset($_SESSION['pending_verification_email']);
                
                // Auto-login
                create_session($user['id'], $user['email'], $user['full_name']);
                redirect('account.php');
                
            } catch (PDOException $e) {
                error_log("Verification Error: " . $e->getMessage());
                $error = "An error occurred. Please try again.";
            }
        }
    }
}

// Handle resend
if (isset($_GET['resend']) && !empty($email)) {
    $user = get_user_by_email($pdo, $email);
    if ($user && $user['is_active'] == 0) {
        $verification_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $verification_expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        $stmt = $pdo->prepare("UPDATE users SET verification_code = ?, verification_expires_at = ? WHERE id = ?");
        $stmt->execute([$verification_code, $verification_expires, $user['id']]);
        
        send_verification_email($email, $verification_code, $user['full_name']);
        $success = "A new verification code has been sent to your email.";
    }
}
?>
<?php
$page_title = "Verify Email";
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
        .code-input {
            letter-spacing: 8px;
            font-size: 24px;
            text-align: center;
            font-weight: bold;
        }
    </style>

    <!-- Verification Form -->
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <!-- Verification Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="text-6xl mb-4">📧</div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Verify Your Email</h1>
                    <p class="text-gray-600 dark:text-gray-400">We've sent a 6-digit verification code to:</p>
                    <p class="text-electric font-semibold mt-2"><?php echo htmlspecialchars($email); ?></p>
                </div>

                <!-- Display Messages -->
                <?php if ($error): ?>
                    <?php echo display_error($error); ?>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <?php echo display_success($success); ?>
                <?php endif; ?>

                <!-- Verification Form -->
                <form method="POST" action="verify.php" class="space-y-5">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                    
                    <!-- Code Input -->
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">Verification Code</label>
                        <input type="text" name="code" maxlength="6" pattern="[0-9]{6}" 
                               placeholder="000000" required
                               class="w-full px-4 py-4 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white form-input code-input">
                    </div>

                    <!-- Verify Button -->
                    <button type="submit" class="w-full btn-gradient text-white py-3 rounded-lg font-semibold">
                        Verify Email
                    </button>
                </form>

                <!-- Resend Link -->
                <div class="text-center mt-6">
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Didn't receive the code? 
                        <a href="verify.php?resend=1" class="text-electric hover:text-tech font-semibold">Resend Code</a>
                    </p>
                </div>

                <!-- Back to Register -->
                <p class="text-center mt-4 text-gray-600 dark:text-gray-400">
                    Wrong email? 
                    <a href="register.php" class="text-electric hover:text-tech font-semibold">Go back to register</a>
                </p>
            </div>

            <!-- Security Info -->
            <div class="mt-8 p-6 bg-gradient-to-r from-electric/10 to-tech/10 dark:from-electric/20 dark:to-tech/20 rounded-xl border border-electric/30">
                <p class="text-gray-700 dark:text-gray-300 text-sm">
                    <i class="fas fa-clock text-electric mr-2"></i>
                    The code expires in 15 minutes for your security.
                </p>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
