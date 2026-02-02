<?php
require_once 'includes/config.php';
$page_title = 'System Error';
require_once 'includes/header.php';
?>

<section class="section-padding d-flex align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="container text-center">
        <div class="error-content" style="max-width: 600px; margin: 0 auto;">
            <i class="fas fa-exclamation-triangle text-warning mb-4" style="font-size: 5rem;"></i>
            <h1 class="display-4 fw-bold mb-3">Something Went Wrong</h1>
            <p class="lead text-muted mb-5">We've encountered an unexpected error. Our technical team has been notified
                and is working to resolve it.</p>

            <div class="d-flex justify-content-center gap-3">
                <a href="index" class="btn btn-primary btn-lg rounded-pill px-5">
                    <i class="fas fa-home me-2"></i>Return Home
                </a>
                <a href="contact" class="btn btn-outline-secondary btn-lg rounded-pill px-5">
                    <i class="fas fa-envelope me-2"></i>Contact Support
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>