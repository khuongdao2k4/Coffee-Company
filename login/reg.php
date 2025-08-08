<?php
require_once('../database/config.php');
require_once('../database/dbhelper.php');
?>
<!DOCTYPE html>
<html lang="en">

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
    <title>Đăng ký tài khoản</title>
    <style>
    /* Form container responsive giống login.php */
    .register-form-container {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 576px) {
        .register-form-container {
            padding: 20px 15px;
        }

        .register-form-container h1 {
            font-size: 1.5rem;
        }
    }
    </style>
</head>

<body>
    <!-- Bootstrap Navbar giống login.php -->
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

    <!-- Main Content responsive -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6">
                <div class="register-form-container">
                    <form action="reg.php" method="POST">
                        <h1 class="text-center mb-4">Đăng ký hệ thống</h1>

                        <div class="form-group">
                            <label for="name">Họ và tên:</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="Họ và tên"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="username">Tài khoản:</label>
                            <input type="text" name="username" id="username" class="form-control"
                                placeholder="Tài khoản" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Mật khẩu:</label>
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="Mật khẩu" required>
                            <small class="form-text text-muted">
                                Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ cái, số và ký tự đặc biệt
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="repassword">Nhập lại mật khẩu:</label>
                            <input type="password" name="repassword" id="repassword" class="form-control"
                                placeholder="Nhập lại mật khẩu" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Số điện thoại:</label>
                            <input type="text" name="phone" id="phone" class="form-control" placeholder="Số điện thoại"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Email"
                                required>
                        </div>

                        <div class="form-group">
                            <button type="submit" name="submit" class="btn btn-primary btn-block">Đăng ký</button>
                        </div>

                        <div class="text-center mt-3">
                            <p class="mb-0">Bạn đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
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

    <?php
  require_once('../database/config.php');
  require_once('../database/dbhelper.php');
  if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['submit']) && $_POST['name'] != "" && $_POST['username'] != "" && $_POST['password'] != "" && $_POST['phone'] != "" && $_POST['email'] != "") {
      $name = $_POST['name'];
      $username = $_POST['username'];
      $pass = $_POST['password'];
      $repass = $_POST['repassword'];
      $phone = $_POST['phone'];
      $email = $_POST['email'];

      // Kiểm tra mật khẩu có đủ yêu cầu không
      $password_pattern = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*.])[A-Za-z\d!@#$%^&*.]{8,20}$/";
      if (!preg_match($password_pattern, $pass)) {
          echo '<script language="javascript">
                  alert("Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ cái, số và ký tự đặc biệt!");
                  window.location = "reg.php";
                </script>';
          die();
      }

      // Kiểm tra trùng mật khẩu không
      if ($pass != $repass) {
          echo '<script language="javascript">
                  alert("Nhập lại mật khẩu không trùng, vui lòng đăng ký lại!");
                  window.location = "reg.php";
                </script>';
          die();
      }

      // Kiểm tra username và email đã tồn tại chưa
      $sql = "SELECT * FROM user WHERE username = '$username' OR email = '$email'";
      $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);
      $result = mysqli_query($conn, $sql);
      if (mysqli_num_rows($result) > 0) {
          echo '<script language="javascript">
                  alert("Tài khoản hoặc Email đã được sử dụng!");
                  window.location = "reg.php";
                </script>';
          die();
      }

      // Thêm user vào cơ sở dữ liệu
      $sql = 'INSERT INTO user(hoten, username, password, phone, email) VALUES ("' . $name . '", "' . $username . '", "' . $pass . '", "' . $phone . '", "' . $email . '")';
      execute($sql);

      echo '<script language="javascript">
              alert("Bạn đăng ký thành công!");
              window.location = "login.php";
            </script>';
  } else {
      echo '<script language="javascript">
              alert("Hãy nhập đủ thông tin!");
              window.location = "reg.php";
            </script>';
  }
  }
  ?>

</body>

</html>