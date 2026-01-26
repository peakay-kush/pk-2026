<?php
$page_title = 'About Us';
require_once 'includes/header.php';
require_once 'includes/db.php';

// Fetch team members from database
$team_members = $conn->query("SELECT * FROM team_members ORDER BY sort_order ASC, name ASC");

// Fetch about hero image
$stmt = $conn->prepare("SELECT * FROM hero_images WHERE category = ? AND is_active = 1 LIMIT 1");
$stmt->execute(['about_hero']);
$about_hero = $stmt->fetch();
?>

<!-- Page Header -->
<section class="hero-section">
    <div class="container">
        <h1>About PK Automations</h1>
        <p class="lead">Innovating since 2015</p>
    </div>
</section>

<!-- Our Story -->
<section class="section-padding">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <div class="position-relative mb-4">
                    <h2 class="display-5 fw-bold mb-3">Our Story</h2>
                    <div style="width: 80px; height: 4px; background: var(--primary-grad); border-radius: 2px;"></div>
                </div>
                <div style="font-family: 'Plus Jakarta Sans', sans-serif;" class="text-muted">
                    <p class="mb-4" style="font-size: 1.1rem; line-height: 1.8;">
                        PK Automations was founded in 2025 with a simple mission: to make advanced electronics and
                        automation solutions accessible to everyone.
                    </p>
                    <p class="mb-4" style="font-size: 1.1rem; line-height: 1.8;">
                        What started as a small electronics shop has grown into a comprehensive service provider
                        offering
                        everything from quality components to custom engineering solutions.
                    </p>
                    <p style="font-size: 1.1rem; line-height: 1.8;">
                        Today, we serve thousands of customers including students, businesses, and organizations across
                        East
                        Africa.
                    </p>
                </div>
            </div>
            <div class="col-lg-6">
                <?php if ($about_hero): ?>
                    <img src="assets/images/hero/<?php echo htmlspecialchars($about_hero['image_path']); ?>"
                        class="img-fluid rounded shadow" alt="Our Story"
                        style="width: 100%; height: 400px; object-fit: cover; border-radius: 12px;">
                <?php else: ?>
                    <div
                        style="background-color: #e8e8e8; height: 400px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <span style="color:#999; font-size:1.2rem;">Our Story</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Our Commitment -->
<section class="section-padding bg-gray-100">
    <div class="container">
        <h2 class="section-title-header">Our
            Commitment</h2>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border">
                    <div class="card-body p-4">
                        <h5 class="text-primary-custom"
                            style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.25rem;">Our
                            Mission</h5>
                        <p class="text-muted" style="font-size: 1.05rem; line-height: 1.8; margin: 0;">
                            To provide high-quality electronics components, innovative solutions, and expert guidance
                            that empower individuals and businesses to innovate and automate.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 border">
                    <div class="card-body p-4">
                        <h5 class="text-primary-custom"
                            style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.25rem;">Our
                            Vision</h5>
                        <p class="text-muted" style="font-size: 1.05rem; line-height: 1.8; margin: 0;">
                            To be the leading electronics and automation solutions provider in East Africa, known for
                            quality, innovation, and customer excellence.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 border">
                    <div class="card-body p-4">
                        <h5 class="text-primary-custom"
                            style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.25rem;">Our
                            Values</h5>
                        <div class="text-muted" style="font-size: 1.05rem; line-height: 2.2;">
                            <p style="margin: 0 0 0.5rem 0;"><i class="fas fa-check"
                                    style="color: var(--accent-color); margin-right: 0.5rem;"></i> Quality & Excellence
                            </p>
                            <p style="margin: 0 0 0.5rem 0;"><i class="fas fa-check"
                                    style="color: var(--accent-color); margin-right: 0.5rem;"></i> Customer First</p>
                            <p style="margin: 0 0 0.5rem 0;"><i class="fas fa-check"
                                    style="color: var(--accent-color); margin-right: 0.5rem;"></i> Innovation & Learning
                            </p>
                            <p style="margin: 0;"><i class="fas fa-check"
                                    style="color: var(--accent-color); margin-right: 0.5rem;"></i> Reliability & Trust
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Meet Our Team -->
<section class="section-padding">
    <div class="container">
        <h2 class="section-title-header">Meet
            Our Team</h2>

        <div class="row g-4">
            <?php while ($member = $team_members->fetch()): ?>
                <div class="col-md-3">
                    <div class="card text-center border overflow-hidden">
                        <div
                            style="background-color: #e8e8e8; height: 250px; display: flex; align-items: center; justify-content: center;">
                            <?php if ($member['image_path']): ?>
                                <img src="assets/images/team/<?php echo htmlspecialchars($member['image_path']); ?>"
                                    alt="<?php echo htmlspecialchars($member['name']); ?>"
                                    style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="fas fa-user" style="font-size:5rem;color:#999;"></i>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-4">
                            <h5 style="font-size: 1.3rem; font-weight: 700; margin-bottom: 0.5rem;">
                                <?php echo htmlspecialchars($member['name']); ?>
                            </h5>
                            <p
                                style="color: var(--accent-color); font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem;">
                                <?php echo htmlspecialchars($member['role']); ?>
                            </p>
                            <?php if ($member['bio']): ?>
                                <p class="text-muted" style="font-size: 0.9rem; margin: 0;">
                                    <?php echo htmlspecialchars($member['bio']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="stats-gradient-section">
    <div class="container">
        <h2 style="text-align: center; font-size: 1.7rem; font-weight: 700; margin-bottom: 2rem;">Why Choose Us?</h2>

        <div class="row g-3 text-center">
            <div class="col-md-3">
                <div style="padding: 1rem 0.5rem;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-box"></i>
                    </div>
                    <h3 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 0.25rem; color: white;">10,000+</h3>
                    <p style="font-size: 0.9rem; margin: 0; color: white; opacity: 0.9;">Products</p>
                </div>
            </div>

            <div class="col-md-3">
                <div style="padding: 1rem 0.5rem;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 0.25rem; color: white;">5,000+</h3>
                    <p style="font-size: 0.9rem; margin: 0; color: white; opacity: 0.9;">Happy Customers</p>
                </div>
            </div>

            <div class="col-md-3">
                <div style="padding: 1rem 0.5rem;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h3 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 0.25rem; color: white;">24/7</h3>
                    <p style="font-size: 0.9rem; margin: 0; color: white; opacity: 0.9;">Fast Delivery</p>
                </div>
            </div>

            <div class="col-md-3">
                <div style="padding: 1rem 0.5rem;">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 0.25rem; color: white;">24/7</h3>
                    <p style="font-size: 0.9rem; margin: 0; color: white; opacity: 0.9;">Support</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>