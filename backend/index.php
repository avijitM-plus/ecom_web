<?php
/**
 * Admin Dashboard
 * RoboMart E-commerce Platform
 */

require_once 'config.php';

$page_title = 'Dashboard';

// Get dashboard statistics
$stats = get_dashboard_stats($pdo);
$recent_orders = get_recent_orders($pdo, 5);

// Include header
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="text-muted mb-0">Overview of your store performance</p>
    </div>
    <div>
        <a href="products/add.php" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Add Product
        </a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <!-- Total Sales -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card gradient-green">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-label">Total Sales</p>
                    <h2 class="stat-value">$<?php echo number_format($stats['total_sales'], 2); ?></h2>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-currency-dollar"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Orders -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card gradient-blue">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-label">Total Orders</p>
                    <h2 class="stat-value"><?php echo number_format($stats['total_orders']); ?></h2>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-bag-check"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Orders -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card gradient-orange">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-label">Pending Orders</p>
                    <h2 class="stat-value"><?php echo number_format($stats['pending_orders']); ?></h2>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Users -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card gradient-purple">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-label">Total Users</p>
                    <h2 class="stat-value"><?php echo number_format($stats['total_users']); ?></h2>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="row">
    <div class="col-12">
        <div class="table-container">
            <div class="p-3 d-flex justify-content-between align-items-center border-bottom border-dark">
                <h5 class="mb-0">Recent Orders</h5>
                <a href="orders/index.php" class="btn btn-sm btn-outline-light">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_orders)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No orders found</td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['full_name'] ?? 'Guest'); ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <?php
                                $badge_class = match($order['status']) {
                                    'completed' => 'bg-success',
                                    'pending' => 'bg-warning text-dark',
                                    'cancelled' => 'bg-danger',
                                    'shipped' => 'bg-primary',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($order['status']); ?></span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td>
                                <a href="orders/view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-light">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="table-container p-4">
            <h5 class="mb-3">Quick Actions</h5>
            <div class="d-flex flex-wrap gap-2">
                <a href="users/add.php" class="btn btn-primary">
                    <i class="bi bi-person-plus me-2"></i>Add User
                </a>
                <a href="users/index.php" class="btn btn-outline-light">
                    <i class="bi bi-people me-2"></i>Manage Users
                </a>
                <a href="<?php echo SITE_URL; ?>" class="btn btn-outline-light" target="_blank">
                    <i class="bi bi-shop me-2"></i>View Store
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
