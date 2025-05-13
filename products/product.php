<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/includes/config.php';
require_once ROOT_PATH . '/includes/db.php';
require_once ROOT_PATH . '/includes/header.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: /projet/products/');
        exit;
    }

    // Incrémenter les vues
    $pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?")->execute([$productId]);

    // Produits similaires
    $stmtRelated = $pdo->prepare("
        SELECT * FROM products 
        WHERE category_id = ? AND id != ? AND stock > 0
        LIMIT 4
    ");
    $stmtRelated->execute([$product['category_id'], $productId]);
    $relatedProducts = $stmtRelated->fetchAll();

} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    header('Location: /projet/products/');
    exit;
}

$pageTitle = $product['name'];
?>

<main class="container py-5">
    <div class="row g-5">
        <!-- Gallery -->
        <div class="col-md-6">
            <div class="neumorphic p-3 rounded-3">
                <img src="/projet/assets/img/products/<?= htmlspecialchars($product['image']) ?>" 
                     class="img-fluid w-100" 
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     loading="lazy">
            </div>
        </div>

        <!-- Details -->
        <div class="col-md-6">
            <div class="neumorphic p-4 rounded-3">
                <h1 class="mb-3"><?= htmlspecialchars($product['name']) ?></h1>
                
                <div class="d-flex align-items-center mb-4">
                    <div class="rating me-3">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="bi bi-star<?= $i <= round($product['rating'] ?? 0) ? '-fill' : '' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <small class="text-muted">(<?= $product['reviews'] ?? 0 ?> avis)</small>
                </div>

                <div class="mb-4">
                    <?php if ($product['compare_price'] > 0): ?>
                        <span class="text-decoration-line-through text-muted me-2">
                            <?= number_format($product['compare_price'], 2) ?> €
                        </span>
                    <?php endif; ?>
                    <span class="display-5 fw-bold text-primary">
                        <?= number_format($product['price'], 2) ?> €
                    </span>
                </div>

                <p class="lead mb-4"><?= htmlspecialchars($product['short_description']) ?></p>

                <form action="/projet/cart/add.php" method="POST" class="mb-4">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-auto">
                            <label for="quantity" class="form-label">Quantité</label>
                        </div>
                        <div class="col-4">
                            <select name="quantity" id="quantity" class="form-select neumorphic-inset">
                                <?php for ($i = 1; $i <= min(10, $product['stock']); $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col">
                            <span class="badge <?= $product['stock'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                <?= $product['stock'] > 0 ? 'En stock' : 'Rupture' ?>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary flex-grow-1 py-3" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                            <i class="bi bi-cart-plus"></i> Ajouter au panier
                        </button>
                        <button type="button" class="btn btn-outline-secondary">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>
                </form>

                <div class="d-flex gap-2">
                    <span class="text-muted">Catégorie:</span>
                    <a href="/projet/products/category.php?slug=<?= urlencode($product['category_slug']) ?>" class="text-decoration-none">
                        <?= htmlspecialchars($product['category_name']) ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Description -->
    <section class="mt-5">
        <div class="neumorphic p-4 rounded-3">
            <h2 class="mb-4">Description</h2>
            <div class="product-description">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </div>
        </div>
    </section>

    <!-- Produits similaires -->
    <?php if (!empty($relatedProducts)): ?>
    <section class="mt-5">
        <h2 class="mb-4">Produits similaires</h2>
        <div class="row g-4">
            <?php foreach ($relatedProducts as $related): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <?php 
                    $productData = $related;
                    include ROOT_PATH . '/includes/partials/product-card.php'; 
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>