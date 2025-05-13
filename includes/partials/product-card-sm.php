<?php
if (!defined('ROOT_PATH')) {
    die('Accès direct interdit');
}

if (!isset($productData)) return;
?>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<div class="product-card-sm neumorphic">
    <a href="/projet/products/product.php?id=<?= $productData['id'] ?>">
        <img src="/projet/assets/img/products/<?= htmlspecialchars($productData['image'] ?? 'default.jpg') ?>" 
             alt="<?= htmlspecialchars($productData['name']) ?>"
             class="img-fluid"
             loading="lazy">
    </a>
    <div class="p-2">
        <h6 class="mb-1">
            <a href="/projet/products/product.php?id=<?= $productData['id'] ?>">
                <?= htmlspecialchars(mb_strimwidth($productData['name'], 0, 30, "...")) ?>
            </a>
        </h6>
        <div class="d-flex justify-content-between align-items-center">
            <span class="price"><?= number_format($productData['price'], 2) ?> €</span>
            <form action="/projet/cart/add.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $productData['id'] ?>">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-cart-plus"></i>
                </button>
            </form>
        </div>
    </div>
</div>