<?php
require_once __DIR__ . '/../includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400, // 1 jour
        'cookie_secure' => true,    // HTTPS seulement
        'cookie_httponly' => true,  // Protection XSS
        'use_strict_mode' => true   // Sécurité renforcée
    ]);
}

/**
 * Authentifie un utilisateur
 */
function authenticateUser($email, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Régénération de l'ID de session
            session_regenerate_id(true);
            
            // Stockage minimal en session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'last_login' => $user['last_login']
            ];
            
            // Mise à jour dernière connexion
            $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")
                ->execute([$user['id']]);
            
            return $user['role'];
        }
        return false;
    } catch (PDOException $e) {
        error_log("Erreur d'authentification: ".$e->getMessage());
        return false;
    }
}

/**
 * Vérifie l'accès admin
 */
function checkAdminAccess() {
    if (!isset($_SESSION['user'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        $_SESSION['error'] = "Accès admin requis";
        header('Location: /projet/login.php');
        exit;
    }
    
    if (!in_array($_SESSION['user']['role'], ['admin', 'superadmin'])) {
        $_SESSION['error'] = "Permissions insuffisantes";
        header('Location: /projet/index.php');
        exit;
    }
}

/**
 * Enregistre un nouvel utilisateur
 */
function registerUser($name, $email, $password, $role = 'client') {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        // Vérification email unique
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            throw new Exception("Email déjà utilisé");
        }
        
        // Hachage mot de passe
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Insertion
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPassword, $role]);
        
        $userId = $pdo->lastInsertId();
        $pdo->commit();
        
        return $userId;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Erreur d'inscription: ".$e->getMessage());
        return false;
    }
}