<?php
// Start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Skip CSRF validation for cart actions (AJAX-based)
$_SESSION['skip_csrf'] = true;

require_once 'includes/config.php';
require_once 'includes/db.php';

// Remove skip flag
unset($_SESSION['skip_csrf']);

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle both GET and POST
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$product_id = (int) ($_POST['product_id'] ?? $_GET['id'] ?? 0);
$quantity = (int) ($_POST['quantity'] ?? 1);

if ($action === 'add' && $product_id > 0) {
    // Get product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if ($product && $product['stock'] > 0) {
        // Add to cart
        if (isset($_SESSION['cart'][$product_id]) && is_numeric($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }

        // Calculate cart count
        $cart_count = array_sum($_SESSION['cart']);

        echo json_encode(['success' => true, 'cart_count' => $cart_count]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product out of stock']);
    }
    exit;
}

if ($action === 'remove' && $product_id > 0) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
            $_SESSION['success'] = 'Product removed from cart!';
        }
    }

    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'update' && $product_id > 0) {
    $new_quantity = (int) ($_POST['quantity'] ?? 1);
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = $new_quantity;
    }

    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'clear') {
    $_SESSION['cart'] = [];
    if (!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
        $_SESSION['success'] = 'Cart cleared!';
    }

    echo json_encode(['success' => true]);
    exit;
}

header('Location: shop.php');
exit;
?>