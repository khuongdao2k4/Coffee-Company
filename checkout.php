<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('utils/config.php');
require_once('database/dbhelper.php');
require_once('utils/utility.php');

// Kiểm tra giỏ hàng hoặc mua ngay
$cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];
if (!is_array($cart)) {
    $cart = [];
}

// Xử lý nút "Mua Ngay" từ details.php
if (isset($_GET['id']) && isset($_GET['num']) && isset($_GET['size']) && isset($_GET['price'])) {
    // Validate parameters
    if (!is_numeric($_GET['id']) || !is_numeric($_GET['num']) || !is_numeric($_GET['price'])) {
        echo '<script>alert("Dữ liệu không hợp lệ!"); window.location="index.php";</script>';
        exit();
    }

    $buyNowItem = [
        'id' => intval($_GET['id']),
        'num' => intval($_GET['num']),
        'size' => filter_var($_GET['size'], FILTER_SANITIZE_STRING),
        'price' => floatval($_GET['price'])
    ];
    // Thay thế giỏ hàng bằng sản phẩm mua ngay
    $cart = [$buyNowItem];
    $isBuyNow = true;
} else {
    $isBuyNow = false;
}

// Kiểm tra đăng nhập
$username = isset($_COOKIE['username']) ? filter_var($_COOKIE['username'], FILTER_SANITIZE_STRING) : '';
if (empty($username)) {
    echo '<script>alert("Vui lòng đăng nhập để tiến hành mua hàng"); window.location="login/login.php";</script>';
    exit();
}

// Lấy ID người dùng với prepared statement
$sqlUser = "SELECT id_user FROM user WHERE username = ? LIMIT 1";
$resultUser = executeResult($sqlUser, [$username]);
if (count($resultUser) == 0) {
    echo '<script>alert("Người dùng không hợp lệ!"); window.location="login/login.php";</script>';
    exit();
}
$id_user = $resultUser[0]['id_user'];

// Lấy danh sách sản phẩm từ giỏ hàng
$idList = [];
foreach ($cart as $item) {
    if ($item['num'] > 0) {
        $idList[] = $item['id'];
    }
}

$cartList = [];
if ($isBuyNow) {
    // Lấy thông tin sản phẩm từ URL (dành cho mua ngay)
    $id = $_GET['id'] ?? '';
    $num = $_GET['num'] ?? 1;
    $size = $_GET['size'] ?? '';
    $price = $_GET['price'] ?? 0;
    $sugar_level = $_GET['sugar_level'] ?? '';
    $ice_level = $_GET['ice_level'] ?? '';

    // Truy vấn thông tin sản phẩm từ DB
    $sql = "SELECT p.id, p.title, p.thumbnail FROM product p WHERE p.id = ? LIMIT 1";
    $result = executeResult($sql, [$id]);

    if (count($result) > 0) {
        $result[0]['size'] = $size;
        $result[0]['price'] = $price;
        $result[0]['num'] = $num;
        $result[0]['sugar_level'] = $sugar_level;
        $result[0]['ice_level'] = $ice_level;
        $cartList = $result;
    }
} else if (count($idList) > 0) {
    // Xử lý giỏ hàng bình thường với prepared statement
    $placeholders = implode(',', array_fill(0, count($idList), '?'));
    $sql = "SELECT p.id, p.title, p.thumbnail, ps.size, ps.price 
            FROM product p
            JOIN product_size ps ON p.id = ps.product_id
            WHERE p.id IN ($placeholders)";
    $rawCartList = executeResult($sql, $idList);
    foreach ($rawCartList as $item) {
        foreach ($cart as $cartItem) {
            if ($item['id'] == $cartItem['id'] && $item['size'] == $cartItem['size']) {
                $item['num'] = $cartItem['num'];
                $cartList[] = $item;
                break;
            }
        }
    }
}

// Tính tổng tiền
function calculateTotal($cart, $cartList, $isBuyNow = false)
{
    $total = 0;
    if ($isBuyNow && count($cartList) > 0) {
        // Tính tổng cho mua ngay
        $item = $cartList[0];
        $total = intval($item['num']) * floatval($item['price']);
    } else {
        // Tính tổng cho giỏ hàng bình thường
        foreach ($cartList as $item) {
            foreach ($cart as $value) {
                if (
                    isset($value['id'], $value['size'], $value['num']) &&
                    $value['id'] == $item['id'] &&
                    $value['size'] == $item['size']
                ) {
                    $total += intval($value['num']) * floatval($item['price']);
                }
            }
        }
    }
    return $total;
}
$total = calculateTotal($cart, $cartList, $isBuyNow);

// Xử lý đơn hàng
$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $phone_number = preg_match('/^[0-9]{10,11}$/', $_POST['phone_number']) ? $_POST['phone_number'] : '';
    $address = trim($_POST['address']);
    $note = trim($_POST['note']);
    $payment_method = $_POST['payment_method'] ?? '';

    // Kiểm tra từng trường và đưa ra thông báo cụ thể
    if (!$fullname) {
        echo '<script>alert("Vui lòng nhập họ và tên!"); window.location="checkout.php";</script>';
        exit();
    }
    if (!$email) {
        echo '<script>alert("Vui lòng nhập email hợp lệ!"); window.location="checkout.php";</script>';
        exit();
    }
    if (!$phone_number) {
        echo '<script>alert("Vui lòng nhập số điện thoại hợp lệ (10-11 số)!"); window.location="checkout.php";</script>';
        exit();
    }
    if (!$address) {
        echo '<script>alert("Vui lòng nhập địa chỉ!"); window.location="checkout.php";</script>';
        exit();
    }
    if (!$payment_method) {
        echo '<script>alert("Vui lòng chọn phương thức thanh toán!"); window.location="checkout.php";</script>';
        exit();
    }

    $payment_status = ($payment_method == 'COD') ? 'confirmed' : 'pending';

    // Thêm đơn hàng
    $orderSql = "INSERT INTO orders (fullname, email, phone_number, address, note, id_user, payment_method, payment_status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($orderSql);
    $stmt->bind_param('ssssssss', $fullname, $email, $phone_number, $address, $note, $id_user, $payment_method, $payment_status);
    $stmt->execute();
    $orderId = $stmt->insert_id;
    $stmt->close();

    // Thêm chi tiết đơn hàng
    if ($isBuyNow) {
        // Mua ngay - 1 sản phẩm
        $item = $cart[0];
        $sugar = isset($item['sugar_level']) ? $item['sugar_level'] : 'Không rõ';
        $ice = isset($item['ice_level']) ? $item['ice_level'] : 'Không rõ';
        $sizeInfo = $item['size'] . ' - Đường: ' . $sugar . ' - Đá: ' . $ice;

        $orderDetailSql = "INSERT INTO order_details (order_id, product_id, size, num, price, id_user, status, created_at, payment_method)
                           VALUES (?, ?, ?, ?, ?, ?, 'Chờ xử lý', NOW(), ?)";
        $stmt = $conn->prepare($orderDetailSql);
        $stmt->bind_param('iisidss', $orderId, $item['id'], $sizeInfo, $item['num'], $item['price'], $id_user, $payment_method);

        if (!$stmt->execute()) {
            error_log("Lỗi khi thêm order_detail: " . $stmt->error);
        }

        $stmt->close();
    } else {
        // Mua qua giỏ hàng
        foreach ($cartList as $item) {
            foreach ($cart as $value) {
                if ($value['id'] == $item['id'] && $value['size'] == $item['size']) {
                    $quantity = $value['num'];
                    $sugar = isset($value['sugar_level']) ? $value['sugar_level'] : 'Không rõ';
                    $ice = isset($value['ice_level']) ? $value['ice_level'] : 'Không rõ';
                    $sizeInfo = $item['size'] . ' - Đường: ' . $sugar . ' - Đá: ' . $ice;

                    $orderDetailSql = "INSERT INTO order_details 
                        (order_id, product_id, size, num, price, id_user, status, created_at, payment_method)
                        VALUES (?, ?, ?, ?, ?, ?, 'Chờ xử lý', NOW(), ?)";
                    $stmt = $conn->prepare($orderDetailSql);
                    $stmt->bind_param('iisidss', $orderId, $item['id'], $sizeInfo, $quantity, $item['price'], $id_user, $payment_method);

                    if (!$stmt->execute()) {
                        error_log("Lỗi thêm order_detail: " . $stmt->error);
                    }

                    $stmt->close();
                }
            }
        }
    }

   // Xoá giỏ hàng sau khi đặt hàng xong
    unset($_SESSION['cart']); 
    echo 
    '<script>
    alert("Đặt hàng thành công!"); 
    document.cookie = "cart=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    window.location="history.php";
    </script>';
    exit();
}

mysqli_close($conn);
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
    <title>Thanh toán</title>
</head>


<body>
    <div id="wrapper">
        <?php require_once('layout/header.php'); ?>


        <main style="padding-bottom: 4rem;">
            <section class="cart">
                <div class="container">
                    <h4 style="text-align: center; font-size: 35px; font-weight: bold;">Tiến hành thanh toán</h4>
                    <div class="row">
                        <div class="panel panel-primary col-md-6">
                            <h4 style="padding: 2rem 0; border-bottom: 1px solid black;">Nhập thông tin mua hàng</h4>
                            <form action="checkout.php" method="POST">
                                <div class="form-group">
                                    <label for="usr">Họ và tên:</label>
                                    <input required="true" type="text" class="form-control" id="usr" name="fullname"
                                        placeholder="Nhập họ và tên">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email:</label>
                                    <input required="true" type="email" class="form-control" id="email" name="email"
                                        placeholder="Nhập email">
                                </div>
                                <div class="form-group">
                                    <label for="phone_number">Số điện thoại:</label>
                                    <input required="true" type="text" class="form-control" id="phone_number"
                                        name="phone_number" placeholder="Nhập số điện thoại">
                                </div>
                                <div class="form-group">
                                    <label for="province">Tỉnh / Thành phố:</label>
                                    <select class="form-control" id="province" required>
                                        <option value="">-- Chọn tỉnh/thành --</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="district">Quận / Huyện:</label>
                                    <select class="form-control" id="district" required>
                                        <option value="">-- Chọn huyện --</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="ward">Phường / Xã:</label>
                                    <select class="form-control" id="ward" required>
                                        <option value="">-- Chọn xã --</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="address">Số nhà, tên đường:</label>
                                    <input type="text" class="form-control" id="address_detail"
                                        placeholder="VD: 12 Nguyễn Huệ" required>
                                </div>

                                <!-- Ẩn input tổng địa chỉ, sẽ gửi cái này -->
                                <input type="hidden" name="address" id="full_address">
                                <div class="form-group">
                                    <label for="note">Ghi chú:</label>
                                    <textarea class="form-control" rows="3" name="note" id="note"
                                        placeholder="Ghi chú nếu có"></textarea>
                                </div>
                                <div class="form-group">
                                    <!-- <label for="payment_method">Chọn hình thức thanh toán:</label><br>
    <input type="radio" id="cod" name="payment_method" value="COD" checked>
    <label for="cod">Thanh toán khi nhận hàng</label><br> -->
                                    <!-- <input type="radio" id="vnpay" name="payment_method" value="VNPay">
    <label for="vnpay">Thanh toán VNPay</label><br> -->
                                </div>


                                <div class="form-group">
                                    <label for="payment_method">Chọn hình thức thanh toán:</label><br>
                                    <input type="radio" id="cod" name="payment_method" value="COD" checked>
                                    <label for="cod">Thanh toán khi nhận hàng</label><br>

                                    <input type="radio" id="banking" name="payment_method" value="BANKING">
                                    <label for="banking">Chuyển khoản Banking</label>

                                    <div id="banking_qr" style="display: none; margin-top: 10px;">
                                        <p>Vui lòng quét mã QR bên dưới để chuyển khoản:</p>
                                        <img src="images/banking.jpg" alt="QR Banking" width="200">
                                        <p><strong>Ghi chú: </strong> SĐT hoặc Tên người đặt</p>
                                        <button type="submit" name="banking_paid" class="btn btn-primary mt-3">Tôi đã
                                            thanh toán</button>
                                    </div>
                                </div>

                                <div id="cod_btn">
                                    <button type="submit" name="cod_order" class="btn btn-success">Đặt hàng</button>
                                </div>
                            </form>
                        </div>


                        <div class="panel panel-primary col-md-6">
                            <h4 style="padding: 2rem 0; border-bottom: 1px solid black;">Đơn hàng của bạn</h4>
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr style="font-weight: 500;text-align: center;">
                                        <td width="50px">STT</td>
                                        <td>Tên Sản Phẩm</td>
                                        <td>Size</td>
                                        <td>Đường</td>
                                        <td>Đá</td>
                                        <td>Giá</td>
                                        <td>Số lượng</td>
                                        <td>Tổng tiền</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 0;
                                    if ($isBuyNow && count($cartList) > 0) {
                                        // Hiển thị sản phẩm mua ngay
                                        $item = $cartList[0];
                                        $num = $item['num'];
                                        $sugar = isset($item['sugar_level']) ? $item['sugar_level'] : 'Không rõ';
                                        $ice = isset($item['ice_level']) ? $item['ice_level'] : 'Không rõ';

                                        echo '
   <tr style="text-align: center;">
        <td>' . (++$count) . '</td>
        <td>' . $item['title'] . '</td>
        <td>' . $item['size'] . '</td>
        <td>' . $sugar . '</td>
        <td>' . $ice . '</td>
        <td>' . number_format($item['price'], 0, ',', '.') . ' VNĐ</td>
        <td>' . $num . '</td>
        <td>' . number_format($num * $item['price'], 0, ',', '.') . ' VNĐ</td>
    </tr>';
                                    } else {
                                        // Hiển thị giỏ hàng bình thường
                                        foreach ($cartList as $item) {
                                            foreach ($cart as $value) {
                                                if ($value['id'] == $item['id'] && $value['size'] == $item['size']) {
                                                    $num = $value['num'];
                                                    $sugar = isset($value['sugar_level']) ? $value['sugar_level'] : 'Không rõ';
                                                    $ice = isset($value['ice_level']) ? $value['ice_level'] : 'Không rõ';

                                                    echo '
                <tr style="text-align: center;">
                    <td>' . (++$count) . '</td>
                    <td>' . $item['title'] . '</td>
                    <td>' . $item['size'] . '</td>
                    <td>' . $sugar . '</td>
                    <td>' . $ice . '</td>
                    <td>' . number_format($item['price'], 0, ',', '.') . ' VNĐ</td>
                    <td>' . $num . '</td>
                    <td>' . number_format($num * $item['price'], 0, ',', '.') . ' VNĐ</td>
                </tr>';
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                </tbody>

                            </table>
                            <h3>Tổng cộng: <?= number_format($total, 0, ',', '.') ?> VNĐ</h3>
                        </div>
                    </div>
                </div>
            </section>



        </main>
        <?php require_once('layout/footer.php'); ?>
    </div>
    <script>
        $(document).ready(function() {
            // Validation form
            $('form').on('submit', function(e) {
                var isValid = true;
                var errors = [];

                // Kiểm tra họ tên
                if ($('#usr').val().trim() === '') {
                    errors.push('Vui lòng nhập họ và tên!');
                    isValid = false;
                }

                // Kiểm tra email
                var email = $('#email').val();
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    errors.push('Vui lòng nhập email hợp lệ!');
                    isValid = false;
                }

                // Kiểm tra số điện thoại
                var phone = $('#phone_number').val();
                var phoneRegex = /^[0-9]{10,11}$/;
                if (!phoneRegex.test(phone)) {
                    errors.push('Số điện thoại phải có 10-11 chữ số!');
                    isValid = false;
                }

                // Kiểm tra địa chỉ
                if ($('#province').val() === '' || $('#district').val() === '' || $('#ward').val() === '') {
                    errors.push('Vui lòng chọn đầy đủ tỉnh/thành, quận/huyện, phường/xã!');
                    isValid = false;
                }

                if ($('#address_detail').val().trim() === '') {
                    errors.push('Vui lòng nhập địa chỉ chi tiết!');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    alert(errors.join('\n'));
                    return false;
                }

                // Kết hợp địa chỉ
                const provinceName = $("#province option:selected").text();
                const districtName = $("#district option:selected").text();
                const wardName = $("#ward").val();
                const detail = $("#address_detail").val();

                const fullAddress = `${detail}, ${wardName}, ${districtName}, ${provinceName}`;
                $("#full_address").val(fullAddress);
            });

            $('input[name="payment_method"]').change(function() {
                if ($('#banking').is(':checked')) {
                    $('#banking_qr').slideDown();
                    $('#cod_btn').hide();
                } else {
                    $('#banking_qr').slideUp();
                    $('#cod_btn').show();
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Load tỉnh/thành
            $.get("https://provinces.open-api.vn/api/?depth=1", function(data) {
                $("#province").append('<option value="">-- Chọn tỉnh/thành --</option>');
                data.forEach(function(province) {
                    $("#province").append(
                        `<option value="${province.code}">${province.name}</option>`);
                });
            });

            // Khi chọn tỉnh, load huyện
            $("#province").on("change", function() {
                const provinceCode = $(this).val();
                $("#district").empty().append('<option value="">-- Chọn huyện --</option>');
                $("#ward").empty().append('<option value="">-- Chọn xã --</option>');

                if (provinceCode) {
                    $.get(`https://provinces.open-api.vn/api/p/${provinceCode}?depth=2`, function(data) {
                        data.districts.forEach(function(district) {
                            $("#district").append(
                                `<option value="${district.code}">${district.name}</option>`
                            );
                        });
                    });
                }
            });

            // Khi chọn huyện, load xã
            $("#district").on("change", function() {
                const districtCode = $(this).val();
                $("#ward").empty().append('<option value="">-- Chọn xã --</option>');

                if (districtCode) {
                    $.get(`https://provinces.open-api.vn/api/d/${districtCode}?depth=2`, function(data) {
                        data.wards.forEach(function(ward) {
                            $("#ward").append(
                                `<option value="${ward.name}">${ward.name}</option>`);
                        });
                    });
                }
            });
        });
    </script>

</body>


</html>




<style>
    .xemlai {
        font-size: 18px;
        font-weight: 500;
        color: blue;
    }


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