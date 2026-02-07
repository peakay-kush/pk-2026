<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float) $_POST['price'];
    $category = trim($_POST['category']);
    $stock = (int) $_POST['stock'];
    $rating = (float) ($_POST['rating'] ?? 5.0);
    $slug = generateSlug($name);

    $image = 'default.png'; // Fallback
    $upload_success = false;
    $upload_error = '';

    // Handle initial image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/products/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = 'product_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image = $new_filename;
                $upload_success = true;
            } else {
                $upload_error = 'Failed to move uploaded file';
            }
        } else {
            $upload_error = 'Invalid file type. Allowed: ' . implode(', ', $allowed_extensions);
        }
    }

    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("INSERT INTO products (name, slug, description, price, category, stock, rating, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $slug, $description, $price, $category, $stock, $rating, $image]);
        $product_id = $conn->lastInsertId();

        // Add the primary image to product_images table
        if ($upload_success) {
            $img_stmt = $conn->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, 1, 0)");
            $img_stmt->execute([$product_id, $image]);
        }

        // Handle additional multiple images
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $total_files = count($_FILES['images']['name']);
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $upload_dir = '../assets/images/products/';

            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_extension = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
                    if (in_array($file_extension, $allowed_extensions)) {
                        $new_filename = 'product_' . $product_id . '_extra_' . time() . '_' . $i . '.' . $file_extension;
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $upload_dir . $new_filename)) {
                            // Max sort order
                            $sort_stmt = $conn->prepare("SELECT COALESCE(MAX(sort_order), -1) + 1 FROM product_images WHERE product_id = ?");
                            $sort_stmt->execute([$product_id]);
                            $sort_order = $sort_stmt->fetchColumn();

                            $img_stmt = $conn->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, 0, ?)");
                            $img_stmt->execute([$product_id, $new_filename, $sort_order]);
                        }
                    }
                }
            }
        }

        $conn->commit();

        $_SESSION['flash_message'] = 'Product added successfully!';
        $_SESSION['flash_type'] = 'success';
        header('Location: products');
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['flash_message'] = 'Error adding product: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
    }
}

$page_title = 'Add New Product';
require_once 'header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-title mb-0"><i class="fas fa-plus"></i> Add New Product</h2>
    <a href="products" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Products
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-box"></i> Product Information
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>

                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter product name"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="10"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price (KSh)</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price"
                                placeholder="0.00" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="" selected disabled>Select Category</option>
                                <option value="Arduino">Arduino</option>
                                <option value="Raspberry Pi">Raspberry Pi</option>
                                <option value="Sensors">Sensors</option>
                                <option value="Modules">Modules</option>
                                <option value="Tools">Tools</option>
                                <option value="Components">Components</option>
                                <option value="Kits">Kits</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="stock" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" id="stock" name="stock" value="0" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="rating" class="form-label">Initial Rating (0-5)</label>
                            <input type="number" step="0.1" min="0" max="5" class="form-control" id="rating"
                                name="rating" value="5.0" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Main Product Image</label>
                        <div class="drop-zone">
                            <i class="fas fa-cloud-upload-alt icon"></i>
                            <div class="text">Drag & drop or <b>browse</b></div>
                            <input type="file" id="image" name="image" accept="image/*" required>
                        </div>
                        <div class="preview-container"></div>
                        <small class="text-muted">This will be the primary image for the product.</small>
                    </div>

                    <div class="mb-3">
                        <label for="images" class="form-label">Additional Images</label>
                        <div class="drop-zone">
                            <i class="fas fa-images icon"></i>
                            <div class="text">Drag & drop multiple or <b>browse</b></div>
                            <input type="file" id="images" name="images[]" accept="image/*" multiple>
                        </div>
                        <div class="preview-container"></div>
                        <small class="text-muted">Supported: JPG, PNG, GIF, WebP</small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Save Product
                        </button>
                        <a href="products" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/kc5p26fenywbh8lgox87s6w0wx2q592j6dgxby5se3cikeku/tinymce/7/tinymce.min.js"
    referrerpolicy="origin"></script>

<script>
    // Initialize TinyMCE
    tinymce.init({
        selector: '#description',
        plugins: [
            'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount', 'image', 'code'
        ],
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat | codesample | code',
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        }
    });
</script>

<?php require_once 'footer.php'; ?>