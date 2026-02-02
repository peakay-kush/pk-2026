<?php
$page_title = 'Home';
$page_description = 'PK Automations - Premier provider of electronics, industrial automation, and professional technical services in Limuru, Kenya.';
require_once 'includes/header.php';
require_once 'includes/db.php';

// Fetch featured products
$products = $conn->query("SELECT * FROM products WHERE featured = 1 ORDER BY created_at DESC LIMIT 6");

// Fetch services
$services = $conn->query("SELECT * FROM services LIMIT 6");

// Fetch latest tutorials
$tutorials = $conn->query("SELECT * FROM tutorials ORDER BY created_at DESC LIMIT 3");

// Fetch testimonials
$testimonials = $conn->query("SELECT * FROM testimonials ORDER BY id DESC LIMIT 6");

// Fetch hero images
$stmt = $conn->prepare("SELECT * FROM hero_images WHERE category = ? AND is_active = 1 LIMIT 1");
$stmt->execute(['home_hero']);
$home_hero = $stmt->fetch();

$stmt->execute(['students_hub']);
$students_hero = $stmt->fetch();
?>

<!-- Hero Section -->
<section class="hero-section hero-fullscreen"
    style="<?php if ($home_hero): ?>background-image: url('<?php echo SITE_URL; ?>/assets/images/hero/<?php echo htmlspecialchars($home_hero['image_path'] ?? ''); ?>'); background-size: cover; background-position: center;<?php endif; ?>">
    <div class="container text-center">
        <h1>Innovate. Automate. Elevate.</h1>
        <p class="lead">Your trusted partner in electronics, automation, and innovation. Quality products and
            expert services.</p>
        <div class="d-flex flex-wrap justify-content-center gap-3 mt-5">
            <a href="shop" class="btn btn-accent btn-lg"><i class="fas fa-shopping-bag"></i> Shop
                Components</a>
            <a href="services" class="btn btn-outline-light btn-lg"><i class="fas fa-arrow-right"></i> Explore
                Services</a>
        </div>
    </div>
</section>

<!-- Services Preview Section -->
<section class="section-padding">
    <div class="container">
        <div class="section-title">
            <h2>Our Services</h2>
        </div>
        <div class="row g-4">
            <?php
            $index = 0;
            while ($service = $services->fetch()):
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card service-card">
                        <div class="card-body">
                            <div class="icon">
                                <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                            </div>
                            <h5 class="card-title"><?php echo htmlspecialchars($service['title']); ?></h5>
                            <p class="card-text">
                                <?php echo htmlspecialchars(substr($service['description'], 0, 100)) . '...'; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php
                $index++;
            endwhile;
            if ($index === 0):
                echo "<div class='col-12 text-center'><p>No services found.</p></div>";
            endif;
            ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="section-padding">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h2 class="mb-0">Featured <span class="text-gradient">Products</span></h2>
            <a href="shop" class="text-decoration-none" style="color: var(--accent-color); font-weight: 600;">View
                All <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            <?php while ($product = $products->fetch()): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card product-card">
                        <span class="category-tag"><?php echo htmlspecialchars($product['category']); ?></span>
                        <div class="product-card-img-container" style="height: 200px;">
                            <img src="<?php echo SITE_URL; ?>/assets/images/products/<?php echo htmlspecialchars($product['image'] ?? 'placeholder.jpg'); ?>"
                                alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <div class="mb-2"><?php echo generateStarRating($product['rating']); ?></div>
                            <p class="price"><?php echo formatPrice($product['price']); ?></p>
                            <button class="btn btn-add-to-cart btn-add-cart" data-product-id="<?php echo $product['id']; ?>"
                                data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Student Hub Preview -->
<section class="section-padding">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="pe-lg-5">
                    <h2 class="mb-4"
                        style="font-family: 'Outfit', sans-serif; font-weight: 700; font-size: 2.5rem; color: #111;">
                        Student
                        Hub - Your Project Partner</h2>
                    <p class="lead mb-4"
                        style="font-family: 'Plus Jakarta Sans', sans-serif; color: #555; line-height: 1.8; font-size: 1.1rem;">
                        We understand the challenges students face with engineering projects. That's why we've created a
                        dedicated support system to help you succeed.
                    </p>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2 d-flex align-items-center">
                            <i class="fas fa-check-circle me-3" style="color: #0d6efd; font-size: 1.25rem;"></i>
                            <span
                                style="font-family: 'Plus Jakarta Sans', sans-serif; color: #333; font-size: 1.1rem; font-weight: 400;">One-on-one
                                project consultation and guidance</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="fas fa-check-circle me-3" style="color: #0d6efd; font-size: 1.25rem;"></i>
                            <span
                                style="font-family: 'Plus Jakarta Sans', sans-serif; color: #333; font-size: 1.1rem; font-weight: 400;">DIY
                                tutorials with code samples</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="fas fa-check-circle me-3" style="color: #0d6efd; font-size: 1.25rem;"></i>
                            <span
                                style="font-family: 'Plus Jakarta Sans', sans-serif; color: #333; font-size: 1.1rem; font-weight: 400;">Simulation
                                support (Proteus, Multisim)</span>
                        </li>
                        <li class="mb-2 d-flex align-items-center">
                            <i class="fas fa-check-circle me-3" style="color: #0d6efd; font-size: 1.25rem;"></i>
                            <span
                                style="font-family: 'Plus Jakarta Sans', sans-serif; color: #333; font-size: 1.1rem; font-weight: 400;">AutoCAD
                                assistance for technical drawings</span>
                        </li>
                        <li class="mb-3 d-flex align-items-center">
                            <i class="fas fa-check-circle me-3" style="color: #0d6efd; font-size: 1.25rem;"></i>
                            <span
                                style="font-family: 'Plus Jakarta Sans', sans-serif; color: #333; font-size: 1.1rem; font-weight: 400;">Custom
                                project kits tailored to your needs</span>
                        </li>
                    </ul>
                    <div>
                        <a href="student_hub" class="btn btn-lg fw-bold shadow-sm"
                            style="background-color: #00E676; color: white; border-radius: 8px; padding: 0.8rem 2rem; border: none;">
                            <i class="fas fa-graduation-cap me-2"></i> Explore Student Hub
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div
                    style="height: 380px; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
                    <?php if ($students_hero): ?>
                        <img src="<?php echo SITE_URL; ?>/assets/images/hero/<?php echo htmlspecialchars($students_hero['image_path'] ?? ''); ?>"
                            class="img-fluid"
                            alt="<?php echo htmlspecialchars($students_hero['category'] ?? 'Student Hub'); ?>"
                            style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                            <i class="fas fa-graduation-cap" style="font-size: 5rem; color: #ccc;"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Tutorials Preview -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="section-title">
            <h2>Latest Tutorials</h2>
            <p>Learn from our comprehensive guides and tutorials</p>
        </div>
        <div class="row g-4">
            <?php while ($tutorial = $tutorials->fetch()): ?>
                <div class="col-md-4">
                    <div class="card tutorial-card">
                        <div class="card-body">
                            <span class="badge mb-2"
                                style="background-color: var(--accent-color); color: white;"><?php echo htmlspecialchars($tutorial['category']); ?></span>
                            <h5 class="card-title" style="color: #0B63CE;">
                                <?php echo htmlspecialchars($tutorial['title']); ?>
                            </h5>
                            <p class="card-text"><?php echo htmlspecialchars($tutorial['excerpt']); ?></p>
                            <a href="tutorial/<?php echo urlencode($tutorial['slug']); ?>"
                                class="btn btn-accent">Read More</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-4">
            <a href="tutorials" class="btn btn-primary btn-lg">View All Tutorials</a>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="section-padding">
    <div class="container">
        <div class="section-title">
            <h2>What Our Clients Say</h2>
            <p>Real feedback from satisfied customers and students</p>
        </div>
        <div class="testimonial-slider">
            <div class="row g-4">
                <?php
                $testimonial_data = $testimonials->fetchAll();
                foreach (array_chunk($testimonial_data, 3) as $chunk_index => $chunk):
                    ?>
                    <div class="testimonial-slide" style="<?php echo $chunk_index > 0 ? 'display:none;' : ''; ?>">
                        <div class="row g-4">
                            <?php foreach ($chunk as $testimonial): ?>
                                <div class="col-md-4">
                                    <div class="card testimonial-card-premium"
                                        style="border: 1px solid #e0e0e0; border-radius: 12px; padding: 1.5rem;">
                                        <div class="mb-3">
                                            <img src="<?php echo SITE_URL; ?>/assets/images/logo 2.png" alt="Logo"
                                                style="width: 60px; height: auto; object-fit: contain;"
                                                onerror="this.style.display='none';">
                                        </div>
                                        <h5
                                            style="font-size: 1.1rem; font-weight: 700; color: #0B63CE; margin-bottom: 0.25rem;">
                                            <?php echo htmlspecialchars($testimonial['name']); ?>
                                        </h5>
                                        <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 1rem;">
                                            <?php echo htmlspecialchars($testimonial['role']); ?>
                                        </p>
                                        <div style="color: var(--accent-color); font-size: 1rem; margin-bottom: 1rem;">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <p class="testimonial-text"
                                            style="font-style: italic; font-size: 0.95rem; line-height: 1.6; margin: 0;">
                                            "<?php echo htmlspecialchars($testimonial['message']); ?>"
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($testimonial_data) > 3): ?>
                <div class="text-center mt-4">
                    <button class="btn btn-outline-primary me-2 testimonial-prev"><i
                            class="fas fa-chevron-left"></i></button>
                    <button class="btn btn-outline-primary testimonial-next"><i class="fas fa-chevron-right"></i></button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container text-center">
        <h2>Ready to Get Started?</h2>
        <p class="lead mb-4">Join thousands of satisfied customers and students who trust us for their tech needs</p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="shop" class="btn btn-light btn-lg"><i class="fas fa-shopping-bag"></i> Shop Now</a>
            <a href="contact" class="btn btn-outline-light btn-lg"><i class="fas fa-envelope"></i> Contact Us</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>