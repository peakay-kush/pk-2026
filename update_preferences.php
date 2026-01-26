<?php
require_once 'includes/header.php';
require_once 'includes/db.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $key = $_POST['key'];
    $value = (int)$_POST['value'];
    
    try {
        // Check if preferences exist
        $check = $conn->prepare("SELECT user_id FROM user_preferences WHERE user_id = ?");
        $check->execute([$user_id]);
        
        if ($check->rowCount() == 0) {
            // Create preferences
            $conn->prepare("INSERT INTO user_preferences (user_id) VALUES (?)")->execute([$user_id]);
        }
        
        // Update preference
        if (in_array($key, ['email_notifications', 'sms_notifications'])) {
            $stmt = $conn->prepare("UPDATE user_preferences SET $key = ? WHERE user_id = ?");
            $stmt->execute([$value, $user_id]);
            echo json_encode(['success' => true]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
