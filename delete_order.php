<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Require login
requireLogin();

$user_id = $_SESSION['user_id'];

// Get order ID from URL
$order_id = $_GET['id'] ?? null;

if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
    setFlashMessage('Invalid security token. Please try again.', 'error');
    header('Location: dashboard.php?section=orders');
    exit;
}

if (!$order_id) {
    setFlashMessage('Invalid order ID', 'error');
    header('Location: dashboard.php?section=orders');
    exit;
}

// Verify that the order belongs to the current user
$verify_query = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
$verify_query->execute([$order_id, $user_id]);
$order = $verify_query->fetch();

if (!$order) {
    setFlashMessage('Order not found or you do not have permission to delete it', 'error');
    header('Location: dashboard.php?section=orders');
    exit;
}

try {
    // Instead of deleting, add to hidden_orders table
    $hide_query = $conn->prepare("INSERT INTO hidden_orders (user_id, order_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE hidden_at = CURRENT_TIMESTAMP");
    $hide_query->execute([$user_id, $order_id]);

    setFlashMessage('Order removed from your history', 'success');
} catch (PDOException $e) {
    setFlashMessage('Error removing order: ' . $e->getMessage(), 'error');
}

// Redirect back to orders page
header('Location: dashboard.php?section=orders');
exit;
