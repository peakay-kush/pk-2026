<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

// Handle status update
if (isset($_GET['update_status'])) {
    // CSRF Protection for state-changing GET request
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        die('Invalid security token. Please refresh and try again.');
    }

    $order_id = (int) $_GET['update_status'];
    $status = $_GET['status'] ?? '';

    if (in_array($status, ['pending', 'paid', 'dispatched', 'complete', 'cancelled'])) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $order_id])) {
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
                $message .= "<p>The status of your order <strong>#" . $order_id . "</strong> has been updated to: <strong>" . ucfirst($status) . "</strong>.</p>";
                $message .= $items_list;
                $message .= $shipping_info;
                $message .= "<p>Payment Method: <strong>" . ucfirst($customer['payment_method']) . "</strong></p>";
                $message .= "<p>You can view your order details in your dashboard.</p>";
                sendEmail($customer['email'], $subject, $message);

                // Email to Admin
                $admin_subject = "Order #" . $order_id . " Status Updated to " . ucfirst($status);
                $admin_message = "<h2>Order Status Update Notification</h2>";
                $admin_message .= "<p>The status of Order <strong>#" . $order_id . "</strong> has been successfully updated.</p>";
                $admin_message .= "<p><strong>Customer:</strong> " . htmlspecialchars($customer['name']) . "</p>";
                $admin_message .= "<p><strong>New Status:</strong> " . ucfirst($status) . "</p>";
                $admin_message .= $items_list;
                $admin_message .= $shipping_info;
                $admin_message .= "<p><strong>Payment Method:</strong> " . ucfirst($customer['payment_method']) . "</p>";
                sendEmail(ADMIN_EMAIL, $admin_subject, $admin_message);
            }

            $_SESSION['flash_message'] = 'Order status updated and notification sent';
            $_SESSION['flash_type'] = 'success';
        }
        header('Location: orders.php');
        exit;
    }
}

$page_title = 'Manage Orders';
require_once 'header.php';

// Filter by status
$status_filter = $_GET['status'] ?? '';
$where_sql = $status_filter ? "WHERE o.status = " . $conn->quote($status_filter) : '';

// Fetch orders
$query_sql = "
    SELECT o.*, u.name as user_name, u.email as user_email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    $where_sql
    ORDER BY o.created_at DESC
";
$orders_stmt = $conn->query($query_sql);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-title mb-0"><i class="fas fa-shopping-cart"></i> Manage Orders</h2>
</div>

<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list"></i> All Orders</h5>
    </div>
    <div class="card-body">
        <div class="mb-4">
            <a href="orders.php" class="btn btn-outline-primary <?php echo !$status_filter ? 'active' : ''; ?>">All
                Orders</a>
            <a href="orders.php?status=pending"
                class="btn btn-outline-warning <?php echo $status_filter == 'pending' ? 'active' : ''; ?>">Pending</a>
            <a href="orders.php?status=paid"
                class="btn btn-outline-success <?php echo $status_filter == 'paid' ? 'active' : ''; ?>">Paid</a>
            <a href="orders.php?status=dispatched"
                class="btn btn-outline-info <?php echo $status_filter == 'dispatched' ? 'active' : ''; ?>">Dispatched</a>
            <a href="orders.php?status=complete"
                class="btn btn-outline-primary <?php echo $status_filter == 'complete' ? 'active' : ''; ?>">Complete</a>
            <a href="orders.php?status=cancelled"
                class="btn btn-outline-danger <?php echo $status_filter == 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders_stmt && $orders_stmt->rowCount() > 0): ?>
                        <?php while ($order = $orders_stmt->fetch()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['user_email']); ?></td>
                                <td><?php echo formatPrice($order['total_amount']); ?></td>
                                <td>
                                    <?php
                                    $badge_map = [
                                        'pending' => 'bg-warning',
                                        'paid' => 'bg-success',
                                        'dispatched' => 'bg-info',
                                        'complete' => 'bg-primary',
                                        'cancelled' => 'bg-danger'
                                    ];
                                    $badge_class = $badge_map[$order['status']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($order['created_at']); ?></td>
                                <td class="text-nowrap">
                                    <a href="order_view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary me-2">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="collapse"
                                        data-bs-target="#statusMenu<?php echo $order['id']; ?>">
                                        Status <i class="fas fa-chevron-down"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr class="collapse" id="statusMenu<?php echo $order['id']; ?>">
                                <td colspan="7" class="p-0">
                                    <div class="list-group list-group-flush">
                                        <a href="orders.php?update_status=<?php echo $order['id']; ?>&status=pending&csrf_token=<?php echo generateCSRFToken(); ?>"
                                            class="list-group-item list-group-item-action">Mark Pending</a>
                                        <a href="orders.php?update_status=<?php echo $order['id']; ?>&status=paid&csrf_token=<?php echo generateCSRFToken(); ?>"
                                            class="list-group-item list-group-item-action">Mark Paid</a>
                                        <a href="orders.php?update_status=<?php echo $order['id']; ?>&status=dispatched&csrf_token=<?php echo generateCSRFToken(); ?>"
                                            class="list-group-item list-group-item-action">Mark Dispatched</a>
                                        <a href="orders.php?update_status=<?php echo $order['id']; ?>&status=complete&csrf_token=<?php echo generateCSRFToken(); ?>"
                                            class="list-group-item list-group-item-action">Mark Complete</a>
                                        <a href="orders.php?update_status=<?php echo $order['id']; ?>&status=cancelled&csrf_token=<?php echo generateCSRFToken(); ?>"
                                            class="list-group-item list-group-item-action">Mark Cancelled</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>