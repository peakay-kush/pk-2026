<?php
/**
 * Full Database Dump Script
 * Generates a complete SQL file with schema AND data
 */
require_once 'includes/config.php';
require_once 'includes/db.php';

$tables = [
    'users',
    'user_preferences',
    'shipping_locations',
    'products',
    'product_images',
    'orders',
    'order_items',
    'hidden_orders',
    'payments',
    'delivery_addresses',
    'services',
    'tutorials',
    'testimonials',
    'contact_messages',
    'team_members',
    'hero_images'
];

$output = "-- Full Database Dump\n";
$output .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($tables as $table) {
    // Drop table
    $output .= "DROP TABLE IF EXISTS `$table`;\n";

    // Create table
    $create = $conn->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
    $create_sql = $create['Create Table'];

    // Fix compatibility for older MySQL/MariaDB versions (e.g. HostPinnacle)
    $create_sql = str_replace('utf8mb4_0900_ai_ci', 'utf8mb4_unicode_ci', $create_sql);
    $create_sql = str_replace('COLLATE=utf8mb4_0900_ai_ci', 'COLLATE=utf8mb4_unicode_ci', $create_sql);

    $output .= $create_sql . ";\n\n";

    // Data
    $data = $conn->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($data)) {
        $output .= "INSERT INTO `$table` (" . implode(', ', array_map(fn($k) => "`$k`", array_keys($data[0]))) . ") VALUES \n";
        $rows = [];
        foreach ($data as $row) {
            $vals = array_map(function ($v) use ($conn) {
                if ($v === null)
                    return "NULL";
                return $conn->quote($v);
            }, $row);
            $rows[] = "(" . implode(', ', $vals) . ")";
        }
        $output .= implode(",\n", $rows) . ";\n\n";
    }
}

$output .= "SET FOREIGN_KEY_CHECKS = 1;\n";

file_put_contents('full_production_data.sql', $output);
echo "Generated full_production_data.sql with " . count($tables) . " tables and their data.\n";
