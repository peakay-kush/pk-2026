<?php
header("Content-Type: application/xml; charset=utf-8");
require_once 'includes/config.php';
require_once 'includes/db.php';

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Static Pages
$static_pages = [
    '',
    '/shop',
    '/services',
    '/tutorials',
    '/about',
    '/contact',
    '/student_hub'
];

foreach ($static_pages as $page) {
    echo '<url>';
    echo '<loc>' . SITE_URL . $page . '</loc>';
    echo '<changefreq>weekly</changefreq>';
    echo '<priority>' . ($page === '' ? '1.0' : '0.8') . '</priority>';
    echo '</url>';
}

// Dynamic Products
try {
    $stmt = $conn->query("SELECT slug, created_at FROM products");
    while ($row = $stmt->fetch()) {
        echo '<url>';
        echo '<loc>' . SITE_URL . '/product/' . urlencode($row['slug']) . '</loc>';
        echo '<lastmod>' . date('Y-m-d', strtotime($row['created_at'])) . '</lastmod>';
        echo '<changefreq>monthly</changefreq>';
        echo '<priority>0.7</priority>';
        echo '</url>';
    }
} catch (Exception $e) {
}

// Dynamic Tutorials
try {
    $stmt = $conn->query("SELECT slug, created_at FROM tutorials");
    while ($row = $stmt->fetch()) {
        echo '<url>';
        echo '<loc>' . SITE_URL . '/tutorial/' . urlencode($row['slug']) . '</loc>';
        echo '<lastmod>' . date('Y-m-d', strtotime($row['created_at'])) . '</lastmod>';
        echo '<changefreq>monthly</changefreq>';
        echo '<priority>0.6</priority>';
        echo '</url>';
    }
} catch (Exception $e) {
}

echo '</urlset>';
?>