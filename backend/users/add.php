<?php
/**
 * User Management - Add New User
 * RoboMart E-commerce Platform
 */

require_once '../config.php';

$page_title = 'Add New User';

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = sanitize_input($_POST['role'] ?? 'user');
    
    // Validation
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!validate_email($email)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        $password_validation = validate_password($password);
        if ($password_validation !== true) {
            $error = $password_validation;
        } else {
            // Create user
            $result = create_user($pdo, $full_name, $email, $password, $role);
            if ($result['success']) {
                header('Location: index.php?success=' . urlencode('User created successfully!'));
                exit;
            } else {
                $error = $result['message'];
            }
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
            <li class="breadcrumb-item active">Add New</li>
        </ol>
    </nav>
    <h1 class="page-title">Add New User</h1>
</div>

<!-- Error Message -->
<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Add User Form -->
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
                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                    </div>
                    
                    <!-- Email -->
                    <div class="col-md-6">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" 
                               placeholder="Enter email address" required
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    
                    <!-- Password -->
                    <div class="col-md-6">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" 
                               placeholder="Enter password" required>
                        <small class="text-muted">Min 8 characters, uppercase, lowercase, and number</small>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" class="form-control" 
                               placeholder="Confirm password" required>
                    </div>
                    
                    <!-- Role -->
                    <div class="col-md-6">
                        <label class="form-label">User Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="user" <?php echo (isset($_POST['role']) && $_POST['role'] === 'user') ? 'selected' : ''; ?>>
                                User
                            </option>
                            <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>
                                Administrator
                            </option>
                        </select>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="mt-4 pt-3 border-top border-dark">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Create User
                    </button>
                    <a href="index.php" class="btn btn-outline-light ms-2">
                        <i class="bi bi-x-lg me-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Sidebar Help -->
    <div class="col-lg-4">
        <div class="table-container p-4">
            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Password Requirements</h6>
            <ul class="small text-muted mb-0">
                <li>Minimum 8 characters</li>
                <li>At least one uppercase letter (A-Z)</li>
                <li>At least one lowercase letter (a-z)</li>
                <li>At least one number (0-9)</li>
            </ul>
        </div>
        
        <div class="table-container p-4 mt-3">
            <h6 class="mb-3"><i class="bi bi-shield-check me-2"></i>User Roles</h6>
            <p class="small text-muted mb-2">
                <strong class="text-primary">Admin:</strong> Full access to admin panel
            </p>
            <p class="small text-muted mb-0">
                <strong class="text-info">User:</strong> Regular store access only
            </p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
