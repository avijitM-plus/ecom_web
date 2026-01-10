<?php
/**
 * Return Management (RMA)
 * RoboMart E-commerce Platform
 */

require_once '../config.php';

// Check permission
if (!check_permission('admin') && !check_permission('warehouse_manager')) {
    redirect('../index.php?error=Access denied');
}

$page_title = 'Return Requests';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $return_id = (int)$_POST['return_id'];
    $action = $_POST['action'];
    $notes = sanitize_input($_POST['admin_notes']);
    
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    
    // If approving, we might want to automatically adjust stock or issue refund
    // For now, just mark as approved/refunded
    if ($action === 'refund') $status = 'refunded';
    
    $stmt = $pdo->prepare("UPDATE returns SET status = ?, admin_notes = ? WHERE id = ?");
    if ($stmt->execute([$status, $notes, $return_id])) {
        // Log inventory change if item is returned to stock
        if ($status === 'refunded') {
            $stmt = $pdo->prepare("SELECT product_id FROM returns WHERE id = ?");
            $stmt->execute([$return_id]);
            $pid = $stmt->fetchColumn();
            log_inventory_change($pdo, $pid, $_SESSION['user_id'], 1, 'Customer Return #' . $return_id);
        }
        $success = "Return status updated to " . ucfirst($status);
    } else {
        $error = "Failed to update return request";
    }
}

// Fetch Returns
$stmt = $pdo->query("
    SELECT r.*, o.id as order_number, u.full_name as user_name, u.email, p.name as product_name 
    FROM returns r 
    JOIN orders o ON r.order_id = o.id 
    JOIN users u ON r.user_id = u.id 
    JOIN products p ON r.product_id = p.id 
    ORDER BY r.created_at DESC
");
$returns = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Return Requests (RMA)</h1>
        <p class="text-muted mb-0">Manage customer returns and refunds</p>
    </div>
</div>

<?php if (isset($success)): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="table-container">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Requests</th>
                    <th>Customer</th>
                    <th>Item</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($returns)): ?>
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">No return requests found</td>
                </tr>
                <?php else: ?>
                <?php foreach ($returns as $rma): ?>
                <tr>
                    <td>
                        <div class="small text-muted"><?php echo date('M d, Y', strtotime($rma['created_at'])); ?></div>
                        <div class="fw-bold">#RMA-<?php echo $rma['id']; ?></div>
                        <small>Order #<?php echo $rma['order_number']; ?></small>
                    </td>
                    <td>
                        <div><?php echo htmlspecialchars($rma['user_name']); ?></div>
                        <small class="text-muted"><?php echo htmlspecialchars($rma['email']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($rma['product_name']); ?></td>
                    <td>
                        <span class="d-inline-block text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($rma['reason']); ?>">
                            <?php echo htmlspecialchars($rma['reason']); ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        $badge_class = match($rma['status']) {
                            'pending' => 'bg-warning text-dark',
                            'approved' => 'bg-info',
                            'refunded' => 'bg-success',
                            'rejected' => 'bg-danger',
                            default => 'bg-secondary'
                        };
                        ?>
                        <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($rma['status']); ?></span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="openRMAModal(<?php echo htmlspecialchars(json_encode($rma)); ?>)">
                            Manage
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Manage RMA Modal -->
<div class="modal fade" id="rmaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Manage Return #<span id="rmaId"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="return_id" id="formRmaId">
                <div class="modal-body">
                    <p><strong>Reason:</strong> <span id="rmaReason"></span></p>
                    <div class="mb-3">
                        <label class="form-label">Admin Notes</label>
                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Internal notes..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <select name="action" class="form-select">
                            <option value="approve">Approve Return (Wait for item)</option>
                            <option value="refund">Issue Refund (Item Received)</option>
                            <option value="reject">Reject Request</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRMAModal(data) {
    document.getElementById('rmaId').textContent = data.id;
    document.getElementById('formRmaId').value = data.id;
    document.getElementById('rmaReason').textContent = data.reason;
    new bootstrap.Modal(document.getElementById('rmaModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>
