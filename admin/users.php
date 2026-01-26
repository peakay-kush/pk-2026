<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

// Handle role update
if (isset($_GET['update_role'])) {
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        die('Invalid security token');
    }
    $user_id = (int) $_GET['update_role'];
    $role = $_GET['role'] ?? '';

    if (in_array($role, ['user', 'admin']) && $user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $user_id]);
        $_SESSION['flash_message'] = 'User role updated';
        $_SESSION['flash_type'] = 'success';
        header('Location: users.php');
        exit;
    }
}

$page_title = 'Manage Users';
require_once 'header.php';

// Fetch users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<h2 class="page-title"><i class="fas fa-users"></i> Manage Users</h2>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge <?php echo $user['role'] == 'admin' ? 'bg-danger' : 'bg-secondary'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($user['created_at']); ?></td>
                            <td>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle"
                                            data-bs-toggle="dropdown">
                                            Change Role
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item"
                                                    href="users.php?update_role=<?php echo $user['id']; ?>&role=user&csrf_token=<?php echo generateCSRFToken(); ?>">Make
                                                    User</a></li>
                                            <li><a class="dropdown-item"
                                                    href="users.php?update_role=<?php echo $user['id']; ?>&role=admin&csrf_token=<?php echo generateCSRFToken(); ?>">Make
                                                    Admin</a></li>
                                        </ul>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">You</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>