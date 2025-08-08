<?php
require_once('../../utils/config.php');
require_once('../../database/config.php');
require_once('../../database/dbhelper.php');

if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    if ($id > 0) {
        try {
            // Xóa các dòng liên quan trong product_size
            $sql1 = "DELETE FROM product_size WHERE product_id = $id";
            execute($sql1);

            // Xóa sản phẩm trong bảng product
            $sql2 = "DELETE FROM product WHERE id = $id";
            execute($sql2);

            echo "success";
        } catch (Exception $e) {
            echo "error: " . $e->getMessage();
        }
    } else {
        echo "error: ID không hợp lệ";
    }
    exit();
}

?>