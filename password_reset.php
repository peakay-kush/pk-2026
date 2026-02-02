<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = false;

if ($token) {
    // Check if token exists and is valid (MySQL syntax)
    $stmt = $conn->prepare('SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()');
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    if ($reset) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'];
            $confirm = $_POST['confirm_password'];
            if (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match.';
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $conn->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$password_hash, $reset['user_id']]);
                $conn->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$reset['user_id']]);
                $success = true;
            }
        }
    } else {
        $error = 'Invalid or expired token.';
    }
} else {
    $error = 'No token provided.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0B63CE 0%, #1e90ff 100%);
            min-height: 100vh;
        }

        .card {
            border-radius: 18px;
        }

        .brand-logo {
            width: 60px;
            margin-bottom: 10px;
        }

        .form-label {
            font-weight: 500;
        }

        .btn-primary,
        .btn-success {
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg p-4">
                    <div class="text-center mb-4">
                        <img src="assets/images/logo 2.png" alt="Logo" class="brand-logo">
                        <h2 class="fw-bold mb-1" style="color:#0B63CE;">Reset Password</h2>
                        <p class="text-muted mb-0">Set a new password for your account</p>
                    </div>
                    <?php if ($success): ?>
                        <div class="alert alert-success text-center">
                            <i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>
                            Your password has been reset.<br>
                            <a href="index" class="btn btn-success mt-3 w-100">Go to Home</a>
                        </div>
                    <?php elseif ($error): ?>
                        <div class="alert alert-danger text-center">
                            <i class="fas fa-exclamation-triangle fa-2x mb-2 text-danger"></i><br>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!$success && (!$error || ($error && $token))): ?>
                        <form method="POST" class="mt-3">
                            <?php echo csrfField(); ?>
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="password" name="password" required
                                    minlength="6" placeholder="Enter new password">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                    required placeholder="Re-enter new password">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://kit.fontawesome.com/2b8e1e2c2b.js" crossorigin="anonymous"></script>
</body>

</html>