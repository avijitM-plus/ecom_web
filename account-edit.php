<?php
/**
 * Edit Profile
 */
require_once 'config.php';

// Require login
require_login();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Fetch current user data
$user = get_user_by_id($pdo, $user_id);
if (!$user) {
    destroy_session();
    redirect('login.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($full_name) || empty($email)) {
        $error = 'Name and email are required.';
    } elseif (!empty($password) && strlen($password) < PASSWORD_MIN_LENGTH) {
        $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Update Profile
        $result = update_profile($pdo, $user_id, $full_name, $email, !empty($password) ? $password : null);
        
        if ($result['success']) {
            // Update Session
            $_SESSION['full_name'] = $full_name;
            $success = 'Profile updated successfully!';
            // Refresh user data
            $user = get_user_by_id($pdo, $user_id);
        } else {
            $error = $result['message'];
        }
    }
}
?>
<?php
$page_title = "Edit Profile";
include 'includes/header.php';
?>

    <main class="container mx-auto px-4 py-12">
        <div class="max-w-2xl mx-auto bg-white dark:bg-gray-800 rounded-xl p-8 shadow-lg">
            <h1 class="text-2xl font-bold mb-6">Edit Profile</h1>
            
            <?php if ($success): ?>
                <div class="bg-green-100 text-green-700 p-4 rounded mb-6"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-100 text-red-700 p-4 rounded mb-6"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-electric dark:bg-gray-700 dark:border-gray-600">
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-electric dark:bg-gray-700 dark:border-gray-600">
                </div>
                
                <hr class="my-6 border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold mb-4">Change Password <span class="text-sm font-normal text-gray-500">(Leave blank to keep current)</span></h3>
                
                <div class="mb-4">
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">New Password</label>
                    <input type="password" name="password" minlength="8"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-electric dark:bg-gray-700 dark:border-gray-600">
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 dark:text-gray-300 font-medium mb-2">Confirm New Password</label>
                    <input type="password" name="confirm_password" minlength="8"
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-electric dark:bg-gray-700 dark:border-gray-600">
                </div>
                
                <div class="flex justify-end space-x-4">
                    <a href="account.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">Cancel</a>
                    <button type="submit" class="px-6 py-2 bg-electric text-white rounded-lg hover:bg-tech transition font-medium">Save Changes</button>
                </div>
            </form>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
