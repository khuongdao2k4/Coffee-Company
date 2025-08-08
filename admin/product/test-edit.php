<?php
// Minimal edit page for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');

require_once('../../database/config.php');
require_once('../../database/dbhelper.php');

$id = isset($_GET['id']) ? $_GET['id'] : '';

if (!$id || !is_numeric($id)) {
    die('Invalid product ID');
}

echo "<h2>Minimal Product Edit - ID: $id</h2>";
echo "<p>Memory at start: " . number_format(memory_get_usage(true) / 1024 / 1024, 2) . " MB</p>";

try {
    // Get basic product info only
    $sql = 'SELECT id, title, number, thumbnail, id_category FROM product WHERE id = ? LIMIT 1';
    $product = executeSingleResult($sql, [$id]);
    
    echo "<p>Memory after basic query: " . number_format(memory_get_usage(true) / 1024 / 1024, 2) . " MB</p>";
    
    if ($product) {
        echo "<h3>Product Info:</h3>";
        echo "<p>Title: " . htmlspecialchars($product['title']) . "</p>";
        echo "<p>Number: " . htmlspecialchars($product['number']) . "</p>";
        echo "<p>Category ID: " . htmlspecialchars($product['id_category']) . "</p>";
        
        // Now try to get content separately
        echo "<p>Trying to get content...</p>";
        $sql_content = 'SELECT LENGTH(content) as content_length FROM product WHERE id = ? LIMIT 1';
        $content_info = executeSingleResult($sql_content, [$id]);
        
        if ($content_info) {
            echo "<p>Content length: " . number_format($content_info['content_length']) . " bytes</p>";
            
            if ($content_info['content_length'] < 100000) {
                // Only get content if it's not too large
                $sql_content_full = 'SELECT content FROM product WHERE id = ? LIMIT 1';
                $content_result = executeSingleResult($sql_content_full, [$id]);
                echo "<p>Content loaded successfully</p>";
            } else {
                echo "<p><strong>Content too large, skipping</strong></p>";
            }
        }
        
        echo "<p>Memory after content check: " . number_format(memory_get_usage(true) / 1024 / 1024, 2) . " MB</p>";
        
        // Check sizes
        echo "<p>Checking sizes...</p>";
        $sql_sizes = 'SELECT COUNT(*) as size_count FROM product_size WHERE product_id = ?';
        $size_count = executeSingleResult($sql_sizes, [$id]);
        echo "<p>Size count: " . $size_count['size_count'] . "</p>";
        
        if ($size_count['size_count'] < 100) {
            $sql_sizes_full = 'SELECT * FROM product_size WHERE product_id = ? LIMIT 100';
            $sizes = executeResult($sql_sizes_full, [$id]);
            echo "<p>Sizes loaded: " . count($sizes) . "</p>";
        } else {
            echo "<p><strong>Too many sizes, skipping</strong></p>";
        }
        
        echo "<p>Memory at end: " . number_format(memory_get_usage(true) / 1024 / 1024, 2) . " MB</p>";
        
    } else {
        echo "<p>Product not found</p>";
    }
    
} catch (Exception $e) {
    echo "<p><strong>Error: " . $e->getMessage() . "</strong></p>";
}

echo "<p><a href='debug-memory.php'>← Back to Debug Overview</a></p>";
?>
