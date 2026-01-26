<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if user is logged in as admin
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_id = (int)($_POST['image_id'] ?? 0);
    $product_id = (int)($_POST['product_id'] ?? 0);
    
    try {
        // Get image info
        $stmt = $conn->prepare("SELECT * FROM product_images WHERE id = ? AND product_id = ?");
        $stmt->execute([$image_id, $product_id]);
        $image = $stmt->fetch();
        
        if ($image) {
            // Delete physical file
            $file_path = '../assets/images/products/' . $image['image_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Delete from database
            $delete_stmt = $conn->prepare("DELETE FROM product_images WHERE id = ?");
            $delete_stmt->execute([$image_id]);
            
            // If this was the primary image, make another image primary
            if ($image['is_primary']) {
                $new_primary = $conn->prepare("SELECT id FROM product_images WHERE product_id = ? ORDER BY sort_order ASC LIMIT 1");
                $new_primary->execute([$product_id]);
                $new_img = $new_primary->fetch();
                
                if ($new_img) {
                    $update_primary = $conn->prepare("UPDATE product_images SET is_primary = 1 WHERE id = ?");
                    $update_primary->execute([$new_img['id']]);
                    
                    // Update products table
                    $get_path = $conn->prepare("SELECT image_path FROM product_images WHERE id = ?");
                    $get_path->execute([$new_img['id']]);
                    $new_path = $get_path->fetchColumn();
                    
                    $update_product = $conn->prepare("UPDATE products SET image = ? WHERE id = ?");
                    $update_product->execute([$new_path, $product_id]);
                }
            }
            
            $_SESSION['flash_message'] = 'Image deleted successfully!';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Image not found';
            $_SESSION['flash_type'] = 'error';
        }
    } catch (Exception $e) {
        $_SESSION['flash_message'] = 'Error deleting image: ' . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
    }
}

header('Location: product_edit.php?id=' . $product_id);
exit;
