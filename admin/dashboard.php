<?php
// Démarrage de session sécurisé
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400,
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

// Inclusions avec vérification de chemin
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/config.php';


// Vérification des privilèges admin
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'superadmin'])) {
    $_SESSION['error'] = "Accès non autorisé";
    header('Location: /projet/index.php');
    exit;
}

// Titre de la page
$pageTitle = "Tableau de bord administrateur";

// Récupération des stats pour le dashboard
try {
    $stats = [
        'products' => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
        'users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn(),
        'revenue' => $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'completed'")->fetchColumn()
    ];
} catch (PDOException $e) {
    error_log("Erreur DB: " . $e->getMessage());
    $stats = [];
}


// En-tête sécurisé
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="display-4">Tableau de bord administrateur</h1>
            <p class="lead">Bienvenue, <?= htmlspecialchars($_SESSION['user']['name'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card neumorphic">
                <div class="card-body">
                    <h5 class="card-title">Produits</h5>
                    <p class="display-4"><?= $stats['products'] ?? 0 ?></p>
                    <a href="/projet/admin/products/list.php" class="btn btn-primary">Gérer</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card neumorphic">
                <div class="card-body">
                    <h5 class="card-title">Utilisateurs</h5>
                    <p class="display-4"><?= $stats['users'] ?? 0 ?></p>
                    <a href="/projet/admin/users/list.php" class="btn btn-primary">Gérer</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card neumorphic">
                <div class="card-body">
                    <h5 class="card-title">Commandes</h5>
                    <p class="display-4"><?= $stats['orders'] ?? 0 ?></p>
                    <a href="/projet/admin/orders/list.php" class="btn btn-primary">Gérer</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card neumorphic">
                <div class="card-body">
                    <h5 class="card-title">Revenu total</h5>
                    <p class="display-4"><?= isset($stats['revenue']) ? number_format($stats['revenue'], 2) . ' €' : '0 €' ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row">
        <div class="col-md-12">
            <div class="card neumorphic mb-4">
                <div class="card-header">
                    <h5>Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3">
                        <a href="/projet/admin/products/add.php" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Ajouter un produit
                        </a>
                        <a href="/projet/admin/users/add.php" class="btn btn-success">
                            <i class="bi bi-person-plus"></i> Ajouter un utilisateur
                        </a>
                        <a href="/projet/admin/orders/pending.php" class="btn btn-warning">
                            <i class="bi bi-hourglass"></i> Commandes en attente
                        </a>
                        <?php if ($_SESSION['user']['role'] === 'superadmin'): ?>
                            <a href="/projet/admin/settings.php" class="btn btn-info">
                                <i class="bi bi-gear"></i> Paramètres système
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dernières activités -->
    <div class="row">
        <div class="col-md-12">
            <div class="card neumorphic">
                <div class="card-header">
                    <h5>Activités récentes</h5>
                </div>
                <div class="card-body">
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT * FROM activities ORDER BY created_at DESC LIMIT 5");
                        if ($stmt->rowCount() > 0) {
                            echo '<ul class="list-group">';
                            while ($activity = $stmt->fetch()) {
                                echo '<li class="list-group-item">';
                                echo htmlspecialchars($activity['description'], ENT_QUOTES, 'UTF-8');
                                echo '<small class="text-muted float-end">';
                                echo date('d/m/Y H:i', strtotime($activity['created_at']));
                                echo '</small>';
                                echo '</li>';
                            }
                            echo '</ul>';
                        } else {
                            echo '<p>Aucune activité récente</p>';
                        }
                    } catch (PDOException $e) {
                        echo '<p class="text-danger">Erreur de chargement des activités</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Pied de page sécurisé
require_once __DIR__ . '/../includes/footer.php';
?>