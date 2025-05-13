<?php
define('ROOT_PATH', dirname(__DIR__)); // ou dirname(__FILE__) selon ta structure
require_once ROOT_PATH . '/../includes/config.php';
require_once ROOT_PATH . '/../includes/header.php';
require_once __DIR__ . '/../includes/bootstrap.php';

// Pagination
$page = max(1, $_GET['page'] ?? 1);
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Récupération des produits
try {
    $products = $pdo->query("
        SELECT p.*, c.name AS category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.stock > 0
        ORDER BY p.created_at DESC
        LIMIT $perPage OFFSET $offset
    ")->fetchAll();

    $totalProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE stock > 0")->fetchColumn();
    $totalPages = ceil($totalProducts / $perPage);

} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    $products = [];
    $totalPages = 1;
}

// Header
$pageTitle = "Tous nos produits";
require_once ROOT_PATH . '/includes/header.php';
?>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="display-5 fw-bold">Nos produits</h1>
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Trier par <i class="bi bi-filter"></i>
            </button>
            <ul class="dropdown-menu neumorphic-dropdown">
                <li><a class="dropdown-item" href="?sort=newest">Nouveautés</a></li>
                <li><a class="dropdown-item" href="?sort=price_asc">Prix croissant</a></li>
                <li><a class="dropdown-item" href="?sort=price_desc">Prix décroissant</a></li>
                <li><a class="dropdown-item" href="?sort=popular">Plus populaires</a></li>
            </ul>
        </div>
    </div>

    <form method="GET" action="/projet/products/search.php" class="mb-4">
        <div class="input-group">
            <div class="input-group-prepend">
                <select name="category" class="form-control neumorphic">
                    <option value="">Toutes catégories</option>
                    <?php
                    $allCategories = $pdo->query("
                        SELECT id, name, slug 
                        FROM categories 
                        WHERE parent_id IS NULL
                        ORDER BY name
                    ")->fetchAll();
                    foreach ($allCategories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="text" name="q" class="form-control neumorphic-inset" placeholder="Rechercher un produit..." required>
            <div class="input-group-append">
                <button type="submit" class="btn btn-primary neumorphic">
                    <i class="fas fa-search"></i> Rechercher
                </button>
            </div>
        </div>
    </form>

    <div class="row g-4">
        <?php if (empty($products)): ?>
            <div class="col-12">
                <div class="alert alert-info neumorphic">
                    Aucun produit disponible pour le moment
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-6 col-lg-4 col-xl-3">
                    <?php include ROOT_PATH . '/includes/partials/product-card.php'; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav class="mt-5">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link neumorphic-hover" href="?page=<?= $i ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</main>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>