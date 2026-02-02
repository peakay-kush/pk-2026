<?php
require 'includes/db.php';
$stmt = $conn->prepare("UPDATE hero_images SET image_path = ? WHERE category = ?");
$stmt->execute(['about_hero_1767472487.jpg', 'home_hero']);
echo "Home hero path updated to an existing file.\n";
