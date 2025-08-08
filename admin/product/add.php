<?php
// Debug errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 30);

require_once('../../utils/config.php');
require_once('../../database/config.php');
require_once('../../database/dbhelper.php');

$id = $title = $number = $thumbnail = $content = $id_category = "";
$sizes = [];
$prices = [];

if (!empty($_POST['title'])) {
    // Lấy dữ liệu từ form
    if (isset($_POST['title'])) {
        $title = $_POST['title'];
        $title = str_replace('"', '\\"', $title);
    }
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $id = str_replace('"', '\\"', $id);
    }

    // Xử lý mảng sizes và prices
    if (isset($_POST['sizes']) && isset($_POST['prices'])) {
        $sizes = $_POST['sizes'];
        $prices = $_POST['prices'];

        // Validate sizes và prices
        if (count($sizes) !== count($prices)) {
            die('Dữ liệu size và giá không khớp');
        }

        // Kiểm tra giá hợp lệ
        foreach ($prices as $price) {
            if (!empty($price) && !is_numeric($price)) {
                die('Giá sản phẩm không hợp lệ');
            }
        }
    }
    if (isset($_POST['number'])) {
        $number = $_POST['number'];
        $number = str_replace('"', '\\"', $number);
    }

    // Nếu number rỗng, gán giá trị mặc định
    if (empty($number)) {
        $number = 0;
    }

    // Kiểm tra upload file thumbnail
    if (!isset($_FILES["thumbnail"])) {
        echo "Dữ liệu không đúng cấu trúc";
        die;
    }

    if (isset($_FILES["thumbnail"]) && $_FILES["thumbnail"]['name'] !== '' && $_FILES["thumbnail"]['error'] != 0) {
        echo "Dữ liệu upload bị lỗi";
        die;
    }

    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["thumbnail"]["name"]);
    $allowUpload = true;
    $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
    $maxfilesize = 800000;
    $allowtypes = array('jpg', 'png', 'jpeg', 'gif');

    if ($_FILES["thumbnail"]["size"] > $maxfilesize) {
        echo "Không được upload ảnh lớn hơn $maxfilesize (bytes).";
        $allowUpload = false;
    }

    if (!in_array($imageFileType, $allowtypes)) {
        echo "Chỉ được upload các định dạng JPG, PNG, JPEG, GIF";
        $allowUpload = false;
    }

    if ($allowUpload) {
        if (move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $target_file)) {
            // ảnh đã upload thành công
        } else {
            echo "Có lỗi xảy ra khi upload file.";
        }
    } else {
        echo "Không upload được file, có thể do file lớn, kiểu file không đúng ...";
    }

    if (isset($_POST['content'])) {
        $content = $_POST['content'];
        // Limit content size to prevent memory issues
        if (strlen($content) > 1000000) { // 1MB limit
            die('Content too large. Please reduce the content size.');
        }
        $content = str_replace('"', '\\"', $content);
    }
    if (isset($_POST['id_category'])) {
        $id_category = $_POST['id_category'];
        $id_category = str_replace('"', '\\"', $id_category);
    }

    if (!empty($title)) {
        $created_at = $updated_at = date('Y-m-d H:i:s');

        // Lưu vào DB
        if (empty($id)) {
            // Thêm sản phẩm mới
            $sql = "INSERT INTO product (title, number, thumbnail, content, id_category, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $product_id = executeInsert($sql, [$title, $number, $target_file, $content, $id_category, $created_at, $updated_at]);

            // Thêm các size và giá
            for ($i = 0; $i < count($sizes); $i++) {
                if (!empty($sizes[$i]) && !empty($prices[$i])) {
                    $sql_size = "INSERT INTO product_size (product_id, size, price) VALUES (?, ?, ?)";
                    execute($sql_size, [$product_id, $sizes[$i], $prices[$i]]);
                }
            }

            header('Location: index.php');
            exit();
        } else {
            // Nếu không upload ảnh mới thì giữ nguyên ảnh cũ
            if (empty($_FILES["thumbnail"]["name"])) {
                $target_file = $_POST['old_thumbnail'];
            }


            // Sửa thông tin sản phẩm
            $sql = "UPDATE product SET title=?, number=?, thumbnail=?, content=?, id_category=?, updated_at=? WHERE id=?";
            execute($sql, [$title, $number, $target_file, $content, $id_category, $updated_at, $id]);

            // Xóa tất cả size cũ
            $sql_delete = "DELETE FROM product_size WHERE product_id=?";
            execute($sql_delete, [$id]);

            // Thêm lại các size mới
            for ($i = 0; $i < count($sizes); $i++) {
                if (!empty($sizes[$i]) && !empty($prices[$i])) {
                    $sql_size = "INSERT INTO product_size (product_id, size, price) VALUES (?, ?, ?)";
                    execute($sql_size, [$id, $sizes[$i], $prices[$i]]);
                }
            }

            header('Location: index.php');
            exit();
        }
    }
}


if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Validate ID to prevent SQL injection or invalid data
    if (!is_numeric($id) || $id <= 0) {
        die('Invalid product ID');
    }

    // Use more specific SELECT to avoid large BLOB fields if not needed
    $sql = 'SELECT id, title, number, thumbnail, content, id_category, created_at, updated_at FROM product WHERE id = ? LIMIT 1';
    $product = executeSingleResult($sql, [$id]);

    if ($product != null) {
        $title = $product['title'];
        $number = $product['number'];
        $thumbnail = $product['thumbnail'];

        // Check content size before assigning
        if (isset($product['content']) && strlen($product['content']) > 500000) {
            $content = substr($product['content'], 0, 500000) . '... [Content truncated due to size]';
        } else {
            $content = $product['content'];
        }

        $id_category = $product['id_category'];
        $created_at = $product['created_at'];
        $updated_at = $product['updated_at'];
    } else {
        die('Product not found');
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Thêm Sản Phẩm</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
</head>

<body>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link" href="../index.php">Thống kê</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../category/">Quản lý danh mục</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="index.php">Quản lý sản phẩm</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../dashboard.php">Quản lý đơn hàng</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../user/">Quản lý người dùng</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../logout.php">Đăng xuất</a>
        </li>
    </ul>
    <div class="container">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h2 class="text-center">Thêm/Sửa Sản Phẩm</h2>
            </div>
            <div class="panel-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Tên Sản Phẩm:</label>
                        <input type="hidden" id="id" name="id" value="<?= htmlspecialchars($id) ?>">

                        <input required="true" type="text" class="form-control" id="title" name="title"
                            value="<?= $title ?>">
                    </div>
                    <div class="form-group">
                        <label for="exampleFormControlSelect1">Chọn Danh Mục</label>
                        <select class="form-control" id="id_category" name="id_category">
                            <option>Chọn danh mục</option>
                            <?php
                            $sql = 'SELECT * FROM category LIMIT 100';
                            $categoryList = executeResult($sql);
                            foreach ($categoryList as $item) {
                                echo '<option value="' . $item['id'] . '" ' . ($item['id'] == $id_category ? 'selected' : '') . '>' . $item['name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sizes">Size và Giá Sản Phẩm:</label>
                        <div class="card">
                            <div class="card-body">
                                <div id="size-price-container">
                                    <?php
                                    // Lấy tất cả size và price hiện tại của sản phẩm
                                    $existing_sizes = [];
                                    if (!empty($id)) {
                                        $sql_all_sizes = 'SELECT * FROM product_size WHERE product_id = ? LIMIT 100';
                                        $existing_sizes = executeResult($sql_all_sizes, [$id]);
                                    }

                                    if (empty($existing_sizes)) {
                                        // Nếu chưa có size nào, hiển thị 1 row mặc định
                                        echo '
        <div class="row mb-2 size-price-row">
            <div class="col-md-2">
                <select class="form-control size-select" name="sizes[]">
                    <option value="">-- Chọn Size --</option>
                    <option value="S">S</option>
                    <option value="M">M</option>
                    <option value="L">L</option>
                    <option value="No Size">No Size</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control" name="prices[]" placeholder="Nhập giá..." min="0" step="1000">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm remove-size">Xóa</button>
            </div>
        </div>';
                                    } else {
                                        // Hiển thị các size hiện tại
                                        foreach ($existing_sizes as $size_item) {
                                            echo '
            <div class="row mb-2 size-price-row">
                <div class="col-md-2">
                    <select class="form-control size-select" name="sizes[]">
                        <option value="">-- Chọn Size --</option>
                        <option value="S"' . ($size_item['size'] == 'S' ? ' selected' : '') . '>S</option>
                        <option value="M"' . ($size_item['size'] == 'M' ? ' selected' : '') . '>M</option>
                        <option value="L"' . ($size_item['size'] == 'L' ? ' selected' : '') . '>L</option>
                        <option value="No Size"' . ($size_item['size'] == 'No Size' ? ' selected' : '') . '>No Size</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control" name="ice_levels[]">
                        <option value="0%">0% Đá</option>
                        <option value="25%">25% Đá</option>
                        <option value="50%">50% Đá</option>
                        <option value="100%">100% Đá</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-control" name="sugar_levels[]">
                        <option value="0%">0% Đường</option>
                        <option value="25%">25% Đường</option>
                        <option value="50%">50% Đường</option>
                        <option value="100%">100% Đường</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="number" class="form-control" name="prices[]" value="' . $size_item['price'] . '" placeholder="Nhập giá..." min="0" step="1000">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm remove-size">Xóa</button>
                </div>
            </div>';
                                        }
                                    }
                                    ?>
                                </div>

                                <button type="button" id="add-size" class="btn btn-success btn-sm mt-2">+ Thêm
                                    Size</button>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="form-group">
                        <label for="name">Số Lượng:</label>
                        <input required="true" type="text" class="form-control" id="number" name="number" value="<?= $number ?>">
                    </div> -->
                    <div class="form-group">
                        <label for="name">Hình Ảnh:</label>
                        <input type="file" class="form-control" id="thumbnail" name="thumbnail">
                        <input type="hidden" name="old_thumbnail" value="<?= htmlspecialchars($thumbnail) ?>">
                    </div>
                    <div class="form-group">
                        <label for="content">Nội Dung Sản Phẩm:</label>
                        <textarea class="form-control" id="content" name="content" rows="5"><?= $content ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {

            // Hàm tạo HTML cho dòng size mới
            function createSizeRow() {
                return `
        <div class="row mb-2 size-price-row">
            <div class="col-md-2">
                <select class="form-control" name="sizes[]">
                    <option value="">-- Chọn Size --</option>
                    <option value="S">Size S</option>
                    <option value="M">Size M</option>
                    <option value="L">Size L</option>
                </select>
            </div>

            <div class="col-md-2">
                <select class="form-control" name="ices[]">
                    <option value="0%">0% Đá</option>
                    <option value="25%">25% Đá</option>
                    <option value="50%">50% Đá</option>
                    <option value="100%">100% Đá</option>
                </select>
            </div>

            <div class="col-md-2">
                <select class="form-control" name="sugars[]">
                    <option value="0%">0% Đường</option>
                    <option value="25%">25% Đường</option>
                    <option value="50%">50% Đường</option>
                    <option value="100%">100% Đường</option>
                </select>
            </div>

            <div class="col-md-4">
                <input type="number" class="form-control" name="prices[]" placeholder="Nhập giá..." min="0" step="1000">
            </div>

            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-sm remove-size">Xóa</button>
            </div>
        </div>
    `;
            }



            // Thêm size mới
            $('#add-size').click(function () {
                var newRow = createSizeRow();
                $('#size-price-container').append(newRow);

                // Tự động focus vào ô nhập giá mới nhất
                $('#size-price-container .size-price-row:last-child input[name="prices[]"]').focus();
            });

            // Xóa size
            $(document).on('click', '.remove-size', function () {
                if ($('.size-price-row').length > 1) {
                    $(this).closest('.size-price-row').remove();
                } else {
                    alert('Phải có ít nhất 1 size cho sản phẩm!');
                }
            });

            // Kiểm tra trùng size
            $(document).on('change', '.size-select', function () {
                var selectedSize = $(this).val();
                var currentRow = $(this).closest('.size-price-row');

                var duplicate = false;
                $('.size-select').each(function () {
                    if ($(this).val() === selectedSize &&
                        !$(this).closest('.size-price-row').is(currentRow) &&
                        selectedSize !== "") {
                        duplicate = true;
                        return false; // thoát vòng each
                    }
                });

                if (duplicate) {
                    alert('Size "' + selectedSize + '" đã được chọn rồi!');
                    $(this).val('');
                }
            });

            // Kiểm tra hợp lệ khi submit
            $('form').submit(function (e) {
                var hasValidSize = false;

                $('.size-price-row').each(function () {
                    var size = $(this).find('.size-select').val();
                    var price = $(this).find('input[name="prices[]"]').val();

                    if (size && price > 0) {
                        hasValidSize = true;
                    }
                });

                if (!hasValidSize) {
                    e.preventDefault();
                    alert('Vui lòng nhập ít nhất 1 size và giá hợp lệ!');
                }
            });
        });
    </script>

</body>

</html>