<?php
require_once __DIR__.'/../includes/auth.php';

// Si déjà connecté, redirection
if (isset($_SESSION['user'])) {
    header('Location: /projet/'.($_SESSION['user']['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php'));
    exit;
}

$pageTitle = "Connexion administrateur";

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "Email requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide";
    }
    
    if (empty($password)) {
        $errors[] = "Mot de passe requis";
    } elseif (strlen($password) < 8) {
        $errors[] = "8 caractères minimum";
    }
    
    if (empty($errors)) {
        $role = authenticateUser($email, $password);
        
        if ($role) {
            // Connexion réussie
            $_SESSION['login_time'] = time();
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            
            // Redirection selon rôle
            $redirect = in_array($role, ['admin', 'superadmin']) 
                ? '/projet/admin/dashboard.php' 
                : '/projet/index.php';
            
            header("Location: $redirect");
            exit;
        } else {
            $errors[] = "Identifiants incorrects";
        }
    }
    
    $_SESSION['error'] = implode("<br>", $errors);
    $_SESSION['form_data'] = ['email' => $email];
    header('Location: /projet/login.php');
    exit;
}
?>