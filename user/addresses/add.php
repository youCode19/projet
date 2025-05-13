<?php
require '../../includes/auth.php';
require '../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isDefault = isset($_POST['is_default']) ? 1 : 0;

    if ($isDefault) {
        // Mettre à jour les autres adresses pour qu'elles ne soient plus par défaut
        $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")
            ->execute([$_SESSION['user']['id']]);
    }

    $stmt = $pdo->prepare("
        INSERT INTO addresses (user_id, name, address, city, postal_code, country, phone, is_default)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user']['id'],
        $_POST['name'],
        $_POST['address'],
        $_POST['city'],
        $_POST['postal_code'],
        $_POST['country'],
        $_POST['phone'],
        $isDefault
    ]);

    $_SESSION['success'] = "Adresse ajoutée avec succès.";
    header('Location: /user/profile.php');
    exit;
}
?>

<div class="container my-5">
    <h1>Ajouter une Adresse</h1>
    <form method="POST">
        <div class="form-group">
            <label for="name">Nom</label>
            <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="address">Adresse</label>
            <input type="text" name="address" id="address" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="city">Ville</label>
            <input type="text" name="city" id="city" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="postal_code">Code Postal</label>
            <input type="text" name="postal_code" id="postal_code" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="country">Pays</label>
            <input type="text" name="country" id="country" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="phone">Téléphone</label>
            <input type="text" name="phone" id="phone" class="form-control" required>
        </div>
        <div class="form-check">
            <input type="checkbox" name="is_default" id="is_default" class="form-check-input">
            <label for="is_default" class="form-check-label">Définir comme adresse par défaut</label>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Ajouter</button>
    </form>
</div>