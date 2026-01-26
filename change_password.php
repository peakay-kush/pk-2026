<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        setFlashMessage('New passwords do not match', 'error');
        header('Location: dashboard.php?section=personal-info');
        exit;
    }
    
    if (strlen($new_password) < 6) {
        setFlashMessage('Password must be at least 6 characters long', 'error');
        header('Location: dashboard.php?section=personal-info');
        exit;
    }
    
    try {
        // Verify current password
        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!password_verify($current_password, $user['password_hash'])) {
            setFlashMessage('Current password is incorrect', 'error');
            header('Location: dashboard.php?section=personal-info');
            exit;
        }
        
        // Update password
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $update->execute([$new_hash, $user_id]);
        
        setFlashMessage('Password changed successfully!', 'success');
    } catch (PDOException $e) {
        setFlashMessage('Error changing password: ' . $e->getMessage(), 'error');
    }
}

header('Location: dashboard.php?section=personal-info');
exit;
?>
