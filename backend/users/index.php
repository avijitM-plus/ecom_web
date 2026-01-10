<?php
/**
 * User Management - List All Users
 * RoboMart E-commerce Platform
 */

require_once '../config.php';

$page_title = 'User Management';

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$role = isset($_GET['role']) ? sanitize_input($_GET['role']) : '';
$status = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

// Get users with pagination
$result = get_all_users($pdo, $page, ADMIN_PER_PAGE, $search, $role, $status);
$users = $result['users'];
$total_pages = $result['pages'];
$current_page = $result['current_page'];
$total_users = $result['total'];

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_result = delete_user($pdo, $_GET['delete']);
    if ($delete_result['success']) {
        header('Location: index.php?success=' . urlencode($delete_result['message']));
        exit;
    } else {
        $error = $delete_result['message'];
    }
}

// Get success/error messages
$success = isset($_GET['success']) ? sanitize_input($_GET['success']) : '';
$error = isset($error) ? $error : (isset($_GET['error']) ? sanitize_input($_GET['error']) : '');

// Include header
include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">User Management</h1>
        <p class="text-muted mb-0">Manage all registered users</p>
    </div>
    <div>
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Add New User
        </a>
    </div>
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

<!-- Search and Filter -->
<div class="table-container mb-4">
    <div class="p-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Search Users</label>
                <div class="input-group">
                    <span class="input-group-text bg-dark border-secondary"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Name or email..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="1" <?php echo $status === '1' ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo $status === '0' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            
            <?php if ($search || $role || $status !== ''): ?>
            <div class="col-12 mt-2 text-end">
                <a href="index.php" class="text-muted text-decoration-none small">
                    <i class="bi bi-x-lg me-1"></i>Clear Filters
                </a>
                <span class="ms-2 text-muted small border-start ps-2"><?php echo $total_users; ?> results</span>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="table-container">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-people fs-1 d-block mb-3"></i>
                        No users found
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td class="text-muted">#<?php echo $user['id']; ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                            <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                        </div>
                    </td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge <?php echo $user['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="edit.php?id=<?php echo $user['id']; ?>" 
                               class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    title="Delete" onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['full_name'])); ?>')">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="p-3 d-flex justify-content-between align-items-center border-top border-dark">
        <div class="text-muted">
            Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
        </div>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php if ($current_page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
                <?php endif; ?>
                
                <?php 
                $start = max(1, $current_page - 2);
                $end = min($total_pages, $current_page + 2);
                for ($i = $start; $i <= $end; $i++): 
                ?>
                <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role); ?>&status=<?php echo urlencode($status); ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
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
