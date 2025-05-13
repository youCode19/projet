<?php
define('ROOT_PATH', dirname(__DIR__, 1));
require_once ROOT_PATH . '/includes/config.php';
require_once ROOT_PATH . '/includes/db.php';
require_once ROOT_PATH . '/includes/header.php';

$searchTerm = trim($_GET['q'] ?? '');
$categorySlug = $_GET['category'] ?? '';
$sortOptions = ['newest', 'price_asc', 'price_desc', 'popular'];
$sort = in_array($_GET['sort'] ?? '', $sortOptions) ? $_GET['sort'] : 'newest';

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

switch ($sort) {
    case 'price_asc':  $sql .= " ORDER BY price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY price DESC"; break;
    case 'popular':    $sql .= " ORDER BY views DESC, sales DESC"; break;
    default:           $sql .= " ORDER BY created_at DESC";
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    $categories = $pdo->query("SELECT id, name, slug FROM categories WHERE parent_id IS NULL ORDER BY name")->fetchAll();

} catch (PDOException $e) {
    error_log("Erreur de recherche: " . $e->getMessage());
    die("Erreur lors de la recherche.");
}
?>

<?php include ROOT_PATH . '/includes/navbar.php'; ?>

<!-- Formulaire de recherche décoré -->
<div class="container text-center py-5">
    <?php include ROOT_PATH . '/includes/partials/search-form.php'; ?>
</div>


<!-- Résultats de la recherche -->
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

<?php include ROOT_PATH . '/includes/footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>