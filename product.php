<?php
$page_title = 'Product Details';
$page_title = 'Product Details';
require_once 'includes/db.php';
require_once 'includes/header.php';

// Get product by slug
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    header('Location: shop.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM products WHERE slug = ?");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: shop.php');
    exit;
}
$page_title = $product['name'];

// Fetch all product images
$img_stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
$img_stmt->execute([$product['id']]);
$product_images = $img_stmt->fetchAll();

// Fetch related products (3 from same category)
$related_stmt = $conn->prepare("SELECT * FROM products WHERE category = ? AND slug != ? LIMIT 3");
$related_stmt->execute([$product['category'], $slug]);
$related_products = $related_stmt->fetchAll();
?>

<!-- Product Details Section -->
<section class="section-padding">
    <div class="container">
        <div class="row g-5">
            <!-- Product Image with Slider -->
            <div class="col-md-5">
                <?php if (!empty($product_images)): ?>
                    <!-- Main Image -->
                    <div class="mb-3 position-relative">
                        <div class="product-card-img-container" style="height: 400px; border-radius: 12px;">
                            <img src="assets/images/products/<?php echo htmlspecialchars($product_images[0]['image_path']); ?>"
                                id="product-main-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>

                        <?php if (count($product_images) > 1): ?>
                            <!-- Navigation Arrows -->
                            <button class="btn btn-light position-absolute top-50 start-0 translate-middle-y ms-2"
                                id="prevImage" style="opacity: 0.8;">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="btn btn-light position-absolute top-50 end-0 translate-middle-y me-2" id="nextImage"
                                style="opacity: 0.8;">
                                <i class="fas fa-chevron-right"></i>
                            </button>

                            <!-- Image Counter -->
                            <div class="position-absolute bottom-0 end-0 m-3">
                                <span class="badge bg-dark" id="imageCounter">1 / <?php echo count($product_images); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (count($product_images) > 1): ?>
                        <!-- Thumbnail Navigation -->
                        <div class="d-flex gap-2 overflow-auto pb-2">
                            <?php foreach ($product_images as $index => $img): ?>
                                <img src="assets/images/products/<?php echo htmlspecialchars($img['image_path']); ?>"
                                    class="img-thumbnail product-thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                                    style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
                                    data-index="<?php echo $index; ?>"
                                    data-src="assets/images/products/<?php echo htmlspecialchars($img['image_path']); ?>"
                                    alt="Thumbnail <?php echo $index + 1; ?>">
                            <?php endforeach; ?>
                        </div>

                        <!-- Store all images data for slider -->
                        <script>
                            const productImages = <?php echo json_encode(array_map(function ($img) {
                                return 'assets/images/products/' . $img['image_path'];
                            }, $product_images)); ?>;
                        </script>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="product-card-img-container" style="height: 400px; border-radius: 12px;">
                        <img src="assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'placeholder.jpg'); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="col-md-7">
                <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($product['category']); ?></span>
                <h1 class="mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>

                <div class="mb-3">
                    <?php echo generateStarRating($product['rating']); ?>
                </div>

                <h2 class="text-primary mb-4"><?php echo formatPrice($product['price']); ?></h2>

                <div class="mb-4">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="badge bg-success">
                            <i class="fas fa-check"></i> In Stock (<?php echo $product['stock']; ?> available)
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger">
                            <i class="fas fa-times"></i> Out of Stock
                        </span>
                    <?php endif; ?>
                </div>

                <h5>Product Description</h5>
                <div class="lead product-description">
                    <?php echo $product['description']; // Allow HTML from TinyMCE, only admins can edit ?>
                </div>

                <hr class="my-4">

                <form method="POST" action="cart_action.php" class="mb-4" id="addToCartForm">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                    <div class="row align-items-end g-3">
                        <div class="col-auto">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1"
                                max="<?php echo $product['stock']; ?>" style="width: 100px;">
                        </div>
                        <div class="col-auto">
                            <?php if ($product['stock'] > 0): ?>
                                <button type="submit" class="btn btn-add-to-cart btn-lg">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-secondary btn-lg" disabled>
                                    <i class="fas fa-times"></i> Out of Stock
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="col-auto">
                            <a href="shop.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-arrow-left"></i> Continue Shopping
                            </a>
                        </div>
                    </div>
                </form>

                <div class="card bg-light">
                    <div class="card-body">
                        <h6><i class="fas fa-truck"></i> Delivery Information</h6>
                        <p class="mb-2">Same-day delivery available in Nairobi</p>
                        <p class="mb-0">Nationwide shipping: 2-3 business days</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<?php if (count($related_products) > 0): ?>
    <section class="section-padding bg-light">
        <div class="container">
            <h3 class="mb-4">Related Products</h3>
            <div class="row g-4">
                <?php foreach ($related_products as $related): ?>
                    <div class="col-md-4">
                        <div class="card product-card h-100">
                            <span class="badge position-absolute"
                                style="top: 10px; right: 10px; background-color: #00E676; color: white; z-index: 10;">
                                <?php echo htmlspecialchars($related['category']); ?>
                            </span>
                            <div class="product-card-img-container" style="height: 200px;">
                                <img src="assets/images/products/<?php echo htmlspecialchars($related['image'] ?? 'placeholder.jpg'); ?>"
                                    alt="<?php echo htmlspecialchars($related['name']); ?>">
                            </div>
                            <div class="card-body">
                                <h5 class="card-title" style="color: #0B63CE;"><?php echo htmlspecialchars($related['name']); ?>
                                </h5>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span style="color: #00C853; font-size: 1.3rem; font-weight: 600;">
                                        <?php echo formatPrice($related['price']); ?>
                                    </span>
                                    <div><?php echo generateStarRating($related['rating']); ?></div>
                                </div>
                                <a href="product.php?slug=<?php echo urlencode($related['slug']); ?>" class="btn w-100"
                                    style="background-color: #00E676; color: white; border: none;">
                                    View Product
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>