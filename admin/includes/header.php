<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Main CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>

<body>
    </head>

    <body>
        <div class="sidebar">
            <div class="sidebar-header">
                <h4><i class="fas fa-cog"></i> Admin Panel</h4>
                <small><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></small>
            </div>
            <div class="sidebar-menu">
                <a href="index.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="products.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="orders.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="users.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="services.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''; ?>">
                    <i class="fas fa-wrench"></i> Services
                </a>
                <a href="tutorials.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) == 'tutorials.php' ? 'active' : ''; ?>">
                    <i class="fas fa-graduation-cap"></i> Tutorials
                </a>
                <a href="payments.php"
                    class="<?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
                    <i class="fas fa-money-bill"></i> Payments
                </a>
                <hr style="border-color: rgba(255,255,255,0.2); margin: 1rem 0;">
                <a href="../index" target="_blank">
                    <i class="fas fa-external-link-alt"></i> View Website
                </a>
                <a href="?logout=1" onclick="return confirm('Are you sure you want to logout?')">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <div class="main-content">
            <div class="top-navbar">
                <h5 class="mb-0"><?php echo $page_title ?? 'Dashboard'; ?></h5>
                <div>
                    <span class="text-muted me-3"><i class="far fa-clock"></i>
                        <?php echo date('F d, Y - h:i A'); ?></span>
                    <span
                        class="badge bg-primary"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                </div>
            </div>

            <div class="content-area">