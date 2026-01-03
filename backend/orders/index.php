<?php
require_once '../../config.php';
 

// Check admin
require_admin();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$search = $_GET['search'] ?? '';
$where_clauses = [];
$params = [];

if ($search) {
    if (is_numeric($search)) {
        $where_clauses[] = "o.id = ?";
        $params[] = $search;
    } else {
        $where_clauses[] = "u.full_name LIKE ?";
        $params[] = "%$search%";
    }
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' OR ', $where_clauses) : '';

// Count Total
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders o LEFT JOIN users u ON o.user_id = u.id $where_sql");
$stmt->execute($params);
$total_orders = $stmt->fetchColumn();
$total_pages = ceil($total_orders / $per_page);

// Fetch Orders
$sql = "SELECT o.*, u.full_name, u.email 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        $where_sql 
        ORDER BY o.created_at DESC 
        LIMIT $per_page OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                            <h6 class="text-white text-capitalize ps-3 mb-0">Order Management</h6>
                            <div class="pe-3">
                                <form action="" method="GET" class="d-flex gap-2">
                                    <input type="text" name="search" class="form-control form-control-sm bg-white px-2" 
                                           placeholder="Search by ID or User" 
                                           value="<?php echo htmlspecialchars($search ?? ''); ?>">
                                    <button type="submit" class="btn btn-sm btn-light mb-0">Search</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Order ID</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">User</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                        <th class="text-secondary opacity-7"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">#<?php echo $order['id']; ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($order['full_name'] ?? 'Unknown'); ?></p>
                                            <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($order['email'] ?? ''); ?></p>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="text-secondary text-xs font-weight-bold">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <?php
                                            $badge_class = match($order['status']) {
                                                'completed' => 'bg-gradient-success',
                                                'pending' => 'bg-gradient-warning',
                                                'cancelled' => 'bg-gradient-danger',
                                                'processing' => 'bg-gradient-info',
                                                'shipped' => 'bg-gradient-primary',
                                                default => 'bg-gradient-secondary'
                                            };
                                            ?>
                                            <span class="badge badge-sm <?php echo $badge_class; ?>"><?php echo ucfirst($order['status']); ?></span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold"><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></span>
                                        </td>
                                        <td class="align-middle">
                                            <a href="view.php?id=<?php echo $order['id']; ?>" class="text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="View order">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                     <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="card-footer px-3 border-0 d-flex flex-column flex-lg-row align-items-center justify-content-between">
                        <nav aria-label="Page navigation example">
                            <ul class="pagination mb-0">
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
