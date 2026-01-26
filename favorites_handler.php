<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Initialize favorites if not exists
if (!isset($_SESSION['favorites'])) {
    $_SESSION['favorites'] = [];
}

$action = $_GET['action'] ?? '';
$product_id = $_GET['id'] ?? 0;
$is_ajax = (isset($_GET['ajax']) && $_GET['ajax'] == 1) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

if ($action === 'add' && $product_id > 0) {
    $success = false;
    if (!in_array($product_id, $_SESSION['favorites'])) {
        $_SESSION['favorites'][] = $product_id;
        $message = 'Product added to favorites!';
        if (!$is_ajax) {
            setFlashMessage($message, 'success');
        }
        $success = true;
    } else {
        $message = 'Product already in favorites!';
        if (!$is_ajax) {
            setFlashMessage($message, 'error');
        }
    }

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message, 'fav_count' => count($_SESSION['favorites'])]);
        exit;
    }

    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'shop.php'));
    exit;
}

if ($action === 'remove' && $product_id > 0) {
    $key = array_search($product_id, $_SESSION['favorites']);
    if ($key !== false) {
        unset($_SESSION['favorites'][$key]);
        $_SESSION['favorites'] = array_values($_SESSION['favorites']); // Re-index array
        $message = 'Product removed from favorites!';
        if (!$is_ajax) {
            setFlashMessage($message, 'success');
        }
    } else {
        $message = 'Product not in favorites!';
    }

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $message, 'fav_count' => count($_SESSION['favorites'])]);
        exit;
    }

    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'favorites.php'));
    exit;
}

if ($is_ajax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

header('Location: shop.php');
exit;
