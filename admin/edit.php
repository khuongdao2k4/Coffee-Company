<?php

use function PHPSTORM_META\type;

require_once('../utils/config.php');
require_once('../database/dbhelper.php');
//     $sql = 'select * from orders where';
//     $orders = executeSingleResult($sql);
//     foreach($orders as $item){
//     $fullname = $orders['fullname'];
//     $phone_number = $orders['phone_number'];
//     $email = $orders['email'];
//     $address = $orders['address'];
//     $note = $orders['note'];
// }

// $sql = "UPDATE order_details SET status='$status'";
?>
<!DOCTYPE html>
<html>

<head>
    <title>Thêm Sản Phẩm</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <!-- Popper JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>

    <!-- summernote -->
    <!-- include summernote css/js -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
</head>

<body>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link" href="index.php">Thống kê</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="category/">Quản lý danh mục</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="product/">Quản lý sản phẩm</a>
        </li>
        <li class="nav-item ">
            <a class="nav-link active" href="dashboard.php">Quản lý đơn hàng</a>
        </li>
        <li class="nav-item ">
            <a class="nav-link " href="user/">Quản lý người dùng</a>
        </li>
        <li class="nav-item ">
            <a class="nav-link " href="logout.php">Đăng xuất</a>
        </li>
    </ul>
    <div class="container">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h2 class="text-center">Edit</h2>
            </div>
            <div class="panel-body">
                <form action="" method="POST">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr style="font-weight: 500;text-align: center;">
                                <td width="50px">STT</td>
                                <td width="200px">Tên User</td>
                                <td>Tên Sản Phẩm/<br>Số lượng</td>
                                <td>Tổng tiền</td>
                                <td width="250px">Địa chỉ</td>
                                <td>Số điện thoại</td>
                                <td>Trạng thái</td>
                                <!-- <td width="50px">Lưu</td> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (isset($_GET['order_id'])) {
                                $order_id = $_GET['order_id'];
                            }

                            $count = 0;
                            $sql = "SELECT order_details.id as detail_id, orders.fullname, orders.address, orders.phone_number, product.title, order_details.num, order_details.price, order_details.status, order_details.order_id 
        FROM orders
        JOIN order_details ON order_details.order_id = orders.id
        JOIN product ON product.id = order_details.product_id
        WHERE order_details.order_id = $order_id";
                            $order_details_List = executeResult($sql);

                            foreach ($order_details_List as $item) {
                                $currentStatus = $item['status'] ?? 'Chờ xử lý';
                                $detail_id = $item['detail_id'];

                                echo '
        <tr style="text-align: center;">
            <td width="50px">' . (++$count) . '</td>
            <td style="text-align:center">' . $item['fullname'] . '</td>
            <td>' . $item['title'] . '<br>(<strong>' . $item['num'] . '</strong>)</td>
            <td class="b-500 red">' . number_format($item['price'], 0, ',', '.') . '<span> VNĐ</span></td>
            <td width="100px">' . $item['address'] . '</td>
            <td width="100px">' . $item['phone_number'] . '</td>
            <td>
                <select name="status" id="status_' . $detail_id . '">
                    <option value="Chờ xử lý"' . ($currentStatus == 'Chờ xử lý' ? ' selected' : '') . '>Chờ xử lý</option>
                    <option value="Đã xác nhận"' . ($currentStatus == 'Đã xác nhận' ? ' selected' : '') . '>Đã xác nhận</option>
                    <option value="Đang chuẩn bị"' . ($currentStatus == 'Đang chuẩn bị' ? ' selected' : '') . '>Đang chuẩn bị</option>
                    <option value="Đang giao"' . ($currentStatus == 'Đang giao' ? ' selected' : '') . '>Đang giao</option>
                    <option value="Đã hoàn thành"' . ($currentStatus == 'Đã hoàn thành' ? ' selected' : '') . '>Đã hoàn thành</option>
                    <option value="Đã hủy"' . ($currentStatus == 'Đã hủy' ? ' selected' : '') . '>Đã hủy</option>
                </select>
            </td>
            <td width="100px">
                <button type="button" class="btn btn-success" onclick="updateStatus(' . $detail_id . ')">Lưu</button>
            </td>
        </tr>
    ';
                            }
                            ?>

                        </tbody>
                    </table> <a href="dashboard.php" class="btn btn-warning">Back</a>
                </form>
                <?php
                require_once('../utils/config.php');
                require_once('../database/dbhelper.php');

                if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['ajax_update'])) {
                    header('Content-Type: application/json'); // Đảm bảo đúng kiểu phản hồi JSON
                
                    $status = $_POST['status'];
                    $detail_id = intval($_POST['detail_id']);

                    $sql = "UPDATE order_details SET status = ? WHERE id = ?";
                    $result = execute($sql, [$status, $detail_id]);

                    if ($result !== false) {
                        echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công!']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Không thể cập nhật trạng thái.']);
                    }
                    exit(); // Dừng xuất HTML
                }
                ?>

            </div>
        </div>
    </div>

    <script>
        function updateStatus(detail_id) {
            var statusValue = document.getElementById('status_' + detail_id).value;

            $.ajax({
                url: '', // gửi về cùng file
                method: 'POST',
                data: {
                    ajax_update: true,
                    status: statusValue,
                    detail_id: detail_id
                },
                dataType: 'json',
                success: function (response) {
                    console.log(response); // Xem JSON nhận được
                    if (response.success) {
                        alert(response.message);
                        window.location.href = 'dashboard.php';
                    } else {
                        alert(response.message);
                    }
                }
                ,
                error: function () {
                    window.location.href = 'dashboard.php';
                }
            });
        }
    </script>

</body>
<style>
    .b-500 {
        font-weight: 500;
    }

    .red {
        color: red;
    }
</style>

</html>