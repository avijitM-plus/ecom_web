<?php
/**
 * Coupon Management
 */
require_once '../../config.php';

$page_title = 'Coupon Management';

// Handle Actions
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $code = strtoupper(trim(sanitize_input($_POST['code'])));
        $type = $_POST['type'];
        $value = floatval($_POST['value']);
        $min_spend = floatval($_POST['min_spend']);
        $expiry = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
        $usage_limit = intval($_POST['usage_limit']);
        
        $res = create_coupon($pdo, $code, $type, $value, $min_spend, $expiry, $usage_limit);
        if ($res['success']) {
            $success = 'Coupon created successfully';
        } else {
            $error = $res['message'];
        }
    }
}

if (isset($_GET['delete'])) {
    $res = delete_coupon($pdo, intval($_GET['delete']));
    if ($res['success']) {
        header('Location: index.php?success=Deleted successfully');
        exit;
    } else {
        $error = $res['message'];
    }
}

if (isset($_GET['success'])) $success = $_GET['success'];

$coupons = get_all_coupons($pdo);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Coupons</h1>
        <p class="text-muted mb-0">Manage discounts and promotions</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCouponModal">
        <i class="bi bi-plus-lg me-2"></i>Add Coupon
    </button>
</div>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($success); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="table-container">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Discount</th>
                    <th>Min Spend</th>
                    <th>Usage</th>
                    <th>Expiry</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($coupons as $c): ?>
                <tr>
                    <td><span class="badge bg-light text-dark font-monospace border"><?php echo htmlspecialchars($c['code']); ?></span></td>
                    <td>
                        <?php echo $c['type'] === 'percent' ? $c['value'] . '%' : '৳' . number_format($c['value'], 2); ?>
                    </td>
                    <td>৳<?php echo number_format($c['min_spend'], 2); ?></td>
                    <td>
                        <?php echo $c['used_count']; ?> 
                        <?php if ($c['usage_limit'] > 0) echo '/ ' . $c['usage_limit']; ?>
                    </td>
                    <td>
                        <?php if ($c['expiry_date']): ?>
                            <span class="<?php echo strtotime($c['expiry_date']) < time() ? 'text-danger' : ''; ?>">
                                <?php echo date('M d, Y', strtotime($c['expiry_date'])); ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">No expiry</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$c['is_active']): ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php elseif ($c['expiry_date'] && strtotime($c['expiry_date']) < time()): ?>
                            <span class="badge bg-danger">Expired</span>
                        <?php else: ?>
                            <span class="badge bg-success">Active</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?delete=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this coupon?');">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($coupons)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-muted">No coupons found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-light">Create New Coupon</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body text-light">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label class="form-label">Coupon Code</label>
                        <input type="text" name="code" class="form-control" required placeholder="e.g. SUMMER2024" style="text-transform: uppercase;">
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="percent">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (৳)</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Value</label>
                            <input type="number" name="value" class="form-control" required step="0.01" min="0">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Minimum Spend (৳)</label>
                        <input type="number" name="min_spend" class="form-control" value="0" step="0.01" min="0">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Usage Limit (0 for unlimited)</label>
                        <input type="number" name="usage_limit" class="form-control" value="0" min="0">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
