<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

if (isset($_GET['id'])) {
    $address_id = (int) $_GET['id'];
    $user_id = $_SESSION['user_id'];

    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        setFlashMessage('Invalid security token. Please try again.', 'error');
        header('Location: dashboard?section=addresses');
        exit;
    }
    try {
        // Debug output
        $debug = [];
        $debug[] = 'Address ID: ' . $address_id;
        $debug[] = 'User ID: ' . $user_id;
        $check = $conn->prepare("SELECT id, user_id FROM delivery_addresses WHERE id = ?");
        $check->execute([$address_id]);
        $row = $check->fetch();
        if ($row) {
            $debug[] = 'DB user_id for address: ' . $row['user_id'];
            if ($row['user_id'] == $user_id) {
                $conn->prepare("DELETE FROM delivery_addresses WHERE id = ?")->execute([$address_id]);
                setFlashMessage('Address deleted successfully!', 'success');
            } else {
                setFlashMessage('Address does not belong to you.', 'error');
            }
        } else {
            $debug[] = 'No address found with this ID.';
            setFlashMessage('Address not found.', 'error');
        }
    } catch (PDOException $e) {
        setFlashMessage('Error deleting address: ' . $e->getMessage(), 'error');
    }
}

header('Location: dashboard?section=addresses');
exit;
?>