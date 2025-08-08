<?php
require_once(dirname(__FILE__) . '/../utils/config.php');
?>

<!-- Admin Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= url('admin/index.php') ?>">Coffee Shop Admin</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? 'active' : '' ?>" 
                       href="<?= url('admin/index.php') ?>">
                        <i class="fas fa-chart-bar"></i> Thống kê
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/category/') !== false) ? 'active' : '' ?>" 
                       href="<?= url('admin/category/') ?>">
                        <i class="fas fa-tags"></i> Quản lý danh mục
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/product/') !== false) ? 'active' : '' ?>" 
                       href="<?= url('admin/product/') ?>">
                        <i class="fas fa-coffee"></i> Quản lý sản phẩm
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : '' ?>" 
                       href="<?= url('admin/dashboard.php') ?>">
                        <i class="fas fa-shopping-cart"></i> Quản lý đơn hàng
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/admin/user/') !== false) ? 'active' : '' ?>" 
                       href="<?= url('admin/user/') ?>">
                        <i class="fas fa-users"></i> Quản lý người dùng
                    </a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> Admin
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?= url('index.php') ?>">
                            <i class="fas fa-home"></i> Về trang chủ
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= url('admin/logout.php') ?>">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
