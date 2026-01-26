<?php
// Fetch shipping locations for checkout dropdown
require_once 'includes/db.php';
$shipping_locations = $conn->query("SELECT id, name, fee FROM shipping_locations ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
