<?php
$page_title = 'Our Services';
$page_description = 'Professional technical services including IoT solutions, Web & App Development, Industrial Automation, and Student Project Support in Kenya.';
require_once 'includes/header.php';
require_once 'includes/db.php';

// Fetch all services
$services = $conn->query("SELECT * FROM services");
?>

<!-- Page Header -->
<section class="hero-section">
    <div class="container">
        <h1>Our Services</h1>
        <p class="lead">Comprehensive tech solutions for businesses and individuals</p>
    </div>
</section>

<!-- Services Section -->
<section class="section-padding">
    <div class="container">
        <?php
        $index = 0;
        while ($service = $services->fetch()):
            $isEven = $index % 2 == 0;
            // Define colors for alternating icons if needed, or keep consistent
            $iconColor = '#0B63CE';
            if ($index % 3 == 0)
                $iconColor = '#0B63CE'; // Blue
            else if ($index % 3 == 1)
                $iconColor = '#00E676'; // Green
            else
                $iconColor = '#FF6D00'; // Orange connection but let's stick to the requested style
            ?>
            <div id="service-<?php echo $service['id']; ?>" class="row align-items-center mb-5 pb-4">
                <?php if ($isEven): ?>
                    <!-- Text Left, Image Right -->
                    <div class="col-lg-5 col-md-6 order-2 order-md-1">
                        <div class="service-content pe-lg-4">
                            <div class="icon-wrapper mb-3">
                                <i class="<?php echo htmlspecialchars($service['icon'] ?? ''); ?>"
                                    style="font-size: 2.5rem; color: <?php echo $iconColor; ?>;"></i>
                            </div>
                            <h2 class="fw-bold mb-3" style="font-family: 'Outfit', sans-serif; color: var(--text-dark);">
                                <?php echo htmlspecialchars($service['title'] ?? ''); ?>
                            </h2>
                            <p class="text-muted mb-4" style="line-height: 1.7; font-size: 1.05rem;">
                                <?php echo htmlspecialchars($service['description'] ?? ''); ?>
                            </p>

                            <!-- Mobile Image (visible only on mobile) -->
                            <div class="d-lg-none mb-4">
                                <div class="service-image-container shadow-sm overflow-hidden"
                                    style="background-color: #ffffff; border-radius: 12px; border: 1px solid #f0f0f0; height: 250px; display: flex; align-items: center; justify-content: center;">
                                    <?php if ($service['image']): ?>
                                        <img src="assets/images/services/<?php echo htmlspecialchars($service['image'] ?? ''); ?>"
                                            class="w-100 h-100" alt="<?php echo htmlspecialchars($service['title'] ?? ''); ?>"
                                            style="object-fit: contain; padding: 10px;">
                                    <?php else: ?>
                                        <i class="<?php echo htmlspecialchars($service['icon'] ?? ''); ?>"
                                            style="font-size: 6rem; color: #e0e0e0;"></i>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <a href="contact?service=<?php echo urlencode($service['title']); ?>" class="btn fw-bold shadow-sm"
                                style="background-color: <?php echo $index % 2 == 0 ? '#0B63CE' : '#00E676'; ?>; color: white; border-radius: 6px; padding: 0.6rem 1.5rem; border: none;">
                                <i class="fas fa-paper-plane me-2"></i> Request This Service
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 offset-lg-1 col-md-6 order-1 order-md-2 d-none d-lg-block">
                        <div class="service-image-container shadow-sm overflow-hidden"
                            style="background-color: #ffffff; border-radius: 12px; border: 1px solid #f0f0f0; height: 320px; display: flex; align-items: center; justify-content: center;">
                            <?php if ($service['image']): ?>
                                <img src="assets/images/services/<?php echo htmlspecialchars($service['image'] ?? ''); ?>"
                                    class="w-100 h-100" alt="<?php echo htmlspecialchars($service['title'] ?? ''); ?>"
                                    style="object-fit: contain; padding: 10px; image-rendering: -webkit-optimize-contrast;">

                            <?php else: ?>
                                <i class="<?php echo htmlspecialchars($service['icon'] ?? ''); ?>"
                                    style="font-size: 8rem; color: #e0e0e0;"></i>

                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Image Left, Text Right -->
                    <div class="col-lg-6 col-md-6 order-1 d-none d-lg-block">
                        <div class="service-image-container shadow-sm overflow-hidden"
                            style="background-color: #ffffff; border-radius: 12px; border: 1px solid #f0f0f0; height: 320px; display: flex; align-items: center; justify-content: center;">
                            <?php if ($service['image']): ?>
                                <img src="assets/images/services/<?php echo htmlspecialchars($service['image'] ?? ''); ?>"
                                    class="w-100 h-100" alt="<?php echo htmlspecialchars($service['title'] ?? ''); ?>"
                                    style="object-fit: contain; padding: 10px; image-rendering: -webkit-optimize-contrast;">
                            <?php else: ?>
                                <i class="<?php echo htmlspecialchars($service['icon'] ?? ''); ?>"
                                    style="font-size: 8rem; color: #e0e0e0;"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-5 offset-lg-1 col-md-6 order-2">
                        <div class="service-content ps-lg-4">
                            <div class="icon-wrapper mb-3">
                                <i class="<?php echo htmlspecialchars($service['icon'] ?? ''); ?>"
                                    style="font-size: 2.5rem; color: #00E676;"></i>
                            </div>
                            <h2 class="fw-bold mb-3" style="font-family: 'Outfit', sans-serif; color: var(--text-dark);">
                                <?php echo htmlspecialchars($service['title'] ?? ''); ?>
                            </h2>
                            <p class="text-muted mb-4" style="line-height: 1.7; font-size: 1.05rem;">
                                <?php echo htmlspecialchars($service['description'] ?? ''); ?>
                            </p>

                            <!-- Mobile Image (visible only on mobile) -->
                            <div class="d-lg-none mb-4">
                                <div class="service-image-container shadow-sm overflow-hidden"
                                    style="background-color: #ffffff; border-radius: 12px; border: 1px solid #f0f0f0; height: 250px; display: flex; align-items: center; justify-content: center;">
                                    <?php if ($service['image']): ?>
                                        <img src="assets/images/services/<?php echo htmlspecialchars($service['image'] ?? ''); ?>"
                                            class="w-100 h-100" alt="<?php echo htmlspecialchars($service['title'] ?? ''); ?>"
                                            style="object-fit: contain; padding: 10px;">
                                    <?php else: ?>
                                        <i class="<?php echo htmlspecialchars($service['icon'] ?? ''); ?>"
                                            style="font-size: 6rem; color: #e0e0e0;"></i>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <a href="contact?service=<?php echo urlencode($service['title']); ?>" class="btn fw-bold shadow-sm"
                                style="background-color: #00E676; color: white; border-radius: 6px; padding: 0.6rem 1.5rem; border: none;">
                                <i class="fas fa-paper-plane me-2"></i> Request This Service
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php
            $index++;
        endwhile;
        ?>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="section-title">
            <h2>Why Choose Our Services?</h2>
            <p>We deliver excellence in every project</p>
        </div>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="text-center">
                    <div class="icon text-primary mb-3" style="font-size: 3rem;">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h5>Certified Professionals</h5>
                    <p>Experienced technicians and engineers</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <div class="icon text-primary mb-3" style="font-size: 3rem;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h5>Quick Turnaround</h5>
                    <p>Fast and efficient service delivery</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <div class="icon text-primary mb-3" style="font-size: 3rem;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5>Quality Guaranteed</h5>
                    <p>100% satisfaction guarantee</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <div class="icon text-primary mb-3" style="font-size: 3rem;">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5>24/7 Support</h5>
                    <p>Always here to help you</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container text-center">
        <h2>Need a Custom Solution?</h2>
        <p class="lead mb-4">Contact us to discuss your specific requirements</p>
        <a href="contact" class="btn btn-lg"><i class="fas fa-envelope"></i> Get in Touch</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>