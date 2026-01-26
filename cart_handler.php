<?php
session_start();
require_once 'includes/db.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_GET['action'] ?? '';
$product_id = (int) ($_GET['id'] ?? 0);

if ($action === 'add' && $product_id > 0) {
    // Get product details
    $stmt = $conn->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if ($product && $product['stock'] > 0) {
        // Add to cart
        if (isset($_SESSION['cart'][$product_id]) && is_numeric($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]++;
        } else {
            $_SESSION['cart'][$product_id] = 1;
        }

        $_SESSION['success'] = 'Product added to cart!';
    } else {
        $_SESSION['error'] = 'Product is out of stock!';
    }

    header('Location: shop.php');
    exit;
}

if ($action === 'remove' && $product_id > 0) {
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['success'] = 'Product removed from cart!';
    }

    header('Location: cart.php');
    exit;
}

if ($action === 'update' && $product_id > 0) {
    $quantity = (int) ($_POST['quantity'] ?? 1);

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] = $quantity;
        $_SESSION['success'] = 'Cart updated!';
    }

    header('Location: cart.php');
    exit;
}

header('Location: shop.php');
exit;
?>