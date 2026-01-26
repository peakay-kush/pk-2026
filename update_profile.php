<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    try {
        // Check if email is already taken by another user
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->execute([$email, $user_id]);
        
        if ($check->rowCount() > 0) {
            setFlashMessage('Email is already taken by another user', 'error');
            header('Location: dashboard.php?section=personal-info');
            exit;
        }
        
        // Update user information
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $user_id]);
        
        // Update session
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        
        setFlashMessage('Profile updated successfully!', 'success');
    } catch (PDOException $e) {
        setFlashMessage('Error updating profile: ' . $e->getMessage(), 'error');
    }
}

header('Location: dashboard.php?section=personal-info');
exit;
?>
