<?php
$page_title = 'Favorites';
require_once 'includes/header.php';
require_once 'includes/db.php';

// Initialize favorites if not exists
if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
}

// Fetch favorite products
$favorite_products = [];
if (!empty($_SESSION['favorites'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['favorites']), '?'));
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($_SESSION['favorites']);
    $favorite_products = $stmt->fetchAll();
}
?>

<!-- Page Header -->
<section class="hero-section">
    <div class="container">
        <h1>
            <i class="fas fa-heart"></i> My Favorites
        </h1>
        <p class="lead">Products you've saved for later</p>
    </div>
</section>

<!-- Favorites Section -->
<section class="favorites-section section-padding section-bg-alt" style="min-height: 60vh;">
    <div class="container">
        <?php if (count($favorite_products) > 0): ?>
            <p class="mb-4">You have <?php echo count($favorite_products); ?> favorite products</p>
            <div class="row g-4">
                <?php foreach ($favorite_products as $product): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm" style="border: 1px solid #e0e0e0; position: relative;">
                            <span class="badge position-absolute"
                                style="top: 10px; right: 10px; background-color: #00E676; color: white; z-index: 10;">
                                <?php echo htmlspecialchars($product['category']); ?>
                            </span>

                            <div class="product-card-img-container" style="height: 220px;">
                                <img src="assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'placeholder.jpg'); ?>"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>

                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"
                                    style="color: #0B63CE; font-size: 1.2rem; font-weight: 600; margin-bottom: 0.75rem;">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </h5>

                                <p class="card-text text-muted"
                                    style="font-size: 0.9rem; margin-bottom: 1rem; min-height: 45px;">
                                    <?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...
                                </p>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h4 style="color: #00C853; font-size: 1.5rem; font-weight: 600; margin: 0;">
                                        <?php echo formatPrice($product['price']); ?>
                                    </h4>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="fas fa-star" style="color: #00C853; font-size: 0.9rem;"></i>
                                        <span
                                            style="font-weight: 500;"><?php echo number_format($product['rating'], 1); ?></span>
                                    </div>
                                </div>

                                <?php if ($product['stock'] > 0): ?>
                                    <p class="text-success small mb-3" style="margin: 0;">In stock: <?php echo $product['stock']; ?>
                                    </p>
                                <?php else: ?>
                                    <p class="text-danger small mb-3" style="margin: 0;">Out of stock</p>
                                <?php endif; ?>

                                <div class="d-flex gap-2 mt-auto">
                                    <?php if ($product['stock'] > 0): ?>
                                        <a href="#" class="btn flex-fill btn-add-to-cart"
                                            data-product-id="<?php echo $product['id']; ?>"
                                            data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                            style="background-color: #00E676; color: white; border: none; padding: 0.65rem 1rem;">
                                            <i class="fas fa-cart-plus"></i> Add to Cart
                                        </a>
                                    <?php endif; ?>
                                    <a href="product.php?slug=<?php echo urlencode($product['slug']); ?>" class="btn"
                                        style="border: 2px solid #00E676; color: #00E676; background: white; padding: 0.65rem 1.5rem;">
                                        View
                                    </a>
                                    <a href="favorites_handler.php?action=remove&id=<?php echo $product['id']; ?>" class="btn"
                                        style="border: 1px solid #dc3545; color: #dc3545; background: white; padding: 0.65rem 1rem;">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="far fa-heart" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                <h3>No Favorites Yet</h3>
                <p class="text-muted mb-4">Start adding products to your favorites list</p>
                <a href="shop.php" class="btn" style="background-color: #00E676; color: white; padding: 0.75rem 2rem;">
                    Browse Products
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>