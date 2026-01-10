<?php
/**
 * Navigation Management - Admin Panel
 * Allows admin to show/hide categories in navbar and set their order
 */
require_once '../../config.php';
require_once '../config.php';

// Check admin
require_admin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_nav'])) {
        // Update show_in_nav for all categories
        $show_in_nav = isset($_POST['show_in_nav']) ? $_POST['show_in_nav'] : [];
        $nav_orders = isset($_POST['nav_order']) ? $_POST['nav_order'] : [];
        
        try {
            // First, set all to not show in nav
            $pdo->exec("UPDATE categories SET show_in_nav = 0");
            
            // Then update the selected ones
            foreach ($show_in_nav as $cat_id) {
                $order = isset($nav_orders[$cat_id]) ? (int)$nav_orders[$cat_id] : 0;
                $stmt = $pdo->prepare("UPDATE categories SET show_in_nav = 1, nav_order = ? WHERE id = ?");
                $stmt->execute([$order, $cat_id]);
            }
            
            // Update order for non-selected items too
            foreach ($nav_orders as $cat_id => $order) {
                if (!in_array($cat_id, $show_in_nav)) {
                    $stmt = $pdo->prepare("UPDATE categories SET nav_order = ? WHERE id = ?");
                    $stmt->execute([(int)$order, $cat_id]);
                }
            }
            
            $success_msg = "Navigation settings updated successfully!";
        } catch (PDOException $e) {
            $error_msg = "Error updating navigation: " . $e->getMessage();
        }
    }
}

// Fetch all categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY nav_order ASC, name ASC")->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                            <h6 class="text-white text-capitalize ps-3">
                                <i class="fas fa-bars me-2"></i>Navigation Management
                            </h6>
                        </div>
                    </div>
                    <div class="card-body px-4 pb-2">
                        
                        <?php if (isset($success_msg)): ?>
                        <div class="alert alert-success text-white" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success_msg; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($error_msg)): ?>
                        <div class="alert alert-danger text-white" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_msg; ?>
                        </div>
                        <?php endif; ?>

                        <div class="alert alert-info text-white mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Tip:</strong> Check the categories you want to display in the navbar. Use the order number to control their position (lower numbers appear first). Maximum 6 categories are shown.
                        </div>

                        <form method="POST" action="">
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                                <i class="fas fa-eye me-1"></i>Show in Nav
                                            </th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Category Name</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Slug</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">
                                                <i class="fas fa-sort-numeric-down me-1"></i>Order
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($categories)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <p class="text-muted mb-0">No categories found. <a href="../categories/add.php">Add a category</a> first.</p>
                                            </td>
                                        </tr>
                                        <?php else: ?>
                                        <?php foreach ($categories as $cat): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="form-check">
                                                    <input type="checkbox" name="show_in_nav[]" value="<?php echo $cat['id']; ?>" 
                                                           class="form-check-input" id="nav_<?php echo $cat['id']; ?>"
                                                           <?php echo $cat['show_in_nav'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="nav_<?php echo $cat['id']; ?>"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($cat['name']); ?></h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-xs text-secondary"><?php echo htmlspecialchars($cat['slug']); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <input type="number" name="nav_order[<?php echo $cat['id']; ?>]" 
                                                       value="<?php echo $cat['nav_order'] ?? 0; ?>" 
                                                       class="form-control form-control-sm text-center" 
                                                       style="width: 70px; margin: 0 auto;"
                                                       min="0" max="100">
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if (!empty($categories)): ?>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" name="update_nav" class="btn bg-gradient-primary">
                                    <i class="fas fa-save me-2"></i>Save Navigation Settings
                                </button>
                            </div>
                            <?php endif; ?>
                        </form>
                        
                        <!-- Preview Section -->
                        <div class="mt-5 pt-4 border-top">
                            <h6 class="mb-3"><i class="fas fa-desktop me-2"></i>Navigation Preview</h6>
                            <div class="bg-dark rounded p-3">
                                <div class="d-flex align-items-center gap-4 flex-wrap">
                                    <span class="text-white">Home</span>
                                    <span class="text-white">All Products</span>
                                    <?php 
                                    $preview_cats = array_filter($categories, function($c) { return $c['show_in_nav']; });
                                    usort($preview_cats, function($a, $b) { return ($a['nav_order'] ?? 0) - ($b['nav_order'] ?? 0); });
                                    $count = 0;
                                    foreach ($preview_cats as $pc): 
                                        if ($count >= 6) break;
                                        $count++;
                                    ?>
                                    <span class="text-info"><?php echo htmlspecialchars($pc['name']); ?></span>
                                    <?php endforeach; ?>
                                    <span class="text-warning"><i class="fas fa-bolt me-1"></i>Flash Sale</span>
                                </div>
                            </div>
                            <small class="text-muted">This preview shows what your navigation bar will look like.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
