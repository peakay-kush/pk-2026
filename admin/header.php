<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Require admin access
requireAdmin();

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Admin Panel' : 'Admin Panel'; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Internal:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        :root {
            /* Brand Colors - Blue & Green Gradient Theme */
            --primary-600: #0B63CE;
            --primary-500: #1e7ae6;
            --primary-400: #3b8eeb;
            --accent-600: #00C853;
            --accent-500: #00F763;
            --accent-400: #33ff85;
            --secondary-600: #0f172a;
            --secondary-500: #1e293b;
            --secondary-100: #f1f5f9;

            /* Brand Gradient */
            --primary-gradient: linear-gradient(135deg, #0B63CE 0%, #00F763 100%);
            --primary-gradient-hover: linear-gradient(135deg, #1e7ae6 0%, #33ff85 100%);

            /* Accents */
            --success-500: #10b981;
            --success-50: #ecfdf5;
            --warning-500: #f59e0b;
            --warning-50: #fffbeb;
            --danger-500: #ef4444;
            --danger-50: #fef2f2;

            /* Layout & Surface */
            --bg-body: #f8fafc;
            --bg-surface: #ffffff;
            --border-color: #e2e8f0;

            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-body);
            color: var(--secondary-600);
            -webkit-font-smoothing: antialiased;
        }

        /* -----------------------
           Sidebar
           ----------------------- */
        .admin-sidebar {
            height: 100vh;
            background: #0f172a;
            /* Slate 900 */
            width: 280px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1030;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .admin-sidebar .admin-header {
            padding: 2rem 1.5rem;
            background: transparent;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .admin-sidebar .admin-header h4 {
            color: white;
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: -0.02em;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
        }

        .admin-sidebar .admin-header small {
            color: #94a3b8;
            font-weight: 500;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: block;
        }

        .admin-sidebar nav {
            padding: 1.5rem 1rem;
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column !important;
            /* Force vertical stacking */
            width: 100%;
        }

        .admin-sidebar .nav-link {
            color: #94a3b8;
            font-size: 0.95rem;
            font-weight: 500;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-left: none;
            width: 100%;
            /* Ensure full width */
        }

        .admin-sidebar .nav-link i {
            width: 1.5rem;
            text-align: center;
            font-size: 1.1rem;
            transition: color 0.2s ease;
        }

        .admin-sidebar .nav-link:hover {
            color: white !important;
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(4px);
        }

        .admin-sidebar .nav-link.active {
            color: white;
            background: var(--primary-gradient);
            box-shadow: 0 4px 12px rgba(11, 99, 206, 0.3);
            font-weight: 600;
        }

        .admin-sidebar .nav-link.active i {
            color: white;
        }

        /* -----------------------
           Main Content Area
           ----------------------- */
        .admin-content {
            margin-left: 280px;
            /* Width of sidebar */
            padding: 2.5rem 3rem;
            width: auto;
            /* Override col width */
            min-height: 100vh;
        }

        /* -----------------------
           Cards & Stats
           ----------------------- */
        .card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: #cbd5e1;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
            font-weight: 700;
            color: var(--secondary-600);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Stat Cards specific */
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .stat-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1rem;
            /* Default icon bg */
            background: var(--secondary-100);
            color: var(--secondary-600);
        }

        /* Stat variations if using blue/green classes */
        .stat-card.blue .icon {
            background: #e3f2fd;
            color: #0B63CE;
        }

        /* Blue-50 to Blue-500 */
        .stat-card.green .icon {
            background: #e8f5e9;
            color: #00C853;
        }

        .stat-card h3 {
            font-size: 1.875rem;
            /* 30px */
            font-weight: 700;
            color: var(--secondary-600);
            margin-bottom: 0.25rem;
            letter-spacing: -0.02em;
        }

        .stat-card p {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* -----------------------
           Typography & Titles
           ----------------------- */
        .page-title {
            font-size: 1.875rem;
            font-weight: 800;
            color: var(--secondary-600);
            letter-spacing: -0.03em;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border: none;
            padding: 0;
        }

        .page-title i {
            color: var(--primary-600);
        }

        h2,
        h3,
        h4,
        h5,
        h6 {
            color: var(--secondary-600);
            font-weight: 700;
        }

        /* -----------------------
           Tables
           ----------------------- */
        .table-responsive {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f8fafc;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            border-top: none;
        }

        .table tbody td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            color: var(--secondary-600);
            border-bottom: 1px solid var(--border-color);
            font-size: 0.95rem;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .table-hover tbody tr:hover {
            background-color: #f8fafc;
        }

        /* -----------------------
           Buttons & Badges
           ----------------------- */
        .btn {
            font-weight: 500;
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(11, 99, 206, 0.3);
        }

        .btn-primary:hover {
            background: var(--primary-gradient-hover);
            border: none;
            transform: translateY(-1px);
            box-shadow: 0 6px 12px -1px rgba(11, 99, 206, 0.4);
        }

        /* Modern Badges (Pills) */
        .badge {
            font-weight: 600;
            padding: 0.35em 0.8em;
            border-radius: 9999px;
            /* Pill shape */
            font-size: 0.75rem;
            letter-spacing: 0.025em;
        }

        /* Soft color badges */
        .bg-success,
        .badge-success {
            background-color: var(--success-50) !important;
            color: var(--success-500) !important;
            border: 1px solid #d1fae5;
        }

        .bg-warning,
        .badge-warning {
            background-color: var(--warning-50) !important;
            color: #d97706 !important;
            border: 1px solid #fde68a;
        }

        /* Darker yellow text */
        .bg-danger,
        .badge-danger {
            background-color: var(--danger-50) !important;
            color: var(--danger-500) !important;
            border: 1px solid #fee2e2;
        }

        .bg-primary,
        .badge-primary {
            background-color: #eef2ff !important;
            color: var(--primary-600) !important;
            border: 1px solid #e0e7ff;
        }

        .bg-secondary {
            background-color: #f1f5f9 !important;
            color: #64748b !important;
            border: 1px solid #e2e8f0;
        }

        /* -----------------------
           Forms
           ----------------------- */
        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-600);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        /* -----------------------
           Utilities & Overrides
           ----------------------- */
        /* Make standard bootstrap bg/text classes respect our variables inside cards if needed */
        a {
            text-decoration: none;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .admin-sidebar {
                transform: translateX(-100%);
                width: 100%;
                height: auto;
                position: relative;
            }

            .admin-sidebar.show {
                transform: translateX(0);
            }

            .admin-content {
                margin-left: 0;
                padding: 1.5rem;
            }
        }

        /* Drag & Drop */
        .drop-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 2.5rem 1.5rem;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            margin-bottom: 0.5rem;
        }

        .drop-zone:hover,
        .drop-zone.dragover {
            border-color: var(--primary-600);
            background: #eff6ff;
        }

        .drop-zone input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .drop-zone .icon {
            font-size: 2.5rem;
            color: #94a3b8;
            margin-bottom: 1rem;
            display: block;
        }

        .drop-zone .text {
            color: #64748b;
            font-weight: 500;
        }

        .drop-zone .text b {
            color: var(--primary-600);
        }

        .preview-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .preview-item {
            position: relative;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            background: #fff;
        }

        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-item .remove-btn {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(239, 68, 68, 0.9);
            color: white;
            border: none;
            border-radius: 4px;
            width: 20px;
            height: 20px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
        }
    </style>

</head>

<body>
    <!-- Layout Wrapper -->
    <div class="d-flex w-100">
        <!-- Sidebar -->
        <div class="admin-sidebar p-0">
            <div class="p-4 text-white admin-header">
                <h4 class="mb-2"><i class="fas fa-shield-halved"></i> Admin Panel</h4>
                <small class="opacity-75"><i class="fas fa-user-circle"></i>
                    <?php echo $_SESSION['user_name']; ?></small>
            </div>
            <nav class="mt-3">
                <a href="index.php" class="nav-link <?php echo $current_page == 'index' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="products.php" class="nav-link <?php echo $current_page == 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="orders.php" class="nav-link <?php echo $current_page == 'orders' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="users.php" class="nav-link <?php echo $current_page == 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="services.php" class="nav-link <?php echo $current_page == 'services' ? 'active' : ''; ?>">
                    <i class="fas fa-wrench"></i> Services
                </a>
                <a href="tutorials.php" class="nav-link <?php echo $current_page == 'tutorials' ? 'active' : ''; ?>">
                    <i class="fas fa-graduation-cap"></i> Tutorials
                </a>
                <a href="payments.php" class="nav-link <?php echo $current_page == 'payments' ? 'active' : ''; ?>">
                    <i class="fas fa-money-bill-wave"></i> Payments
                </a>
                <a href="testimonials.php"
                    class="nav-link <?php echo $current_page == 'testimonials' ? 'active' : ''; ?>">
                    <i class="fas fa-comments"></i> Testimonials
                </a>
                <a href="hero_images.php"
                    class="nav-link <?php echo $current_page == 'hero_images' ? 'active' : ''; ?>">
                    <i class="fas fa-images"></i> Hero Images
                </a>
                <a href="team_members.php"
                    class="nav-link <?php echo $current_page == 'team_members' ? 'active' : ''; ?>">
                    <i class="fas fa-user-friends"></i> Team Members
                </a>
                <a href="messages.php" class="nav-link <?php echo $current_page == 'messages' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i> Messages
                </a>
                <a href="shipping_locations.php"
                    class="nav-link <?php echo $current_page == 'shipping_locations' ? 'active' : ''; ?>">
                    <i class="fas fa-map-marker-alt"></i> Shipping Locations
                </a>
                <hr class="border-light">
                <a href="<?php echo SITE_URL; ?>/index.php" class="nav-link">
                    <i class="fas fa-globe"></i> View Website
                </a>
                <a href="<?php echo SITE_URL; ?>/logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="admin-content p-4 w-100">
            <?php
            // Display flash message if exists
            $flash = getFlashMessage();
            if ($flash):
                ?>
                <div class="alert alert-<?php echo $flash['type'] == 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show"
                    role="alert">
                    <?php echo htmlspecialchars($flash['text']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>