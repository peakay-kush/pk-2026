<?php
$page_title = 'Tutorials';
$page_description = 'Learn electronics and automation with our step-by-step tutorials. Covering Arduino, Raspberry Pi, IoT, and more for all skill levels.';
require_once 'includes/header.php';
require_once 'includes/db.php';

// Get category filter
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch tutorials
$tutorials = [];
if ($category) {
    $stmt = $conn->prepare("SELECT * FROM tutorials WHERE category = ? ORDER BY created_at DESC");
    $stmt->execute([$category]);
    $tutorials = $stmt->fetchAll();
} else {
    $result = $conn->query("SELECT * FROM tutorials ORDER BY created_at DESC");
    if ($result) {
        $tutorials = $result->fetchAll();
    }
}

// Fetch categories
$categories = $conn->query("SELECT DISTINCT category FROM tutorials ORDER BY category")->fetchAll();
?>

<!-- Page Header -->
<section class="hero-section">
    <div class="container">
        <h1><i class="fas fa-book-open"></i> Tutorials & Guides</h1>
        <p class="lead">Learn electronics, programming, and IoT from our comprehensive tutorials</p>
    </div>
</section>

<!-- Tutorials Section -->
<section class="section-padding">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filter -->
            <div class="col-md-3 mb-4">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header border-0 py-3" style="background: var(--primary-grad); color: white;">
                        <h5 class="mb-0 fw-bold" style="font-family: 'Outfit', sans-serif;">
                            <i class="fas fa-filter me-2"></i>Filter by Category
                        </h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="tutorials.php"
                            class="list-group-item list-group-item-action py-3 <?php echo !$category ? 'active fw-bold' : ''; ?>"
                            style="<?php echo !$category ? 'background-color: #E3F2FD; color: var(--primary-color); border-left: 4px solid var(--primary-color);' : 'color: #555;'; ?>">
                            All Tutorials
                        </a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="tutorials.php?category=<?php echo urlencode($cat['category']); ?>"
                                class="list-group-item list-group-item-action py-3 <?php echo $category == $cat['category'] ? 'active fw-bold' : ''; ?>"
                                style="<?php echo $category == $cat['category'] ? 'background-color: #E3F2FD; color: var(--primary-color); border-left: 4px solid var(--primary-color);' : 'color: #555;'; ?>">
                                <?php echo htmlspecialchars($cat['category']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Tutorials Grid -->
            <div class="col-md-9">
                <?php if (count($tutorials) > 0): ?>
                    <div class="row g-4">
                        <?php foreach ($tutorials as $tutorial): ?>
                            <div class="col-md-6">
                                <div class="card tutorial-card h-100 border-0 shadow-sm"
                                    style="border-radius: 12px; overflow: hidden; transition: transform 0.2s;">
                                    <?php if ($tutorial['image']): ?>
                                        <div style="height: 200px; overflow: hidden;">
                                            <img src="<?php echo htmlspecialchars($tutorial['image']); ?>" class="card-img-top"
                                                alt="<?php echo htmlspecialchars($tutorial['title']); ?>"
                                                style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body p-4 d-flex flex-column">
                                        <div class="mb-2">
                                            <span class="badge rounded-pill fw-normal"
                                                style="background-color: #00E676; color: #000; padding: 0.5em 1em; font-size: 0.75rem;">
                                                <?php echo htmlspecialchars($tutorial['category']); ?>
                                            </span>
                                        </div>
                                        <h5 class="card-title fw-bold mb-3"
                                            style="font-family: 'Outfit', sans-serif; color: var(--text-dark); font-size: 1.25rem;">
                                            <?php echo htmlspecialchars($tutorial['title']); ?>
                                        </h5>
                                        <p class="card-text mb-4 text-muted" style="line-height: 1.6; font-size: 0.95rem;">
                                            <?php echo htmlspecialchars(substr($tutorial['excerpt'], 0, 100)) . '...'; ?>
                                        </p>
                                        <div class="mt-auto d-flex justify-content-between align-items-center">
                                            <small class="text-secondary d-flex align-items-center fw-medium">
                                                <i class="fas fa-calendar-alt me-2" style="color: #999;"></i>
                                                <?php echo formatDate($tutorial['created_at']); ?>
                                            </small>
                                            <a href="tutorial.php?slug=<?php echo urlencode($tutorial['slug']); ?>"
                                                class="btn fw-bold"
                                                style="background-color: #00E676; color: #000; border-radius: 6px; padding: 0.5rem 1.2rem; font-size: 0.9rem;">
                                                Read Tutorial <i class="fas fa-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No tutorials found in this category.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container text-center">
        <h2>Need Help with a Specific Project?</h2>
        <p class="lead mb-4">Get personalized guidance from our experts</p>
        <a href="contact.php" class="btn btn-lg"><i class="fas fa-envelope"></i> Contact Us</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>