<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
    $user_id = $_SESSION['user_id'];
    $file = $_FILES['profile_photo'];
    
    // Validate file
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        setFlashMessage('Invalid file type. Only JPG, PNG and GIF are allowed', 'error');
        header('Location: dashboard.php?section=personal-info');
        exit;
    }
    
    if ($file['size'] > $max_size) {
        setFlashMessage('File is too large. Maximum size is 5MB', 'error');
        header('Location: dashboard.php?section=personal-info');
        exit;
    }
    
    try {
        // Create upload directory if it doesn't exist
        $upload_dir = 'assets/images/profiles/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
        $filepath = $upload_dir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Update database
            $stmt = $conn->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
            $stmt->execute(['profiles/' . $filename, $user_id]);
            
            setFlashMessage('Profile photo updated successfully!', 'success');
        } else {
            setFlashMessage('Error uploading file', 'error');
        }
    } catch (Exception $e) {
        setFlashMessage('Error: ' . $e->getMessage(), 'error');
    }
}

header('Location: dashboard.php?section=personal-info');
exit;
?>
