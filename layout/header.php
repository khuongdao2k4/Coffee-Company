<!DOCTYPE html>
<html lang="en">
<?php
require_once('utils/config.php');
require_once('database/config.php');
require_once('database/dbhelper.php');
session_start();
// if (isset($_COOKIE['username']) && isset($_COOKIE['password'])) {
//     echo "<script>alert('Welcome back!');</script>";

// } else {
//     echo"<script>alert('Please log in to continue.');</script>";
// }

?>



<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/details.css">
    <link rel="stylesheet" href="plugin/fontawesome/css/all.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <title>Coffee shop</title>

    <!-- Custom CSS cho responsive product cards -->
    <style>
        /* Product Card Styling */
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .card-img-top-wrapper {
            position: relative;
            overflow: hidden;
            height: 200px;
        }

        .product-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-thumbnail {
            transform: scale(1.05);
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            line-height: 1.4;
            margin-bottom: 0.5rem;
            color: #333;
            /* Giới hạn text trong 2 dòng */
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .price-badge {
            font-size: 1rem !important;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .card-img-top-wrapper {
                height: 150px;
            }

            .product-title {
                font-size: 1rem;
            }

            .price-badge {
                font-size: 0.9rem !important;
                padding: 0.4rem 0.8rem;
            }
        }

        @media (min-width: 992px) {
            .card-img-top-wrapper {
                height: 220px;
            }
        }

        /* Search section responsive */
        .search-quan {
            margin: 2rem 0;
        }

        @media (max-width: 768px) {
            .search-quan {
                margin: 1rem 0;
            }

            .search-quan input {
                font-size: 14px;
            }
        }

        /* Carousel responsive */
        .program-carousel {
            margin: 2rem 0;
        }

        .program-carousel img {
            border-radius: 12px;
            max-height: 300px;
            object-fit: cover;
        }

        @media (max-width: 768px) {
            .program-carousel img {
                max-height: 200px;
            }
        }

        /* Responsive Pagination */
        .pagination {
            gap: 0.25rem;
        }

        .pagination .page-link {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            color: #495057;
            transition: all 0.2s ease;
        }

        .pagination .page-link:hover {
            background-color: #e9ecef;
            border-color: #adb5bd;
            color: #495057;
        }

        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
        }

        /* Mobile pagination improvements */
        @media (max-width: 576px) {
            .pagination {
                gap: 0.15rem;
            }

            .pagination .page-link {
                padding: 0.375rem 0.5rem;
                font-size: 0.875rem;
                min-width: 36px;
                text-align: center;
            }

            .pagination .page-item:not(.disabled) .page-link {
                margin: 0 2px;
            }

            /* Ẩn một số trang trên màn hình rất nhỏ */
            .pagination .page-item:nth-child(n+6):nth-last-child(n+4) {
                display: none;
            }
        }

        @media (max-width: 420px) {
            .pagination .page-link {
                padding: 0.25rem 0.4rem;
                font-size: 0.8rem;
                min-width: 32px;
            }
        }
    </style>
</head>

<body>
    <div id="wrapper">
        <!-- ===== HEADER START ===== -->
        <header class="shadow-sm border-bottom py-2" style="background-color: #f5f5f5 !important">
            <nav class="navbar navbar-expand-lg " style="background-color: #f5f5f5 !important">
                <div class="container d-flex align-items-center justify-content-between">

                    <!-- LOGO -->
                    <a class="navbar-brand" href="index.php">
                        <img src="images/logo.svg" alt="Logo" style="height:150px;">
                    </a>

                    <!-- TOGGLER (Hiện trên mobile) -->
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <!-- MENU -->
                    <div class="collapse navbar-collapse justify-content-center" id="navbarMain">
                        <ul class="navbar-nav gap-4 text-center">
                            <li class="nav-item"><a class="nav-link fw-semibold" href="index.php">Trang chủ</a></li>

                            <li class="nav-item dropdown">
                                <a class="nav-link fw-semibold dropdown-toggle" href="#" role="button"
                                    data-bs-toggle="dropdown">Thực đơn</a>
                                <ul class="dropdown-menu">
                                    <?php
                                    $cats = executeResult("SELECT * FROM category");
                                    foreach ($cats as $c) {
                                        echo '<li><a class="dropdown-item" href="thucdon.php?id_category='
                                            . $c['id'] . '">' . $c['name'] . '</a></li>';
                                    }
                                    ?>
                                </ul>
                            </li>

                            <li class="nav-item"><a class="nav-link fw-semibold" href="about.php">Về chúng tôi</a></li>
                            <li class="nav-item"><a class="nav-link fw-semibold" href="sendMail.php">Liên hệ</a></li>
                        </ul>
                    </div>

                    <!-- ICONS: Cart + User -->
                    <div class="d-flex align-items-center gap-3">
                        <!-- Cart -->
                        <a href="cart.php" class="btn btn-outline-secondary position-relative rounded-circle p-2">
                            <i class="fas fa-shopping-bag"></i>
                            <?php
                            /*
                            $count = 0;
                            if (isset($_COOKIE['cart'])) {
                                foreach (json_decode($_COOKIE['cart'], true) as $it) $count += $it['num'];
                            }
                            if ($count) {
                                echo '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">'
                                      .$count.'</span>';
                            }
                            */
                            ?>
                        </a>

                        <!-- User -->
                        <?php if (isset($_COOKIE['username'])): ?>
                            <?php $u = htmlspecialchars($_COOKIE['username']); ?>
                            <div class="dropdown">
                                <a class="btn btn-outline-secondary rounded-pill px-3 py-1 dropdown-toggle"
                                    data-bs-toggle="dropdown">
                                    <?= $u ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <?php if ($u === 'Admin'): ?>
                                        <li><a class="dropdown-item" href="admin/"><i
                                                    class="fas fa-user-edit me-1"></i>Admin</a></li>
                                    <?php else: ?>
                                        <li><a class="dropdown-item" href="login/changePass.php"><i
                                                    class="fas fa-exchange-alt me-1"></i>Đổi mật khẩu</a></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item" href="login/logout.php"><i
                                                class="fas fa-sign-out-alt me-1"></i>Đăng xuất</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a href="login/login.php" class="btn btn-outline-secondary rounded-pill px-3 py-1">Đăng nhập</a>
                        <?php endif; ?>
                    </div>

                </div>
            </nav>
        </header>
        <!-- ===== HEADER END ===== -->


        <!-- optional: tweak icon size on mobile -->
        <style>
            .navbar-nav .nav-link {
                font-size: 1.5rem;
                padding: 0.5rem 1rem;
                color: #000;
            }

            .navbar-nav .nav-link:hover,
            .navbar-nav .nav-link:focus {
                color: #a05b2a;
                /* Màu nâu cam chủ đạo */
            }

            @media (max-width: 767.98px) {
                .navbar-nav {
                    gap: 1rem !important;
                }

                .navbar-brand img {
                    height: 50px !important;
                }
            }
        </style>
    </div>
</body>