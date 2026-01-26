<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

// Handle delete
if (isset($_GET['delete'])) {
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        die('Invalid security token');
    }
    $id = (int) $_GET['delete'];
    $conn->query("DELETE FROM contact_messages WHERE id = $id");
    $_SESSION['flash_message'] = 'Message deleted successfully';
    $_SESSION['flash_type'] = 'success';
    header('Location: messages.php');
    exit;
}

$page_title = 'Contact Messages';
require_once 'header.php';

// Fetch messages
$messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-title mb-0 border-0 p-0"><i class="fas fa-envelope"></i> Contact Messages</h2>
    <span class="badge bg-primary rounded-pill px-3 py-2"><?php echo count($messages); ?> Messages</span>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <?php if (count($messages) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Name / Email</th>
                            <th>Message Preview</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $message): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($message['name']); ?></div>
                                    <div class="small text-muted"><?php echo htmlspecialchars($message['email']); ?></div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 300px;">
                                        <?php echo htmlspecialchars($message['message']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?php echo date('M d, Y', strtotime($message['created_at'])); ?>
                                    </span>
                                    <div class="small text-muted mt-1">
                                        <?php echo date('h:i A', strtotime($message['created_at'])); ?>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal"
                                        data-bs-target="#messageModal<?php echo $message['id']; ?>">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <a href="messages.php?delete=<?php echo $message['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to delete this message?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-inbox text-muted opacity-25" style="font-size: 4rem;"></i>
                </div>
                <h5 class="text-muted">No messages found</h5>
                <p class="text-muted small">New contact form submissions will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modals (Outside Table) -->
<?php foreach ($messages as $message): ?>
    <div class="modal fade" id="messageModal<?php echo $message['id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">
                        <i class="fas fa-user-circle text-primary me-2"></i>
                        <?php echo htmlspecialchars($message['name']); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($message['email']); ?>
                        </span>
                        <small class="text-muted">
                            <?php echo date('F d, Y h:i A', strtotime($message['created_at'])); ?>
                        </small>
                    </div>
                    <div class="p-3 bg-light rounded border">
                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-reply"></i> Reply via Email
                    </a>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php require_once 'footer.php'; ?>