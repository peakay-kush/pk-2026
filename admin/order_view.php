<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

$order_id = (int) ($_GET['id'] ?? 0);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    if (in_array($new_status, ['pending', 'paid', 'dispatched', 'complete', 'cancelled'])) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if ($stmt->execute([$new_status, $order_id])) {
            // Fetch customer email, payment method, and shipping info for notification
            $customer_stmt = $conn->prepare("
                SELECT u.email, u.name, o.payment_method, o.shipping_address_line, o.shipping_street, o.shipping_city, o.shipping_phone, o.shipping_fee, sl.name as location_name 
                FROM users u 
                JOIN orders o ON o.user_id = u.id 
                LEFT JOIN shipping_locations sl ON o.shipping_location_id = sl.id
                WHERE o.id = ?
            ");
            $customer_stmt->execute([$order_id]);
            $customer = $customer_stmt->fetch();

            if ($customer) {
                // Fetch order items for the email
                $items_stmt = $conn->prepare("SELECT oi.quantity, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                $items_stmt->execute([$order_id]);
                $items_data = $items_stmt->fetchAll();

                $items_list = "<h3>Order Items:</h3><ul>";
                foreach ($items_data as $item) {
                    $items_list .= "<li>" . htmlspecialchars($item['name']) . " x " . $item['quantity'] . "</li>";
                }
                $items_list .= "</ul>";

                $shipping_info = "<h3>Shipping Information:</h3>";
                $shipping_info .= "<p><strong>Address:</strong> " . htmlspecialchars($customer['shipping_address_line'] ?? 'N/A') . "</p>";
                $shipping_info .= "<p><strong>Street:</strong> " . htmlspecialchars($customer['shipping_street'] ?? 'N/A') . "</p>";
                $shipping_info .= "<p><strong>City:</strong> " . htmlspecialchars($customer['shipping_city'] ?? 'N/A') . "</p>";
                $shipping_info .= "<p><strong>Phone:</strong> " . htmlspecialchars($customer['shipping_phone'] ?? 'N/A') . "</p>";
                $shipping_info .= "<p><strong>Location:</strong> " . htmlspecialchars($customer['location_name'] ?? 'N/A') . " (KSh " . number_format($customer['shipping_fee'] ?? 0, 2) . ")</p>";

                // Email to Customer
                $subject = "Order Status Updated - #" . $order_id;
                $message = "<h2>Hello " . htmlspecialchars($customer['name']) . ",</h2>";
                $message .= "<p>The status of your order <strong>#" . $order_id . "</strong> has been updated to: <strong>" . ucfirst($new_status) . "</strong>.</p>";
                $message .= $items_list;
                $message .= $shipping_info;
                $message .= "<p>Payment Method: <strong>" . ucfirst($customer['payment_method']) . "</strong></p>";
                $message .= "<p>You can view your order details in your dashboard.</p>";
                sendEmail($customer['email'], $subject, $message);

                // Email to Admin
                $admin_subject = "Order #" . $order_id . " Status Updated to " . ucfirst($new_status);
                $admin_message = "<h2>Order Status Update Notification</h2>";
                $admin_message .= "<p>The status of Order <strong>#" . $order_id . "</strong> has been successfully updated.</p>";
                $admin_message .= "<p><strong>Customer:</strong> " . htmlspecialchars($customer['name']) . "</p>";
                $admin_message .= "<p><strong>New Status:</strong> " . ucfirst($new_status) . "</p>";
                $admin_message .= $items_list;
                $admin_message .= $shipping_info;
                $admin_message .= "<p><strong>Payment Method:</strong> " . ucfirst($customer['payment_method']) . "</p>";
                sendEmail(ADMIN_EMAIL, $admin_subject, $admin_message);
            }

            $_SESSION['flash_message'] = 'Order status updated successfully and notifications sent!';
            $_SESSION['flash_type'] = 'success';
        }
        header("Location: order_view?id=$order_id");
        exit;
    }
}

// Fetch order with user details
$stmt = $conn->prepare("
    SELECT o.*, u.name as user_name, u.email as user_email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['flash_message'] = 'Order not found';
    $_SESSION['flash_type'] = 'error';
    header('Location: orders');
    exit;
}

$page_title = 'View Order';
require_once 'header.php';

// Fetch order items from order_items table
$items_stmt = $conn->prepare("
    SELECT oi.*, p.name as product_name 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$items_stmt->execute([$order_id]);
$items = [];
while ($row = $items_stmt->fetch()) {
    $items[] = [
        'id' => $row['product_id'],
        'name' => $row['product_name'] ?? 'Product #' . $row['product_id'],
        'quantity' => $row['quantity'],
        'price' => $row['price']
    ];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-title mb-0"><i class="fas fa-receipt"></i> Order #<?php echo $order['id']; ?></h2>
    <a href="orders" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Orders
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Order Items -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-shopping-bag"></i> Order Items
            </div>
            <div class="card-body p-0">
                <?php if (empty($items) || !is_array($items)): ?>
                    <div class="alert alert-warning m-3">
                        <i class="fas fa-exclamation-triangle"></i> No items found in this order.
                        The order data may be incomplete.
                        <br><small class="text-muted">Raw data:
                            <?php echo htmlspecialchars($order['items'] ?? 'NULL'); ?></small>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 50%;">Product</th>
                                    <th style="width: 15%;">Quantity</th>
                                    <th style="width: 17.5%;">Price</th>
                                    <th style="width: 17.5%;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $subtotal = 0;
                                foreach ($items as $item):
                                    $item_total = ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
                                    $subtotal += $item_total;
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['name'] ?? 'Unknown Product'); ?></strong>
                                            <?php if (isset($item['id'])): ?>
                                                <br><small class="text-muted">Product ID: <?php echo $item['id']; ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo $item['quantity'] ?? 0; ?></strong></td>
                                        <td><?php echo formatPrice($item['price'] ?? 0); ?></td>
                                        <td><strong><?php echo formatPrice($item_total); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td><strong><?php echo formatPrice($subtotal); ?></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong
                                            class="text-primary fs-5"><?php echo formatPrice($order['total_amount']); ?></strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user"></i> Customer Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($order['user_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['user_email']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Payment Method:</strong> <span
                                class="badge bg-info"><?php echo ucfirst($order['payment_method']); ?></span></p>
                        <p><strong>Order Date:</strong>
                            <?php echo date('M d, Y g:i A', strtotime($order['created_at'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Order Status -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Order Status
            </div>
            <div class="card-body">
                <?php
                $status_badge = 'bg-secondary';
                switch ($order['status']) {
                    case 'pending':
                        $status_badge = 'bg-warning';
                        break;
                    case 'paid':
                        $status_badge = 'bg-success';
                        break;
                    case 'dispatched':
                        $status_badge = 'bg-info';
                        break;
                    case 'complete':
                        $status_badge = 'bg-primary';
                        break;
                    case 'cancelled':
                        $status_badge = 'bg-danger';
                        break;
                }
                ?>
                <h4 class="text-center mb-4">
                    <span class="badge <?php echo $status_badge; ?> w-100 py-3">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </h4>

                <form method="POST">
                    <?php echo csrfField(); ?>
                    <div class="mb-3">
                        <label class="form-label">Update Status</label>
                        <select name="status" class="form-select">
                            <option value="dispatched" <?php echo $order['status'] == 'dispatched' ? 'selected' : ''; ?>>
                                Dispatched</option>
                            <option value="complete" <?php echo $order['status'] == 'complete' ? 'selected' : ''; ?>>
                                Complete</option>
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending
                            </option>
                            <option value="paid" <?php echo $order['status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>
                                Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sync"></i> Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-invoice-dollar"></i> Order Summary
            </div>
            <div class="card-body">
                <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
                <p><strong>Total Amount:</strong> <span
                        class="text-primary h5"><?php echo formatPrice($order['total_amount']); ?></span></p>
                <p><strong>Items:</strong> <?php echo count($items); ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>