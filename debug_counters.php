<?php
require 'includes/db.php';
$res = $conn->query('SELECT COUNT(*) FROM products WHERE featured = 1')->fetchColumn();
echo 'Featured products: ' . $res . PHP_EOL;
$res = $conn->query('SELECT COUNT(*) FROM services')->fetchColumn();
echo 'Services: ' . $res . PHP_EOL;
$res = $conn->query('SELECT COUNT(*) FROM tutorials')->fetchColumn();
echo 'Tutorials: ' . $res . PHP_EOL;
$res = $conn->query('SELECT COUNT(*) FROM hero_images')->fetchColumn();
echo 'Hero images: ' . $res . PHP_EOL;
$res = $conn->query('SELECT * FROM products LIMIT 5')->fetchAll();
echo 'All products (first 5): ' . count($res) . PHP_EOL;
foreach ($res as $r) {
    echo "- " . $r['name'] . " (Featured: " . $r['featured'] . ")" . PHP_EOL;
}
