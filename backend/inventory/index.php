<?php
/**
 * Inventory Management
 * RoboMart E-commerce Platform
 */

require_once '../config.php';

// Check permission
if (!check_permission('admin') && !check_permission('warehouse_manager')) {
    redirect('../index.php?error=Access denied');
}

$page_title = 'Inventory Management';

// Handle Stock Adjustment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adjust_stock'])) {
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $type = $_POST['type']; // 'add' or 'subtract'
    $reason = sanitize_input($_POST['reason']);
    
    if ($product_id && $quantity > 0) {
        $change = ($type === 'subtract') ? -$quantity : $quantity;
        if (log_inventory_change($pdo, $product_id, $_SESSION['user_id'], $change, $reason)) {
            $success = "Stock updated successfully";
        } else {
            $error = "Failed to update stock";
        }
    }
}

// Get all products with low stock indicator
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all'; // all, low_stock

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter === 'low_stock') {
    $sql .= " AND stock <= low_stock_threshold";
}

$sql .= " ORDER BY (stock <= low_stock_threshold) DESC, stock ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Inventory Management</h1>
        <p class="text-muted mb-0">Track stock levels and adjustments</p>
    </div>
    
    <div class="btn-group">
        <a href="index.php" class="btn btn-outline-light <?php echo $filter == 'all' ? 'active' : ''; ?>">All Products</a>
        <a href="index.php?filter=low_stock" class="btn btn-outline-warning <?php echo $filter == 'low_stock' ? 'active' : ''; ?>">
            <i class="bi bi-exclamation-triangle me-2"></i>Low Stock
        </a>
    </div>
</div>

<!-- Messages -->
<?php if (isset($success)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i><?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Inventory Table -->
<div class="table-container">
    <div class="p-3">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
    
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Current Stock</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <?php $is_low = $product['stock'] <= $product['low_stock_threshold']; ?>
                <tr class="<?php echo $is_low ? 'bg-opacity-10 bg-warning' : ''; ?>">
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <?php if ($product['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="rounded" width="40" height="40" style="object-fit: cover;">
                            <?php endif; ?>
                            <div class="fw-bold"><?php echo htmlspecialchars($product['name']); ?></div>
                        </div>
                    </td>
                    <td>
                        <span class="fs-5 fw-bold <?php echo $is_low ? 'text-warning' : ''; ?>">
                            <?php echo $product['stock']; ?>
                        </span>
                        <small class="text-muted d-block">Threshold: <?php echo $product['low_stock_threshold']; ?></small>
                    </td>
                    <td>
                        <?php if ($product['stock'] == 0): ?>
                            <span class="badge bg-danger">Out of Stock</span>
                        <?php elseif ($is_low): ?>
                            <span class="badge bg-warning text-dark">Low Stock</span>
                        <?php else: ?>
                            <span class="badge bg-success">In Stock</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="openAdjustModal(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>', <?php echo $product['stock']; ?>)">
                            <i class="bi bi-pencil-square me-1"></i>Adjust
                        </button>
                        <a href="logs.php?product_id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-light">
                            <i class="bi bi-clock-history"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Adjust Stock</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="adjust_stock" value="1">
                <input type="hidden" name="product_id" id="modeProductId">
                
                <div class="modal-body">
                    <p class="mb-3">Adjusting stock for: <strong id="modalProductName" class="text-primary"></strong></p>
                    
                    <div class="mb-3">
                        <label class="form-label">Action</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type" id="typeAdd" value="add" checked>
                            <label class="btn btn-outline-success" for="typeAdd">
                                <i class="bi bi-plus-lg me-2"></i>Add Stock
                            </label>

                            <input type="radio" class="btn-check" name="type" id="typeSub" value="subtract">
                            <label class="btn btn-outline-danger" for="typeSub">
                                <i class="bi bi-dash-lg me-2"></i>Remove Stock
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" required min="1">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <select name="reason" class="form-select">
                            <option value="restock">Restock / Shipment Received</option>
                            <option value="audit">Inventory Audit Correction</option>
                            <option value="damage">Damaged / Expired</option>
                            <option value="return">Customer Return</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAdjustModal(id, name, currentStock) {
    document.getElementById('modeProductId').value = id;
    document.getElementById('modalProductName').textContent = name;
    new bootstrap.Modal(document.getElementById('adjustModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>
