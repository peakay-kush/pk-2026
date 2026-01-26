<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

// Handle add/edit team member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_member'])) {
    $id = $_POST['id'] ?? null;
    $name = $_POST['name'] ?? '';
    $role = $_POST['role'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $sort_order = (int) ($_POST['sort_order'] ?? 0);

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../assets/images/team/';

        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['image']['type'];

        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['flash_message'] = 'Invalid file type. Only JPG, PNG, GIF, and WEBP allowed.';
            $_SESSION['flash_type'] = 'error';
            header('Location: team_members.php');
            exit;
        }

        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $new_filename = 'team_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_path = $new_filename;
        } else {
            $_SESSION['flash_message'] = 'Failed to upload image. Check directory permissions.';
            $_SESSION['flash_type'] = 'error';
            header('Location: team_members.php');
            exit;
        }
    }

    if ($id) {
        // Update
        if ($image_path) {
            // Delete old image
            $stmt = $conn->prepare("SELECT image_path FROM team_members WHERE id = ?");
            $stmt->execute([$id]);
            $old_member = $stmt->fetch();
            if ($old_member && $old_member['image_path']) {
                $old_file = __DIR__ . '/../assets/images/team/' . $old_member['image_path'];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }

            $stmt = $conn->prepare("UPDATE team_members SET name = ?, role = ?, bio = ?, image_path = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$name, $role, $bio, $image_path, $sort_order, $id]);
        } else {
            $stmt = $conn->prepare("UPDATE team_members SET name = ?, role = ?, bio = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$name, $role, $bio, $sort_order, $id]);
        }
        $_SESSION['flash_message'] = 'Team member updated successfully!';
    } else {
        // Insert - require image for new members from template
        if (!$image_path) {
            $_SESSION['flash_message'] = 'Please upload an image for the team member.';
            $_SESSION['flash_type'] = 'error';
            header('Location: team_members.php');
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO team_members (name, role, bio, image_path, sort_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $role, $bio, $image_path, $sort_order]);
        $_SESSION['flash_message'] = 'Team member added successfully!';
    }

    $_SESSION['flash_type'] = 'success';
    header('Location: team_members.php');
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        die('Invalid security token');
    }
    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("SELECT image_path FROM team_members WHERE id = ?");
    $stmt->execute([$id]);
    $member = $stmt->fetch();

    if ($member && $member['image_path']) {
        $file_path = '../assets/images/team/' . $member['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    $stmt = $conn->prepare("DELETE FROM team_members WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['flash_message'] = 'Team member deleted';
    $_SESSION['flash_type'] = 'success';
    header('Location: team_members.php');
    exit;
}

$page_title = 'Team Members';
require_once 'header.php';

// Fetch all team members
$members_result = $conn->query("SELECT * FROM team_members ORDER BY sort_order ASC, name ASC");
$members = $members_result->fetchAll();

// Get member for editing
$edit_member = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM team_members WHERE id = ?");
    $stmt->execute([(int) $_GET['edit']]);
    $edit_member = $stmt->fetch();
}
?>

<div class="container-fluid py-3">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="fas fa-users me-2"></i>Team Members</h2>
        </div>
        <div class="col-auto">
            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal"
                data-bs-target="#addMemberModal">
                <i class="fas fa-plus me-2"></i>Add Team Member
            </button>
        </div>
    </div>

    <?php if (count($members) == 0): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                <h4>No Team Members Yet</h4>
                <p class="text-muted">Click "Add Team Member" to start building your team</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($members as $member): ?>
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <div class="card h-100">
                        <?php if ($member['image_path']): ?>
                            <img src="../assets/images/team/<?php echo htmlspecialchars($member['image_path']); ?>"
                                class="card-img-top" style="height: 280px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 280px;">
                                <i class="fas fa-user fa-5x text-secondary"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title mb-2"><?php echo htmlspecialchars($member['name']); ?></h5>
                            <h6 class="text-primary mb-3"><?php echo htmlspecialchars($member['role']); ?></h6>
                            <p class="card-text text-muted small">
                                <?php echo htmlspecialchars(substr($member['bio'], 0, 120)); ?>...
                            </p>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="row g-2">
                                <div class="col-6">
                                    <a href="team_members.php?edit=<?php echo $member['id']; ?>"
                                        class="btn btn-outline-primary w-100">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="team_members.php?delete=<?php echo $member['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>"
                                        class="btn btn-outline-danger w-100"
                                        onclick="return confirm('Delete <?php echo htmlspecialchars($member['name']); ?>?');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Add Team Member
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name *</label>
                        <input type="text" name="name" class="form-control form-control-lg" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Role/Position *</label>
                        <input type="text" name="role" class="form-control form-control-lg" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Bio/Description</label>
                        <textarea name="bio" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Profile Photo *</label>
                        <div class="drop-zone">
                            <i class="fas fa-cloud-upload-alt icon"></i>
                            <div class="text">Drag & drop or <b>browse</b></div>
                            <input type="file" name="image" accept="image/*" required>
                        </div>
                        <div class="preview-container"></div>
                        <small class="text-muted">JPG, PNG, GIF or WEBP. Recommended: 500x500px</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Display Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_member" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Add Member
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Member Modal -->
<?php if ($edit_member): ?>
    <div class="modal fade" id="editMemberModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Team Member
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="id" value="<?php echo $edit_member['id']; ?>">
                    <div class="modal-body">
                        <?php if ($edit_member['image_path']): ?>
                            <div class="text-center mb-3">
                                <img src="../assets/images/team/<?php echo htmlspecialchars($edit_member['image_path']); ?>"
                                    class="img-thumbnail" style="max-height: 200px;">
                                <p class="text-muted small mt-2">Current photo</p>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Full Name *</label>
                            <input type="text" name="name" class="form-control form-control-lg"
                                value="<?php echo htmlspecialchars($edit_member['name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Role/Position *</label>
                            <input type="text" name="role" class="form-control form-control-lg"
                                value="<?php echo htmlspecialchars($edit_member['role']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Bio/Description</label>
                            <textarea name="bio" class="form-control"
                                rows="4"><?php echo htmlspecialchars($edit_member['bio']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Profile Photo (Optional - leave blank to keep current)</label>
                            <div class="drop-zone">
                                <i class="fas fa-cloud-upload-alt icon"></i>
                                <div class="text">Drag & drop or <b>browse</b></div>
                                <input type="file" name="image" accept="image/*">
                            </div>
                            <div class="preview-container"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Display Order</label>
                            <input type="number" name="sort_order" class="form-control"
                                value="<?php echo $edit_member['sort_order']; ?>">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="save_member" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>Update Member
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var editModal = new bootstrap.Modal(document.getElementById('editMemberModal'));
            editModal.show();
        });
    </script>
<?php endif; ?>

<?php require_once 'footer.php'; ?>