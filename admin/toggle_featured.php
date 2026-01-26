<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireAdmin();

if (isset($_GET['id']) && isset($_GET['toggle'])) {
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        die('Invalid security token');
    }
    $product_id = (int) $_GET['id'];
    $toggle = (int) $_GET['toggle'];

    $stmt = $conn->prepare("UPDATE products SET featured = ? WHERE id = ?");
    $stmt->execute([$toggle, $product_id]);

    $_SESSION['flash_message'] = $toggle ? 'Product marked as featured!' : 'Product removed from featured';
    $_SESSION['flash_type'] = 'success';
}

header('Location: products.php');
exit;
