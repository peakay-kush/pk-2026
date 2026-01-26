<?php
$page_title = 'Contact Us';
require_once 'includes/functions.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/db.php';

$success = false;
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['error'] = 'Name, Email, and Message are required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email format';
    } else {
        // Insert into database
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message, phone, subject, created_at) VALUES (?, ?, ?, ?, ?, NOW())");

        if ($stmt->execute([$name, $email, $message, $phone, $subject])) {
            setFlashMessage('Thank you! Your message has been sent successfully. We\'ll get back to you within 24 hours.', 'success');
            // Send email notification
            $email_subject = "New Contact: $subject";
            $email_message = "<h2>New Contact Form Submission</h2>";
            $email_message .= "<p><strong>Name:</strong> $name</p>";
            $email_message .= "<p><strong>Email:</strong> $email</p>";
            $email_message .= "<p><strong>Phone:</strong> $phone</p>";
            $email_message .= "<p><strong>Subject:</strong> $subject</p>";
            $email_message .= "<p><strong>Message:</strong><br>$message</p>";
            sendEmail(ADMIN_EMAIL, $email_subject, $email_message);
            // Redirect before any output
            header('Location: contact.php?sent=1');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to send message. Please try again.';
        }
    }
}

require_once 'includes/header.php';
?>
<?php if (isset($_GET['sent'])): ?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            var form = document.querySelector('form[method="POST"]');
            if (form) form.reset();
        });
    </script>
<?php endif; ?>

<!-- Page Header -->
<section class="hero-section">
    <div class="container">
        <h1>Contact Us</h1>
        <p class="lead">We'd love to hear from you. Get in touch with our team today.</p>
    </div>
</section>

<!-- Contact Section -->
<section style="padding: 4rem 0;">
    <div class="container">
        <div class="row g-4">
            <!-- Contact Form -->
            <div class="col-lg-6">
                <h2 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 1.5rem;">Send us a Message</h2>

                <form method="POST">
                    <?php echo csrfField(); ?>
                    <div class="mb-3">
                        <label class="form-label text-primary-custom"
                            style="font-size: 0.9rem; font-weight: 500;">Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-primary-custom"
                                style="font-size: 0.9rem; font-weight: 500;">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="your@email.com" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-primary-custom"
                                style="font-size: 0.9rem; font-weight: 500;">Phone</label>
                            <input type="tel" name="phone" class="form-control" placeholder="+254 112 961 056">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-primary-custom"
                            style="font-size: 0.9rem; font-weight: 500;">Subject</label>
                        <input type="text" name="subject" class="form-control" placeholder="How can we help?">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-primary-custom"
                            style="font-size: 0.9rem; font-weight: 500;">Message</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="Your message here..."
                            required></textarea>
                    </div>

                    <button type="submit" class="btn w-100"
                        style="background-color: var(--accent-color); border: none; color: white; font-weight: 600; padding: 0.875rem; border-radius: 8px; font-size: 1.05rem;">
                        Send Message
                    </button>
                </form>
            </div>

            <!-- Contact Information -->
            <div class="col-lg-6">
                <h2 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 1.5rem;">Get in Touch</h2>

                <!-- Phone Card -->
                <div class="card mb-3">
                    <div class="card-body" style="padding: 1.5rem 1.5rem !important;">
                        <div class="d-flex align-items-center">
                            <div class="icon-box-gradient"
                                style="width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas fa-phone" style="color: white; font-size: 1.4rem;"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="text-primary-custom"
                                    style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem;">
                                    Phone</h5>
                                <p style="margin: 0; font-size: 1rem; font-weight: 500;">+254 112 961 056
                                </p>
                                <p class="text-muted" style="margin: 0; font-size: 0.85rem;">Mon-Fri: 8am-5pm EAT</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Card -->
                <div class="card mb-3">
                    <div class="card-body" style="padding: 1.5rem 1.5rem !important;">
                        <div class="d-flex align-items-center">
                            <div class="icon-box-gradient"
                                style="width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas fa-envelope" style="color: white; font-size: 1.4rem;"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="text-primary-custom"
                                    style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem;">
                                    Email</h5>
                                <p style="margin: 0; font-size: 1rem; font-weight: 500;">
                                    pk.automations.ke@gmail.com</p>
                                <p class="text-muted" style="margin: 0; font-size: 0.85rem;">We reply within 24 hours
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Card -->
                <div class="card mb-3">
                    <div class="card-body" style="padding: 1.5rem 1.5rem !important;">
                        <div class="d-flex align-items-center">
                            <div class="icon-box-gradient"
                                style="width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas fa-map-marker-alt" style="color: white; font-size: 1.4rem;"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="text-primary-custom"
                                    style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem;">
                                    Location</h5>
                                <p style="margin: 0; font-size: 1rem; font-weight: 500;">Limuru, Kenya</p>
                                <p class="text-muted" style="margin: 0; font-size: 0.85rem;">Business Center
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WhatsApp Card -->
                <div class="card mb-3">
                    <div class="card-body" style="padding: 1.5rem 1.5rem !important;">
                        <div class="d-flex align-items-center">
                            <div class="icon-box-gradient"
                                style="width: 60px; height: 60px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fab fa-whatsapp" style="color: white; font-size: 1.4rem;"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="text-primary-custom"
                                    style="font-size: 1.2rem; font-weight: 700; margin-bottom: 0.2rem;">
                                    WhatsApp</h5>
                                <p style="margin: 0; font-size: 1rem; font-weight: 500;"><a
                                        href="https://wa.me/254112961056"
                                        style="color: var(--accent-color); text-decoration: none;">Chat with us now</a>
                                </p>
                                <p class="text-muted" style="margin: 0; font-size: 0.85rem;">Quick responses</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section style="padding: 0 0 4rem 0;">
    <div class="container">
        <div style="border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.8195017076755!2d36.81667731475393!3d-1.2833729359719735!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f10d6b8f9b8b9%3A0x1c8f3f5b5f5f5f5f!2sNairobi%2C%20Kenya!5e0!3m2!1sen!2sus!4v1234567890123!5m2!1sen!2sus"
                width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</section>

<!-- Quick Answers Section -->
<section class="qa-section-bg" style="padding: 4rem 0;">
    <div class="container">
        <h2 style="text-align: center; font-size: 2rem; font-weight: 700; margin-bottom: 3rem;">Quick Answers</h2>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="card h-100" style="text-align: center;">
                    <div class="card-body p-4">
                        <p style="font-size: 0.95rem; font-weight: 600; margin-bottom: 1rem;">What are your
                            business hours?</p>
                        <p class="text-primary-custom" style="font-size: 1.05rem; font-weight: 700; margin: 0;">Mon-Fri:
                            8am-5pm,<br>Sat: 9am-2pm EAT</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100" style="text-align: center;">
                    <div class="card-body p-4">
                        <p style="font-size: 0.95rem; font-weight: 600; margin-bottom: 1rem;">Do you ship
                            outside Kenya?</p>
                        <p class="text-primary-custom" style="font-size: 1.05rem; font-weight: 700; margin: 0;">Yes, we
                            ship
                            across<br>East Africa with tracking</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100" style="text-align: center;">
                    <div class="card-body p-4">
                        <p style="font-size: 0.95rem; font-weight: 600; margin-bottom: 1rem;">What is your
                            return policy?</p>
                        <p class="text-primary-custom" style="font-size: 1.05rem; font-weight: 700; margin: 0;">30-day
                            money-back<br>guarantee on most products</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100" style="text-align: center;">
                    <div class="card-body p-4">
                        <p style="font-size: 0.95rem; font-weight: 600; margin-bottom: 1rem;">Do you offer
                            bulk discounts?</p>
                        <p class="text-primary-custom" style="font-size: 1.05rem; font-weight: 700; margin: 0;">Yes!
                            Contact us
                            for<br>wholesale pricing</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>