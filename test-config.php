<?php
require_once('utils/config.php');

echo "<h2>🔧 Config Test - Coffee Shop Application</h2>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; font-family: monospace;'>";

echo "<h3>📍 Base Configuration:</h3>";
echo "<strong>BASE_URL:</strong> " . BASE_URL . "<br>";
echo "<strong>BASE_PATH:</strong> " . BASE_PATH . "<br><br>";

echo "<h3>🌐 Server Information:</h3>";
echo "<strong>HTTP_HOST:</strong> " . $_SERVER['HTTP_HOST'] . "<br>";
echo "<strong>SCRIPT_NAME:</strong> " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "<strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "<br><br>";

echo "<h3>🔗 URL Examples:</h3>";
echo "<strong>Home:</strong> <a href='" . url() . "'>" . url() . "</a><br>";
echo "<strong>Admin:</strong> <a href='" . url('admin/') . "'>" . url('admin/') . "</a><br>";
echo "<strong>Login:</strong> <a href='" . url('login/login.php') . "'>" . url('login/login.php') . "</a><br>";
echo "<strong>Products:</strong> <a href='" . url('admin/product/') . "'>" . url('admin/product/') . "</a><br><br>";

echo "<h3>📁 Path Examples:</h3>";
echo "<strong>Libs:</strong> " . path('libs/') . "<br>";
echo "<strong>Images:</strong> " . path('images/') . "<br>";
echo "<strong>Database:</strong> " . path('database/') . "<br><br>";

echo "<h3>✅ Test Results:</h3>";
if (file_exists(path('database/config.php'))) {
    echo "✅ Database config exists<br>";
} else {
    echo "❌ Database config not found<br>";
}

if (file_exists(path('utils/config.php'))) {
    echo "✅ Utils config exists<br>";
} else {
    echo "❌ Utils config not found<br>";
}

echo "</div>";

echo "<h3>🎯 How to use:</h3>";
echo "<pre style='background: #222; color: #0f0; padding: 15px; border-radius: 5px;'>";
echo "// In your PHP files:\n";
echo "require_once('utils/config.php'); // or adjust path as needed\n\n";
echo "// For URLs:\n";
echo "echo url('admin/dashboard.php'); // Outputs: http://localhost/coffeeshop/admin/dashboard.php\n";
echo "header('Location: ' . url('login/login.php'));\n\n";
echo "// For file paths:\n";
echo "require_once(path('database/dbhelper.php'));\n";
echo "include path('layout/header.php');\n\n";
echo "// For includes:\n";
echo "require_once(includeFile('libs/PHPMailer/src/PHPMailer.php'));";
echo "</pre>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 40px; background: #f0f2f5; }
h2 { color: #2c3e50; }
h3 { color: #34495e; margin-top: 20px; }
a { color: #3498db; text-decoration: none; }
a:hover { text-decoration: underline; }
pre { overflow-x: auto; }
</style>
