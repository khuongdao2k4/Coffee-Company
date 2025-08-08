<?php require "layout/header.php"; ?>
<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('database/config.php');
require_once('database/dbhelper.php');
require_once('utils/utility.php');
// Lấy id từ trang index.php truyền sang rồi hiển thị nó
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Validate ID to prevent SQL injection
    if (!is_numeric($id) || $id <= 0) {
        header('Location: index.php');
        die();
    }

    // Use prepared statement for security
    $sql = 'SELECT * FROM product WHERE id = ? LIMIT 1';
    $product = executeSingleResult($sql, [$id]);

    // Kiểm tra nếu ko có id sp đó thì trả về index.php
    if ($product == null) {
        header('Location: index.php');
        die();
    }

//nếu là tra sưa sẽ hiển thị đá và đường
$is_trasua = false;

if ($product && isset($product['id_category']) && $product['id_category'] == 1) {
    $is_trasua = true;
}
    // Use prepared statement for sizes query
    $sqlSizes = 'SELECT size, price FROM product_size WHERE product_id = ?';
    $sizes = executeResult($sqlSizes, [$id]);
}
//buy now
?>
<div id="fb-root"></div>
<script async defer crossorigin="anonymous"
    src="https://connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v11.0&appId=264339598396676&autoLogAppEvents=1"
    nonce="8sTfFiF4"></script>
<!-- END HEADR -->
<main class="py-4">
    <!-- Search Section -->
    <div class="container mb-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <form action="thucdon.php" method="GET" class="w-100">
                        <input name="search" type="text" class="form-control" placeholder="Tìm món hoặc thức ăn">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Section -->
    <div class="container">
        <div class="row">
            <!-- Main Product Info -->
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h1 class="card-title mb-4"><?= $product['title'] ?></h1>

                        <div class="row">
                            <div class="col-md-6">
                                <img src="<?= 'admin/product/' . $product['thumbnail'] ?>" alt="<?= $product['title'] ?>"
                                    class="img-fluid rounded mb-3">
                            </div>
                            <div class="col-md-6">
                                <p class="mb-3"><?= $product['content'] ?></p>

                                <!-- Size Selection -->
                                <div class="mb-3">
                                    <label class="form-label">Size:</label>
                                    <div class="btn-group d-flex flex-wrap" role="group">
                                        <?php if (count($sizes) > 0): ?>
                                            <?php foreach ($sizes as $index => $size): ?>
                                                <input type="radio" class="btn-check" name="size" id="size<?= $index ?>"
                                                    value="<?= $size['size'] ?>" data-price="<?= $size['price'] ?>"
                                                    <?= $index === 0 ? 'checked' : '' ?>>
                                                <label class="btn btn-outline-primary" for="size<?= $index ?>">
                                                    <?= $size['size'] ?>
                                                </label>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">No Size</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <!-- Chỉ hiển thị đá và đường nếu là Trà sữa -->
                                <?php if ($is_trasua): ?>
                                    <div class="row mt-4">
                                        <div class="col-md-6 mb-3">
                                            <label for="ice_level" class="form-label fw-semibold">
                                                <i class="fas fa-ice-cream me-1"></i> Mức đá
                                            </label>
                                            <select name="ice_level" id="ice_level" class="form-select border-primary shadow-sm rounded">
                                                <option value="0%">0% - Không đá</option>
                                                <option value="25%">25% - Ít đá</option>
                                                <option value="50%">50% - Vừa</option>
                                                <option value="100%">100% - Nhiều đá</option>
                                            </select>
                                        </div>
                                
                                        <div class="col-md-6 mb-3">
                                            <label for="sugar_level" class="form-label fw-semibold">
                                                <i class="fas fa-cube me-1"></i> Mức đường
                                            </label>
                                            <select name="sugar_level" id="sugar_level" class="form-select border-success shadow-sm rounded">
                                                <option value="0%">0% - Không đường</option>
                                                <option value="25%">25% - Ít ngọt</option>
                                                <option value="50%">50% - Vừa ngọt</option>
                                                <option value="100%">100% - Ngọt nhiều</option>
                                            </select>
                                        </div>
                                    </div>
                                
                                <?php endif; ?>
                                <!-- Quantity -->
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Số lượng:</label>
                                    <input type="number" class="form-control" id="quantity" value="1" min="1"
                                        style="max-width: 100px;">
                                </div>

                                <!-- Price -->
                                <div class="mb-4">
                                    <h4 class="text-primary">
                                        Giá: <span
                                            id="price-display"><?= count($sizes) > 0 ? number_format($sizes[0]['price'], 0, ',', '.') : '0' ?></span>
                                        VNĐ
                                    </h4>
                                    <span id="hidden-price"
                                        style="display: none;"><?= count($sizes) > 0 ? $sizes[0]['price'] : '0' ?></span>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-grid gap-2">
                                    <button class="btn btn-primary btn-lg" onclick="addToCart(<?= $id ?>)">
                                        <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                                    </button>
                                    <button class="btn btn-success btn-lg" onclick="buyNow(<?= $id ?>)">
                                        <i class="fas fa-shopping-bag"></i> Mua ngay
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Facebook Comments -->
                <div class="card">
                    <div class="card-body">
                        <div class="fb-comments" data-href="http://localhost/PROJECT/details.php" data-width="100%"
                            data-numposts="5"></div>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Suggestions -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Gợi ý cho bạn</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php
                            $sql = "
                            SELECT 
                                product.id, 
                                product.title, 
                                product.thumbnail, 
                                MIN(product_size.price) AS price
                            FROM product 
                            LEFT JOIN product_size ON product.id = product_size.product_id 
                            WHERE product.id != ?
                            GROUP BY product.id
                            LIMIT 6";

                            $productList = executeResult($sql, [$id]);
                            foreach ($productList as $item) {
                                echo '
                                <div class="col-6 col-lg-12 mb-3">
                                    <div class="card h-100">
                                        <a href="details.php?id=' . $item['id'] . '" class="text-decoration-none">
                                            <img src="admin/product/' . $item['thumbnail'] . '" 
                                                 class="card-img-top" alt="' . $item['title'] . '" 
                                                 style="height: 120px; object-fit: cover;">
                                            <div class="card-body p-2">
                                                <h6 class="card-title text-dark">' . $item['title'] . '</h6>
                                                <p class="card-text text-muted small">
                                                    Giá: ' . number_format($item['price'], 0, ',', '.') . ' VNĐ
                                                </p>
                                            </div>
                                        </a>
                                    </div>
                                </div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once('layout/footer.php'); ?>
</div>
<script>
    // JavaScript cho Bootstrap size selection và cart functionality
    let isAddingToCart = false;

    // Xử lý chọn size với Bootstrap radio buttons
    document.querySelectorAll('input[name="size"]').forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.checked) {
                const price = this.getAttribute('data-price');
                document.getElementById('price-display').innerText = parseInt(price).toLocaleString();
                document.getElementById('hidden-price').innerText = price;
                updatePrice();
            }
        });
    });

    // Cập nhật giá khi thay đổi số lượng
    document.getElementById('quantity').addEventListener('change', updatePrice);

    function updatePrice() {
        const price = parseFloat(document.getElementById('hidden-price').innerText || document.querySelector(
            'input[name="size"]:checked')?.getAttribute('data-price') || 0);
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        const totalPrice = price * quantity;
        document.getElementById('price-display').innerText = totalPrice.toLocaleString();
    }
function addToCart(id) {
    if (isAddingToCart) return;
    isAddingToCart = true;

    const quantity = document.getElementById('quantity').value;
    const selectedSize = document.querySelector('input[name="size"]:checked');
    const size = selectedSize ? selectedSize.value : 'No Size';
    const price = selectedSize ? selectedSize.getAttribute('data-price') : '0';

    // Lấy mức đường và đá nếu có
    const sugarLevelElement = document.querySelector('select[name="sugar_level"]');
    const iceLevelElement = document.querySelector('select[name="ice_level"]');
    const sugar_level = sugarLevelElement ? sugarLevelElement.value : '';
    const ice_level = iceLevelElement ? iceLevelElement.value : '';

    if (!selectedSize && document.querySelectorAll('input[name="size"]').length > 0) {
        alert("Vui lòng chọn size sản phẩm.");
        isAddingToCart = false;
        return;
    }

    $.post('api/cookie.php', {
        action: 'add',
        id: id,
        num: quantity,
        size: size,
        price: price,
        sugar_level: sugar_level,
        ice_level: ice_level
    }, function (data) {
        alert("Sản phẩm đã được thêm vào giỏ hàng!");
        window.location.href = 'cart.php';
    }).fail(function () {
        alert("Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng.");
    }).always(function () {
        isAddingToCart = false;
    });

    console.log("Sugar Level:", sugar_level);
    console.log("Ice Level:", ice_level);
}


   function buyNow(id) {
    const quantity = document.getElementById('quantity').value;
    const selectedSize = document.querySelector('input[name="size"]:checked');
    const size = selectedSize ? selectedSize.value : 'No Size';
    const price = selectedSize ? selectedSize.getAttribute('data-price') : '0';

    // Kiểm tra đã chọn size chưa (nếu có nhiều size)
    if (!selectedSize && document.querySelectorAll('input[name="size"]').length > 0) {
        alert("Vui lòng chọn size sản phẩm.");
        return;
    }

    // Lấy mức đường và đá giống hàm addToCart
    const sugarLevelElement = document.querySelector('select[name="sugar_level"]');
    const iceLevelElement = document.querySelector('select[name="ice_level"]');
    const sugar_level = sugarLevelElement ? sugarLevelElement.value : '';
    const ice_level = iceLevelElement ? iceLevelElement.value : '';

    const checkoutUrl = `checkout.php?id=${id}&num=${quantity}&size=${encodeURIComponent(size)}&price=${price}&sugar_level=${encodeURIComponent(sugar_level)}&ice_level=${encodeURIComponent(ice_level)}`;
    window.location.href = checkoutUrl;
}

</script>
</body>

</html>
<style>
    /* Bổ sung CSS cho Bootstrap components */
    .btn-check:checked+.btn-outline-primary {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }

    /* Card hover effect cho sản phẩm gợi ý */
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
    }

    .card-body {
        margin-top: 50px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }

        .btn-lg {
            padding: 0.75rem 1rem;
            font-size: 1rem;
        }
    }

    @media (max-width: 576px) {
        .card-body {
            padding: 0.75rem;
        }

        .card-title {
            font-size: 1.25rem;
        }

        .d-grid.gap-2>.btn {
            margin-bottom: 0.5rem;
        }
    }
</style>