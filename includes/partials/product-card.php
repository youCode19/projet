<?php
if (!defined('ROOT_PATH')) die('Accès direct interdit');
if (!isset($productData)) return;
?>

<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<div class="product-card neumorphic mb-4">
    <?php if ($productData['compare_price'] > 0 && $productData['compare_price'] > $productData['price']): ?>
    <span class="badge bg-danger discount-badge">
        -<?= number_format(100 - ($productData['price'] / $productData['compare_price'] * 100), 0) ?>%
    </span>
    <?php endif; ?>
    <a href="/projet/products/product.php?id=<?= $productData['id'] ?>">
        <img src="/projet/assets/img/products/<?= htmlspecialchars($productData['image'] ?? 'default.jpg') ?>"
             alt="<?= htmlspecialchars($productData['name']) ?>"
             class="img-fluid product-image"
             loading="lazy">
    </a>
    <div class="card-body">
        <h5 class="product-title">
            <a href="/projet/products/product.php?id=<?= $productData['id'] ?>">
                <?= htmlspecialchars($productData['name']) ?>
            </a>
        </h5>
        <div class="product-price mb-2">
            <?php if ($productData['compare_price'] > 0): ?>
                <span class="text-muted text-decoration-line-through me-2">
                    <?= number_format($productData['compare_price'], 2) ?> €
                </span>
            <?php endif; ?>
            <span class="fw-bold text-primary">
                <?= number_format($productData['price'], 2) ?> €
            </span>
        </div>
        <form action="/projet/cart/add.php" method="POST" class="add-to-cart">
            <input type="hidden" name="product_id" value="<?= $productData['id'] ?>">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-cart-plus"></i> Ajouter
            </button>
        </form>
    </div>
</div>