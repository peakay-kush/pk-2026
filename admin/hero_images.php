<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    $category = $_POST['category'];
    $title = $_POST['title'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../assets/images/hero/';
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['image']['type'];
        if (in_array($file_type, $allowed_types)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = $category . '_' . time() . '.' . $ext;
            $filepath = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                // Deactivate other images in this category
                $stmt = $conn->prepare("UPDATE hero_images SET is_active = 0 WHERE category = ?");
                $stmt->execute([$category]);
                // Insert new image
                $stmt = $conn->prepare("INSERT INTO hero_images (category, title, image_path, is_active) VALUES (?, ?, ?, 1)");
                $stmt->execute([$category, $title, $filename]);
                header('Location: hero_images.php?success=1');
                exit;
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        die('Invalid security token');
    }
    $id = $_GET['delete'];
    $stmt = $conn->prepare("SELECT image_path FROM hero_images WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetch();
    if ($image) {
        $filepath = __DIR__ . '/../assets/images/hero/' . $image['image_path'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        $stmt = $conn->prepare("DELETE FROM hero_images WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: hero_images.php');
    exit;
}

// Handle activate
if (isset($_GET['activate'])) {
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        die('Invalid security token');
    }
    $id = $_GET['activate'];
    // Get category
    $stmt = $conn->prepare("SELECT category FROM hero_images WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetch();
    if ($image) {
        // Deactivate all in category
        $stmt = $conn->prepare("UPDATE hero_images SET is_active = 0 WHERE category = ?");
        $stmt->execute([$image['category']]);
        // Activate this one
        $stmt = $conn->prepare("UPDATE hero_images SET is_active = 1 WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: hero_images.php');
    exit;
}



// Define hero image categories
$categories = [
    'home_hero' => 'Homepage Hero',
    'students_hub' => 'Students Hub',
    'services_hero' => 'Services Page',
    'tutorials_hero' => 'Tutorials Page',
    'about_hero' => 'About Page'
];

require_once 'header.php';
?>
<div class="row g-3">
    <?php foreach ($categories as $cat_key => $cat_name): ?>
        <?php
        $stmt = $conn->prepare("SELECT * FROM hero_images WHERE category = ? ORDER BY created_at DESC");
        $stmt->execute([$cat_key]);
        $images = $stmt->fetchAll();
        $active_image = null;
        foreach ($images as $img) {
            if ($img['is_active']) {
                $active_image = $img;
                break;
            }
        }
        ?>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-image"></i> <?php echo $cat_name; ?></h5>
                    <button class="btn btn-success btn-sm ms-auto" data-bs-toggle="modal"
                        data-bs-target="#uploadModal<?php echo $cat_key; ?>">
                        <i class="fas fa-upload"></i> Upload
                    </button>
                </div>
                <div class="card-body">
                    <?php if ($active_image): ?>
                        <div class="mb-3">
                            <img src="../assets/images/hero/<?php echo htmlspecialchars($active_image['image_path']); ?>"
                                class="img-fluid rounded shadow-sm" style="width: 100%; height: 200px; object-fit: cover;"
                                alt="<?php echo htmlspecialchars($active_image['title']); ?>">
                            <div class="mt-3 d-flex justify-content-between align-items-center gap-3">
                                <div>
                                    <strong><?php echo htmlspecialchars($active_image['title']); ?></strong>
                                    <span class="badge bg-success ms-2">Active</span>
                                </div>
                                <a href="hero_images.php?delete=<?php echo $active_image['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this hero image?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted bg-light rounded">
                            <i class="fas fa-image mb-2" style="font-size: 3rem; opacity: 0.3;"></i>
                            <p class="m-0">No active image</p>
                        </div>
                    <?php endif; ?>

                    <?php if (count($images) > 1): ?>
                        <hr class="my-3">
                        <h6 class="mb-3 text-muted text-uppercase small fw-bold">History (<?php echo count($images); ?>)</h6>
                        <div class="row g-2">
                            <?php foreach ($images as $image): ?>
                                <div class="col-4">
                                    <div
                                        class="card h-100 <?php echo $image['is_active'] ? 'border-success' : 'border-0 bg-light'; ?>">
                                        <img src="../assets/images/hero/<?php echo htmlspecialchars($image['image_path']); ?>"
                                            class="card-img-top" style="height: 80px; object-fit: cover;"
                                            alt="<?php echo htmlspecialchars($image['title']); ?>">
                                        <div class="card-body p-2">
                                            <small class="d-block mb-2 text-truncate"
                                                title="<?php echo htmlspecialchars($image['title']); ?>">
                                                <?php echo htmlspecialchars($image['title']); ?>
                                            </small>
                                            <div class="d-grid gap-1">
                                                <?php if (!$image['is_active']): ?>
                                                    <a href="hero_images.php?activate=<?php echo $image['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>"
                                                        class="btn btn-sm btn-outline-success py-0" style="font-size: 0.7rem;">
                                                        Activate
                                                    </a>
                                                <?php endif; ?>
                                                <a href="hero_images.php?delete=<?php echo $image['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>"
                                                    class="btn btn-sm btn-outline-danger py-0" style="font-size: 0.7rem;"
                                                    onclick="return confirm('Delete this image?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <div class="modal fade" id="uploadModal<?php echo $cat_key; ?>" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload <?php echo $cat_name; ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <?php echo csrfField(); ?>
                        <div class="modal-body">
                            <input type="hidden" name="action" value="upload">
                            <input type="hidden" name="category" value="<?php echo $cat_key; ?>">

                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Image</label>
                                <div class="drop-zone">
                                    <i class="fas fa-cloud-upload-alt icon"></i>
                                    <div class="text">Drag & drop or <b>browse</b></div>
                                    <input type="file" name="image" accept="image/*" required>
                                </div>
                                <div class="preview-container"></div>
                                <small class="form-text text-muted">
                                    Recommended: 1920x600px, Max 2MB. Formats: JPG, PNG, GIF, WEBP
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Upload Image</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>



<?php require_once 'footer.php'; ?>