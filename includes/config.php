<?php
/**
 * Configuration File
 * Contains site-wide settings and constants
 */

// Load .env variables
require_once __DIR__ . '/env.php';

// Database Configuration
define('DB_DRIVER', 'mysql'); // Options: 'sqlite', 'mysql'. Set to 'mysql' for hosting.

// SQLite Configuration
define('DB_PATH', __DIR__ . '/../database/tech_electronics.db');

// MySQL Configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'pk_automations');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASSWORD'] ?? 'Aa@12345678');

// Site Configuration
define('SITE_NAME', 'PK Automations');
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    ? "https" : "http";

// Production Domain
$host = $_SERVER['HTTP_HOST'] ?? 'pkautomations.co.ke';
define('SITE_URL', "$protocol://$host");
define('ADMIN_EMAIL', 'pk.automations.ke@gmail.com');

// Google Analytics Details (Official GA4)
define('GA_MEASUREMENT_ID', 'G-D2WXD7MZLF');
define('GA_STREAM_ID', '13366781875');

// File Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../assets/images/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', ($protocol === 'https' ? 1 : 0));

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Africa/Nairobi');

// CSRF Protection & Global Security
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SESSION['skip_csrf'])) {
    require_once __DIR__ . '/functions.php';
    validateCSRF();
}

// Error Handling (Production)
define('PRODUCTION', ($_ENV['APP_ENV'] ?? 'local') === 'production');

if (PRODUCTION) {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    // Create logs directory if it doesn't exist
    $log_dir = __DIR__ . '/../logs';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    ini_set('error_log', $log_dir . '/php_error.log');

    // Security Headers
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-XSS-Protection: 1; mode=block");
    if ($protocol === 'https') {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    }

    // Global Error/Exception Handler
    set_exception_handler(function ($exception) {
        error_log($exception);
        if (!headers_sent()) {
            http_response_code(500);
            include __DIR__ . '/../error.php';
        }
        exit;
    });
}
?>