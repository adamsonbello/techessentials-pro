<?php
/**
 * TechEssentials Pro - API Router
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 */

// Définir la constante avant d'inclure config
define('TECHESSENTIALS_PRO', true);

// Inclure la configuration
require_once dirname(__DIR__) . '/includes/config.php';
require_once INCLUDES_PATH . 'functions.php';

// Headers CORS et API
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: ' . (ENV === 'production' ? 'https://techessentialspro.com' : '*'));
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 3600');

// Gérer les requêtes OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Rate Limiting par IP
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$cache_key = 'api_rate_limit_' . md5($client_ip);

if (!checkRateLimit($client_ip, API_RATE_LIMIT)) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error' => 'Rate limit exceeded. Maximum ' . API_RATE_LIMIT . ' requests per hour.',
        'code' => 'RATE_LIMIT_EXCEEDED'
    ]);
    exit();
}

// Parser la route
$request_uri = $_SERVER['REQUEST_URI'] ?? '';
$script_name = dirname($_SERVER['SCRIPT_NAME']);
$route = str_replace($script_name, '', $request_uri);
$route = ltrim(parse_url($route, PHP_URL_PATH), '/');

// Extraire les segments de la route
$segments = array_filter(explode('/', $route));
$controller_name = $segments[0] ?? 'home';
$action = $segments[1] ?? 'index';
$id = $segments[2] ?? null;

// Méthode HTTP
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Récupérer les données de la requête
$input_data = [];
switch ($method) {
    case 'GET':
        $input_data = $_GET;
        break;
    case 'POST':
    case 'PUT':
    case 'DELETE':
        $raw_input = file_get_contents('php://input');
        $input_data = json_decode($raw_input, true) ?? [];
        // Fallback pour les formulaires
        if (empty($input_data)) {
            $input_data = $_POST;
        }
        break;
}

// Log de debug
if (DEBUG) {
    logError("API Request: {$method} /{$route} - Controller: {$controller_name}, Action: {$action}");
}

// Routage
try {
    $response = routeRequest($controller_name, $action, $method, $input_data, $id);
    echo $response;
    
} catch (Exception $e) {
    http_response_code(500);
    
    $error_response = [
        'success' => false,
        'error' => 'Internal server error',
        'code' => 'INTERNAL_ERROR'
    ];
    
    if (DEBUG) {
        $error_response['debug'] = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }
    
    logError("API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    echo json_encode($error_response);
}

/**
 * Router principal
 */
function routeRequest($controller_name, $action, $method, $data, $id = null) {
    // Normaliser le nom du contrôleur
    $controller_class = ucfirst($controller_name) . 'Controller';
    $controller_file = ROOT_PATH . 'api/controllers/' . $controller_class . '.php';
    
    // Vérifier si le contrôleur existe
    if (!file_exists($controller_file)) {
        http_response_code(404);
        return json_encode([
            'success' => false,
            'error' => 'Controller not found: ' . $controller_name,
            'code' => 'CONTROLLER_NOT_FOUND'
        ]);
    }
    
    // Inclure et instancier le contrôleur
    require_once $controller_file;
    
    if (!class_exists($controller_class)) {
        http_response_code(500);
        return json_encode([
            'success' => false,
            'error' => 'Controller class not found: ' . $controller_class,
            'code' => 'CONTROLLER_CLASS_NOT_FOUND'
        ]);
    }
    
    $controller = new $controller_class();
    
    // Construire le nom de la méthode
    $method_name = strtolower($method) . ucfirst($action);
    
    // Vérifier si la méthode existe
    if (!method_exists($controller, $method_name)) {
        // Essayer avec la méthode générique
        $generic_method = strtolower($method);
        if (method_exists($controller, $generic_method)) {
            $method_name = $generic_method;
        } else {
            http_response_code(405);
            return json_encode([
                'success' => false,
                'error' => 'Method not allowed: ' . $method . ' ' . $action,
                'code' => 'METHOD_NOT_ALLOWED'
            ]);
        }
    }
    
    // Appeler la méthode du contrôleur
    return $controller->$method_name($data, $id);
}

/**
 * Rate limiting simple
 */
function checkRateLimit($client_ip, $limit_per_hour = 100) {
    $cache_file = ROOT_PATH . 'cache/rate_limit_' . md5($client_ip) . '.json';
    
    // Créer le dossier cache s'il n'existe pas
    if (!file_exists(dirname($cache_file))) {
        mkdir(dirname($cache_file), 0777, true);
    }
    
    $now = time();
    $hour_ago = $now - 3600;
    
    // Lire le cache existant
    $requests = [];
    if (file_exists($cache_file)) {
        $data = file_get_contents($cache_file);
        $requests = json_decode($data, true) ?? [];
    }
    
    // Filtrer les requêtes de la dernière heure
    $requests = array_filter($requests, function($timestamp) use ($hour_ago) {
        return $timestamp > $hour_ago;
    });
    
    // Vérifier la limite
    if (count($requests) >= $limit_per_hour) {
        return false;
    }
    
    // Ajouter la requête actuelle
    $requests[] = $now;
    
    // Sauvegarder le cache
    file_put_contents($cache_file, json_encode($requests));
    
    return true;
}