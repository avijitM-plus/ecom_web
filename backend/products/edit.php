<?php
/**
 * Product Management - Edit Product
 * RoboMart E-commerce Platform
 */

require_once '../config.php';

$page_title = 'Edit Product';

// Get product ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    header('Location: index.php?error=' . urlencode('Invalid product ID'));
    exit;
}

// Get product data
$product = get_product_by_id($pdo, $id);
if (!$product) {
    header('Location: index.php?error=' . urlencode('Product not found'));
    exit;
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $discount_percent = floatval($_POST['discount_percent'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $image_url = sanitize_input($_POST['image_url'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Handle image upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/products/';
        
        // Create directory if not exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file = $_FILES['image_file'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($file_ext, $allowed_exts)) {
            $error = 'Invalid image format. Allowed: JPG, PNG, GIF, WEBP';
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB max
            $error = 'Image file too large. Maximum size: 5MB';
        } else {
            // Generate unique filename
            $new_filename = 'product_' . $id . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // Set image URL to the uploaded file
                $image_url = 'uploads/products/' . $new_filename;
            } else {
                $error = 'Failed to upload image. Please try again.';
            }
        }
    }
    
    // Validation
    if (empty($error) && (empty($name) || $price <= 0)) {
        $error = 'Product name and valid price are required.';
    } elseif (empty($error) && ($discount_percent < 0 || $discount_percent > 100)) {
        $error = 'Discount must be between 0 and 100%.';
    } elseif (empty($error)) {
        // Update product
        $result = update_product($pdo, $id, $name, $description, $price, $stock, $image_url, $is_active, '', $is_featured, $discount_percent);
        if ($result['success']) {
            $success = $result['message'];
            // Refresh product data
            $product = get_product_by_id($pdo, $id);
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
            <li class="breadcrumb-item active">Edit Product</li>
        </ol>
    </nav>
    <h1 class="page-title">Edit Product</h1>
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

<!-- Edit Product Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="table-container p-4">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="row g-4">
                    <!-- Product Name -->
                    <div class="col-12">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" 
                               placeholder="e.g. Arduino Uno R3" required
                               value="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    
                    <!-- Description -->
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4" 
                                  placeholder="Product feature and details..."><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    
                    <!-- Price -->
                    <div class="col-md-4">
                        <label class="form-label">Price (৳) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-light">৳</span>
                            <input type="number" name="price" class="form-control" 
                                   step="0.01" min="0" placeholder="0.00" required
                                   value="<?php echo htmlspecialchars($product['price']); ?>">
                        </div>
                    </div>
                    
                    <!-- Discount -->
                    <div class="col-md-4">
                        <label class="form-label">Discount (%)</label>
                        <div class="input-group">
                            <input type="number" name="discount_percent" class="form-control" 
                                   step="0.01" min="0" max="100" placeholder="0"
                                   value="<?php echo htmlspecialchars($product['discount_percent'] ?? 0); ?>">
                            <span class="input-group-text bg-dark border-secondary text-light">%</span>
                        </div>
                        <small class="text-muted">0-100%. Final price: ৳<span id="finalPrice"><?php 
                            $discount = $product['discount_percent'] ?? 0;
                            echo number_format(get_discounted_price($product['price'], $discount), 2); 
                        ?></span></small>
                    </div>
                    
                    <!-- Stock -->
                    <div class="col-md-4">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock" class="form-control" 
                               min="0" placeholder="0"
                               value="<?php echo htmlspecialchars($product['stock']); ?>">
                    </div>
                    
                    <!-- Image Upload -->
                    <div class="col-12">
                        <label class="form-label">Product Image</label>
                        
                        <!-- Current Image Preview -->
                        <?php if ($product['image_url']): ?>
                        <div class="mb-3">
                            <img src="../../<?php echo htmlspecialchars($product['image_url']); ?>" 
                                 alt="Current Image" class="img-thumbnail" style="max-height: 100px;">
                            <small class="d-block text-muted">Current image</small>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Upload File -->
                        <div class="mb-2">
                            <label class="form-label small text-muted">Upload New Image</label>
                            <input type="file" name="image_file" class="form-control" 
                                   accept="image/jpeg,image/png,image/gif,image/webp">
                            <small class="text-muted">Max 5MB. Formats: JPG, PNG, GIF, WEBP</small>
                        </div>
                        
                        <div class="text-center text-muted my-2">- OR -</div>
                        
                        <!-- Image URL -->
                        <div>
                            <label class="form-label small text-muted">Image URL</label>
                            <input type="url" name="image_url" class="form-control" 
                                   placeholder="https://example.com/image.jpg"
                                   value="<?php echo htmlspecialchars($product['image_url']); ?>">
                            <small class="text-muted">Or enter a direct link to an image</small>
                        </div>
                    </div>
                    
                    <!-- Status -->
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_active" class="form-check-input" id="is_active"
                                   <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">Active (Visible in store)</label>
                        </div>
                        <div class="form-check form-switch mt-2">
                            <input type="checkbox" name="is_featured" class="form-check-input" id="is_featured"
                                   <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_featured">Featured Product</label>
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
    
    <!-- Preview Sidebar -->
    <div class="col-lg-4">
        <div class="table-container p-4">
            <h6 class="mb-3">Preview</h6>
            <div class="card bg-dark border-secondary">
                <?php if ($product['image_url']): ?>
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="card-img-top" alt="Product Preview" style="max-height: 200px; object-fit: cover;">
                <?php else: ?>
                <div class="bg-secondary d-flex align-items-center justify-content-center text-white-50" style="height: 200px;">
                    <i class="bi bi-image fs-1"></i>
                </div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <p class="card-text text-primary fw-bold">৳<?php echo number_format($product['price'], 2); ?></p>
                    <p class="card-text small text-muted">
                        <?php echo $product['stock'] > 0 ? 'In Stock (' . $product['stock'] . ')' : 'Out of Stock'; ?>
                    </p>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-top border-secondary border-opacity-25">
                <h6 class="text-danger mb-3">Danger Zone</h6>
                <button type="button" class="btn btn-outline-danger w-100" 
                        onclick="confirmDelete(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars(addslashes($product['name'])); ?>')">
                    <i class="bi bi-trash me-2"></i>Delete Product
                </button>
            </div>
        </div>
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
