<?php
/**
 * Configuration file for Coffee Shop application
 * Defines base URLs and paths to make the application portable
 */

// Tự động phát hiện base URL của ứng dụng
function getBaseUrl() {
    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || 
                 $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    $requestUri = $_SERVER['REQUEST_URI'];
    
    // Lấy thư mục gốc của ứng dụng từ REQUEST_URI
    $scriptName = dirname($_SERVER['SCRIPT_NAME']);
    
    // Nếu app nằm trong thư mục con (như /coffeeshop/)
    if ($scriptName !== '/') {
        // Tìm thư mục gốc của ứng dụng
        $pathParts = explode('/', trim($scriptName, '/'));
        $appFolder = '/' . $pathParts[0];
    } else {
        $appFolder = '';
    }
    
    return $protocol . $domainName . $appFolder;
}

// Tự động phát hiện base path (cho include/require)
function getBasePath() {
    return dirname(dirname(__FILE__)); // Lên 2 cấp từ utils/
}

// Define constants
define('BASE_URL', getBaseUrl());
define('BASE_PATH', getBasePath());

// Hàm tiện ích để tạo URL
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

// Hàm tiện ích để tạo đường dẫn file
function path($path = '') {
    return BASE_PATH . '/' . ltrim($path, '/');
}

// Hàm để include file với đường dẫn tương đối an toàn
function includeFile($relativePath) {
    return BASE_PATH . '/' . ltrim($relativePath, '/');
}

// Debug functions (chỉ dùng khi cần thiết)
function debugPaths() {
    echo "<pre>";
    echo "BASE_URL: " . BASE_URL . "\n";
    echo "BASE_PATH: " . BASE_PATH . "\n";
    echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
    echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
    echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "\n";
    echo "</pre>";
}
