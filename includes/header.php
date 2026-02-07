<?php
// Display flash message if exists
if (!function_exists('getFlashMessage')) {
    require_once __DIR__ . '/functions.php';
}
$flash = getFlashMessage();
if ($flash):
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof showNotification === 'function') {
                showNotification(
                    '<?php echo addslashes($flash['text']); ?>',
                    '<?php echo $flash['type']; ?>'
                );
            }
        });
    </script>
<?php endif; ?>
<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php if (defined('GA_MEASUREMENT_ID') && GA_MEASUREMENT_ID !== 'G-XXXXXXXXXX'): ?>
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo GA_MEASUREMENT_ID; ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() { dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', '<?php echo GA_MEASUREMENT_ID; ?>');
        </script>
    <?php endif; ?>
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description"
        content="<?php echo isset($page_description) ? htmlspecialchars($page_description) : 'PK Automations - Your trusted partner in electronics, automation, and innovation in Kenya.'; ?>">

    <?php
    // Construct current URL for canonical and OG tags
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $current_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    // Default Social Media Meta
    $og_title = isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME;
    $og_desc = isset($page_description) ? $page_description : 'Your trusted partner in electronics, automation, and innovation.';
    $og_image = isset($page_image) ? SITE_URL . '/' . $page_image : SITE_URL . '/assets/images/logo%202.png';
    ?>

    <!-- Canonical Link -->
    <link rel="canonical" href="<?php echo $current_url; ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?php echo isset($og_type) ? $og_type : 'website'; ?>">
    <meta property="og:url" content="<?php echo $current_url; ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($og_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($og_desc); ?>">
    <meta property="og:image" content="<?php echo $og_image; ?>">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo $current_url; ?>">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($og_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($og_desc); ?>">
    <meta name="twitter:image" content="<?php echo $og_image; ?>">

    <!-- Google Fonts - Outfit & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap CSS (absolute path) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome (absolute path) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS (absolute path) -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/toast.css">

    <!-- Favicon (optional, for branding) -->
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/images/logo%202.png">

    <script>
        window.csrfToken = '<?php echo generateCSRFToken(); ?>';
        window.SITE_URL = '<?php echo SITE_URL; ?>';
    </script>

    <!-- Global Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "LocalBusiness",
      "name": "<?php echo SITE_NAME; ?>",
      "image": "<?php echo SITE_URL; ?>/assets/images/logo%202.png",
      "@id": "<?php echo SITE_URL; ?>",
      "url": "<?php echo SITE_URL; ?>",
      "telephone": "+254712345678",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Limuru Road",
        "addressLocality": "Limuru",
        "addressRegion": "Kiambu",
        "postalCode": "00217",
        "addressCountry": "KE"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": -1.107,
        "longitude": 36.641
      },
      "openingHoursSpecification": {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": [
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday",
          "Saturday"
        ],
        "opens": "08:00",
        "closes": "18:00"
      },
      "sameAs": [
        "https://www.facebook.com/pkautomations",
        "https://twitter.com/pkautomations",
        "https://www.instagram.com/pkautomations"
      ]
    }
    </script>
    <?php echo $extra_head ?? ''; ?>
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm py-2">
        <div class="container">
            <!-- Brand Logo -->
            <a class="navbar-brand me-0" href="<?php echo SITE_URL; ?>/index">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo 2.png" alt="PK Automations"
                    style="height: 68px; width: auto; max-width: 250px; margin: 0;">
            </a>

            <!-- Mobile Action Icons (Visible on Mobile, Hidden on LG) -->
            <div class="d-flex d-lg-none align-items-center gap-2 ms-auto me-2">
                <button id="darkModeToggleMobile" class="nav-icon-link" style="font-size: 1.2rem; padding: 0.4rem;">
                    <i class="far fa-moon"></i>
                </button>
                <a href="<?php echo SITE_URL; ?>/cart" class="nav-icon-link nav-cart position-relative"
                    style="font-size: 1.2rem; padding: 0.4rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        style="width: 20px; height: 20px;">
                        <circle cx="8" cy="21" r="1" />
                        <circle cx="19" cy="21" r="1" />
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                    </svg>
                    <?php $cart_count = getCartItemCount(); ?>
                    <span class="nav-badge"
                        style="background-color: #00E676; <?php echo $cart_count > 0 ? '' : 'display: none;'; ?>">
                        <?php echo $cart_count; ?>
                    </span>
                </a>
                <a href="<?php echo SITE_URL; ?>/favorites" class="nav-icon-link nav-favorites position-relative"
                    style="font-size: 1.2rem; padding: 0.4rem;">
                    <i class="far fa-heart"></i>
                    <?php $fav_count = getFavoritesCount(); ?>
                    <span class="nav-badge"
                        style="background-color: #00E676; <?php echo $fav_count > 0 ? '' : 'display: none;'; ?>">
                        <?php echo $fav_count; ?>
                    </span>
                </a>
            </div>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon" style="width: 1.2rem; height: 1.2rem;"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'index' ? 'active' : ''; ?>"
                            href="<?php echo SITE_URL; ?>/index">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'shop' ? 'active' : ''; ?>"
                            href="<?php echo SITE_URL; ?>/shop">Shop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'services' ? 'active' : ''; ?>"
                            href="<?php echo SITE_URL; ?>/services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'tutorials' ? 'active' : ''; ?>"
                            href="<?php echo SITE_URL; ?>/tutorials">Tutorials</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'student_hub' ? 'active' : ''; ?>"
                            href="<?php echo SITE_URL; ?>/student_hub">Student Hub</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'about' ? 'active' : ''; ?>"
                            href="<?php echo SITE_URL; ?>/about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'contact' ? 'active' : ''; ?>"
                            href="<?php echo SITE_URL; ?>/contact">Contact</a>
                    </li>

                    <!-- Mobile-only Account Links -->
                    <li class="nav-item d-lg-none border-top mt-2 pt-2">
                        <?php if (isLoggedIn()): ?>
                            <a class="nav-link" href="<?php echo SITE_URL; ?>/dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                            <?php if (isAdmin()): ?>
                                <a class="nav-link text-primary-custom" href="<?php echo SITE_URL; ?>/admin/">
                                    <i class="fas fa-cog me-2"></i> Admin Panel
                                </a>
                            <?php endif; ?>
                            <a class="nav-link text-danger" href="<?php echo SITE_URL; ?>/logout">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        <?php else: ?>
                            <a class="nav-link text-primary-custom fw-bold" href="<?php echo SITE_URL; ?>/login">
                                <i class="fas fa-sign-in-alt me-2"></i> Login / Register
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>

                <!-- Desktop Action Icons (Hidden on Mobile) -->
                <div class="d-none d-lg-flex align-items-center gap-4 ms-4">
                    <button id="darkModeToggle" class="nav-icon-link" title="Toggle Dark Mode">
                        <i class="far fa-moon"></i>
                    </button>
                    <a href="<?php echo SITE_URL; ?>/cart" class="nav-icon-link nav-cart position-relative"
                        title="Cart">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="8" cy="21" r="1" />
                            <circle cx="19" cy="21" r="1" />
                            <path
                                d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                        </svg>
                        <?php $cart_count = getCartItemCount(); ?>
                        <span class="nav-badge"
                            style="background-color: var(--accent-color); <?php echo $cart_count > 0 ? '' : 'display: none;'; ?>">
                            <?php echo $cart_count; ?>
                        </span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/favorites" class="nav-icon-link nav-favorites position-relative"
                        title="Favorites">
                        <i class="far fa-heart"></i>
                        <?php $fav_count = getFavoritesCount(); ?>
                        <span class="nav-badge"
                            style="background-color: var(--accent-color); <?php echo $fav_count > 0 ? '' : 'display: none;'; ?>">
                            <?php echo $fav_count; ?>
                        </span>
                    </a>

                    <?php if (isLoggedIn()): ?>
                        <?php
                        $user_photo = null;
                        if (isset($_SESSION['user_id'])) {
                            $photo_query = $conn->prepare("SELECT profile_photo FROM users WHERE id = ?");
                            $photo_query->execute([$_SESSION['user_id']]);
                            $photo_result = $photo_query->fetch();
                            $user_photo = $photo_result['profile_photo'] ?? null;
                        }
                        ?>
                        <div class="dropdown">
                            <button class="btn btn-link text-decoration-none p-0 dropdown-toggle nav-profile-btn"
                                type="button" data-bs-toggle="dropdown">
                                <div class="nav-profile-img">
                                    <?php if ($user_photo && file_exists(__DIR__ . '/../assets/images/' . $user_photo)): ?>
                                        <img src="<?php echo SITE_URL; ?>/assets/images/<?php echo htmlspecialchars($user_photo); ?>"
                                            alt="Profile">
                                    <?php else: ?>
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode(sanitize($_SESSION['user_name'])); ?>&background=0B63CE&color=fff&size=128"
                                            alt="Profile">
                                    <?php endif; ?>
                                </div>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/dashboard"><i
                                            class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/"><i
                                                class="fas fa-cog"></i> Admin Panel</a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout"><i
                                            class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/login" class="btn btn-outline-primary shadow-sm"
                            style="border-radius: 20px; padding: 0.4rem 1.5rem; font-weight: 500;">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Removed flash message and toast logic -->