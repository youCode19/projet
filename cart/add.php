 
<?php
require '../includes/auth.php';
require '../includes/db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'] ?? 1;
    
    // Récupérer le produit
    $product = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
    $product->execute([$productId]);
    $product = $product->fetch();
    
    if ($product) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $quantity
            ];
        }
        
        $_SESSION['cart_count'] = array_sum(array_column($_SESSION['cart'], 'quantity'));
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'cart_count' => $_SESSION['cart_count']]);
        exit;
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Produit non trouvé']);
?>