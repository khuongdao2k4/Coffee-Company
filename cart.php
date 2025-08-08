<?php
require_once('utils/config.php');
require_once('database/dbhelper.php');
require_once('utils/utility.php');
$cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];

$idList = [];
foreach ($cart as $item) {
    $idList[] = $item['id'];
}

$cartList = [];
if (count($idList) > 0) {
    $idList = implode(',', $idList);
    $sql = "SELECT p.id, p.title, p.thumbnail, ps.size, ps.price FROM product p
            JOIN product_size ps ON p.id = ps.product_id
            WHERE p.id IN ($idList)";
    $cartList = executeResult($sql);
} else {
    $cartList = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="plugin/fontawesome/css/all.css">
    <link rel="stylesheet" href="css/cart.css">

    <title>Giỏ hàng</title>
</head>

<body>
    <div id="wrapper">
        <?php require_once('layout/header.php'); ?>
        <main style="padding-bottom: 4rem;">
            <section class="cart">
                <div class="container-top">
                    <div class="panel panel-primary">
                        <div class="panel-heading" style="padding: 1rem 0;">
                            <ul class="nav nav-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" href="cart.php">Giỏ hàng</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="history.php">Lịch sử mua hàng</a>
                                </li>
                            </ul>
                            <h2 style="padding-top:2rem" class="">Giỏ hàng</h2>
                        </div>
                        <div class="panel-body"></div>
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr style="font-weight: 500;text-align: center;">
                                    <td width="50px">STT</td>
                                    <td>Ảnh</td>
                                    <td>Tên Sản Phẩm</td>
                                    <td>Size</td>
                                    <td>Giá</td>
                                    <td>Số lượng</td>
                                    <td width="50px"></td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $count = 0;
                                $total = 0;

                                foreach ($cartList as $item) {
                                    $num = 0;
                                    $size = $item['size'];
                                    $price = $item['price'];

                                    // Tìm số lượng từ $cart
                                    foreach ($cart as $value) {
                                        if ($value['id'] == $item['id'] && $value['size'] == $size) {
                                            $num = $value['num'];
                                            break;
                                        }
                                    }

                                    if ($num > 0) {
                                        $total += $num * $price;

                                        // Lấy đá và đường
                                        $ice = 'Không chọn';
                                        $sugar = 'Không chọn';
                                        foreach ($cart as $value) {
                                            if ($value['id'] == $item['id'] && $value['size'] == $size) {
                                                $ice = $value['ice_level'] ?? 'Không chọn';
                                                $sugar = $value['sugar_level'] ?? 'Không chọn';
                                                break;
                                            }
                                        }

                                        echo '
<tr style="text-align: center;">
    <td width="50px">' . (++$count) . '</td>
    <td style="text-align:center">
        <img src="admin/product/' . $item['thumbnail'] . '" alt="" style="width: 50px">
    </td>
    <td>' . $item['title'] . '</td>
    <td>
        Size: ' . $size . '<br>
        Đá: ' . $ice . '<br>
        Đường: ' . $sugar . '
    </td>

    <td class="b-500 red">' . number_format($price, 0, ',', '.') . '<span> VNĐ</span></td>
    <td width="100px">
        <input type="number" class="form-control quantity-input" min="1" value="' . $num . '" 
        onchange="updateQuantity(' . $item['id'] . ', \'' . $size . '\', this.value, \'' . $sugar . '\', \'' . $ice . '\')">
    </td>
    <td>
        <button class="btn btn-danger" onclick="deleteFromCart(' . $item['id'] . ', \'' . $size . '\', \'' . $sugar . '\', \'' . $ice . '\')">Xoá</button>
    </td>
</tr>';
                                    }
                                }
                                ?>

                            </tbody>
                        </table>

                        <?php if ($count > 0): ?>
                            <!-- Tổng giỏ hàng -->
                            <h3>Tổng cộng: <span id="cart-total"><?php echo number_format($total, 0, ',', '.') ?> VNĐ</span>
                            </h3>

                            <button class="btn btn-success" onclick="handleCheckout()">Thanh toán</button>
                        <?php else: ?>
                            <h3>Giỏ hàng trống!</h3>
                            <!-- Nút thanh toán bị vô hiệu hóa khi giỏ hàng trống -->
                            <button class="btn btn-success" disabled>Thanh toán</button>
                        <?php endif; ?>

                    </div>
                </div>
            </section>
        </main>
        <?php require_once('layout/footer.php'); ?>
    </div>
    <script type="text/javascript">
        function deleteFromCart(id, size, sugar_level, ice_level) {
            $.post('api/cookie.php', {
                'action': 'delete',
                'id': id,
                'size': size,
                'sugar_level': sugar_level,
                'ice_level': ice_level
            }, function (data) {
                alert("Sản phẩm đã được xóa khỏi giỏ hàng!");
                location.reload();
            });
        }

        function updateQuantity(id, size, quantity, sugar, ice) {
            if (quantity < 1) {
                alert("Số lượng phải lớn hơn 0!");
                return;
            }

            $.post('api/cookie.php', {
                action: 'update',
                id: id,
                size: size,
                num: quantity, // Đúng tên biến mà PHP đang dùng
                sugar: sugar,
                ice: ice
            }, function (response) {
                const data = JSON.parse(response);
                const itemTotal = data.price * quantity;

                // ✅ Cập nhật tổng tiền của từng dòng
                const rowTotal = document.querySelector(`#row-total-${id}-${size}`);
                if (rowTotal) {
                    rowTotal.textContent = itemTotal.toLocaleString('vi-VN') + " VNĐ";
                }

                // ✅ Cập nhật tổng cộng
                const totalElement = document.getElementById('cart-total');
                if (totalElement) {
                    totalElement.textContent = data.total.toLocaleString('vi-VN') + " VNĐ";
                }
            });
        }



        function checkLogin() {
            // Kiểm tra xem người dùng đã đăng nhập chưa bằng cách check cookie username
            var username = getCookie('username');

            if (!username || username === '') {
                return false; // Chưa đăng nhập
            }

            return true; // Đã đăng nhập
        }

        function handleCheckout() {
            // Kiểm tra đăng nhập trước khi thanh toán
            if (!checkLogin()) {
                // Nếu chưa đăng nhập, hiển thị modal xác nhận
                if (confirm(
                    'Bạn cần đăng nhập để thực hiện thanh toán.\n\nChọn "OK" để chuyển đến trang đăng nhập\nChọn "Cancel" để tiếp tục mua sắm'
                )) {
                    // Lưu URL hiện tại để redirect về sau khi đăng nhập
                    sessionStorage.setItem('redirectAfterLogin', window.location.href);
                    window.location.href = 'login/login.php';
                }
                return false;
            }

            // Nếu đã đăng nhập, kiểm tra giỏ hàng có sản phẩm không
            var cart = getCookie('cart');
            if (!cart || cart === '' || cart === '[]') {
                alert('Giỏ hàng của bạn đang trống!');
                return false;
            }

            // Tất cả điều kiện OK, chuyển đến checkout
            window.location.href = 'checkout.php';
        }

        // Hàm tiện ích để đọc cookie
        function getCookie(name) {
            var nameEQ = name + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }
    </script>
</body>
<style>
    .b-500 {
        font-weight: 500;
    }

    .bold {
        font-weight: bold;
    }

    .red {
        color: rgba(207, 16, 16, 0.815);
    }
</style>

</html>