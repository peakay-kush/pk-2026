<?php
/**
 * Database Connection
 * Establishes and maintains SQLite database connection
 */

require_once __DIR__ . '/config.php';

// Create database directory if it doesn't exist
$db_dir = dirname(DB_PATH);
if (!file_exists($db_dir)) {
    mkdir($db_dir, 0755, true);
}

// Create PDO connection
try {
    if (defined('DB_DRIVER') && DB_DRIVER === 'mysql') {
        // MySQL Connection
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true,
        ];
        $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
    } else {
        // SQLite Connection (Default)
        $conn = new PDO('sqlite:' . DB_PATH);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        // Enable foreign keys
        $conn->exec('PRAGMA foreign_keys = ON');
    }
} catch (PDOException $e) {
    // Log the actual error
    error_log("Database Connection Error: " . $e->getMessage());

    // Show friendly error in production
    if (defined('PRODUCTION') && PRODUCTION) {
        if (file_exists(__DIR__ . '/../error.php')) {
            include __DIR__ . '/../error.php';
        } else {
            die("<h1>Service Unavailable</h1><p>We are currently experiencing technical difficulties. Please try again later.</p>");
        }
        exit;
    }

    // If MySQL connection fails, help valid configuration (Dev mode only)
    if (defined('DB_DRIVER') && DB_DRIVER === 'mysql') {
        die("MySQL Connection failed: " . $e->getMessage() . "<br>Please check your config.php settings and ensure the database '" . DB_NAME . "' exists.");
    }
    die("Connection failed: " . $e->getMessage());
}

/**
 * Execute a prepared statement
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind
 * @return PDOStatement|bool
 */
function executeQuery($sql, $params = [])
{
    global $conn;

    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query error: " . $e->getMessage());
        return false;
    }
}

/**
 * Escape string for safe SQL usage
 * @param string $string
 * @return string
 */
function escapeString($string)
{
    global $conn;
    return $conn->quote($string);
}

/**
 * Get last insert ID
 * @return string
 */
function getLastInsertId()
{
    global $conn;
    return $conn->lastInsertId();
}
?>