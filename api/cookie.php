<?php
require_once('../utils/utility.php');

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? '';
$size = $_POST['size'] ?? '';
$num = intval($_POST['num'] ?? 1);
$price = floatval($_POST['price'] ?? 0);
$sugar_level = $_POST['sugar_level'] ?? '';
$ice_level = $_POST['ice_level'] ?? '';

$cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];

switch ($action) {
    case 'add':
        $isFind = false;
        foreach ($cart as &$item) {
            if (
                $item['id'] == $id &&
                $item['size'] == $size &&
                $item['sugar_level'] == $sugar_level &&
                $item['ice_level'] == $ice_level
            ) {
                $item['num'] += $num;
                $isFind = true;
                break;
            }
        }

        if (!$isFind) {
            $cart[] = [
                'id' => $id,
                'size' => $size,
                'num' => $num,
                'price' => $price,
                'sugar_level' => $sugar_level,
                'ice_level' => $ice_level
            ];
        }

        setcookie('cart', json_encode($cart), time() + 30 * 24 * 60 * 60, '/');
        echo json_encode(['status' => 'success']);
        exit;

    case 'delete':
        foreach ($cart as $key => $item) {
            if (
                $item['id'] == $id &&
                $item['size'] == $size &&
                $item['sugar_level'] == $sugar_level &&
                $item['ice_level'] == $ice_level
            ) {
                unset($cart[$key]);
                break;
            }
        }

        setcookie('cart', json_encode(array_values($cart)), time() + 30 * 24 * 60 * 60, '/');
        echo json_encode(['status' => 'success']);
        exit;

    case 'update':
        $id = $_POST['id'] ?? '';
        $size = $_POST['size'] ?? '';
        $num = intval($_POST['num'] ?? 1);
        $sugar_level = $_POST['sugar'] ?? '';
        $ice_level = $_POST['ice'] ?? '';

        $item_price = 0;

        foreach ($cart as &$item) {
            if (
                $item['id'] == $id &&
                $item['size'] == $size &&
                $item['sugar_level'] == $sugar_level &&
                $item['ice_level'] == $ice_level
            ) {
                $item['num'] = $num;
                $item_price = $item['price'];
                break;
            }
        }

        setcookie('cart', json_encode(array_values($cart)), time() + 30 * 24 * 60 * 60, '/');

        $total = 0;
        foreach ($cart as $c) {
            $total += $c['price'] * $c['num'];
        }

        echo json_encode([
            'price' => $item_price,
            'total' => $total
        ]);
        exit;
}

// Trường hợp không khớp action nào
echo json_encode(['status' => 'invalid_action']);
exit;
