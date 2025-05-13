<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
checkAdminAccess();

$orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll();
$pageTitle = "Gestion des commandes";
require_once '../../includes/header.php';
?>
<div class="container my-5">
    <h1>Gestion des commandes</h1>
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Client</th>
                <th>Total</th>
                <th>Statut</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= htmlspecialchars($order['order_number'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($order['user_id'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= number_format($order['total'], 2) ?> €</td>
                <td><?= htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($order['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <a href="view.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-info">Voir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once '../../includes/footer.php'; ?>