<?php
require 'includes/db.php';
$res = $conn->query('SELECT category, is_active FROM hero_images')->fetchAll();
foreach ($res as $r) {
    echo "- Category: " . $r['category'] . " (Active: " . $r['is_active'] . ")\n";
}
