<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $passwordConfirm = $_POST['password_confirm'];

    // Contrôle de saisie avec expressions régulières
    if (!preg_match('/^[a-zA-Z\s]{3,50}$/', $name)) {
        $_SESSION['error'] = "Le nom doit contenir entre 3 et 50 caractères alphabétiques.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "L'adresse email n'est pas valide.";
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $_SESSION['error'] = "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
    } elseif ($password !== $passwordConfirm) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = "Cette adresse email est déjà utilisée.";
        } else {
            // Inscription de l'utilisateur
            $userId = registerUser($name, $email, $password);
            if ($userId) {
                // Connecter automatiquement l'utilisateur
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $_SESSION['user'] = $stmt->fetch();

                $_SESSION['success'] = "Inscription réussie. Bienvenue, $name !";
                header('Location: /');
                exit;
            } else {
                $_SESSION['error'] = "Une erreur est survenue lors de l'inscription.";
            }
        }
    }

    // Redirection en cas d'erreur
    header('Location: /');
    exit;
}
?>