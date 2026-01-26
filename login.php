<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$page_title = 'Login';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = loginUser($email, $password);

    if ($result['success']) {
        header('Location: index.php');
        exit;
    } else {
        $error = $result['message'];
    }
}

require_once 'includes/header.php';
?>

<section class="section-padding">
</section>
<style>
    body {
        background: linear-gradient(135deg, #0B63CE 0%, #1e90ff 100%);
        min-height: 100vh;
    }

    .login-card {
        border-radius: 18px;
    }

    .brand-logo {
        width: 60px;
        margin-bottom: 10px;
    }

    .form-label {
        font-weight: 500;
    }

    .btn-primary {
        border-radius: 8px;
    }
</style>
<section class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg p-4 login-card">
                    <div class="text-center mb-4">
                        <img src="assets/images/logo 2.png" alt="Logo" class="brand-logo">
                        <h2 class="fw-bold mb-1" style="color:#0B63CE;">Sign In</h2>
                        <p class="text-muted mb-0">Login to your account</p>
                    </div>
                    <?php if ($error): ?>
                        <div class="alert alert-danger text-center"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="POST" class="mt-3">
                        <?php echo csrfField(); ?>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <a href="password_reset_request.php" class="small text-primary">Forgot password?</a>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="register.php">Don't have an account? Register</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="https://kit.fontawesome.com/2b8e1e2c2b.js" crossorigin="anonymous"></script>
</section>

<?php require_once 'includes/footer.php'; ?>