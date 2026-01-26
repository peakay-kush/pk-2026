<?php
$page_title = 'Dashboard';
require_once 'header.php';

// Fetch statistics
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch()['count'];
$pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch()['count'];
$total_revenue = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE status = 'paid'")->fetch()['total'] ?? 0;

// Fetch recent orders
$recent_orders = $conn->query("SELECT o.*, u.name as user_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10");

// Fetch recent messages
$recent_messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
?>

<h1 class="page-title"><i class="fas fa-chart-line"></i> Dashboard Overview</h1>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card blue text-center">
            <div class="icon">
                <i class="fas fa-box"></i>
            </div>
            <h3><?php echo $total_products; ?></h3>
            <p>Total Products</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card green text-center">
            <div class="icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h3><?php echo $total_orders; ?></h3>
            <p>Total Orders</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card blue text-center">
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <h3><?php echo $total_users; ?></h3>
            <p>Total Users</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card green text-center">
            <div class="icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <h3><?php echo formatPrice($total_revenue); ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
</div>

<!-- Pending Orders Alert -->
<?php if ($pending_orders > 0): ?>
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i> You have <strong><?php echo $pending_orders; ?></strong> pending
        order(s) to process.
        <a href="orders.php?status=pending" class="alert-link">View pending orders</a>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Recent Orders -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Recent Orders</h5>
                <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Order ID</th>
                                <th style="width: 180px;">Customer</th>
                                <th style="width: 140px;">Amount</th>
                                <th style="width: 120px;">Status</th>
                                <th style="width: 140px;">Date</th>
                                <th style="width: 100px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recent_orders->fetch()): ?>
                                <tr>
                                    <td><strong>#<?php echo $order['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                    <td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                                    <td>
                                        <?php
                                        $badge_class = $order['status'] == 'paid' ? 'bg-success' : ($order['status'] == 'pending' ? 'bg-warning' : 'bg-danger');
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($order['created_at']); ?></td>
                                    <td>
                                        <a href="order_view.php?id=<?php echo $order['id']; ?>"
                                            class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Messages -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Recent Messages</h5>
                <a href="messages.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if ($recent_messages->rowCount() > 0): ?>
                    <div class="row g-3">
                        <?php while ($message = $recent_messages->fetch()): ?>
                            <div class="col-md-6">
                                <div class="p-3 border rounded bg-light">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong class="d-block"><?php echo htmlspecialchars($message['name']); ?></strong>
                                            <small class="text-muted"><?php echo htmlspecialchars($message['email']); ?></small>
                                        </div>
                                        <small class="text-muted"><?php echo formatDate($message['created_at']); ?></small>
                                    </div>
                                    <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                                        <?php echo htmlspecialchars(substr($message['message'], 0, 100)) . '...'; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-4">No messages yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>