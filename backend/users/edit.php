<?php
/**
 * User Management - Edit User
 * RoboMart E-commerce Platform
 */

require_once '../config.php';

$page_title = 'Edit User';

// Get user ID from URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$user_id) {
    header('Location: index.php?error=' . urlencode('Invalid user ID'));
    exit;
}

// Get user data
$user = get_user_by_id($pdo, $user_id);
if (!$user) {
    header('Location: index.php?error=' . urlencode('User not found'));
    exit;
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = sanitize_input($_POST['role'] ?? 'user');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($full_name) || empty($email)) {
        $error = 'Name and email are required.';
    } elseif (!validate_email($email)) {
        $error = 'Please enter a valid email address.';
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!empty($password)) {
        $password_validation = validate_password($password);
        if ($password_validation !== true) {
            $error = $password_validation;
        }
    }
    
    if (empty($error)) {
        // Update user
        $result = update_user($pdo, $user_id, $full_name, $email, $role, $is_active, $password ?: null);
        if ($result['success']) {
            $success = $result['message'];
            // Refresh user data
            $user = get_user_by_id($pdo, $user_id);
        } else {
            $error = $result['message'];
        }
    }
}

// Include header
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Page Header -->
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-2">
            <li class="breadcrumb-item"><a href="../index.php" class="text-muted">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php" class="text-muted">Users</a></li>
            <li class="breadcrumb-item active">Edit User</li>
        </ol>
    </nav>
    <h1 class="page-title">Edit User</h1>
</div>

<!-- Messages -->
<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Edit User Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="table-container p-4">
            <form method="POST" action="">
                <div class="row g-4">
                    <!-- Full Name -->
                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" 
                               placeholder="Enter full name" required
                               value="<?php echo htmlspecialchars($user['full_name']); ?>">
                    </div>
                    
                    <!-- Email -->
                    <div class="col-md-6">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" 
                               placeholder="Enter email address" required
                               value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                    
                    <!-- New Password -->
                    <div class="col-md-6">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" 
                               placeholder="Leave blank to keep current">
                        <small class="text-muted">Only fill if changing password</small>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="col-md-6">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" 
                               placeholder="Confirm new password">
                    </div>
                    
                    <!-- Role -->
                    <div class="col-md-6">
                        <label class="form-label">User Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>
                                User
                            </option>
                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>
                                Administrator
                            </option>
                        </select>
                    </div>
                    
                    <!-- Active Status -->
                    <div class="col-md-6">
                        <label class="form-label d-block">Account Status</label>
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active"
                                   <?php echo isset($user['is_active']) && $user['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">Active Account</label>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="mt-4 pt-3 border-top border-dark">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-2"></i>Save Changes
                    </button>
                    <a href="index.php" class="btn btn-outline-light ms-2">
                        <i class="bi bi-x-lg me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- User Info Sidebar -->
    <div class="col-lg-4">
        <div class="table-container p-4">
            <div class="text-center mb-4">
                <div class="avatar mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
                <h5 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h5>
                <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                    <?php echo ucfirst($user['role']); ?>
                </span>
            </div>
            
            <hr class="border-dark">
            
            <div class="small">
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">User ID</span>
                    <span>#<?php echo $user['id']; ?></span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Joined</span>
                    <span><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                </div>
            </div>
        </div>
        
        <?php if ($user_id != $_SESSION['user_id']): ?>
        <div class="table-container p-4 mt-3 border-danger">
            <h6 class="text-danger mb-3"><i class="bi bi-exclamation-triangle me-2"></i>Danger Zone</h6>
            <p class="small text-muted mb-3">Delete this user account permanently. This action cannot be undone.</p>
            <button type="button" class="btn btn-outline-danger btn-sm w-100"
                    onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['full_name'])); ?>')">
                <i class="bi bi-trash me-2"></i>Delete User
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-danger">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
                <p class="text-danger small mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="deleteConfirmBtn" class="btn btn-danger">Delete User</a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(userId, userName) {
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deleteConfirmBtn').href = 'index.php?delete=' + userId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>
