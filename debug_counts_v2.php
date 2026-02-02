<?php
require 'includes/db.php';
$p_feat = $conn->query('SELECT COUNT(*) FROM products WHERE featured = 1')->fetchColumn();
$p_all = $conn->query('SELECT COUNT(*) FROM products')->fetchColumn();
$serv = $conn->query('SELECT COUNT(*) FROM services')->fetchColumn();
$tuts = $conn->query('SELECT COUNT(*) FROM tutorials')->fetchColumn();
$hero = $conn->query('SELECT COUNT(*) FROM hero_images')->fetchColumn();

echo "Featured Products: $p_feat\n";
echo "Total Products: $p_all\n";
echo "Services: $serv\n";
echo "Tutorials: $tuts\n";
echo "Hero Images: $hero\n";
