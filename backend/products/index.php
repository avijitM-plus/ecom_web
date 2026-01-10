<?php
/**
 * Product Management - List All Products
 * RoboMart E-commerce Platform
 */

require_once '../config.php';

$page_title = 'Product Management';

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : ''; // 'active' or 'draft' or empty
$stock_filter = isset($_GET['stock']) ? sanitize_input($_GET['stock']) : '';

// Get products with pagination
// Note: For admin, we default status to empty to show ALL products unless filtered.
// But get_all_products defaults to 'active'.
// If $status_filter is empty, we pass 'all' or similar? 
// No, the function logic is: if status == 'active' -> is_active=1, if 'draft' -> is_active=0. 
// If I pass '', it falls through and shows all.
// So:
$status_param = $status_filter;
if ($status_filter === '') {
    // If no filter selected, we want ALL products.
    // My function implementation:
    // if ($status === 'active') ... elseif ($status === 'draft') ...
    // So if I pass '', it adds NO clause for is_active. Correct.
    $status_param = ''; 
}

$result = get_all_products($pdo, $page, ADMIN_PER_PAGE, $search, $category_filter, $min_price, $max_price, $status_param, $stock_filter);
$products = $result['products'];
$total_pages = $result['total_pages'];
$current_page = $result['current_page'];
$total_products = $result['total_items'];

// Fetch categories for filter dropdown
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories_list = $stmt->fetchAll();

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_result = delete_product($pdo, $_GET['delete']);
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
        <h1 class="page-title">Product Management</h1>
        <p class="text-muted mb-0">Manage store inventory</p>
    </div>
    <div>
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Add New Product
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
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Product name..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Category</label>
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories_list as $cat): ?>
                    <option value="<?php echo $cat['slug']; ?>" <?php echo $category_filter === $cat['slug'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="draft" <?php echo $status_filter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Stock</label>
                <select name="stock" class="form-select">
                    <option value="">All Stock</option>
                    <option value="instock" <?php echo $stock_filter === 'instock' ? 'selected' : ''; ?>>In Stock</option>
                    <option value="lowstock" <?php echo $stock_filter === 'lowstock' ? 'selected' : ''; ?>>Low Stock (<=5)</option>
                    <option value="outofstock" <?php echo $stock_filter === 'outofstock' ? 'selected' : ''; ?>>Out of Stock</option>
                </select>
            </div>
            
             <div class="col-md-3">
                <label class="form-label">Price Range</label>
                <div class="input-group">
                    <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?php echo $min_price ?: ''; ?>">
                    <span class="input-group-text">-</span>
                    <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?php echo $max_price ?: ''; ?>">
                </div>
            </div>

            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-filter me-1"></i>Apply Filters
                </button>
                 <?php if ($search || $category_filter || $status_filter || $stock_filter || $min_price || $max_price): ?>
                <a href="index.php" class="btn btn-outline-light">
                    <i class="bi bi-x-lg me-1"></i>Clear
                </a>
                <?php endif; ?>
                 <div class="mt-2 text-muted small"><?php echo $total_products; ?> products found</div>
            </div>
        </form>
    </div>
</div>

<!-- Products Table -->
<div class="table-container">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-box-seam fs-1 d-block mb-3"></i>
                        No products found
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td class="text-muted">#<?php echo $product['id']; ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <?php if ($product['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
                                     style="width: 48px; height: 48px;">
                                    <i class="bi bi-image text-white-50"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <div class="fw-bold"><?php echo htmlspecialchars($product['name']); ?></div>
                                <small class="text-muted"><?php echo substr(htmlspecialchars($product['description']), 0, 50) . '...'; ?></small>
                            </div>
                        </div>
                    </td>
                    <td>৳<?php echo number_format($product['price'], 2); ?></td>
                    <td>
                        <?php if ($product['stock'] <= 5): ?>
                            <span class="text-danger fw-bold"><?php echo $product['stock']; ?></span>
                        <?php else: ?>
                            <?php echo $product['stock']; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?php echo $product['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                            <?php echo $product['is_active'] ? 'Active' : 'Draft'; ?>
                        </span>
                    </td>
                    <td class="text-center">
                        <a href="toggle_featured.php?id=<?php echo $product['id']; ?>" class="text-decoration-none">
                            <?php if ($product['is_featured']): ?>
                                <i class="bi bi-star-fill text-warning fs-5" title="Featured"></i>
                            <?php else: ?>
                                <i class="bi bi-star text-secondary fs-5" title="Not Featured"></i>
                            <?php endif; ?>
                        </a>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="edit.php?id=<?php echo $product['id']; ?>" 
                               class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    title="Delete" onclick="confirmDelete(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>')">
                                <i class="bi bi-trash"></i>
                            </button>
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
                    <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>&stock=<?php echo urlencode($stock_filter); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>">
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
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>&stock=<?php echo urlencode($stock_filter); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>&status=<?php echo urlencode($status_filter); ?>&stock=<?php echo urlencode($stock_filter); ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>">
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
                <p>Are you sure you want to delete product <strong id="deleteProductName"></strong>?</p>
                <p class="text-danger small mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="deleteConfirmBtn" class="btn btn-danger">Delete Product</a>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    document.getElementById('deleteProductName').textContent = name;
    document.getElementById('deleteConfirmBtn').href = 'index.php?delete=' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>
