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
    $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_message'] = 'Service deleted successfully!';
    $_SESSION['flash_type'] = 'success';
    header('Location: services');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $icon = trim($_POST['icon']);

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../assets/images/services/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['image']['type'], $allowed_types)) {
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $new_filename = 'service_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = $new_filename;

                // Delete old image if updating
                if ($id) {
                    $stmt = $conn->prepare("SELECT image FROM services WHERE id = ?");
                    $stmt->execute([$id]);
                    $old_service = $stmt->fetch();
                    if ($old_service && $old_service['image']) {
                        $old_file = $upload_dir . $old_service['image'];
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                }
            }
        }
    }

    if ($id) {
        // Update
        if ($image_path) {
            $stmt = $conn->prepare("UPDATE services SET title = ?, description = ?, icon = ?, image = ? WHERE id = ?");
            $stmt->execute([$title, $description, $icon, $image_path, $id]);
        } else {
            $stmt = $conn->prepare("UPDATE services SET title = ?, description = ?, icon = ? WHERE id = ?");
            $stmt->execute([$title, $description, $icon, $id]);
        }
        $_SESSION['flash_message'] = 'Service updated successfully!';
        $_SESSION['flash_type'] = 'success';
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO services (title, description, icon, image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $icon, $image_path]);
        $_SESSION['flash_message'] = 'Service added successfully!';
        $_SESSION['flash_type'] = 'success';
    }

    header('Location: services');
    exit;
}

$page_title = 'Services Management';
require_once 'header.php';

// Get service for editing
$edit_service = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $edit_service = $stmt->fetch();
}

// Get all services
$services = $conn->query("SELECT * FROM services ORDER BY id DESC")->fetchAll();
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
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h2 class="page-title mb-0"><i class="fas fa-wrench"></i> Services Management</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#serviceModal">
                <i class="fas fa-plus"></i> Add New Service
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> All Services
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Icon</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><strong>#<?php echo $service['id']; ?></strong></td>
                                    <td>
                                        <div class="icon-circle">
                                            <i class="<?php echo htmlspecialchars($service['icon']); ?> fa-2x"></i>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($service['image']): ?>
                                            <img src="../assets/images/services/<?php echo htmlspecialchars($service['image']); ?>"
                                                style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;"
                                                alt="<?php echo htmlspecialchars($service['title']); ?>">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($service['title']); ?></strong></td>
                                    <td><small
                                            class="text-muted"><?php echo htmlspecialchars(substr($service['description'], 0, 100)) . '...'; ?></small>
                                    </td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-sm btn-primary me-2"
                                            onclick="editService(<?php echo htmlspecialchars(json_encode($service)); ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="?delete=<?php echo $service['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this service?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
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

<!-- Service Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add New Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="serviceForm" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="serviceId">

                    <div class="mb-3">
                        <label class="form-label">Service Title</label>
                        <input type="text" class="form-control" name="title" id="serviceTitle" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="serviceDescription" rows="4"
                            required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Icon Class (Font Awesome)</label>
                        <input type="text" class="form-control" name="icon" id="serviceIcon" placeholder="fas fa-bolt"
                            required>
                        <small class="text-muted">Example: fas fa-bolt, fas fa-cog, fas fa-wrench</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="fas fa-image"></i> Service Image (Optional)</label>
                        <div class="drop-zone">
                            <i class="fas fa-cloud-upload-alt icon"></i>
                            <div class="text">Drag & drop or <b>browse</b></div>
                            <input type="file" name="image"
                                accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        </div>
                        <div class="preview-container"></div>
                        <small class="text-muted">Recommended: 400x300px, displayed next to service on website</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editService(service) {
        document.getElementById('modalTitle').textContent = 'Edit Service';
        document.getElementById('serviceId').value = service.id;
        document.getElementById('serviceTitle').value = service.title;
        document.getElementById('serviceDescription').value = service.description;
        document.getElementById('serviceIcon').value = service.icon;

        new bootstrap.Modal(document.getElementById('serviceModal')).show();
    }

    // Reset form when modal is closed
    document.getElementById('serviceModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('modalTitle').textContent = 'Add New Service';
        document.getElementById('serviceForm').reset();
    });
</script>

<?php require_once 'footer.php'; ?>