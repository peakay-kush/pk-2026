<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

$product_id = (int) ($_GET['id'] ?? 0);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float) $_POST['price'];
    $category = trim($_POST['category']);
    $stock = (int) $_POST['stock'];
    $rating = (float) $_POST['rating'];
    $image = trim($_POST['current_image']); // Keep current image by default

    $upload_success = false;
    $upload_error = '';
    $images_uploaded = 0;

    // Handle multiple image uploads
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $upload_dir = '../assets/images/products/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $total_files = count($_FILES['images']['name']);

        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $file_extension = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));

                if (in_array($file_extension, $allowed_extensions)) {
                    $new_filename = 'product_' . $product_id . '_' . time() . '_' . $i . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;

                    if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $upload_path)) {
                        // Get the current max sort order for this product
                        $sort_stmt = $conn->prepare("SELECT COALESCE(MAX(sort_order), -1) + 1 FROM product_images WHERE product_id = ?");
                        $sort_stmt->execute([$product_id]);
                        $sort_order = $sort_stmt->fetchColumn();

                        // Check if this is the first image (make it primary)
                        $is_primary_stmt = $conn->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ?");
                        $is_primary_stmt->execute([$product_id]);
                        $is_primary = ($is_primary_stmt->fetchColumn() == 0) ? 1 : 0;

                        // Insert into product_images table
                        $img_stmt = $conn->prepare("INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, ?, ?)");
                        $img_stmt->execute([$product_id, $new_filename, $is_primary, $sort_order]);

                        // Update main product image if this is the primary image
                        if ($is_primary) {
                            $image = $new_filename;
                        }

                        $images_uploaded++;
                        $upload_success = true;
                    }
                } else {
                    $upload_error .= "File {$_FILES['images']['name'][$i]} has invalid type. ";
                }
            }
        }
    }

    // Handle single image upload (backwards compatibility)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/products/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = 'product_' . $product_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if it exists
                $old_image = trim($_POST['current_image']);
                if ($old_image && file_exists('../assets/images/products/' . $old_image)) {
                    unlink('../assets/images/products/' . $old_image);
                }
                $image = $new_filename;
                $upload_success = true;
                $images_uploaded++;

                // Add to product_images table
                $img_stmt = $conn->prepare("REPLACE INTO product_images (product_id, image_path, is_primary, sort_order) VALUES (?, ?, 1, 0)");
                $img_stmt->execute([$product_id, $new_filename]);
            } else {
                $upload_error = 'Failed to move uploaded file';
            }
        } else {
            $upload_error = 'Invalid file type. Allowed: ' . implode(', ', $allowed_extensions);
        }
    }

    try {
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category = ?, stock = ?, rating = ?, image = ? WHERE id = ?");
        $result = $stmt->execute([$name, $description, $price, $category, $stock, $rating, $image, $product_id]);

        $message = 'Product updated successfully!';
        if ($images_uploaded > 0) {
            $message .= " {$images_uploaded} image(s) uploaded.";
        } elseif ($upload_error) {
            $message .= ' Warning: ' . $upload_error;
        }

        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = 'success';
        header('Location: products.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['flash_message'] = 'Error updating product: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
    }
}

// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['flash_message'] = 'Product not found';
    $_SESSION['flash_type'] = 'error';
    header('Location: products.php');
    exit;
}

// Fetch all images for this product
$img_stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
$img_stmt->execute([$product_id]);
$product_images = $img_stmt->fetchAll();

$page_title = 'Edit Product';
require_once 'header.php';
$img_stmt->execute([$product_id]);
$product_images = $img_stmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-title mb-0"><i class="fas fa-edit"></i> Edit Product</h2>
    <a href="products.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Products
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-box"></i> Product Information
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="current_image"
                        value="<?php echo htmlspecialchars($product['image']); ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"
                            required><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price (KSh)</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price"
                                value="<?php echo $product['price']; ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="Arduino" <?php echo $product['category'] == 'Arduino' ? 'selected' : ''; ?>>Arduino</option>
                                <option value="Raspberry Pi" <?php echo $product['category'] == 'Raspberry Pi' ? 'selected' : ''; ?>>Raspberry Pi</option>
                                <option value="Sensors" <?php echo $product['category'] == 'Sensors' ? 'selected' : ''; ?>>Sensors</option>
                                <option value="Modules" <?php echo $product['category'] == 'Modules' ? 'selected' : ''; ?>>Modules</option>
                                <option value="Tools" <?php echo $product['category'] == 'Tools' ? 'selected' : ''; ?>>
                                    Tools</option>
                                <option value="Components" <?php echo $product['category'] == 'Components' ? 'selected' : ''; ?>>Components</option>
                                <option value="Kits" <?php echo $product['category'] == 'Kits' ? 'selected' : ''; ?>>Kits
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="stock" class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" id="stock" name="stock"
                                value="<?php echo $product['stock']; ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="rating" class="form-label">Rating (0-5)</label>
                            <input type="number" step="0.1" min="0" max="5" class="form-control" id="rating"
                                name="rating" value="<?php echo $product['rating']; ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Product Image (Single)</label>
                        <div class="drop-zone">
                            <i class="fas fa-cloud-upload-alt icon"></i>
                            <div class="text">Drag & drop or <b>browse</b></div>
                            <input type="file" id="image" name="image" accept="image/*">
                        </div>
                        <div class="preview-container"></div>
                        <small class="text-muted">Current: <?php echo htmlspecialchars($product['image']); ?> | Leave
                            empty to keep current</small>
                    </div>

                    <div class="mb-3">
                        <label for="images" class="form-label">Add Multiple Images</label>
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
                            <i class="fas fa-save"></i> Update Product
                        </button>
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Products
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-box"></i> Product Preview
            </div>
            <div class="card-body">
                <!-- Product Info -->
                <div class="text-center mb-4 pb-3 border-bottom">
                    <h5 class="mb-2"><?php echo htmlspecialchars($product['name']); ?></h5>
                    <h6 class="text-primary mb-1"><?php echo formatPrice($product['price']); ?></h6>
                    <small class="text-muted">Stock: <?php echo $product['stock']; ?> units | Rating:
                        <?php echo $product['rating']; ?>/5</small>
                    <div class="mt-2">
                        <?php if ($product['featured']): ?>
                            <a href="toggle_featured.php?id=<?php echo $product_id; ?>&toggle=0&csrf_token=<?php echo generateCSRFToken(); ?>"
                                class="btn btn-sm btn-warning">
                                <i class="fas fa-star"></i> Featured
                            </a>
                        <?php else: ?>
                            <a href="toggle_featured.php?id=<?php echo $product_id; ?>&toggle=1&csrf_token=<?php echo generateCSRFToken(); ?>"
                                class="btn btn-sm btn-outline-warning">
                                <i class="far fa-star"></i> Mark as Featured
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Product Images -->
                <div>
                    <h6 class="mb-3"><i class="fas fa-images"></i> Images (<?php echo count($product_images); ?>)</h6>
                    <?php if (!empty($product_images)): ?>
                        <div class="row g-2">
                            <?php
                            $primaryCount = 0;
                            foreach ($product_images as $img):
                                $isPrimary = ($img['is_primary'] == 1 && $primaryCount == 0);
                                if ($isPrimary)
                                    $primaryCount++;
                                ?>
                                <div class="col-6">
                                    <div class="position-relative" style="height: 140px;">
                                        <img src="../assets/images/products/<?php echo htmlspecialchars($img['image_path']); ?>"
                                            class="img-fluid rounded w-100 h-100" style="object-fit: cover;"
                                            alt="Product Image">
                                        <?php if ($isPrimary): ?>
                                            <span class="badge bg-success position-absolute top-0 start-0 m-1"
                                                style="font-size: 0.65rem;">
                                                <i class="fas fa-star"></i> Primary
                                            </span>
                                        <?php endif; ?>
                                        <form method="POST" action="delete_product_image.php"
                                            class="position-absolute bottom-0 end-0 m-1">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="image_id" value="<?php echo $img['id']; ?>">
                                            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Delete this image?');" title="Delete image"
                                                style="padding: 0.25rem 0.4rem;">
                                                <i class="fas fa-trash" style="font-size: 0.75rem;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-image fa-2x mb-2 opacity-50"></i>
                            <p class="mb-0 small">No images uploaded yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/qtfzaflz9y6nb7hy0dvj1uqw5uwlzk7bp4zm93whxc9nydld/tinymce/6/tinymce.min.js"
    referrerpolicy="origin"></script>

<script>
    // Initialize TinyMCE
    tinymce.init({
        selector: '#description',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat | codesample',
        height: 400,
        codesample_languages: [
            { text: 'HTML/XML', value: 'markup' },
            { text: 'JavaScript', value: 'javascript' },
            { text: 'CSS', value: 'css' },
            { text: 'PHP', value: 'php' },
            { text: 'C++', value: 'cpp' },
            { text: 'C#', value: 'csharp' },
            { text: 'Python', value: 'python' }
        ],
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        }
    });

    // Fix for TinyMCE dialogs (like Code Sample) not allowing focus inside Bootstrap Modals
    // Not strictly needed here as this is not a modal, but good for consistency
    document.addEventListener('focusin', (e) => {
        if (e.target.closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
            e.stopImmediatePropagation();
        }
    });
</script>

<?php require_once 'footer.php'; ?>