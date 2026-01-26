<?php
$page_title = 'My Dashboard';
require_once 'includes/header.php';

require_once 'includes/db.php';

// Require login
requireLogin();

$user_id = $_SESSION['user_id'];

// Show flash message if set
// Removed flash message and toast logic
?>
<?php if (!empty($_SESSION['order_success'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            showNotification('Order placed successfully!', 'success');
        });
    </script>
    <?php unset($_SESSION['order_success']); ?>
<?php endif; ?>
<?php

// Get current section (default: personal-info)
$section = $_GET['section'] ?? 'personal-info';

// Fetch user data
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->execute([$user_id]);
$user = $user_query->fetch();

// Fetch user preferences
$pref_query = $conn->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
$pref_query->execute([$user_id]);
$preferences = $pref_query->fetch();
if (!$preferences) {
    // Create default preferences
    $conn->prepare("INSERT INTO user_preferences (user_id) VALUES (?)")->execute([$user_id]);
    $preferences = ['email_notifications' => 1, 'sms_notifications' => 1];
}

// Fetch delivery addresses
$addr_query = $conn->prepare("SELECT * FROM delivery_addresses WHERE user_id = ? ORDER BY is_primary DESC, id ASC");
$addr_query->execute([$user_id]);
$addresses = $addr_query->fetchAll();

// Count statistics
try {
    $total_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = $user_id")->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $total_orders = 0;
}

try {
    // Try to get total spent - handle if column doesn't exist
    $total_spent_query = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE user_id = $user_id AND status = 'paid'");
    $total_spent = $total_spent_query->fetch()['total'] ?? 0;
} catch (PDOException $e) {
    // If total_amount column doesn't exist, try alternative column names or set to 0
    try {
        $total_spent_query = $conn->query("SELECT SUM(amount) as total FROM orders WHERE user_id = $user_id AND status = 'paid'");
        $total_spent = $total_spent_query->fetch()['total'] ?? 0;
    } catch (PDOException $e2) {
        $total_spent = 0;
    }
}
?>

<!-- Profile Hero Banner -->
<section class="profile-hero">
    <div class="container text-center">
        <div class="profile-photo-wrapper">
            <img src="<?php echo $user['profile_photo'] ? SITE_URL . '/assets/images/' . $user['profile_photo'] : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=0B63CE&color=fff&size=200'; ?>"
                alt="Profile" class="profile-photo">
        </div>
        <h2 class="text-white mt-3 mb-1"><?php echo htmlspecialchars($user['name']); ?></h2>
        <p class="text-white-50"><?php echo htmlspecialchars($user['email']); ?></p>
    </div>
</section>

<!-- Dashboard Content -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="row g-4">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <!-- Account Menu -->
                <div class="dashboard-card mb-4">
                    <h5 class="card-header">Account Menu</h5>
                    <div class="list-group list-group-flush">
                        <a href="?section=personal-info"
                            class="list-group-item list-group-item-action <?php echo $section == 'personal-info' ? 'active' : ''; ?>">
                            <i class="fas fa-user"></i> Personal Information
                        </a>
                        <a href="?section=addresses"
                            class="list-group-item list-group-item-action <?php echo $section == 'addresses' ? 'active' : ''; ?>">
                            <i class="fas fa-map-marker-alt"></i> Delivery Addresses
                        </a>
                        <a href="?section=settings"
                            class="list-group-item list-group-item-action <?php echo $section == 'settings' ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i> Account Settings
                        </a>
                        <?php if (isAdmin()): ?>
                            <a href="db_manager.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-database"></i> Database Manager
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo SITE_URL; ?>/logout.php"
                            class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="dashboard-card">
                    <h5 class="card-header">Quick Stats</h5>
                    <div class="card-body text-center">
                        <div class="stat-item mb-3">
                            <h2 class="stat-number text-primary"><?php echo $total_orders; ?></h2>
                            <p class="text-muted mb-0">Total Orders</p>
                        </div>
                        <div class="stat-item">
                            <h2 class="stat-number text-success">Ksh <?php echo number_format($total_spent, 0); ?></h2>
                            <p class="text-muted mb-0">Total Spent</p>
                        </div>
                        <a href="?section=orders" class="btn btn-outline-primary btn-sm w-100 mt-3">
                            <i class="fas fa-list"></i> View Orders
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <?php if ($section == 'personal-info'): ?>
                    <!-- Personal Information -->
                    <div class="dashboard-card">
                        <h5 class="card-header">Personal Information</h5>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted text-uppercase small fw-bold ls-1">Full Name</label>
                                    <p class="profile-value mb-0"><?php echo htmlspecialchars($user['name']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted text-uppercase small fw-bold ls-1">Email
                                        Address</label>
                                    <p class="profile-value mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted text-uppercase small fw-bold ls-1">Phone
                                        Number</label>
                                    <p class="profile-value mb-0">
                                        <?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted text-uppercase small fw-bold ls-1">Member
                                        Since</label>
                                    <p class="profile-value mb-0">
                                        <?php echo date('d F Y', strtotime($user['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <label class="form-label text-muted text-uppercase small fw-bold ls-1">Account
                                                Status</label>
                                            <div class="mt-1">
                                                <span
                                                    class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold">
                                                    <i class="fas fa-check-circle me-1"></i> Active
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-4 opacity-10">
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                    <i class="fas fa-edit"></i> Edit Profile
                                </button>
                                <button class="btn btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#changePhotoModal">
                                    <i class="fas fa-camera"></i> Change Profile Photo
                                </button>
                                </a>
                            </div>
                        </div>
                    </div>

                <?php elseif ($section == 'addresses'): ?>
                    <!-- Delivery Addresses -->
                    <div class="dashboard-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Delivery Addresses</h5>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                                <i class="fas fa-plus"></i> Edit Addresses
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if (count($addresses) > 0): ?>
                                <div class="row g-3">
                                    <?php foreach ($addresses as $address): ?>
                                        <div class="col-md-6">
                                            <div
                                                class="address-card <?php echo $address['is_primary'] ? 'address-primary' : 'address-secondary'; ?>">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="mb-0"><i class="fas fa-map-marker-alt"></i>
                                                        <?php echo $address['is_primary'] ? 'Primary' : 'Secondary'; ?></h6>
                                                    <span
                                                        class="badge <?php echo $address['is_primary'] ? 'bg-primary' : 'bg-secondary'; ?>">
                                                        <?php echo $address['is_primary'] ? 'PRIMARY' : 'SECONDARY'; ?>
                                                    </span>
                                                </div>
                                                <p class="mb-1"><strong><?php echo htmlspecialchars($address['name']); ?></strong>
                                                </p>
                                                <p class="mb-1"><i class="fas fa-phone"></i>
                                                    <?php echo htmlspecialchars($address['phone']); ?></p>
                                                <?php if ($address['street']): ?>
                                                    <p class="mb-1"><i class="fas fa-road"></i>
                                                        <?php echo htmlspecialchars($address['street']); ?></p>
                                                <?php endif; ?>
                                                <p class="mb-1"><i class="fas fa-building"></i>
                                                    <?php echo htmlspecialchars($address['address_line']); ?></p>
                                                <?php if ($address['postal_code']): ?>
                                                    <p class="mb-1"><i class="fas fa-envelope"></i>
                                                        <?php echo htmlspecialchars($address['postal_code']); ?></p>
                                                <?php endif; ?>
                                                <p class="mb-2"><i class="fas fa-map-pin"></i>
                                                    <?php echo htmlspecialchars($address['city']); ?></p>
                                                <div class="d-flex gap-2 mt-2">
                                                    <?php if (!$address['is_primary']): ?>
                                                        <a href="set_primary_address.php?id=<?php echo $address['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>"
                                                            class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-star"></i> Set Primary
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="delete_address.php?id=<?php echo $address['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>"
                                                        class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('Are you sure you want to delete this address?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-map-marker-alt text-muted" style="font-size: 4rem;"></i>
                                    <h4 class="mt-3">No Addresses Added</h4>
                                    <p class="text-muted">Add a delivery address to get started</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php elseif ($section == 'settings'): ?>
                    <!-- Account Settings -->
                    <div class="dashboard-card">
                        <h5 class="card-header">Account Settings</h5>
                        <div class="card-body">
                            <!-- Security -->
                            <div class="settings-section mb-4">
                                <h6><i class="fas fa-shield-alt"></i> Security</h6>
                                <a href="password_reset_request.php" class="btn btn-outline-primary mt-2">
                                    <i class="fas fa-key"></i> Change Password
                                </a>
                            </div>

                            <hr>

                            <!-- Notifications -->
                            <div class="settings-section">
                                <h6><i class="fas fa-bell"></i> Notifications</h6>

                                <div class="notification-item d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <strong>Email Notifications</strong>
                                        <p class="text-muted small mb-0">Receive order updates via email</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="emailNotif" <?php echo $preferences['email_notifications'] ? 'checked' : ''; ?>>
                                    </div>
                                </div>

                                <div class="notification-item d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <strong>SMS Notifications</strong>
                                        <p class="text-muted small mb-0">Receive order updates via SMS</p>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="smsNotif" <?php echo $preferences['sms_notifications'] ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Danger Zone -->
                            <div class="settings-section">
                                <h6 class="text-danger"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h6>
                                <button class="btn btn-outline-danger mt-2" data-bs-toggle="modal"
                                    data-bs-target="#deleteAccountModal">
                                    <i class="fas fa-trash"></i> Delete Account
                                </button>
                            </div>
                        </div>
                    </div>

                <?php elseif ($section == 'orders'): ?>
                    <!-- Orders List -->
                    <?php
                    $orders_query = $conn->prepare("
                        SELECT o.* 
                        FROM orders o
                        LEFT JOIN hidden_orders h ON o.id = h.order_id AND h.user_id = ?
                        WHERE o.user_id = ? AND h.id IS NULL
                        ORDER BY o.created_at DESC
                    ");
                    $orders_query->execute([$user_id, $user_id]);
                    $orders = $orders_query->fetchAll();
                    ?>
                    <div class="dashboard-card">
                        <h5 class="card-header">My Orders</h5>
                        <div class="card-body">
                            <?php if (count($orders) > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Date</th>
                                                <th>Total Amount</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($orders as $order): ?>
                                                <tr>
                                                    <td>#<?php echo $order['id']; ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                                    <td>Ksh
                                                        <?php echo number_format(isset($order['total_amount']) ? $order['total_amount'] : ($order['amount'] ?? 0), 2); ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $badge_class = '';
                                                        switch ($order['status']) {
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
                                                    </td>
                                                    <td>
                                                        <a href="order_details.php?id=<?php echo $order['id']; ?>"
                                                            class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-shopping-bag text-muted" style="font-size: 4rem;"></i>
                                    <h4 class="mt-3">No Orders Yet</h4>
                                    <p class="text-muted">Start shopping to see your orders here</p>
                                    <a href="shop.php" class="btn btn-primary">Browse Products</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="update_profile.php">
                <?php echo csrfField(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name"
                            value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email"
                            value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" name="phone"
                            value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="change_password.php">
                <?php echo csrfField(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-control" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Photo Modal -->
<div class="modal fade" id="changePhotoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Profile Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="update_photo.php" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Choose Photo</label>
                        <input type="file" class="form-control" name="profile_photo" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Photo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="addAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Delivery Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="add_address.php">
                <?php echo csrfField(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Street</label>
                        <input type="text" class="form-control" name="street" placeholder="e.g., Kimathi Street">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address Line</label>
                        <input type="text" class="form-control" name="address_line"
                            placeholder="e.g., Building name, floor, apartment" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Postal Code</label>
                        <input type="text" class="form-control" name="postal_code">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" name="city" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_primary" id="isPrimary">
                            <label class="form-check-label" for="isPrimary">Set as primary address</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Address</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="delete_account.php">
                <?php echo csrfField(); ?>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning!</strong> This action cannot be undone. All your data including orders,
                        addresses, and preferences will be permanently deleted.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm by entering your password:</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete My Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Handle notification toggles - check if elements exist first
    const emailNotif = document.getElementById('emailNotif');
    const smsNotif = document.getElementById('smsNotif');

    if (emailNotif) {
        emailNotif.addEventListener('change', function () {
            updatePreference('email_notifications', this.checked ? 1 : 0);
        });
    }

    if (smsNotif) {
        smsNotif.addEventListener('change', function () {
            updatePreference('sms_notifications', this.checked ? 1 : 0);
        });
    }

    function updatePreference(key, value) {
        fetch('update_preferences.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `key=${key}&value=${value}&csrf_token=<?php echo generateCSRFToken(); ?>`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Preference updated successfully');
                }
            })
            .catch(error => console.error('Error:', error));
    }
</script>

<style>
    .order-toast {
        position: fixed;
        top: 32px;
        right: 32px;
        background: linear-gradient(90deg, #0B63CE 60%, #2D9CDB 100%);
        color: #fff;
        padding: 1.1rem 2.2rem;
        border-radius: 10px;
        font-size: 1.15rem;
        font-weight: 700;
        box-shadow: 0 4px 16px rgba(11, 99, 206, 0.13);
        z-index: 9999;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s;
    }

    .order-toast.show {
        opacity: 1;
        pointer-events: auto;
    }

    .profile-value {
        font-family: 'Outfit', sans-serif;
        font-size: 1.15rem;
        font-weight: 600;
        color: var(--text-dark);
        letter-spacing: -0.01em;
    }

    .ls-1 {
        letter-spacing: 0.1em;
    }

    .bg-success-subtle {
        background-color: #d1e7dd;
    }

    .text-success {
        color: #198754 !important;
    }

    .border-success-subtle {
        border-color: #a3cfbb !important;
    }
</style>
<?php require_once 'includes/footer.php'; ?>