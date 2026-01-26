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
        header('Location: dashboard.php?section=addresses');
        exit;
    }

    try {
        // Verify the address belongs to the user
        $check = $conn->prepare("SELECT id FROM delivery_addresses WHERE id = ? AND user_id = ?");
        $check->execute([$address_id, $user_id]);

        if ($check->rowCount() > 0) {
            // Unset all primary addresses for this user
            $conn->prepare("UPDATE delivery_addresses SET is_primary = 0 WHERE user_id = ?")->execute([$user_id]);

            // Set this address as primary
            $conn->prepare("UPDATE delivery_addresses SET is_primary = 1 WHERE id = ?")->execute([$address_id]);

            setFlashMessage('Primary address updated successfully!', 'success');
        } else {
            setFlashMessage('Address not found', 'error');
        }
    } catch (PDOException $e) {
        setFlashMessage('Error updating address: ' . $e->getMessage(), 'error');
    }
}

header('Location: dashboard.php?section=addresses');
exit;
?>