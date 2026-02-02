<?php
$page_title = 'Terms & Conditions';
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<section class="section-padding bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-md border-0">
                    <div class="card-body p-5">
                        <h1 class="mb-4 text-center">Terms & Conditions</h1>
                        <p class="text-muted text-center mb-5">Last updated:
                            <?php echo date('F d, Y'); ?>
                        </p>

                        <div class="terms-content">
                            <h4>1. Introduction</h4>
                            <p>Welcome to PK Automations. By accessing our website and using our services, you agree to
                                correct, update, and promptly correct your account information as accuracy is critical
                                for our services.</p>

                            <h4>2. Use of Our Services</h4>
                            <p>You agree to use our website and services only for lawful purposes. You are prohibited
                                from using our site to transmit any material that is infringing, threatening, false,
                                misleading, abusive, libelous, invasive of privacy, or otherwise objectionable.</p>

                            <h4>3. Account Registration</h4>
                            <p>To access certain features of our website, you may be required to register for an
                                account. You are responsible for maintaining the confidentiality of your account
                                credentials and for all activities that occur under your account.</p>

                            <h4>4. Product Information and Pricing</h4>
                            <p>We strive to provide accurate product descriptions and pricing. However, errors may
                                occur. We reserve the right to correct any errors and to change information at any time
                                without prior notice.</p>

                            <h4>5. Intellectual Property</h4>
                            <p>All content included on this site, such as text, graphics, logos, images, and software,
                                is the property of PK Automations or its content suppliers and protected by
                                international copyright laws.</p>

                            <h4>6. Limitation of Liability</h4>
                            <p>PK Automations shall not be liable for any direct, indirect, incidental, special, or
                                consequential damages resulting from the use or inability to use our services or
                                products.</p>

                            <h4>7. Changes to Terms</h4>
                            <p>We reserve the right to modify these terms at any time. Your continued use of the website
                                after any changes indicates your acceptance of the new terms.</p>

                            <h4>8. Contact Us</h4>
                            <p>If you have any questions about these Terms & Conditions, please contact us at <a
                                    href="mailto:pk.automations.ke@gmail.com">pk.automations.ke@gmail.com</a>.</p>
                        </div>

                        <div class="text-center mt-5">
                            <a href="register" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Back to Registration
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .terms-content h4 {
        color: var(--primary-color);
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        font-weight: 700;
    }

    .terms-content p {
        color: var(--text-muted);
        line-height: 1.7;
        margin-bottom: 1rem;
    }
</style>

<?php require_once 'includes/footer.php'; ?>