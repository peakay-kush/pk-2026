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
    $stmt = $conn->prepare("DELETE FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_message'] = 'Testimonial deleted successfully!';
    $_SESSION['flash_type'] = 'success';
    header('Location: testimonials.php');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name']);
    $position = trim($_POST['position']);
    $content = trim($_POST['content']);
    $rating = (int) $_POST['rating'];

    if ($id) {
        // Update
        $stmt = $conn->prepare("UPDATE testimonials SET name = ?, role = ?, message = ? WHERE id = ?");
        $stmt->execute([$name, $position, $content, $id]);
        $_SESSION['flash_message'] = 'Testimonial updated successfully!';
        $_SESSION['flash_type'] = 'success';
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO testimonials (name, role, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $position, $content]);
        $_SESSION['flash_message'] = 'Testimonial added successfully!';
        $_SESSION['flash_type'] = 'success';
    }

    header('Location: testimonials.php');
    exit;
}

$page_title = 'Testimonials Management';
require_once 'header.php';

// Get testimonials
$testimonials = $conn->query("SELECT id, name, role as position, message as content, 5 as rating FROM testimonials ORDER BY id DESC")->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="page-title mb-0"><i class="fas fa-comments"></i> Testimonials Management</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#testimonialModal">
                <i class="fas fa-plus"></i> Add New Testimonial
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> All Testimonials
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Content</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($testimonials as $testimonial): ?>
                                <tr>
                                    <td><strong>#<?php echo $testimonial['id']; ?></strong></td>
                                    <td><strong><?php echo htmlspecialchars($testimonial['name']); ?></strong></td>
                                    <td><small
                                            class="text-muted"><?php echo htmlspecialchars($testimonial['position']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars(substr($testimonial['content'], 0, 80)) . '...'; ?></td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-sm btn-primary me-2"
                                            onclick="editTestimonial(<?php echo htmlspecialchars(json_encode($testimonial)); ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="?delete=<?php echo $testimonial['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this testimonial?')">
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

<!-- Testimonial Modal -->
<div class="modal fade" id="testimonialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add New Testimonial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="testimonialForm">
                <?php echo csrfField(); ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="testimonial_id">

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="position" class="form-label">Position/Title</label>
                        <input type="text" class="form-control" id="position" name="position" required>
                    </div>

                    <div class="mb-3">
                        <label for="content" class="form-label">Testimonial Content</label>
                        <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Testimonial</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editTestimonial(testimonial) {
        document.getElementById('testimonial_id').value = testimonial.id;
        document.getElementById('name').value = testimonial.name;
        document.getElementById('position').value = testimonial.position;
        document.getElementById('content').value = testimonial.content;
        document.getElementById('modalTitle').textContent = 'Edit Testimonial';

        new bootstrap.Modal(document.getElementById('testimonialModal')).show();
    }

    // Reset form when modal is closed
    document.getElementById('testimonialModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('testimonialForm').reset();
        document.getElementById('testimonial_id').value = '';
        document.getElementById('modalTitle').textContent = 'Add New Testimonial';
    });
</script>

<?php require_once 'footer.php'; ?>