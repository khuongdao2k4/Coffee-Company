<?php require "layout/header.php"; ?>
<?php
require_once('database/config.php');
require_once('database/dbhelper.php');
?>
<!-- END HEADR -->
<main>
    <div class="container">
        <!-- Bootstrap Responsive Search Section -->
        <div class="row justify-content-center my-4">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="search-quan">
                    <form action="thucdon.php" method="GET" class="position-relative">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input name="search" type="text" class="form-control border-start-0 ps-0" 
                                   placeholder="Tìm món hoặc thức ăn...">
                            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bootstrap Responsive Carousel -->
        <div class="row my-5">
            <div class="col-12">
                <section class="program-carousel">
                    <h2 class="text-center mb-4">Chương trình của quán</h2>
                    <div id="programCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                        <div class="carousel-inner">
                            <!-- Slide 1 -->
                            <div class="carousel-item active">
                                <a href="link-to-detail-page-1">
                                    <img src="images/icon/ct1.png" class="d-block w-100" alt="Chương trình 1">
                                </a>
                            </div>
                            <!-- Slide 2 -->
                            <div class="carousel-item">
                                <a href="link-to-detail-page-2">
                                    <img src="images/icon/ct2.png" class="d-block w-100" alt="Chương trình 2">
                                </a>
                            </div>
                            <!-- Slide 3 -->
                            <div class="carousel-item">
                                <a href="link-to-detail-page-3">
                                    <img src="images/icon/ct3.png" class="d-block w-100" alt="Chương trình 3">
                                </a>
                            </div>
                        </div>
                        <!-- Điều hướng carousel -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#programCarousel"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#programCarousel"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </section>
            </div>
        </div>
        <!-- Bootstrap Responsive Grid for Products -->
        <section class="main py-5">
            <section class="restaurants">
                <div class="container">
                    <div class="row mb-4">
                        <div class="col-12">
                            <h1 class="text-center mb-4">Thực đơn tại quán</h1>
                        </div>
                    </div>
                    
                    <!-- Bootstrap Grid cho sản phẩm -->
                    <div class="row g-4">
                        <?php
                        try {
                            if (isset($_GET['page'])) {
                                $page = $_GET['page'];
                            } else {
                                $page = 1;
                            }
                            $limit = 12;
                            $start = ($page - 1) * $limit;
                            //giá thấp nhất
                            $sql = "
                            SELECT 
                                product.id, 
                                product.title, 
                                product.thumbnail, 
                                MIN(product_size.price) AS price 
                            FROM product 
                            LEFT JOIN product_size ON product.id = product_size.product_id 
                            GROUP BY product.id 
                            LIMIT $start, $limit
                        ";
                        $productList = executeResult($sql);
                        
                           
                            $index = 1;
                            foreach ($productList as $item) {
                                echo '
                                <!-- Responsive Grid: 1 col trên mobile, 2 trên tablet, 3 trên desktop, 4 trên large screen -->
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                    <div class="card h-100 shadow-sm product-card">
                                        <a href="details.php?id=' . $item['id'] . '" class="text-decoration-none">
                                            <div class="card-img-top-wrapper">
                                                <img class="card-img-top product-thumbnail" 
                                                     src="admin/product/'. $item['thumbnail'] . '" 
                                                     alt="' . $item['title'] . '">
                                            </div>
                                            <div class="card-body d-flex flex-column">
                                                <h5 class="card-title product-title text-dark">' . $item['title'] . '</h5>
                                                <div class="mt-auto">
                                                    <span class="badge bg-primary fs-6 price-badge">' . number_format($item['price'], 0, ',', '.') . ' VNĐ</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                ';
                            }
                        } catch (Exception $e) {
                            die("Lỗi thực thi sql: " . $e->getMessage());
                        }
                        ?>
                    </div>
                      <!-- Bootstrap Responsive Pagination -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <nav aria-label="Product pagination">
                                <ul class="pagination justify-content-center flex-wrap">
                                    <?php
                                    $sql = "SELECT * FROM `product`";
                                    $conn = mysqli_connect(HOST, USERNAME, PASSWORD, DATABASE);
                                    $result = mysqli_query($conn, $sql);
                                    if (mysqli_num_rows($result)) {
                                        $numrow = mysqli_num_rows($result);
                                        $total_pages = ceil($numrow / 12);
                                    }
                                    
                                    // Hiển thị tối đa 5 trang (mobile-friendly)
                                    $range = 2; // Số trang hiển thị mỗi bên current page
                                    $start_page = max(1, $page - $range);
                                    $end_page = min($total_pages, $page + $range);
                                    
                                    // Nút Previous
                                    if ($page > 1) {
                                        echo '<li class="page-item">
                                                <a class="page-link" href="?page=' . ($page - 1) . '" aria-label="Previous">
                                                    <span aria-hidden="true">&laquo;</span>
                                                </a>
                                              </li>';
                                    }
                                    
                                    // Trang đầu + dấu ...
                                    if ($start_page > 1) {
                                        echo '<li class="page-item">
                                                <a class="page-link" href="?page=1">1</a>
                                              </li>';
                                        if ($start_page > 2) {
                                            echo '<li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                  </li>';
                                        }
                                    }
                                    
                                    // Các trang trong range
                                    for ($i = $start_page; $i <= $end_page; $i++) {
                                        $active = ($i == $page) ? 'active' : '';
                                        echo '<li class="page-item ' . $active . '">
                                                <a class="page-link" href="?page=' . $i . '">' . $i . '</a>
                                              </li>';
                                    }
                                    
                                    // Dấu ... + trang cuối
                                    if ($end_page < $total_pages) {
                                        if ($end_page < $total_pages - 1) {
                                            echo '<li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                  </li>';
                                        }
                                        echo '<li class="page-item">
                                                <a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a>
                                              </li>';
                                    }
                                    
                                    // Nút Next
                                    if ($page < $total_pages) {
                                        echo '<li class="page-item">
                                                <a class="page-link" href="?page=' . ($page + 1) . '" aria-label="Next">
                                                    <span aria-hidden="true">&raquo;</span>
                                                </a>
                                              </li>';
                                    }
                                    ?>
                                </ul>
                                
                                <!-- Mobile: Hiển thị thông tin trang hiện tại -->
                                <div class="d-block d-sm-none text-center mt-3">
                                    <small class="text-muted">
                                        Trang <?php echo $page; ?> / <?php echo $total_pages; ?>
                                    </small>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </section>
        </section>
    </div>
</main>
<?php require_once('layout/footer.php'); ?>
</div>
</body>

</html>