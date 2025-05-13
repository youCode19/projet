<?php
require '../../includes/auth.php';
require '../../includes/db.php';
checkAdminAccess();

$productId = $_GET['id'] ?? 0;

// Récupérer le produit
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    die("Produit introuvable.");
}

$categories = $pdo->query("SELECT id, name FROM categories")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['name'])));

        $stmt = $pdo->prepare("
            UPDATE products SET 
                category_id = ?, name = ?, slug = ?, short_description = ?, 
                description = ?, price = ?, compare_price = ?, cost_price = ?,
                stock = ?, is_featured = ?, is_bestseller = ?, is_new = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $_POST['category_id'] ?: null,
            $_POST['name'],
            $slug,
            $_POST['short_description'],
            $_POST['description'],
            $_POST['price'],
            $_POST['compare_price'] ?: null,
            $_POST['cost_price'] ?: null,
            $_POST['stock'],
            isset($_POST['is_featured']) ? 1 : 0,
            isset($_POST['is_bestseller']) ? 1 : 0,
            isset($_POST['is_new']) ? 1 : 0,
            $productId
        ]);

        $pdo->commit();
        $_SESSION['success'] = "Produit mis à jour avec succès";
        header("Location: edit.php?id=$productId");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur: " . $e->getMessage();
    }
}
?>

<div class="container my-5">
    <h1>Modifier le Produit</h1>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label for="name">Nom</label>
            <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="category_id">Catégorie</label>
            <select name="category_id" id="category_id" class="form-control">
                <option value="">Aucune</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= $product['category_id'] == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="short_description">Description courte</label>
            <textarea name="short_description" id="short_description" class="form-control"><?= htmlspecialchars($product['short_description']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control"><?= htmlspecialchars($product['description']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="price">Prix</label>
            <input type="number" name="price" id="price" class="form-control" value="<?= $product['price'] ?>" required>
        </div>
        <div class="form-group">
            <label for="compare_price">Prix comparatif</label>
            <input type="number" name="compare_price" id="compare_price" class="form-control" value="<?= $product['compare_price'] ?>">
        </div>
        <div class="form-group">
            <label for="cost_price">Prix de revient</label>
            <input type="number" name="cost_price" id="cost_price" class="form-control" value="<?= $product['cost_price'] ?>">
        </div>
        <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" name="stock" id="stock" class="form-control" value="<?= $product['stock'] ?>" required>
        </div>
        <div class="form-check">
            <input type="checkbox" name="is_featured" id="is_featured" class="form-check-input" <?= $product['is_featured'] ? 'checked' : '' ?>>
            <label for="is_featured" class="form-check-label">Produit vedette</label>
        </div>
        <div class="form-check">
            <input type="checkbox" name="is_bestseller" id="is_bestseller" class="form-check-input" <?= $product['is_bestseller'] ? 'checked' : '' ?>>
            <label for="is_bestseller" class="form-check-label">Meilleure vente</label>
        </div>
        <div class="form-check">
            <input type="checkbox" name="is_new" id="is_new" class="form-check-input" <?= $product['is_new'] ? 'checked' : '' ?>>
            <label for="is_new" class="form-check-label">Nouveau produit</label>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Mettre à jour</button>
    </form>
</div>