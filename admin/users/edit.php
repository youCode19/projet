<?php
require '../../includes/auth.php';
require '../../includes/db.php';
checkAdminAccess();

$userId = $_GET['id'] ?? 0;

// Récupérer l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    die("Utilisateur introuvable.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
        $stmt->execute([
            $_POST['name'],
            $_POST['email'],
            $_POST['role'],
            $userId
        ]);

        $_SESSION['success'] = "Utilisateur mis à jour avec succès.";
        header('Location: list.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la mise à jour : " . $e->getMessage();
    }
}
?>

<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

<div class="container my-5">
    <h1>Modifier l'utilisateur</h1>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label for="name">Nom</label>
            <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="form-group">
            <label for="role">Rôle</label>
            <select name="role" id="role" class="form-control">
                <option value="client" <?= $user['role'] === 'client' ? 'selected' : '' ?>>Client</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="superadmin" <?= $user['role'] === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Mettre à jour</button>
    </form>
</div>