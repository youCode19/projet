<?php
require '../../includes/auth.php';
require '../../includes/db.php';
checkAdminAccess();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Récupérer les produits
$products = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
    LIMIT :offset, :perPage
");
$products->bindValue(':offset', $offset, PDO::PARAM_INT);
$products->bindValue(':perPage', $perPage, PDO::PARAM_INT);
$products->execute();

// Compter le total
$total = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalPages = ceil($total / $perPage);
?>
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des Produits</h1>
        <a href="add.php" class="btn btn-primary">Ajouter un Produit</a>
    </div>

    <div class="neumorphic p-4">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Catégorie</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= $product['id'] ?></td>
                    <td><img src="/assets/img/products/<?= $product['image'] ?>" width="50"></td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= number_format($product['price'], 2) ?> €</td>
                    <td><?= $product['stock'] ?></td>
                    <td><?= $product['category_name'] ?? 'Aucune' ?></td>
                    <td>
                        <a href="edit.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-warning">Éditer</a>
                        <form action="delete.php" method="POST" class="d-inline">
                            <input type="hidden" name="id" value="<?= $product['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce produit ?')">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</div>