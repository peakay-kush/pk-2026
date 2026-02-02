<?php
/**
 * PK Automations - Live Site Diagnostic Tool
 * Upload this to your server to find why the page is blank or missing data.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PK Automations - Diagnostic Report</h1>";

// 1. Check PHP Version
echo "<h2>1. Environment</h2>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Current Directory: " . __DIR__ . "<br>";

// 2. Check .env file
echo "<h2>2. Environment Variables (.env)</h2>";
$env_path = __DIR__ . '/.env';
if (file_exists($env_path)) {
    echo "✅ .env file found.<br>";
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            // Hide password
            if (stripos($name, 'PASS') !== false)
                $value = "********";
            echo " - $name: $value<br>";
        }
    }
} else {
    echo "❌ .env file NOT found. Dot-files are often hidden in cPanel. Please ensure it was uploaded.<br>";
}

// 3. Check Database Connection
echo "<h2>3. Database Connection</h2>";
require_once 'includes/config.php';
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connection Successful!<br>";
    echo "Connected to: " . DB_NAME . " on " . DB_HOST . " using user " . DB_USER . "<br>";

    // 4. Check Tables and Data
    echo "<h2>4. Data Verification</h2>";
    $tables = ['products', 'services', 'tutorials', 'hero_images', 'users'];
    foreach ($tables as $table) {
        try {
            $count = $conn->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            echo " - Table `$table`: $count rows found.<br>";
            if ($count == 0) {
                echo "   ⚠️ WARNING: Table `$table` is empty. Please import the full SQL file.<br>";
            }
        } catch (Exception $e) {
            echo " - ❌ Table `$table` Error: " . $e->getMessage() . "<br>";
        }
    }

} catch (PDOException $e) {
    echo "❌ Connection Failed: " . $e->getMessage() . "<br>";
    echo "Tip: On HostPinnacle, your DB Name and User MUST have a prefix (e.g., peakayku_pk_automations).<br>";
}

// 5. Check Critical Folders
echo "<h2>5. File System</h2>";
$folders = ['assets/images/products', 'assets/images/hero', 'vendor'];
foreach ($folders as $folder) {
    if (is_dir(__DIR__ . '/' . $folder)) {
        echo "✅ Folder `$folder` exists.<br>";
    } else {
        echo "❌ Folder `$folder` is MISSING!<br>";
    }
}

echo "<br><br><strong>Delete this file once diagnostics are complete for security!</strong>";
