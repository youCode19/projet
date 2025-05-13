 
<?php
require '../includes/auth.php';
require_once ROOT_PATH .'/projet/includes/prev.html'; 


if (empty($_SESSION['cart'])) {
    header('Location: view.php');
    exit;
}

// Récupérer les adresses du client
$addresses = $pdo->prepare("
    SELECT * FROM addresses 
    WHERE user_id = ? 
    ORDER BY is_default DESC
");
$addresses->execute([$_SESSION['user']['id']]);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation
    $paymentMethod = $_POST['payment_method'] ?? '';
    $shippingAddressId = $_POST['shipping_address'] ?? '';
    
    if (!in_array($paymentMethod, ['card', 'cash_on_delivery'])) {
        $_SESSION['error'] = "Méthode de paiement invalide";
        header('Location: checkout.php');
        exit;
    }
    
    // Création de la commande
    $orderNumber = 'CMD-' . strtoupper(uniqid());
    $total = 0;
    
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Insertion de la commande
        $stmt = $pdo->prepare("
            INSERT INTO orders (
                user_id, order_number, status, total, payment_method,
                payment_status, shipping_address
            ) VALUES (?, ?, 'pending', ?, ?, 'pending', ?)
        ");
        $stmt->execute([
            $_SESSION['user']['id'],
            $orderNumber,
            $total,
            $paymentMethod,
            json_encode($addresses->fetch(PDO::FETCH_ASSOC))
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Insertion des articles
        foreach ($_SESSION['cart'] as $id => $item) {
            $stmt = $pdo->prepare("
                INSERT INTO order_items (
                    order_id, product_id, name, price, quantity, total, image
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $orderId,
                $id,
                $item['name'],
                $item['price'],
                $item['quantity'],
                $item['price'] * $item['quantity'],
                $item['image']
            ]);
        }
        
        $pdo->commit();
        
        // Vider le panier
        $_SESSION['cart'] = [];
        $_SESSION['cart_count'] = 0;
        
        // Redirection vers confirmation
        header('Location: /user/orders.php?order_id=' . $orderId);
        exit;
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur lors de la commande: " . $e->getMessage();
        header('Location: checkout.php');
        exit;
    }
}
?>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<div class="container my-5">
    <h1 class="mb-4">Finaliser la Commande</h1>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-7">
            <div class="neumorphic p-4 mb-4">
                <h3 class="mb-3">Adresse de Livraison</h3>
                
                <?php if ($addresses->rowCount() > 0): ?>
                    <?php foreach ($addresses as $address): ?>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="radio" 
                               name="shipping_address" 
                               id="address_<?= $address['id'] ?>" 
                               value="<?= $address['id'] ?>"
                               <?= $address['is_default'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="address_<?= $address['id'] ?>">
                            <strong><?= $address['first_name'] ?> <?= $address['last_name'] ?></strong><br>
                            <?= $address['address1'] ?><br>
                            <?= $address['zip_code'] ?> <?= $address['city'] ?><br>
                            <?= $address['country'] ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning">
                        Aucune adresse enregistrée. <a href="/user/profile.php">Ajoutez une adresse</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-md-5">
            <div class="neumorphic p-4">
                <h3 class="mb-3">Récapitulatif</h3>
                
                <table class="table">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                    <tr>
                        <td><?= $item['name'] ?> × <?= $item['quantity'] ?></td>
                        <td class="text-end"><?= number_format($item['price'] * $item['quantity'], 2) ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <th>Total</th>
                        <th class="text-end"><?= number_format($total, 2) ?> €</th>
                    </tr>
                </table>
                
                <h4 class="mt-4">Méthode de Paiement</h4>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" id="cash" value="cash_on_delivery" checked>
                    <label class="form-check-label" for="cash">Paiement à la livraison</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" id="card" value="card">
                    <label class="form-check-label" for="card">Carte bancaire</label>
                </div>
                
                <button type="submit" class="btn btn-success w-100 mt-4">Confirmer la commande</button>
            </div>
        </div>
    </div>
</div>