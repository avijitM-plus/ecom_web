<?php
/**
 * Shipping Management
 * RoboMart E-commerce Platform
 */

require_once '../config.php';

// Check permission
if (!check_permission('admin') && !check_permission('warehouse_manager')) {
    redirect('../index.php?error=Access denied');
}

$page_title = 'Shipping Management';
$message = '';
$error = '';

/** Handle Zone Creation */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_zone') {
    $zone_name = sanitize_input($_POST['zone_name']);
    $countries = isset($_POST['countries']) ? json_encode($_POST['countries']) : '[]';
    
    if ($zone_name) {
        $stmt = $pdo->prepare("INSERT INTO shipping_zones (zone_name, countries) VALUES (?, ?)");
        if ($stmt->execute([$zone_name, $countries])) {
            $message = "Zone created successfully";
        } else {
            $error = "Failed to create zone";
        }
    }
}

/** Handle Rate Creation */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_rate') {
    $zone_id = (int)$_POST['zone_id'];
    $min_weight = (float)$_POST['min_weight'];
    $max_weight = $_POST['max_weight'] ? (float)$_POST['max_weight'] : null;
    $cost = (float)$_POST['cost'];

    $stmt = $pdo->prepare("INSERT INTO shipping_rates (zone_id, min_weight, max_weight, cost) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$zone_id, $min_weight, $max_weight, $cost])) {
        $message = "Rate added successfully";
    } else {
        $error = "Failed to add rate";
    }
}

/** Handle Deletion */
if (isset($_GET['delete_zone'])) {
    $id = (int)$_GET['delete_zone'];
    // Delete associated rates first
    $pdo->prepare("DELETE FROM shipping_rates WHERE zone_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM shipping_zones WHERE id = ?")->execute([$id]);
    header("Location: index.php");
    exit;
}

if (isset($_GET['delete_rate'])) {
    $id = (int)$_GET['delete_rate'];
    $pdo->prepare("DELETE FROM shipping_rates WHERE id = ?")->execute([$id]);
    header("Location: index.php");
    exit;
}

// Fetch Zones and Rates
$zones = $pdo->query("SELECT * FROM shipping_zones ORDER BY zone_name")->fetchAll();
foreach ($zones as &$zone) {
    $stmt = $pdo->prepare("SELECT * FROM shipping_rates WHERE zone_id = ? ORDER BY min_weight");
    $stmt->execute([$zone['id']]);
    $zone['rates'] = $stmt->fetchAll();
}
unset($zone); // Break the reference with the last element

$country_list = get_countries();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Shipping Management</h1>
        <p class="text-muted mb-0">Manage shipping zones and rates</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addZoneModal">
        <i class="bi bi-plus-lg me-2"></i>Add Shipping Zone
    </button>
</div>

<?php if ($message): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
    <?php foreach ($zones as $zone): ?>
    <div class="col-12 col-xl-6">
        <div class="card bg-dark border-secondary h-100">
            <div class="card-header border-secondary d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><?php echo htmlspecialchars($zone['zone_name']); ?></h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-primary" onclick="openRateModal(<?php echo $zone['id']; ?>, '<?php echo htmlspecialchars(addslashes($zone['zone_name'])); ?>')">
                        <i class="bi bi-plus"></i> Add Rate
                    </button>
                    <a href="?delete_zone=<?php echo $zone['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this zone and all its rates?')">
                        <i class="bi bi-trash"></i>
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Countries:</small>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        <?php 
                        $zone_countries = json_decode($zone['countries'], true) ?: [];
                        if (empty($zone_countries)) echo '<span class="text-white-50">Global / Rest of World</span>';
                        foreach ($zone_countries as $code) {
                            echo '<span class="badge bg-secondary">' . ($country_list[$code] ?? $code) . '</span>';
                        }
                        ?>
                    </div>
                </div>
                
                <h6 class="border-bottom border-secondary pb-2 mb-3">Rates</h6>
                <?php if (empty($zone['rates'])): ?>
                    <p class="text-muted small">No rates defined. Free shipping?</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-dark table-borderless">
                            <thead>
                                <tr class="text-muted">
                                    <th>Weight Range</th>
                                    <th>Cost</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($zone['rates'] as $rate): ?>
                                <tr>
                                    <td>
                                        <?php 
                                        echo $rate['min_weight'] . 'kg - ';
                                        echo $rate['max_weight'] ? $rate['max_weight'] . 'kg' : 'Up';
                                        ?>
                                    </td>
                                    <td>$<?php echo number_format($rate['cost'], 2); ?></td>
                                    <td class="text-end">
                                        <a href="?delete_rate=<?php echo $rate['id']; ?>" class="text-danger">
                                            <i class="bi bi-x"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add Zone Modal -->
<div class="modal fade" id="addZoneModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">New Shipping Zone</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_zone">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Zone Name</label>
                        <input type="text" name="zone_name" class="form-control" required placeholder="e.g., North America">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Countries</label>
                        <select name="countries[]" class="form-select" multiple size="5">
                            <?php foreach ($country_list as $code => $name): ?>
                                <option value="<?php echo $code; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-muted">Hold Ctrl to select multiple. Leave empty for catch-all.</div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Zone</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Rate Modal -->
<div class="modal fade" id="addRateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Add Shipping Rate for <span id="rateZoneName"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_rate">
                <input type="hidden" name="zone_id" id="rateZoneId">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Min Weight (kg)</label>
                            <input type="number" step="0.1" name="min_weight" class="form-control" value="0">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Max Weight (kg)</label>
                            <input type="number" step="0.1" name="max_weight" class="form-control" placeholder="Optional">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cost ($)</label>
                        <input type="number" step="0.01" name="cost" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Rate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRateModal(zoneId, zoneName) {
    document.getElementById('rateZoneId').value = zoneId;
    document.getElementById('rateZoneName').textContent = zoneName;
    new bootstrap.Modal(document.getElementById('addRateModal')).show();
}
</script>

<?php include '../includes/footer.php'; ?>
