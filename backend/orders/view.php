<?php
require_once '../../config.php';
require_once '../config.php'; 

// Check admin
require_admin();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($order_id === 0) {
    header('Location: index.php');
    exit;
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    $valid_statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
    
    if (in_array($new_status, $valid_statuses)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $success_msg = "Order status updated successfully.";
    }
}

// Fetch Order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}

// Fetch User
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$order['user_id']]);
$user = $stmt->fetch();

// Fetch Order Items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.image_url 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <div class="container-fluid py-4">
        
        <?php if (isset($success_msg)): ?>
        <div class="alert alert-success text-white" role="alert">
            <?php echo $success_msg; ?>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                            <h6 class="text-white text-capitalize ps-3">Order #<?php echo $order['id']; ?> Details</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Product</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Price</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Qty</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div>
                                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="avatar avatar-sm me-3 border-radius-lg" alt="product">
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="text-secondary text-xs font-weight-bold">$<?php echo number_format($item['price'], 2); ?></span>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="text-secondary text-xs font-weight-bold"><?php echo $item['quantity']; ?></span>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="text-secondary text-xs font-weight-bold">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="border-top">
                                        <td colspan="3" class="text-end pe-4 font-weight-bold">Total Amount:</td>
                                        <td class="text-center font-weight-bold text-primary">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-secondary shadow-secondary border-radius-lg pt-4 pb-3">
                            <h6 class="text-white text-capitalize ps-3">Customer Info</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h6>
                        <p class="text-xs text-secondary mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                        
                        <h6 class="text-sm font-weight-bold mb-1">Shipping Address:</h6>
                        <p class="text-sm text-secondary mb-3"><?php echo htmlspecialchars($order['shipping_address']); ?></p>
                        
                        <h6 class="text-sm font-weight-bold mb-1">Payment Method:</h6>
                        <p class="text-sm text-secondary mb-3 text-capitalize"><?php echo htmlspecialchars($order['payment_method']); ?></p>
                        
                        <hr class="dark horizontal my-3">
                        
                        <h6 class="text-sm font-weight-bold mb-2">Update Status:</h6>
                        <form method="POST">
                            <div class="input-group input-group-outline mb-3">
                                <select name="status" class="form-control" onchange="this.form.submit()">
                                    <?php 
                                    $statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
                                    foreach ($statuses as $s): 
                                    ?>
                                    <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($s); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
                
                 <div class="text-center">
                    <a href="index.php" class="btn btn-outline-secondary w-100">Back to Orders</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
