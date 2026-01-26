<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $password = $_POST['password'];
    
    try {
        // Verify password
        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!password_verify($password, $user['password_hash'])) {
            setFlashMessage('Incorrect password', 'error');
            header('Location: dashboard.php?section=settings');
            exit;
        }
        
        // Delete user account (cascade will delete related data)
        $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
        
        // Destroy session
        session_destroy();
        
        setFlashMessage('Your account has been deleted successfully', 'success');
        header('Location: index.php');
        exit;
        
    } catch (PDOException $e) {
        setFlashMessage('Error deleting account: ' . $e->getMessage(), 'error');
        header('Location: dashboard.php?section=settings');
        exit;
    }
}

header('Location: dashboard.php?section=settings');
exit;
?>
