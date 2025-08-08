<?php
// Simple debug script to identify memory issues
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '256M'); // Lower limit to catch issues faster

require_once('../../database/config.php');
require_once('../../database/dbhelper.php');

echo "<h2>Memory Debug Tool</h2>";

// Check if specific product ID is provided
$product_id = isset($_GET['id']) ? $_GET['id'] : null;

if ($product_id) {
    echo "<h3>Checking Product ID: $product_id</h3>";
    
    // Check basic product info first
    echo "<p>Step 1: Basic product info...</p>";
    $sql = 'SELECT id, title, LENGTH(content) as content_length FROM product WHERE id = ? LIMIT 1';
    $result = executeSingleResult($sql, [$product_id]);
    
    if ($result) {
        echo "<p>✓ Product found: " . htmlspecialchars($result['title']) . "</p>";
        echo "<p>Content length: " . number_format($result['content_length']) . " bytes</p>";
        
        if ($result['content_length'] > 100000) {
            echo "<p><strong>⚠️ WARNING: Content is very large!</strong></p>";
        }
    } else {
        echo "<p>❌ Product not found</p>";
    }
    
    // Check product sizes
    echo "<p>Step 2: Product sizes...</p>";
    $sql_sizes = 'SELECT COUNT(*) as size_count FROM product_size WHERE product_id = ?';
    $size_result = executeSingleResult($sql_sizes, [$product_id]);
    
    if ($size_result) {
        echo "<p>Number of sizes: " . $size_result['size_count'] . "</p>";
        
        if ($size_result['size_count'] > 50) {
            echo "<p><strong>⚠️ WARNING: Too many sizes!</strong></p>";
        }
    }
    
} else {
    echo "<h3>Database Overview</h3>";
    
    // Check total products
    $sql = 'SELECT COUNT(*) as total FROM product';
    $result = executeSingleResult($sql);
    echo "<p>Total products: " . $result['total'] . "</p>";
    
    // Check for products with large content
    $sql = 'SELECT id, title, LENGTH(content) as content_length FROM product WHERE LENGTH(content) > 50000 ORDER BY content_length DESC LIMIT 5';
    $large_content = executeResult($sql);
    
    if (!empty($large_content)) {
        echo "<h4>Products with large content:</h4>";
        echo "<ul>";
        foreach ($large_content as $item) {
            echo "<li>ID: {$item['id']} - {$item['title']} - " . number_format($item['content_length']) . " bytes 
                  <a href='debug-memory.php?id={$item['id']}'>[Check]</a></li>";
        }
        echo "</ul>";
    }
    
    // Check for products with many sizes
    $sql = 'SELECT p.id, p.title, COUNT(ps.id) as size_count 
            FROM product p 
            LEFT JOIN product_size ps ON p.id = ps.product_id 
            GROUP BY p.id 
            HAVING size_count > 10 
            ORDER BY size_count DESC 
            LIMIT 5';
    $many_sizes = executeResult($sql);
    
    if (!empty($many_sizes)) {
        echo "<h4>Products with many sizes:</h4>";
        echo "<ul>";
        foreach ($many_sizes as $item) {
            echo "<li>ID: {$item['id']} - {$item['title']} - {$item['size_count']} sizes
                  <a href='debug-memory.php?id={$item['id']}'>[Check]</a></li>";
        }
        echo "</ul>";
    }
}

echo "<p>Memory used: " . number_format(memory_get_usage(true) / 1024 / 1024, 2) . " MB</p>";
echo "<p>Peak memory: " . number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB</p>";
?>
