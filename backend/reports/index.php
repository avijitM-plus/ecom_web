<?php
/**
 * Reports & Analytics
 * RoboMart E-commerce Platform
 */

require_once '../config.php';

// Check permission
if (!check_permission('admin') && !check_permission('sales_manager')) {
    redirect('../index.php?error=Access denied');
}

$page_title = 'Reports & Analytics';

// Date Filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Fetch Sales Data for Chart
$stmt = $pdo->prepare("
    SELECT DATE(created_at) as date, SUM(total_amount) as total, COUNT(*) as count 
    FROM orders 
    WHERE status != 'cancelled' 
    AND DATE(created_at) BETWEEN ? AND ? 
    GROUP BY DATE(created_at) 
    ORDER BY date ASC
");
$stmt->execute([$start_date, $end_date]);
$sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for Chart.js
$chart_labels = [];
$chart_sales = [];
$chart_orders = [];

foreach ($sales_data as $day) {
    $chart_labels[] = date('M d', strtotime($day['date']));
    $chart_sales[] = $day['total'];
    $chart_orders[] = $day['count'];
}

// Top Selling Products
$stmt = $pdo->prepare("
    SELECT p.name, SUM(oi.quantity) as total_sold, SUM(oi.price * oi.quantity) as revenue 
    FROM order_items oi 
    JOIN orders o ON oi.order_id = o.id 
    JOIN products p ON oi.product_id = p.id 
    WHERE o.status != 'cancelled' 
    AND DATE(o.created_at) BETWEEN ? AND ? 
    GROUP BY p.id 
    ORDER BY total_sold DESC 
    LIMIT 10
");
$stmt->execute([$start_date, $end_date]);
$top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate Period Totals
$period_revenue = array_sum($chart_sales);
$period_orders = array_sum($chart_orders);

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<!-- Charts.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Page Header & Filter -->
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Reports & Analytics</h1>
        <p class="text-muted mb-0">Sales performance and insights</p>
    </div>
    
    <form class="d-flex gap-2 align-items-center bg-dark p-2 rounded border border-secondary">
        <input type="date" name="start_date" class="form-control form-control-sm bg-dark text-light border-secondary" 
               value="<?php echo $start_date; ?>">
        <span class="text-muted">-</span>
        <input type="date" name="end_date" class="form-control form-control-sm bg-dark text-light border-secondary" 
               value="<?php echo $end_date; ?>">
        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
    </form>
</div>

<!-- Key Metrics -->
<div class="row g-4 mb-4">
    <div class="col-12 col-md-4">
        <div class="stat-card gradient-green h-100">
            <h6 class="stat-label mb-2">Total Revenue (Period)</h6>
            <h2 class="stat-value"><?php echo number_format($period_revenue, 2); ?></h2>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card gradient-blue h-100">
            <h6 class="stat-label mb-2">Total Orders (Period)</h6>
            <h2 class="stat-value"><?php echo number_format($period_orders); ?></h2>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="stat-card gradient-purple h-100">
            <h6 class="stat-label mb-2">Avg Order Value</h6>
            <h2 class="stat-value">
                <?php echo $period_orders > 0 ? number_format($period_revenue / $period_orders, 2) : '0.00'; ?>
            </h2>
        </div>
    </div>
</div>

<!-- Sales Chart -->
<div class="card bg-dark border-secondary mb-4">
    <div class="card-header border-secondary">
        <h5 class="card-title mb-0"><i class="bi bi-graph-up me-2"></i>Sales Overview</h5>
    </div>
    <div class="card-body">
        <canvas id="salesChart" height="100"></canvas>
    </div>
</div>

<!-- Top Products Table -->
<div class="card bg-dark border-secondary">
    <div class="card-header border-secondary">
        <h5 class="card-title mb-0"><i class="bi bi-trophy me-2"></i>Top Selling Products</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Units Sold</th>
                    <th>Revenue Generated</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($top_products)): ?>
                <tr>
                    <td colspan="3" class="text-center py-4 text-muted">No sales data for this period</td>
                </tr>
                <?php else: ?>
                <?php foreach ($top_products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo $product['total_sold']; ?></td>
                    <td>$<?php echo number_format($product['revenue'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            label: 'Revenue ($)',
            data: <?php echo json_encode($chart_sales); ?>,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Orders',
            data: <?php echo json_encode($chart_orders); ?>,
            borderColor: '#198754',
            backgroundColor: 'rgba(25, 135, 84, 0.1)',
            tension: 0.4,
            fill: true,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                grid: { color: 'rgba(255, 255, 255, 0.1)' }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                grid: { drawOnChartArea: false }
            },
            x: {
                grid: { color: 'rgba(255, 255, 255, 0.1)' }
            }
        },
        plugins: {
            legend: { labels: { color: '#fff' } }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
