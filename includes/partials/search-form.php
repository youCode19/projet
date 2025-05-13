<form method="GET" action="">
  <div class="input-group neumorphic p-1 rounded-pill">
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
          <option value="<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>" <?= (isset($_GET['category']) && $_GET['category'] === $cat['slug']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <input type="text" name="q" class="form-control neumorphic-inset" placeholder="Rechercher un produit..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" required>
    <div class="input-group-append">
      <button type="submit" class="btn btn-primary neumorphic">
        <i class="fas fa-search"></i> Rechercher
      </button>
    </div>
  </div>
</form>