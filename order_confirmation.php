$<?php
echo 'DEBUG: order_confirmation.php loaded<br>';
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$page_title = 'Order Confirmation';
require_once 'includes/header.php';
require_once 'includes/db.php';

// Require login
requireLogin();

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id === 0) {
    redirect('dashboard.php', 'Invalid order', 'error');
}

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    redirect('dashboard.php', 'Order not found', 'error');
}

// Fetch order items
$items_stmt = $conn->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$items_stmt->execute([$order_id]);
$order_items = $items_stmt;
?>

<!-- Page Header -->
<section class="section-padding bg-light">
    <div class="container text-center">
        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
        <h1 class="mt-3">Order Confirmed!</h1>
        <p class="lead">Thank you for your purchase</p>
    </div>
</section>

<!-- Order Details -->
<section class="section-padding">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Order #<?php echo $order['id']; ?></h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Order Date:</strong><br><?php echo formatDate($order['created_at']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Status:</strong><br>
                                    <span class="badge bg-warning"><?php echo ucfirst($order['status']); ?></span>
                                </p>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">Order Items</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = $order_items->fetch()): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" 
                                                     style="width: 50px; height: 50px; object-fit: cover;" 
                                                     class="me-2 rounded"
                                                     alt="Product Image">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo formatPrice($item['price']); ?></td>
                                        <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td><strong class="text-primary"><?php echo formatPrice($order['total_amount']); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle"></i> A confirmation email has been sent to <strong><?php echo htmlspecialchars($_SESSION['user_email']); ?></strong>
                        </div>
                        
                        <div class="mt-4 d-flex gap-2 justify-content-center">
                            <a href="dashboard.php" class="btn btn-primary">
                                <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                            </a>
                            <a href="shop.php" class="btn btn-outline-primary">
                                <i class="fas fa-shopping-bag"></i> Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
