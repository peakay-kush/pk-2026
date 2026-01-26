<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $street = trim($_POST['street']);
    $address_line = trim($_POST['address_line']);
    $postal_code = trim($_POST['postal_code']);
    $city = trim($_POST['city']);
    $is_primary = isset($_POST['is_primary']) ? 1 : 0;
    
    try {
        // If setting as primary, unset other primary addresses
        if ($is_primary) {
            $conn->prepare("UPDATE delivery_addresses SET is_primary = 0 WHERE user_id = ?")->execute([$user_id]);
        }
        
        // Insert new address
        $stmt = $conn->prepare("INSERT INTO delivery_addresses (user_id, name, phone, street, address_line, postal_code, city, is_primary) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $phone, $street, $address_line, $postal_code, $city, $is_primary]);
        
        setFlashMessage('Address added successfully!', 'success');
    } catch (PDOException $e) {
        setFlashMessage('Error adding address: ' . $e->getMessage(), 'error');
    }
}

header('Location: dashboard.php?section=addresses');
exit;
?>
