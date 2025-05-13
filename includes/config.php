<?php
// config.php - Configuration centrale sécurisée

// 1. Environnement et sécurité
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/errors.log');
define('ENVIRONMENT', 'development'); // 'production' en live

// 2. Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecommerce_neumorphic');
define('DB_USER', 'root'); // Jamais 'root' en production
define('DB_PASS', 'youyou19'); // À remplacer par un mot de passe fort
define('DB_CHARSET', 'utf8mb4');

// 3. Configuration du site
define('SITE_NAME', 'MonShop Néomorphique');
define('SITE_URL', 'https://' . $_SERVER['HTTP_HOST']); // Force HTTPS
define('ADMIN_EMAIL', 'admin@monshop.com');
define('CSRF_TOKEN_EXPIRE', 3600); // 1 heure

// 4. Sécurité des sessions
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400, // 1 jour
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => true,    // HTTPS seulement
        'httponly' => true,  // Protection contre XSS
        'samesite' => 'Strict'
    ]);
    session_start();
    session_regenerate_id(true); // Anti fixation de session
}

// 5. Autoloader sécurisé
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../classes/' . str_replace(['\\', '../'], ['/', ''], $class) . '.php';
    if (file_exists($file)) {
        require $file;
    } else {
        throw new Exception("Classe $class introuvable");
    }
});

// 6. Connexion DB sécurisée
try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log('DB Error: ' . $e->getMessage());
    if (ENVIRONMENT === 'development') {
        die('Erreur DB: ' . $e->getMessage());
    } else {
        exit('Erreur système. Veuillez réessayer plus tard.');
    }
}

// 7. Fonctions d'authentification améliorées
function isAdmin() {
    return isset($_SESSION['user']['role']) && 
           in_array($_SESSION['user']['role'], ['admin', 'superadmin']) &&
           $_SESSION['user']['ip'] === $_SERVER['REMOTE_ADDR'];
}

function requireAdmin() {
    if (!isAdmin()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        $_SESSION['error'] = "Accès admin requis";
        header('Location: /login');
        exit;
    }
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']) &&
           hash_equals($_SESSION['csrf_token'], $token) &&
           (time() - $_SESSION['csrf_token_time']) < CSRF_TOKEN_EXPIRE;
}

// 8. Fonctions utilitaires sécurisées
function redirect($url, $statusCode = 303) {
    header('Location: ' . SITE_URL . $url, true, $statusCode);
    exit;
}

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8', false);
}

function cleanInput($input) {
    $search = [
        '@<script[^>]*?>.*?</script>@si',
        '@<[\/\!]*?[^<>]*?>@si',
        '@<style[^>]*?>.*?</style>@siU',
        '@<![\s\S]*?--[ \t\n\r]*>@'
    ];
    return preg_replace($search, '', $input);
}

// 9. Gestion des erreurs personnalisée
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno] $errstr in $errfile on line $errline");
    if (ENVIRONMENT === 'development') {
        echo "<div class='alert alert-danger'>[$errno] $errstr in $errfile on line $errline</div>";
    }
    return true;
});

// 10. Protection contre les attaques
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (ENVIRONMENT === 'production') {
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
}