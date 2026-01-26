<?php
/**
 * Clear Cart
 * Clears the shopping cart and redirects
 */

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Clear cart
$_SESSION['cart'] = [];

// Optional: Clear favorites too if requested (the original file cleared both, adhering to that)
// Actually original file said "Cart and favorites cleared!" so I should probably keep that behavior 
// or maybe just clear cart if the user expected just cart?
// The file name is clear_cart.php. Clearing favorites seems aggressive.
// But valid user feedback implies "Cart cleared successfully". 
// I'll stick to clearing what was there but maybe separate concerns later.
// original: $_SESSION['favorites'] = [];
// I will keep it to match previous behavior but it is odd. 
$_SESSION['favorites'] = [];

setFlashMessage('Cart and favorites cleared successfully!', 'success');

// Redirect to cart or shop
header('Location: cart.php');
exit;
?>