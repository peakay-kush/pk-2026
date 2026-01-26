<?php
require_once 'includes/config.php';

require_once 'includes/auth.php';
require_once 'includes/functions.php';

logoutUser();
// Restart session to set flash message after destroying old session
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}
redirect('login.php', 'You have been logged out successfully', 'success');
?>
