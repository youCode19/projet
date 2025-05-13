<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/includes/config.php';
require_once ROOT_PATH . '/includes/db.php';
require_once ROOT_PATH . '/includes/header.php';

$categorySlug = $_GET['slug'] ?? '';

try {
    // Récupérer la catégorie
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$categorySlug]);
    $category = $stmt->fetch();

    if (!$category) {
        header('Location: /projet/products/');
        exit;
    }

    // Récupérer les produits de la catégorie
    $stmtProducts = $pdo->prepare("
        SELECT * FROM products
        WHERE category_id = ? AND stock > 0
        ORDER BY created_at DESC
    ");
    $stmtProducts->execute([$category['id']]);
    $products = $stmtProducts->fetchAll();

} catch (PDOException $e) {
    error_log("Erreur DB : " . $e->getMessage());
    header('Location: /projet/products/');
    exit;
}

$pageTitle = $category['name'];
?>

<main class="container py-5">
    <h1 class="mb-4"><?= htmlspecialchars($category['name']) ?></h1>
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <?php
                $productData = $product;
                include ROOT_PATH . '/includes/partials/product-card.php';
                ?>
            </div>
        <?php endforeach; ?>
        <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="alert alert-warning">Aucun produit trouvé dans cette catégorie.</div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>