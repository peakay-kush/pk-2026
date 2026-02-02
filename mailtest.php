<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Enable error reporting for this test
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- DIAGNOSTICS ---
echo "<h3>Path Diagnostics:</h3>";
echo "Current Root: " . __DIR__ . "<br>";
$vendor_exists = is_dir(__DIR__ . '/vendor') ? '✅ YES' : '❌ NO';
echo "Vendor Folder exists? $vendor_exists<br>";

$autoload_path = __DIR__ . '/vendor/autoload.php';
echo "Checking Autoload at: $autoload_path (" . (file_exists($autoload_path) ? '✅ FOUND' : '❌ MISSING') . ")<br>";

$manual_path = __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
echo "Checking Manual path at: $manual_path (" . (file_exists($manual_path) ? '✅ FOUND' : '❌ MISSING') . ")<br>";

// Manual PHPMailer load
if (file_exists($autoload_path)) {
    require_once $autoload_path;
}

if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    if (file_exists($manual_path)) {
        require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
        require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    die("<h3 style='color: red;'>CRITICAL ERROR: PHPMailer classes still not found. Please ensure the 'vendor' folder is uploaded to your server inside public_html.</h3>");
}

echo "<h2>Detailed SMTP Debug Test</h2>";

$mail = new PHPMailer(true);

try {
    // --- SERVER SETTINGS ---
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Show full conversation with Gmail
    $mail->isSMTP();
    $mail->Host = $_ENV['EMAIL_HOST'] ?? 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['EMAIL_USERNAME'] ?? '';
    $mail->Password = $_ENV['EMAIL_PASSWORD'] ?? '';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
    $mail->Port = 465;

    // --- RECIPIENTS ---
    $mail->setFrom($_ENV['EMAIL_FROM'] ?? 'noreply@example.com', 'PK Automations Test');
    $mail->addAddress('peakaymr@gmail.com');

    // --- CONTENT ---
    $mail->isHTML(true);
    $mail->Subject = 'PK Automations - Debug Test';
    $mail->Body = 'Testing email with full debug output.';

    echo "<pre>"; // For better visibility of debug output
    $mail->send();
    echo "</pre>";
    echo "<h3 style='color: green;'>✅ Success! Email sent.</h3>";
} catch (Exception $e) {
    echo "</pre>";
    echo "<h3 style='color: red;'>❌ Failed! Error: {$mail->ErrorInfo}</h3>";
    echo "<p>General Message: {$e->getMessage()}</p>";
}
?>