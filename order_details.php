<?php
$page_title = 'Order Details';
require_once 'includes/header.php';
require_once 'includes/db.php';

requireLogin();

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Fetch order details
$order_query = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$order_query->execute([$order_id, $user_id]);
$order = $order_query->fetch();

if (!$order) {
    setFlashMessage('Order not found', 'error');
    header('Location: dashboard.php?section=orders');
    exit;
}

// Fetch order items
$items_query = $conn->prepare("
    SELECT oi.*, p.name as product_name, p.image as product_image 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$items_query->execute([$order_id]);
$items = $items_query->fetchAll();

// Get total amount
$total = isset($order['total_amount']) ? $order['total_amount'] : ($order['amount'] ?? 0);
?>

<section class="section-padding bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-receipt"></i> Order #<?php echo $order['id']; ?></h4>
                        <a href="dashboard.php?section=orders" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Order Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Order Information</h6>
                                <p class="mb-1"><strong>Order Date:</strong> <?php echo date('F d, Y', strtotime($order['created_at'])); ?></p>
                                <p class="mb-1"><strong>Status:</strong> 
                                    <?php
                                    $badge_class = '';
                                    switch($order['status']) {
                                        case 'paid':
                                            $badge_class = 'bg-success';
                                            break;
                                        case 'pending':
                                            $badge_class = 'bg-warning';
                                            break;
                                        case 'cancelled':
                                            $badge_class = 'bg-danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </p>
                                <p class="mb-0"><strong>Total Amount:</strong> Ksh <?php echo number_format($total, 2); ?></p>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <h6>Order Items</h6>
                        <?php if (count($items) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if ($item['product_image']): ?>
                                            <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo $item['product_image']; ?>" 
                                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                 style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($item['product_name'] ?? 'Product'); ?>
                                        </td>
                                        <td>Ksh <?php echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>Ksh <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>Ksh <?php echo number_format($total, 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No items found for this order.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
