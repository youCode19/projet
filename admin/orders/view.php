<?php
require '../../includes/auth.php';
require '../../includes/db.php';
checkAdminAccess();

$orderId = $_GET['id'] ?? 0;

// Retrieve order
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    die("Commande introuvable.");
}

// Retrieve order items
$orderItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$orderItems->execute([$orderId]);
?>
<div class="container my-5">
    <h1>Détails de la commande #<?= htmlspecialchars($order['order_number']) ?></h1>
    <p><strong>Statut :</strong> <?= htmlspecialchars($order['status']) ?></p>
    <p><strong>Total :</strong> <?= number_format($order['total'], 2) ?> €</p>

    <h3>Articles</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Prix</th>
                <th>Quantité</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orderItems as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= number_format($item['price'], 2) ?> €</td>
                <td><?= $item['quantity'] ?></td>
                <td><?= number_format($item['total'], 2) ?> €</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>