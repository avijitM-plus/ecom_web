<?php
/**
 * Product Management - Add New Product
 * RoboMart E-commerce Platform
 */

require_once '../config.php';

$page_title = 'Add New Product';

$error = '';
$success = '';

// Fetch Categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$all_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
$cats_map = [];
foreach ($all_categories as $c) $cats_map[$c['id']] = $c['name'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $image_url = sanitize_input($_POST['image_url'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Categories
    $selected_ids = isset($_POST['categories']) && is_array($_POST['categories']) ? $_POST['categories'] : [];
    $selected_names = [];
    foreach ($selected_ids as $cid) {
        if (isset($cats_map[$cid])) $selected_names[] = $cats_map[$cid];
    }
    $category_str = implode(', ', $selected_names); // Legacy support

    // Validation
    if (empty($name) || $price <= 0) {
        $error = 'Product name and valid price are required.';
    } else {
        // Create product
        $result = create_product($pdo, $name, $description, $price, $stock, $image_url, $is_active, $category_str, $is_featured);
        
        if ($result['success']) {
            $product_id = $result['id'];
            
            // Link Categories in Pivot
            if (!empty($selected_ids)) {
                try {
                    $pivot_stmt = $pdo->prepare("INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)");
                    foreach ($selected_ids as $cid) {
                        $pivot_stmt->execute([$product_id, $cid]);
                    }
                } catch (Exception $e) {
                    // Ignore duplicate errors
                }
            }
            
            header('Location: index.php?success=' . urlencode('Product created successfully!'));
            exit;
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
            <li class="breadcrumb-item"><a href="index.php" class="text-muted">Products</a></li>
            <li class="breadcrumb-item active">Add New</li>
        </ol>
    </nav>
    <h1 class="page-title">Add New Product</h1>
</div>

<!-- Error Message -->
<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Add Product Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="table-container p-4">
            <form method="POST" action="">
                <div class="row g-4">
                    <!-- Product Name -->
                    <div class="col-12">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" 
                               placeholder="e.g. Arduino Uno R3" required
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>
                    
                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" 
                                  placeholder="Product feature and details..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <!-- Categories -->
                    <div class="col-12">
                        <label class="form-label d-flex justify-content-between">
                            <span>Categories</span>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                + New Category
                            </button>
                        </label>
                        <div class="card bg-light p-3" style="max-height: 200px; overflow-y: auto;">
                            <div id="category_list">
                                <?php if (empty($all_categories)): ?>
                                    <div class="text-muted small">No categories found. Create one!</div>
                                <?php else: ?>
                                    <?php foreach ($all_categories as $cat): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="categories[]" 
                                               value="<?php echo $cat['id']; ?>" id="cat_<?php echo $cat['id']; ?>"
                                               <?php echo (isset($_POST['categories']) && in_array($cat['id'], $_POST['categories'])) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="cat_<?php echo $cat['id']; ?>">
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Price -->
                    <div class="col-md-6">
                        <label class="form-label">Price ($) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-light">$</span>
                            <input type="number" name="price" class="form-control" 
                                   step="0.01" min="0" placeholder="0.00" required
                                   value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <!-- Stock -->
                    <div class="col-md-6">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock" class="form-control" 
                               min="0" placeholder="0"
                               value="<?php echo htmlspecialchars($_POST['stock'] ?? '0'); ?>">
                    </div>
                    
                    <!-- Image URL -->
                    <div class="col-12">
                        <label class="form-label">Image URL</label>
                        <input type="url" name="image_url" class="form-control" 
                               placeholder="https://example.com/image.jpg"
                               value="<?php echo htmlspecialchars($_POST['image_url'] ?? ''); ?>">
                    </div>
                    
                    <!-- Options -->
                    <div class="col-12">
                        <div class="d-flex gap-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_active" class="form-check-input" id="is_active" checked>
                                <label class="form-check-label" for="is_active">Active (Visible)</label>
                            </div>
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured">
                                <label class="form-check-label" for="is_featured">Featured Product</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="mt-4 pt-3 border-top border-dark">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Create Product
                    </button>
                    <a href="index.php" class="btn btn-outline-light ms-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Guidelines -->
    <div class="col-lg-4">
        <div class="table-container p-4">
            <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Guidelines</h6>
            <ul class="small text-muted mb-0 space-y-2">
                <li><strong>Categories:</strong> Select all relevant categories.</li>
                <li><strong>Featured:</strong> Check to show in "Best Sellers" on Home.</li>
                <li><strong>Images:</strong> 1:1 Aspect ratio recommended.</li>
            </ul>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text" id="new_category_name" class="form-control" placeholder="e.g. Drones">
                    <div class="text-danger small mt-1" id="cat_error"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="createNewCategory()">Create</button>
            </div>
        </div>
    </div>
</div>

<script>
async function createNewCategory() {
    const nameInput = document.getElementById('new_category_name');
    const name = nameInput.value.trim();
    const errorDiv = document.getElementById('cat_error');
    
    if (!name) {
        errorDiv.textContent = 'Name is required';
        return;
    }
    
    try {
        const response = await fetch('../categories/ajax_add.php', {
            method: 'POST',
            body: JSON.stringify({ name: name }),
            headers: { 'Content-Type': 'application/json' }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Add to list
            const cat = data.category;
            const item = `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="categories[]" 
                           value="${cat.id}" id="cat_${cat.id}" checked>
                    <label class="form-check-label" for="cat_${cat.id}">
                        ${cat.name}
                    </label>
                </div>
            `;
            document.getElementById('category_list').insertAdjacentHTML('beforeend', item);
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addCategoryModal'));
            modal.hide();
            nameInput.value = '';
            errorDiv.textContent = '';
        } else {
            errorDiv.textContent = data.message || 'Error occurred';
        }
    } catch (e) {
        errorDiv.textContent = 'Network error: ' + e.message;
    }
}
</script>

<?php include '../includes/footer.php'; ?>
