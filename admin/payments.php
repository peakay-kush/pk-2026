<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

// Handle payment status update
if (isset($_POST['update_status'])) {
    $payment_id = (int) $_POST['payment_id'];
    $new_status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE payments SET payment_status = ? WHERE id = ?");
    $stmt->execute([$new_status, $payment_id]);

    $_SESSION['flash_message'] = 'Payment status updated successfully!';
    $_SESSION['flash_type'] = 'success';
    header('Location: payments.php');
    exit;
}

$page_title = 'Payments Management';
require_once 'header.php';

// Get all payments with order and user details
$payments = $conn->query("
    SELECT p.*, o.id as order_id, o.total_amount as total, u.name as user_name, u.email as user_email
    FROM payments p
    LEFT JOIN orders o ON p.order_id = o.id
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY p.created_at DESC
")->fetchAll();

// Calculate statistics
$total_payments = count($payments);
$total_amount = array_sum(array_column($payments, 'amount'));
$pending_payments = count(array_filter($payments, fn($p) => $p['payment_status'] === 'pending'));
$completed_payments = count(array_filter($payments, fn($p) => $p['payment_status'] === 'completed'));
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $_SESSION['success'];
        unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="page-title"><i class="fas fa-money-bill-wave"></i> Payments Management</h2>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-primary"><?php echo number_format($total_payments); ?></h3>
                <p class="text-muted mb-0">Total Payments</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success">KSh <?php echo number_format($total_amount, 2); ?></h3>
                <p class="text-muted mb-0">Total Amount</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-warning"><?php echo number_format($pending_payments); ?></h3>
                <p class="text-muted mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-info"><?php echo number_format($completed_payments); ?></h3>
                <p class="text-muted mb-0">Completed</p>
            </div>
        </div>
    </div>
</div>

<!-- Payments Table -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Payment Method</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td>#<?php echo $payment['id']; ?></td>
                                    <td>
                                        <a href="orders.php?view=<?php echo $payment['order_id']; ?>"
                                            class="text-decoration-none">
                                            #<?php echo $payment['order_id']; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($payment['user_name']); ?></strong><br>
                                        <small
                                            class="text-muted"><?php echo htmlspecialchars($payment['user_email']); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $method_badge = match ($payment['payment_method']) {
                                            'MPESA' => 'bg-success',
                                            'card' => 'bg-info',
                                            'cash' => 'bg-warning',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?php echo $method_badge; ?>">
                                            <?php echo htmlspecialchars($payment['payment_method']); ?>
                                        </span>
                                    </td>
                                    <td><strong>KSh <?php echo number_format($payment['amount'], 2); ?></strong></td>
                                    <td>
                                        <?php
                                        $status_badge = match ($payment['payment_status']) {
                                            'pending' => 'bg-warning',
                                            'completed' => 'bg-success',
                                            'failed' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?php echo $status_badge; ?>">
                                            <?php echo ucfirst($payment['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($payment['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary"
                                            onclick="updatePaymentStatus(<?php echo $payment['id']; ?>, '<?php echo $payment['payment_status']; ?>')">
                                            <i class="fas fa-edit"></i> Update
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Payment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <?php echo csrfField(); ?>
                <div class="modal-body">
                    <input type="hidden" name="payment_id" id="paymentId">

                    <div class="mb-3">
                        <label class="form-label">Payment Status</label>
                        <select class="form-select" name="status" id="paymentStatus" required>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updatePaymentStatus(paymentId, currentStatus) {
        document.getElementById('paymentId').value = paymentId;
        document.getElementById('paymentStatus').value = currentStatus;
        new bootstrap.Modal(document.getElementById('statusModal')).show();
    }
</script>

<?php require_once 'footer.php'; ?>