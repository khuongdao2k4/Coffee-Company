<?php
/**
 * Script to update all PHP files to use the new config system
 * Tự động thêm require_once('utils/config.php') vào các file cần thiết
 */

require_once('utils/config.php');

echo "<h2>🔧 Auto-Fix Hard-coded Paths</h2>";

// Danh sách các file cần kiểm tra
$filesToCheck = [
    'index.php',
    'cart.php', 
    'checkout.php',
    'details.php',
    'history.php',
    'product.php',
    'thucdon.php',
    'about.php',
    'thanks.php',
    'sendMail.php',
    'admin/dashboard.php',
    'admin/edit.php',
    'admin/logout.php',
    'menu-con/banhmi.php',
    'menu-con/caphe.php', 
    'menu-con/monannhe.php',
    'menu-con/trasua.php',
    'menu-con/tratraicay.php',
    'layout/footer.php',
    'api/checkout-form.php',
    'api/cookie.php',
    'cart/pay-cart.php',
    'login/changePass.php',
    'login/connect.php',
    'login/logout.php', 
    'login/reg.php'
];

$fixedFiles = [];
$errors = [];

foreach ($filesToCheck as $file) {
    $fullPath = path($file);
    
    if (!file_exists($fullPath)) {
        echo "⚠️ File not found: $file<br>";
        continue;
    }
    
    $content = file_get_contents($fullPath);
    
    // Kiểm tra xem file đã include config chưa
    if (strpos($content, "require_once('utils/config.php')") === false && 
        strpos($content, 'require_once("utils/config.php")') === false &&
        strpos($content, "require_once('../utils/config.php')") === false &&
        strpos($content, 'require_once("../utils/config.php")') === false &&
        strpos($content, "require_once('../../utils/config.php')") === false) {
        
        // Tìm vị trí thích hợp để thêm config
        $lines = explode("\n", $content);
        $insertIndex = -1;
        
        for ($i = 0; $i < count($lines); $i++) {
            // Tìm dòng <?php đầu tiên
            if (trim($lines[$i]) == '<?php' && $insertIndex == -1) {
                $insertIndex = $i + 1;
                break;
            }
        }
        
        if ($insertIndex > -1) {
            // Xác định đường dẫn tương đối đến config
            $depth = substr_count($file, '/');
            $configPath = str_repeat('../', $depth) . 'utils/config.php';
            
            // Thêm dòng require config
            array_splice($lines, $insertIndex, 0, "require_once('$configPath');");
            
            $newContent = implode("\n", $lines);
            
            // Backup file gốc
            $backupPath = $fullPath . '.backup';
            copy($fullPath, $backupPath);
            
            // Ghi file mới
            if (file_put_contents($fullPath, $newContent)) {
                $fixedFiles[] = $file;
                echo "✅ Fixed: $file (added config include)<br>";
            } else {
                $errors[] = "Cannot write to: $file";
                echo "❌ Error writing: $file<br>";
            }
        } else {
            echo "⚠️ Cannot find <?php tag in: $file<br>";
        }
    } else {
        echo "✅ Already has config: $file<br>";
    }
}

echo "<br><h3>📋 Summary:</h3>";
echo "<strong>Files fixed:</strong> " . count($fixedFiles) . "<br>";
echo "<strong>Errors:</strong> " . count($errors) . "<br>";

if (!empty($fixedFiles)) {
    echo "<br><strong>Fixed files:</strong><br>";
    foreach ($fixedFiles as $file) {
        echo "- $file<br>";
    }
}

if (!empty($errors)) {
    echo "<br><strong>Errors:</strong><br>";
    foreach ($errors as $error) {
        echo "- $error<br>";
    }
}

echo "<br><p><strong>Note:</strong> Backup files (.backup) have been created for all modified files.</p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #2c3e50; }
h3 { color: #34495e; }
</style>
