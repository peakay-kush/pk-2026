<?php
$page_title = 'Shop';
$page_description = 'Shop a wide range of electronics, sensors, microcontrollers, and automation components at PK Automations. High quality and affordable prices.';
require_once 'includes/header.php';
require_once 'includes/db.php';

// Get filters
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch products
$where_clauses = [];
$params = [];
$types = '';

if ($category) {
    $where_clauses[] = "category = ?";
    $params[] = $category;
    $types .= 's';
}

if ($search) {
    $where_clauses[] = "(name LIKE ? OR description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= 'ss';
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

if (!empty($params)) {
    $stmt = $conn->prepare("SELECT * FROM products $where_sql ORDER BY name");
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} else {
    $products = $conn->query("SELECT * FROM products ORDER BY name")->fetchAll();
}

// Fetch categories
$categories = $conn->query("SELECT DISTINCT category FROM products ORDER BY category")->fetchAll();
?>

<!-- Page Header -->
<section class="hero-section">
    <div class="container">
        <h1>Shop Electronics & Components</h1>
        <p class="lead">Browse our extensive collection of quality products</p>
    </div>
</section>

<!-- Shop Section -->
<section class="shop-section section-padding section-bg-alt">
    <div class="container">
        <!-- Search Bar -->
        <div class="row mb-4">
            <div class="col-12">
                <form method="GET">
                    <div class="input-group shadow-sm" style="border-radius: 12px; overflow: hidden; background: white; padding: 0.5rem;">
                        <span class="input-group-text bg-white border-0 ps-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-0 shadow-none" name="search" 
                               placeholder="Search products..." 
                               value="<?php echo htmlspecialchars($search); ?>"
                               style="font-size: 1rem; padding: 0.75rem;">
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <h5 class="mb-4 fw-bold" style="font-family: 'Outfit', sans-serif;"><i class="fas fa-filter me-2"></i> Filters</h5>
                        
                        <!-- Category Filter -->
                        <h6 class="mb-3 fw-bold" style="color: #0B63CE; font-family: 'Outfit', sans-serif;">Category</h6>
                        <form method="GET" id="filterForm">
                            <div class="mb-4" style="max-height: 400px; overflow-y: auto;">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="category" value="" 
                                           id="cat-all" <?php echo !$category ? 'checked' : ''; ?> 
                                           onchange="this.form.submit()" style="cursor: pointer;">
                                    <label class="form-check-label" for="cat-all" style="cursor: pointer; color: #555;">All</label>
                                </div>
                                <?php foreach ($categories as $cat): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="category" 
                                           value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                           id="cat-<?php echo md5($cat['category']); ?>"
                                           <?php echo $category == $cat['category'] ? 'checked' : ''; ?>
                                           onchange="this.form.submit()" style="cursor: pointer;">
                                    <label class="form-check-label" for="cat-<?php echo md5($cat['category']); ?>" style="cursor: pointer; color: #555;">
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Sort By -->
                            <h6 class="mb-3 fw-bold" style="color: #0B63CE; font-family: 'Outfit', sans-serif;">Sort By</h6>
                            <select class="form-select mb-4 border-0 bg-light" name="sort" onchange="this.form.submit()" style="padding: 0.75rem; border-radius: 8px;">
                                <option value="newest">Newest</option>
                                <option value="price_low">Price: Low to High</option>
                                <option value="price_high">Price: High to Low</option>
                                <option value="name">Name: A-Z</option>
                            </select>

                            <a href="shop.php" class="btn w-100 fw-bold py-2" 
                               style="background-color: #00E676; color: white; border: none; border-radius: 8px;">
                                Clear Filters
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-md-9">
                <p class="mb-3 text-muted">Showing <?php echo count($products); ?> products</p>
                
                <?php if (count($products) > 0): ?>
                <div class="row g-4">
                    <?php foreach ($products as $product): ?>
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; min-height: 480px; overflow: hidden; transition: transform 0.2s;">
                            <!-- Category Badge -->
                             <span class="badge position-absolute rounded-pill fw-normal" 
                                  style="top: 15px; right: 15px; background-color: #00E676; color: white; z-index: 10; padding: 0.5em 1em;">
                                <?php echo htmlspecialchars($product['category']); ?>
                            </span>

                            <!-- Product image -->
                            <div class="product-card-img-container" style="height: 240px;">
                                <img src="assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'placeholder.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            
                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="card-title fw-bold mb-2" style="color: #0B63CE; font-family: 'Outfit', sans-serif; font-size: 1.15rem;">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </h5>
                                
                                <p class="card-text text-muted small mb-3" style="line-height: 1.5; min-height: 40px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                    <?php echo htmlspecialchars(strip_tags($product['description'])); ?>
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h4 class="fw-bold mb-0" style="color: #00E676; font-family: 'Outfit', sans-serif;">
                                        <?php echo formatPrice($product['price']); ?>
                                    </h4>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="fas fa-star" style="color: #00E676; font-size: 0.9rem;"></i>
                                        <span class="fw-bold text-dark small"><?php echo number_format($product['rating'], 1); ?></span>
                                    </div>
                                </div>

                                <?php if ($product['stock'] > 0): ?>
                                    <p class="small mb-3" style="margin: 0; color: #00E676; font-weight: 500;">In stock: <?php echo $product['stock']; ?></p>
                                <?php else: ?>
                                    <p class="text-danger small mb-3" style="margin: 0; font-weight: 500;">Out of stock</p>
                                <?php endif; ?>
                                
                                <div class="d-flex gap-2 align-items-center mt-auto">
                                    <?php if ($product['stock'] > 0): ?>
                                    <button 
                                       class="btn btn-add-to-cart flex-grow-1 d-flex align-items-center justify-content-center fw-bold text-white shadow-sm" 
                                       data-product-id="<?php echo $product['id']; ?>" 
                                       data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                       type="button"
                                       style="border: none; padding: 0.6rem; border-radius: 8px;">
                                        <i class="fas fa-shopping-cart me-2"></i> Add
                                    </button>
                                    <?php else: ?>
                                    <button class="btn flex-grow-1" 
                                            style="background-color: #f0f0f0; color: #999; border: none; padding: 0.6rem; font-weight: 500; border-radius: 8px;"
                                            disabled>
                                        Out of Stock
                                    </button>
                                    <?php endif; ?>
                                    
                                    <a href="product.php?slug=<?php echo urlencode($product['slug']); ?>" 
                                       class="btn fw-bold d-flex align-items-center justify-content-center"
                                       style="border: 2px solid #00E676; color: #00E676; background: white; padding: 0.6rem 1.2rem; border-radius: 8px; text-decoration: none;">
                                        View
                                    </a>
                                    
                                    <button class="btn btn-add-favorite d-flex align-items-center justify-content-center shadow-sm"
                                       data-product-id="<?php echo $product['id']; ?>"
                                       style="border: 1px solid #eee; width: 42px; height: 42px; background: white; border-radius: 8px; color: #999;">
                                        <i class="far fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="alert alert-light text-center shadow-sm border-0 py-5">
                    <i class="fas fa-search mb-3 text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mb-2">No products found</h5>
                    <p class="text-muted">Try adjusting your search or filters</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container text-center">
        <h2>Can't Find What You're Looking For?</h2>
        <p class="lead mb-4">Contact us for custom orders and special requests</p>
        <a href="contact.php" class="btn btn-lg"><i class="fas fa-envelope"></i> Contact Us</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
