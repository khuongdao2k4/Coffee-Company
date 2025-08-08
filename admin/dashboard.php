<?php
require_once('../utils/config.php');
require_once('../database/dbhelper.php');
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
                <h2 class="text-center" style="padding:20px 0px">Quản lý đơn hàng</h2>
            </div>
            <div class="panel-body">
                <form action="" method="POST">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr style="font-weight: 500;text-align: center;">
                                <td width="50px">STT</td>
                                <td width="80px">Mã Đơn Hàng</td>
                                <td width="120px">Tên User</td>
                                <td width="150px">Tên Sản Phẩm/<br>Số lượng</td>
                                <td width="50px">Size</td>
                                <td width="200px">Địa chỉ</td>
                                <td width="150px">Ngày Đặt <br> Hàng</td>
                                <td width="130px">Số điện thoại </td>
                                <td width="150px">Ghi chú</td>
                                <td>Tổng tiền</td>
                                <td>Phương thức</td>
                                <td width="120px">Trạng thái</td>
                                <td width="100px">Hành động</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {

                                if (isset($_GET['page'])) {
                                    $page = $_GET['page'];
                                } else {
                                    $page = 1;
                                }
                                $limit = 10;
                                $start = ($page - 1) * $limit;
                                $sql = "SELECT 
           o.id AS order_id,
           o.fullname,
           o.address,
           o.phone_number,
           o.payment_method,
           o.order_date,
           o.note,
           SUM(od.num * od.price) AS total_price,
           GROUP_CONCAT(CONCAT(p.title, ' (', od.num, ')') SEPARATOR ', ') AS product_list,
           GROUP_CONCAT(od.size SEPARATOR ', ') AS sizes,
           o.payment_status,
           MAX(od.status) AS status  -- LẤY TRẠNG THÁI TỪ BẢNG order_details
        FROM orders o
        JOIN order_details od ON o.id = od.order_id
        JOIN product p ON p.id = od.product_id
        GROUP BY o.id
        ORDER BY o.order_date DESC
        LIMIT $start, $limit";



                                $order_details_List = executeResult($sql);
                                $total = 0;
                                $count = 0;
                                foreach ($order_details_List as $item) {
                                    // Hiển thị trạng thái từ order_details.status với màu sắc phù hợp
                                    $statusClass = '';
                                    $statusText = '';
                                    // Kiểm tra và xử lý trạng thái - nếu NULL hoặc rỗng thì mặc định là 'Chờ xử lý'
                                    $currentStatus = (!empty($item['status']) && $item['status'] !== null) ? $item['status'] : 'Chờ xử lý';

                                    // Debug để kiểm tra giá trị status
                                    // echo "<!-- Status from DB: '" . $item['status'] . "' Current: '" . $currentStatus . "' -->";
                            
                                    // Nếu là lần chạy đầu tiên và status là NULL, cập nhật thành 'Chờ xử lý'
                                    // if (empty($item['status']) || $item['status'] === null) {
                                    //     // Cập nhật status mặc định cho bản ghi cũ
                                    //     $updateSql = "UPDATE order_details SET status = 'Chờ xử lý' WHERE id = " . $item['id'];
                                    //     execute($updateSql);
                                    //     $currentStatus = 'Chờ xử lý';
                                    // }
                            
                                    $currentStatus = (!empty($item['status']) && $item['status'] !== null) ? $item['status'] : 'Chờ xử lý';

                                    switch ($currentStatus) {
                                        case 'Chờ xử lý':
                                        case 'pending':
                                            $statusClass = 'badge badge-warning';
                                            $statusText = 'Chờ xử lý';
                                            break;
                                        case 'Đã xác nhận':
                                        case 'confirmed':
                                            $statusClass = 'badge badge-success';
                                            $statusText = 'Đã xác nhận';
                                            break;
                                        case 'Đang chuẩn bị':
                                        case 'processing':
                                            $statusClass = 'badge badge-info';
                                            $statusText = 'Đang chuẩn bị';
                                            break;
                                        case 'Đang giao':
                                        case 'shipping':
                                            $statusClass = 'badge badge-primary';
                                            $statusText = 'Đang giao';
                                            break;
                                        case 'Đã hoàn thành':
                                        case 'completed':
                                            $statusClass = 'badge badge-dark';
                                            $statusText = 'Đã hoàn thành';
                                            break;
                                        case 'Đã hủy':
                                        case 'cancelled':
                                            $statusClass = 'badge badge-danger';
                                            $statusText = 'Đã hủy';
                                            break;
                                        default:
                                            $statusClass = 'badge badge-secondary';
                                            $statusText = $currentStatus;
                                    }
                                    echo '
                                        <tr style="text-align: center;">
                                          <td>' . (++$count) . '</td>
                                          <td>' . $item['order_id'] . '</td>
                                          <td>' . $item['fullname'] . '</td>
                                          <td>' . $item['product_list'] . '</td>
                                          <td>' . $item['sizes'] . '</td>
                                          <td>' . $item['address'] . '</td>
                                          <td style="color:green;">' . $item['order_date'] . '</td>
                                          <td>' . $item['phone_number'] . '</td>
                                          <td>' .
                                        (!empty($item['note'])
                                            ? '<span style="color: black;">' . htmlspecialchars($item['note']) . '</span>'
                                            : '<span style="color: orange;">Trống</span>'
                                        ) .
                                        '</td>
                                          <td class="b-500 red">' . number_format($item['total_price'], 0, ',', '.') . '<span> VNĐ</span></td>
                                          <td>' . $item['payment_method'] . '</td>
                                          <td><span class="' . $statusClass . '">' . $statusText . '</span></td>
                                          <td> <a href="edit.php?order_id=' . $item['order_id'] . '" class="btn btn-success btn-sm">Edit</a> </td>
                                        </tr>
                                        ';
                                }
                            } catch (Exception $e) {
                                die("Lỗi thực thi sql: " . $e->getMessage());
                            }
                            ?>
                        </tbody>
                    </table>
                </form>
            </div>
            <ul class="pagination">
                <?php
                $sql = "SELECT * from orders, order_details, product
                        where order_details.order_id=orders.id and product.id=order_details.product_id";
                $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);
                $result = mysqli_query($conn, $sql);
                $current_page = 0;
                if (mysqli_num_rows($result)) {
                    $numrow = mysqli_num_rows($result);
                    $current_page = ceil($numrow / 10);
                }
                for ($i = 1; $i <= $current_page; $i++) {
                    // If it's the current page, display the page number as active
                    echo '<li class="page-item"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>
</body>
<style>
    .b-500 {
        font-weight: 500;
    }

    .red {
        color: red;
    }

    .green {
        color: green;
    }
</style>

</html>