<?php
require_once('../utils/config.php');
require_once('../database/config.php');
require_once('../database/dbhelper.php');
if (isset($_POST["submit"]) && !empty($_POST["username"]) && !empty($_POST["password"])) {
    $username = trim(strip_tags($_POST["username"]));
    $password = trim(strip_tags($_POST["password"]));
    // $password = md5($password); // Thay thế bằng cơ chế mã hóa mạnh hơn nếu cần.

    $con = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);
    $sql = "SELECT * FROM user WHERE username = '$username' AND password = '$password'";
    $user = mysqli_query($con, $sql);

   if ($username === 'Admin' && $password === '1010') {
    session_start();
    setcookie("username", $username, time() + 30 * 24 * 60 * 60, '/');
    setcookie("password", $password, time() + 30 * 24 * 60 * 60, '/');

    header("Location: ../admin/index.php");
    exit();

} else if (mysqli_num_rows($user) > 0) {
    session_start();
    setcookie("username", $username, time() + 30 * 24 * 60 * 60, '/');
    setcookie("password", $password, time() + 30 * 24 * 60 * 60, '/');    // Kiểm tra nếu có redirect URL từ sessionStorage (qua JavaScript)
    // Mặc định redirect về trang chủ
    $redirectUrl = url("../index.php");
    
    echo '<script>
        // Kiểm tra nếu có URL redirect được lưu trong sessionStorage
        var redirectUrl = sessionStorage.getItem("redirectAfterLogin");
        if (redirectUrl) {
            sessionStorage.removeItem("redirectAfterLogin");
            window.location.href = redirectUrl;
        } else {
            window.location.href = "' . $redirectUrl . '";
        }
    </script>';
    exit();

} else {
    echo '<script>
        alert("Tài khoản và mật khẩu không chính xác!"); 
        window.location = "login.php";
    </script>';
}
}
?>
<!DOCTYPE html>
<html lang="en">
<?php
require_once('../database/config.php');
require_once('../database/dbhelper.php');
?>




<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <!-- Popper JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="plugin/fontawesome/css/all.css">
    <link rel="stylesheet" href="header.css">
    <title>Đăng nhập</title>
    <style>
    /* Form container responsive với Bootstrap */
    .login-form-container {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 576px) {
        .login-form-container {
            padding: 20px 15px;
        }

        .login-form-container h1 {
            font-size: 1.5rem;
        }
    }
    </style>
</head>

<body>
    <!-- Bootstrap Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <img src="../images/logo.svg" alt="Logo" height="100">
            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Trang chủ</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Thực đơn
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <?php
                            $sql = "SELECT * FROM category";
                            $result = executeResult($sql);
                            foreach ($result as $item) {
                                echo '<a class="dropdown-item" href="../thucdon.php?id_category=' . $item['id'] . '">' . $item['name'] . '</a>';
                            }
                            ?>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../about.php">Về chúng tôi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../sendMail.php">Liên hệ</a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../cart.php">
                            <img src="../images/icon/cart.svg" alt="Cart" height="20">
                            <?php
                            $cart = [];
                            if (isset($_COOKIE['cart'])) {
                                $json = $_COOKIE['cart'];
                                $cart = json_decode($json, true);
                            }
                            $count = 0;
                            foreach ($cart as $item) {
                                $count += $item['num'];
                            }
                            if ($count > 0) {
                                echo '<span class="badge badge-primary ml-1">' . $count . '</span>';
                            }
                            ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <?php
                        if (isset($_COOKIE['username'])) {
                            echo '<a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'
                                . $_COOKIE['username'] . 
                                '</a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                                    <a class="dropdown-item" href="changePass.php"><i class="fas fa-exchange-alt"></i> Đổi mật khẩu</a>
                                    <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                                </div>';
                        } else {
                            echo '<a class="nav-link" href="login.php">Đăng nhập</a>';
                        }
                        ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4">
                <div class="login-form-container">
                    <form action="login.php" method="POST">
                        <h1 class="text-center mb-4">Đăng nhập hệ thống</h1>

                        <div class="form-group">
                            <label for="username">Tài khoản:</label>
                            <input type="text" name="username" id="username" class="form-control"
                                placeholder="Tài khoản" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Mật khẩu:</label>
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="Mật khẩu" required>
                        </div>

                        <div class="form-group text-right">
                            <a href="forget.php" class="small">Quên mật khẩu?</a>
                        </div>

                        <div class="form-group">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">Đăng nhập</button>
                        </div>

                        <div class="text-center mt-3">
                            <p class="mb-0">Bạn chưa có tài khoản? <a href="reg.php">Đăng ký</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
    // Bootstrap navbar sẽ tự động xử lý responsive
    $(function() {
        // Đảm bảo dropdown hoạt động
        $('.dropdown-toggle').dropdown();
    });
    </script>
</body>

</html>