<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$page_title = 'Create Account';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index');
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        $result = registerUser($name, $email, $password);

        if ($result['success']) {
            $success = true;
        } else {
            $error = $result['message'];
        }
    }
}

require_once 'includes/header.php';
?>

<div class="register-page-wrapper">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-75">
            <div class="col-md-8 col-lg-6 col-xl-5">

                <!-- Main Card -->
                <div class="card glass-card shadow-lg border-0 overflow-hidden fade-in-up">
                    <div class="card-body p-4 p-md-5">

                        <!-- Header -->
                        <div class="text-center mb-4">
                            <div class="icon-box mb-3 mx-auto">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h2 class="fw-bold mb-2">Create Account</h2>
                            <p class="text-muted">Join PK Automations today</p>
                        </div>

                        <!-- Alerts -->
                        <?php if ($success): ?>
                            <div class="alert alert-success d-flex align-items-center border-0 shadow-sm" role="alert">
                                <i class="fas fa-check-circle fs-4 me-2"></i>
                                <div>
                                    <h6 class="alert-heading mb-1">Registration Successful!</h6>
                                    <p class="mb-0 small">Welcome aboard! <a href="login"
                                            class="alert-link text-decoration-underline">Log in now</a> to start shopping.
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger d-flex align-items-center border-0 shadow-sm" role="alert">
                                <i class="fas fa-exclamation-circle fs-4 me-2"></i>
                                <div>
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Form -->
                        <?php if (!$success): ?>
                            <form method="POST" class="needs-validation" novalidate>
                                <?php echo csrfField(); ?>

                                <!-- Full Name -->
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="name" name="name" placeholder="John Doe"
                                        required>
                                    <label for="name"><i class="fas fa-user me-2 text-primary-custom"></i>Full Name</label>
                                </div>

                                <!-- Email -->
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="name@example.com" required>
                                    <label for="email"><i class="fas fa-envelope me-2 text-primary-custom"></i>Email
                                        Address</label>
                                </div>

                                <!-- Password -->
                                <div class="form-floating mb-3 position-relative">
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="Password" required minlength="6">
                                    <label for="password"><i
                                            class="fas fa-lock me-2 text-primary-custom"></i>Password</label>
                                    <button
                                        class="btn btn-link position-absolute top-50 end-0 translate-middle-y text-decoration-none text-muted pe-3 toggle-password"
                                        type="button" style="z-index: 5;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>

                                <!-- Confirm Password -->
                                <div class="form-floating mb-4">
                                    <input type="password" class="form-control" id="confirm_password"
                                        name="confirm_password" placeholder="Confirm Password" required minlength="6">
                                    <label for="confirm_password"><i
                                            class="fas fa-lock me-2 text-primary-custom"></i>Confirm Password</label>
                                    <div class="form-text ms-1"><small>Must be at least 6 characters long.</small></div>
                                </div>

                                <!-- Terms -->
                                <div class="mb-4 form-check">
                                    <input type="checkbox" class="form-check-input" id="terms" required
                                        style="cursor: pointer;">
                                    <label class="form-check-label small user-select-none" for="terms"
                                        style="cursor: pointer;">
                                        I agree to the <a href="terms"
                                            class="text-primary fw-bold text-decoration-none">Terms & Conditions</a>
                                    </label>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-primary w-100 btn-lg mb-4 shadow-sm">
                                    Create Account
                                </button>

                                <!-- Login Link -->
                                <div class="text-center">
                                    <span class="text-muted small">Already have an account?</span>
                                    <a href="login" class="text-primary fw-bold text-decoration-none ms-1">Login
                                        here</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Trust Badges (Optional decoration) -->
                <div class="text-center mt-4 text-muted opacity-75">
                    <small><i class="fas fa-shield-alt me-1"></i> Secure Registration</small>
                    <span class="mx-2">&bull;</span>
                    <small><i class="fas fa-lock me-1"></i> Data Privacy</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom Page Styles */
    .register-page-wrapper {
        min-height: calc(100vh - 76px);
        /* Full height minus typical navbar */
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        padding: 3rem 0;
        display: flex;
        align-items: center;
    }

    body.dark-mode .register-page-wrapper {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    body.dark-mode .glass-card {
        background: rgba(30, 41, 59, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .icon-box {
        width: 70px;
        height: 70px;
        background: var(--primary-grad);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        box-shadow: 0 10px 20px rgba(11, 99, 206, 0.2);
    }

    .form-floating>.form-control:focus~label,
    .form-floating>.form-control:not(:placeholder-shown)~label {
        color: var(--primary-color);
        opacity: 1;
        transform: scale(0.85) translateY(-0.75rem) translateX(0.15rem);
    }

    .form-floating>.form-control {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding-left: 1rem;
    }

    .form-floating>label {
        padding-left: 1rem;
        color: #94a3b8;
    }

    body.dark-mode .form-floating>.form-control {
        background-color: #0f172a;
        border-color: #334155;
        color: #fff;
    }
</style>

<script>
    // Password Toggle Script
    document.querySelector('.toggle-password').addEventListener('click', function () {
        const passwordInput = document.getElementById('password');
        const icon = this.querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    // Form Validation logic
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms)
            .forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
    })()
</script>

<?php require_once 'includes/footer.php'; ?>