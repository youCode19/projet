<?php
// Initialisation
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/projet/includes/config.php';
require_once ROOT_PATH . '/projet/includes/db.php';
require_once ROOT_PATH . '/projet/includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$pageTitle = "Accueil - " . SITE_NAME;
$pageDescription = "Boutique en ligne avec design Neumorphique. Découvrez nos produits exclusifs.";

require_once ROOT_PATH . '/projet/includes/header.php';
?>

<!-- Section de recherche -->
<section class="hero text-center neumorphic mb-5 py-5">
    <div class="container py-5">
        <?php include ROOT_PATH . '/projet/includes/partials/search-form.php'; ?>
    </div>
</section>

<?php
// Traitement de la recherche
$searchTerm = trim($_GET['q'] ?? '');
$categorySlug = $_GET['category'] ?? '';
$products = [];

if ($searchTerm !== '' || $categorySlug !== '') {
    $params = [];
    if (!empty($searchTerm)) {
        $sql = "SELECT * FROM products WHERE MATCH(name, short_description, description) AGAINST (? IN NATURAL LANGUAGE MODE)";
        $params[] = $searchTerm;
        if ($categorySlug) {
            $sql .= " AND category_id IN (SELECT id FROM categories WHERE slug = ?)";
            $params[] = $categorySlug;
        }
    } else {
        $sql = "SELECT * FROM products";
        if ($categorySlug) {
            $sql .= " WHERE category_id IN (SELECT id FROM categories WHERE slug = ?)";
            $params[] = $categorySlug;
        }
    }
    $sql .= " ORDER BY created_at DESC";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur de recherche: " . $e->getMessage());
        $products = [];
    }
}
?>

<?php if (!empty($searchTerm) || !empty($categorySlug)): ?>
    <div class="container my-4">
        <h3>Résultats pour "<?= htmlspecialchars($searchTerm) ?>"</h3>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card neumorphic p-3 h-100">
                        <img src="/projet/assets/img/products/<?= htmlspecialchars($product['image'] ?? 'default.jpg') ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($product['short_description'] ?? '') ?></p>
                            <p class="text-primary font-weight-bold"><?= number_format($product['price'], 2) ?> DH</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
                <div class="col-12">
                    <div class="alert alert-warning">Aucun produit trouvé.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Vidéo de fond + Carousel -->
<div class="video-background-container mb-5">
    <video autoplay loop muted playsinline class="backgroundvid">
        <source src="/projet/assets/vids/vidback.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>
    <div class="carousel-on-video">
        <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
            <ol class="carousel-indicators">
                <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
                <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
                <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
            </ol>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="d-flex justify-content-center align-items-center" style="height:300px;">
                        <h2 class="text-white">Bienvenue sur <?= SITE_NAME ?></h2>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="d-flex justify-content-center align-items-center" style="height:300px;">
                        <h2 class="text-white">Découvrez nos nouveautés</h2>
                    </div>
                </div>
            </div>
            <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Précédent</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Suivant</span>
            </a>
        </div>
    </div>
</div>

<!-- Produits vedettes -->
<?php
try {
    $featuredProducts = $pdo->query("
        SELECT p.id, p.name, p.slug, p.price, p.compare_price, p.image,
               c.name AS category_name, c.slug AS category_slug
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.is_featured = 1 AND p.stock > 0
        ORDER BY p.created_at DESC LIMIT 8
    ")->fetchAll();

    if ($featuredProducts): ?>
        <section class="featured-products mb-5">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title">Produits Vedettes</h2>
                    <a href="/projet/products/?filter=featured" class="btn btn-outline-primary">Voir plus</a>
                </div>
                <div class="row g-4">
                    <?php foreach ($featuredProducts as $product): ?>
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <?php
                            $productData = $product;
                            include ROOT_PATH . '/projet/includes/partials/product-card.php';
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif;
} catch (PDOException $e) {
    error_log("Erreur DB : " . $e->getMessage());
}
?>

<!-- Catégories -->
<?php
try {
    $categories = $pdo->query("
        SELECT id, name, slug, image
        FROM categories
        WHERE parent_id IS NULL AND is_featured = 1
        ORDER BY display_order LIMIT 6
    ")->fetchAll();
    
    if ($categories): ?>
        <section class="category-section mb-5">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title">Nos Catégories</h2>
                    <a href="/projet/categories/" class="btn btn-outline-primary">Toutes les catégories</a>
                </div>
                <div class="row g-4">
                    <?php foreach ($categories as $category): ?>
                        <div class="col-md-4 col-lg-2">
                            <a href="/projet/products/category.php?slug=<?= urlencode($category['slug']) ?>" class="category-card neumorphic">
                                <div class="category-image">
                                    <img src="/projet/assets/img/categories/<?= htmlspecialchars($category['image'] ?? 'default.jpg', ENT_QUOTES, 'UTF-8') ?>"
                                         alt="<?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>"
                                         loading="lazy">
                                </div>
                                <h3><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif;
} catch (PDOException $e) {
    error_log("Erreur DB : " . $e->getMessage());
}
?>

<!-- Nouveautés & Best-sellers -->
<div class="container mb-5">
    <div class="row">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <?php
            try {
                $newProducts = $pdo->query("
                    SELECT id, name, slug, price, image
                    FROM products
                    WHERE is_new = 1 AND stock > 0
                    ORDER BY created_at DESC LIMIT 4
                ")->fetchAll();

                if ($newProducts): ?>
                    <section class="neumorphic p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3>Nouveautés</h3>
                            <a href="/projet/products/?filter=new" class="btn btn-sm btn-outline-primary">Voir tout</a>
                        </div>
                        <div class="row g-3">
                            <?php foreach ($newProducts as $product): ?>
                                <div class="col-6">
                                    <?php
                                    $productData = $product;
                                    include ROOT_PATH . '/projet/includes/partials/product-card-sm.php';
                                    ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif;
            } catch (PDOException $e) {
                error_log("Erreur DB : " . $e->getMessage());
            } ?>
        </div>
        
        <div class="col-lg-6">
            <?php
            try {
                $bestSellers = $pdo->query("
                    SELECT id, name, slug, price, image
                    FROM products
                    WHERE is_bestseller = 1 AND stock > 0
                    ORDER BY sales DESC LIMIT 4
                ")->fetchAll();

                if ($bestSellers): ?>
                    <section class="neumorphic p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3>Meilleures Ventes</h3>
                            <a href="/projet/products/?filter=bestseller" class="btn btn-sm btn-outline-primary">Voir tout</a>
                        </div>
                        <div class="row g-3">
                            <?php foreach ($bestSellers as $product): ?>
                                <div class="col-6">
                                    <?php
                                    $productData = $product;
                                    include ROOT_PATH . '/projet/includes/partials/product-card-sm.php';
                                    ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif;
            } catch (PDOException $e) {
                error_log("Erreur DB : " . $e->getMessage());
            } ?>
        </div>
    </div>
</div>

<!-- Avantages -->
<section class="advantages neumorphic p-4 mb-5">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-3">
                <div class="advantage-item p-3">
                    <i class="bi bi-truck fs-1 text-primary"></i>
                    <h5>Livraison Rapide</h5>
                    <p class="mb-0">Expédition sous 24h</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="advantage-item p-3">
                    <i class="bi bi-arrow-repeat fs-1 text-primary"></i>
                    <h5>Retours Faciles</h5>
                    <p class="mb-0">30 jours pour changer d'avis</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="advantage-item p-3">
                    <i class="bi bi-shield-check fs-1 text-primary"></i>
                    <h5>Paiement Sécurisé</h5>
                    <p class="mb-0">Cryptage SSL</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="advantage-item p-3">
                    <i class="bi bi-headset fs-1 text-primary"></i>
                    <h5>Support 24/7</h5>
                    <p class="mb-0">Assistance permanente</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once ROOT_PATH . '/projet/includes/footer.php'; ?>