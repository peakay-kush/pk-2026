<?php
require 'includes/db.php';
$res = $conn->query('SELECT category, image_path FROM hero_images')->fetchAll();
foreach ($res as $r) {
    echo "- Category: " . $r['category'] . " | Path: " . $r['image_path'] . "\n";
}
