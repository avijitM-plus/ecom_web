<?php
/**
 * System Settings
 * RoboMart E-commerce Platform
 */

require_once '../config.php';

// Check permission (Admin only for now)
require_admin();

$page_title = 'System Settings';

// Handle Form Submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'general' => ['site_name', 'currency_symbol', 'contact_email', 'contact_phone'],
        'payment' => ['tax_rate'],
        'shipping' => ['shipping_flat_rate']
    ];

    $has_error = false;
    
    foreach ($settings as $group => $keys) {
        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                $result = update_setting($pdo, $key, sanitize_input($_POST[$key]), $group);
                if (!$result['success']) {
                    $has_error = true;
                    $error = $result['message'];
                }
            }
        }
    }

    if (!$has_error) {
        $success = 'Settings updated successfully';
    }
}

// Fetch current values
$current_settings = [];
$keys_to_fetch = ['site_name', 'currency_symbol', 'contact_email', 'contact_phone', 'tax_rate', 'shipping_flat_rate'];
foreach ($keys_to_fetch as $key) {
    $current_settings[$key] = get_setting($pdo, $key) ?? '';
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">System Settings</h1>
        <p class="text-muted mb-0">Manage global store configuration</p>
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

<div class="row">
    <div class="col-12 col-xl-8">
        <form method="POST" action="">
            
            <!-- General Settings -->
            <div class="card bg-dark border-secondary mb-4">
                <div class="card-header border-secondary">
                    <h5 class="card-title mb-0"><i class="bi bi-gear me-2"></i>General Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-control" 
                               value="<?php echo htmlspecialchars($current_settings['site_name']); ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Currency Symbol</label>
                            <input type="text" name="currency_symbol" class="form-control" 
                                   value="<?php echo htmlspecialchars($current_settings['currency_symbol']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" 
                                   value="<?php echo htmlspecialchars($current_settings['contact_email']); ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Phone</label>
                        <input type="text" name="contact_phone" class="form-control" 
                               value="<?php echo htmlspecialchars($current_settings['contact_phone']); ?>">
                    </div>
                </div>
            </div>
            
            <!-- Payment & Tax -->
            <div class="card bg-dark border-secondary mb-4">
                <div class="card-header border-secondary">
                    <h5 class="card-title mb-0"><i class="bi bi-credit-card me-2"></i>Payment & Tax</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Tax Rate (%)</label>
                        <input type="number" step="0.01" name="tax_rate" class="form-control" 
                               value="<?php echo htmlspecialchars($current_settings['tax_rate']); ?>">
                        <div class="form-text text-muted">Applied to all orders before shipping</div>
                    </div>
                </div>
            </div>
            
            <!-- Shipping -->
            <div class="card bg-dark border-secondary mb-4">
                <div class="card-header border-secondary">
                    <h5 class="card-title mb-0"><i class="bi bi-truck me-2"></i>Shipping</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Flat Rate Shipping Cost</label>
                        <div class="input-group">
                            <span class="input-group-text"><?php echo htmlspecialchars($current_settings['currency_symbol'] ?: '$'); ?></span>
                            <input type="number" step="0.01" name="shipping_flat_rate" class="form-control" 
                                   value="<?php echo htmlspecialchars($current_settings['shipping_flat_rate']); ?>">
                        </div>
                        <div class="form-text text-muted">Default shipping cost if no other rules apply</div>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Save Changes
                </button>
            </div>
            
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
