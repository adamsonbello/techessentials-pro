<?php
/**
 * TechEssentials Pro - Configuration Centrale
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 */

// Empêcher l'accès direct
if (!defined('TECHESSENTIALS_PRO')) {
    define('TECHESSENTIALS_PRO', true);
}

// ===============================
// ENVIRONNEMENT
// ===============================
define('ENV', 'development'); // 'development' | 'production'
define('DEBUG', ENV === 'development');

// ===============================
// CHEMINS
// ===============================
define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('ASSETS_PATH', '/techessentialspro/assets/');
define('ADMIN_PATH', '/techessentialspro/admin/');
define('BLOG_PATH', '/techessentialspro/blog/');
define('API_PATH', '/techessentialspro/api/');

// URLs absolues
define('BASE_URL', 'http://localhost/techessentialspro/');
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOADS_URL', BASE_URL . 'uploads/');

// ===============================
// BASE DE DONNÉES
// ===============================
$DB_CONFIG = [
    'main' => [
        'host' => 'localhost',
        'dbname' => 'techessentials',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ],
    'blog' => [
        'host' => 'localhost',
        'dbname' => 'techessentials_blog',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ]
];

// ===============================
// LANGUES
// ===============================
define('DEFAULT_LANG', 'en');
define('AVAILABLE_LANGS', ['en', 'fr']);

// ===============================
// EMAIL
// ===============================
$EMAIL_CONFIG = [
    'smtp' => [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'secure' => 'tls',
        'auth' => true,
        'username' => 'newsletter@techessentialspro.com',
        'password' => 'your-app-password'
    ],
    'from' => [
        'email' => 'newsletter@techessentialspro.com',
        'name' => 'TechEssentials Pro'
    ]
];

// ===============================
// SEO
// ===============================
$SEO_CONFIG = [
    'default_title' => 'TechEssentials Pro - Best Tech for Remote Workers',
    'default_description' => 'Discover essential tech accessories and productivity tools for remote workers. Expert reviews, honest recommendations.',
    'default_keywords' => 'remote work, tech accessories, home office, productivity tools',
    'author' => 'TechEssentials Pro Team',
    'og_image' => ASSETS_URL . 'images/og-default.jpg',
    'twitter_handle' => '@techessentials'
];

// ===============================
// TRACKING
// ===============================
$TRACKING_CONFIG = [
    'google_analytics' => 'G-XXXXXXXXXX',
    'google_tag_manager' => 'GTM-XXXXXXX',
    'facebook_pixel' => 'XXXXXXXXXXXXXXX',
    'hotjar_id' => 'XXXXXXX'
];

// ===============================
// SÉCURITÉ
// ===============================
define('SESSION_LIFETIME', 7200); // 2 heures
define('CSRF_TOKEN_NAME', 'techessentials_csrf');
define('API_RATE_LIMIT', 100); // requêtes par heure

// ===============================
// FEATURES FLAGS
// ===============================
$FEATURES = [
    'newsletter' => true,
    'reviews' => true,
    'blog' => true,
    'deals' => true,
    'contact' => true,
    'crm' => false, // En développement
    'api_v2' => false // En développement
];

// ===============================
// AUTOLOADER
// ===============================
spl_autoload_register(function ($class) {
    $directories = [
        ROOT_PATH . 'classes/',
        ROOT_PATH . 'api/controllers/',
        ROOT_PATH . 'api/models/',
        INCLUDES_PATH
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ===============================
// FONCTIONS GLOBALES
// ===============================

/**
 * Connexion PDO avec gestion d'erreurs
 */
function getDB($config_key = 'main') {
    global $DB_CONFIG;
    
    static $connections = [];
    
    if (isset($connections[$config_key])) {
        return $connections[$config_key];
    }
    
    try {
        $config = $DB_CONFIG[$config_key];
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        
        $pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        
        $connections[$config_key] = $pdo;
        return $pdo;
        
    } catch (PDOException $e) {
        if (DEBUG) {
            die("Erreur DB ({$config_key}): " . $e->getMessage());
        } else {
            error_log("DB Error: " . $e->getMessage());
            die("Erreur de connexion. Veuillez réessayer plus tard.");
        }
    }
}

/**
 * Debug helper
 */
function dd($data, $label = null) {
    if (DEBUG) {
        echo "<pre style='background:#f5f5f5; padding:10px; border:2px solid #667eea;'>";
        if ($label) echo "<strong>{$label}:</strong><br>";
        print_r($data);
        echo "</pre>";
        die();
    }
}

/**
 * Log helper
 */
function logError($message, $file = 'error.log') {
    $log_file = ROOT_PATH . 'logs/' . $file;
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[{$timestamp}] {$message}" . PHP_EOL;
    
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0777, true);
    }
    
    error_log($log_message, 3, $log_file);
}

/**
 * URL Helper
 */
function url($path = '') {
    return BASE_URL . ltrim($path, '/');
}

/**
 * Asset URL Helper
 */
function asset($path) {
    return ASSETS_URL . ltrim($path, '/');
}

/**
 * Redirection sécurisée
 */
function redirect($url, $permanent = false) {
    header('Location: ' . $url, true, $permanent ? 301 : 302);
    exit();
}

/**
 * CSRF Protection
 */
function generateCSRF() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCSRF($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && 
           hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Sanitization helpers
 */
function clean($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function cleanArray($array) {
    return array_map('clean', $array);
}

// ===============================
// INITIALISATION SESSION
// ===============================
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => '/',
        'domain' => '',
        'secure' => (ENV === 'production'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Définir la langue de session
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = DEFAULT_LANG;
}

// ===============================
// HEADERS DE SÉCURITÉ
// ===============================
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    if (ENV === 'production') {
        header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' https://www.googletagmanager.com; style-src \'self\' \'unsafe-inline\';');
    }
}

// ===============================
// TIMEZONE
// ===============================
date_default_timezone_set('Europe/Paris');

// ===============================
// ERROR REPORTING
// ===============================
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . 'logs/php_errors.log');
}


// Dans includes/config.php
//define('ENV', 'production'); // ⚠️ Changer en production
//$DB_CONFIG['main']['password'] = 'VOTRE_MDP'; // ⚠️ Configurer DB
//$EMAIL_CONFIG['smtp']['username'] = 'VOTRE_EMAIL'; // ⚠️ Configurer SMTP
