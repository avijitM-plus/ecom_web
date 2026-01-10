<?php
/**
 * Newsletter Subscribers Management
 * RoboMart E-commerce Platform
 */

require_once '../config.php';

// Check admin permission
if (!check_permission('admin')) {
    redirect('../index.php?error=Access denied');
}

$page_title = 'Newsletter Subscribers';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    
    if ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Subscriber deleted successfully.";
    } elseif ($action === 'toggle_status' && $id > 0) {
        $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Subscriber status updated.";
    } elseif ($action === 'export') {
        // Export to CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="newsletter_subscribers_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Email', 'Status', 'Subscribed At', 'IP Address']);
        
        $stmt = $pdo->query("SELECT * FROM newsletter_subscribers ORDER BY subscribed_at DESC");
        while ($row = $stmt->fetch()) {
            fputcsv($output, [
                $row['id'],
                $row['email'],
                $row['is_active'] ? 'Active' : 'Inactive',
                $row['subscribed_at'],
                $row['ip_address']
            ]);
        }
        fclose($output);
        exit;
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$where = [];
$params = [];

if ($status_filter === 'active') {
    $where[] = "is_active = 1";
} elseif ($status_filter === 'inactive') {
    $where[] = "is_active = 0";
}

if ($search) {
    $where[] = "email LIKE ?";
    $params[] = "%{$search}%";
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get stats
$total_subscribers = $pdo->query("SELECT COUNT(*) FROM newsletter_subscribers")->fetchColumn();
$active_subscribers = $pdo->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE is_active = 1")->fetchColumn();
$new_this_month = $pdo->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE subscribed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();

// Get subscribers
$sql = "SELECT * FROM newsletter_subscribers $where_clause ORDER BY subscribed_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$subscribers = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Newsletter Subscribers</h1>
        <p class="text-muted mb-0">Manage your newsletter mailing list</p>
    </div>
    <form method="POST" class="d-inline">
        <input type="hidden" name="action" value="export">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-download me-2"></i>Export CSV
        </button>
    </form>
</div>

<?php if (isset($success)): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card text-center p-4">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary mx-auto mb-3" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-envelope fs-4"></i>
            </div>
            <h3 class="fw-bold mb-1"><?php echo number_format($total_subscribers); ?></h3>
            <p class="text-muted mb-0">Total Subscribers</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card text-center p-4">
            <div class="stat-icon bg-success bg-opacity-10 text-success mx-auto mb-3" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-check-circle fs-4"></i>
            </div>
            <h3 class="fw-bold mb-1"><?php echo number_format($active_subscribers); ?></h3>
            <p class="text-muted mb-0">Active Subscribers</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card text-center p-4">
            <div class="stat-icon bg-info bg-opacity-10 text-info mx-auto mb-3" style="width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-graph-up fs-4"></i>
            </div>
            <h3 class="fw-bold mb-1"><?php echo number_format($new_this_month); ?></h3>
            <p class="text-muted mb-0">New This Month</p>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="table-container p-3 mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Search Email</label>
            <input type="text" name="search" class="form-control" 
                   placeholder="Search by email..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All</option>
                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-outline-primary">
                <i class="bi bi-search me-1"></i>Filter
            </button>
            <a href="index.php" class="btn btn-outline-secondary ms-2">Reset</a>
        </div>
    </form>
</div>

<!-- Subscribers Table -->
<div class="table-container">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Subscribed</th>
                    <th>IP Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($subscribers)): ?>
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="bi bi-envelope-x fs-1 d-block mb-2"></i>
                        No subscribers found
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($subscribers as $sub): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary bg-opacity-10 text-primary me-3" 
                                 style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <?php echo strtoupper(substr($sub['email'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="fw-medium"><?php echo htmlspecialchars($sub['email']); ?></div>
                                <small class="text-muted">#<?php echo $sub['id']; ?></small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if ($sub['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div><?php echo date('M d, Y', strtotime($sub['subscribed_at'])); ?></div>
                        <small class="text-muted"><?php echo date('h:i A', strtotime($sub['subscribed_at'])); ?></small>
                    </td>
                    <td>
                        <span class="text-muted"><?php echo htmlspecialchars($sub['ip_address'] ?? 'N/A'); ?></span>
                    </td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?php echo $sub['id']; ?>">
                            <input type="hidden" name="action" value="toggle_status">
                            <button type="submit" class="btn btn-sm btn-outline-<?php echo $sub['is_active'] ? 'warning' : 'success'; ?>" 
                                    title="<?php echo $sub['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                <i class="bi bi-<?php echo $sub['is_active'] ? 'pause-circle' : 'play-circle'; ?>"></i>
                            </button>
                        </form>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this subscriber?');">
                            <input type="hidden" name="id" value="<?php echo $sub['id']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if (count($subscribers) > 0): ?>
    <div class="p-3 border-top border-secondary text-muted small">
        Showing <?php echo count($subscribers); ?> subscriber(s)
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
