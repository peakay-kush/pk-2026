<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$email_sent = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if user exists
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            // Generate token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            // Store token
            $conn->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)')->execute([$user['id'], $token, $expires]);
            // Send email
            $reset_link = SITE_URL . '/password_reset.php?token=' . $token;
            $subject = 'Password Reset Request';
            $message = 'Click the link below to reset your password:<br><a href="' . $reset_link . '">' . $reset_link . '</a><br>This link will expire in 1 hour.';
            sendEmail($email, $subject, $message);
            $email_sent = true;
        } else {
            $error = 'No account found with that email.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Password Reset Request</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="mb-3">Reset Your Password</h3>
                        <?php if ($email_sent): ?>
                            <div class="alert alert-success">A password reset link has been sent to your email.</div>
                        <?php elseif ($error): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <?php echo csrfField(); ?>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
                        </form>
                        <a href="login.php" class="d-block mt-3">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>