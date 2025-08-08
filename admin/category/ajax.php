<?php
// Thêm debug để xem lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../../database/config.php');
require_once('../../database/dbhelper.php');
if (!empty($_POST)) {
	if (isset($_POST['action'])) {
		$action = $_POST['action'];

		switch ($action) {
			case 'delete':
				if (isset($_POST['id'])) {
					$id = (int)$_POST['id']; // Ép kiểu integer để đảm bảo an toàn

					if ($id > 0) {
						try {
							// Kết nối database
							$conn = getConnection();
							
							// Cập nhật sản phẩm để tránh lỗi foreign key
							$sql1 = "UPDATE product SET id_category = NULL WHERE id_category = $id";
							mysqli_query($conn, $sql1);
							
							// Xóa category
							$sql2 = "DELETE FROM category WHERE id = $id";
							$result = mysqli_query($conn, $sql2);
							
							if ($result) {
								$affected_rows = mysqli_affected_rows($conn);
								if ($affected_rows > 0) {
									echo "success";
								} else {
									echo "error: Không tìm thấy danh mục để xóa (ID: $id)";
								}
							} else {
								echo "error: Lỗi SQL: " . mysqli_error($conn);
							}
							
							mysqli_close($conn);
						} catch (Exception $e) {
							echo "error: " . $e->getMessage();
						}
					} else {
						echo "error: ID không hợp lệ";
					}
				}
				break;
		}
	}
}?>