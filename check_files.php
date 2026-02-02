<?php
echo "<h1>File Structure Diagnostic</h1>";

echo "<h3>1. Checking Root Path:</h3>";
echo "Current Directory: " . __DIR__ . "<br>";

echo "<h3>2. Checking Vendor folder:</h3>";
$vendorPath = __DIR__ . '/vendor';
if (is_dir($vendorPath)) {
    echo "✅ Vendor folder exists.<br>";
    echo "<strong>Contents of /vendor/</strong>:<br>";
    $items = scandir($vendorPath);
    echo "<ul>";
    foreach ($items as $item) {
        if ($item !== '.' && $item !== '..') {
            echo "<li>" . (is_dir($vendorPath . '/' . $item) ? '📁 ' : '📄 ') . $item . "</li>";
        }
    }
    echo "</ul>";
} else {
    echo "❌ Vendor folder does NOT exist at: $vendorPath<br>";
}

echo "<h3>3. Searching for PHPMailer specifically:</h3>";
// Check common deep paths
$deepPath = $vendorPath . '/phpmailer/phpmailer/src/PHPMailer.php';
if (file_exists($deepPath)) {
    echo "✅ Success! PHPMailer.php found at: $deepPath<br>";
} else {
    echo "❌ PHPMailer.php NOT found at: $deepPath<br>";

    // Check if it's nested (e.g., vendor/vendor/...)
    $nestedPath = $vendorPath . '/vendor';
    if (is_dir($nestedPath)) {
        echo "⚠️ WARNING: It looks like you have a nested vendor folder (vendor/vendor).<br>";
        echo "Please move all files from <strong>vendor/vendor/</strong> up into <strong>vendor/</strong>.<br>";
    }
}

echo "<h3>4. Server Info:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "User: " . get_current_user() . "<br>";
?>