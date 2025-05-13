<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<?php
require '../includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? null;
    $quantity = (int)($_POST['quantity'] ?? 1);
    if ($productId && isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId]['quantity'] = $quantity;
        $_SESSION['cart_count'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
        echo json_encode([
            'success' => true,
            'cart_count' => $_SESSION['cart_count'],
            'item_total' => $_SESSION['cart'][$productId]['price'] * $quantity,
            'cart_total' => array_sum(array_map(function($item) {
                return $item['price'] * $item['quantity'];
            }, $_SESSION['cart']))
        ]);
        exit;
    }
}
echo json_encode(['success' => false]);
exit;
?>