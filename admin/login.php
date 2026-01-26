<?php
require_once '../includes/db.php';

// Redirect if already logged in as admin
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // Check admin credentials
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_name'] = $admin['name'];
            $_SESSION['user_email'] = $admin['email'];
            $_SESSION['user_role'] = 'admin';
            $_SESSION['admin_logged_in'] = true;

            // Ensure session is written before redirect
            session_write_close();
            session_start();

            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - PK Automations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6FB7E5;
            --accent-color: #00C853;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color), #0d47a1);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-color), #1976d2);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .login-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .login-header h2 {
            margin: 0;
            font-weight: 700;
        }

        .login-body {
            padding: 2.5rem 2rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(11, 99, 206, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color), #094a9f);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.875rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            box-shadow: 0 4px 10px rgba(11, 99, 206, 0.3);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #094a9f, var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(11, 99, 206, 0.5);
            color: white;
        }

        .alert {
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-user-shield"></i>
                <h2>Admin Panel</h2>
                <p class="mb-0">PK Automations</p>
            </div>
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <?php echo csrfField(); ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-login w-100">
                        <i class="fas fa-sign-in-alt"></i> Login to Dashboard
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="../index.php" class="text-muted"><i class="fas fa-home"></i> Back to Website</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>