<?php
/**
 * Inventory Logs
 * RoboMart E-commerce Platform
 */

require_once '../config.php';

// Check permission
if (!check_permission('admin') && !check_permission('warehouse_manager')) {
    redirect('../index.php?error=Access denied');
}

$page_title = 'Inventory Logs';
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;

// Helper to get product name if filtering
$filter_product_name = '';
if ($product_id) {
    $p = get_product_by_id($pdo, $product_id);
    if ($p) $filter_product_name = $p['name'];
}

$logs = get_inventory_logs($pdo, $product_id, 50);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Inventory Logs</h1>
        <p class="text-muted mb-0">
            <?php if ($filter_product_name): ?>
                History for: <strong class="text-light"><?php echo htmlspecialchars($filter_product_name); ?></strong>
            <?php else: ?>
                Recent stock adjustments
            <?php endif; ?>
        </p>
    </div>
    <div>
        <?php if ($filter_product_name): ?>
            <a href="logs.php" class="btn btn-outline-light">View All Logs</a>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary">Back to Inventory</a>
    </div>
</div>

<div class="table-container">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>User</th>
                    <th>Change</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">No logs found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="text-nowrap text-muted"><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                    <td>
                        <a href="index.php?search=<?php echo urlencode($log['product_name']); ?>" class="text-decoration-none text-light fw-bold">
                            <?php echo htmlspecialchars($log['product_name']); ?>
                        </a>
                    </td>
                    <td>
                        <span class="badge bg-dark border border-secondary text-secondary">
                            <?php echo htmlspecialchars($log['user_name']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($log['quantity_change'] > 0): ?>
                            <span class="text-success fw-bold">+<?php echo $log['quantity_change']; ?></span>
                        <?php else: ?>
                            <span class="text-danger fw-bold"><?php echo $log['quantity_change']; ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($log['reason'] ?? 'N/A'); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
