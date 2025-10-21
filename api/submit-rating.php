<?php
/**
 * TechEssentials Pro - API Submit Rating
 * Enregistre un avis produit avec rating et commentaire
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Configuration
define('TECHESSENTIALS_PRO', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Fonction de nettoyage des données
function cleanInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// Fonction pour obtenir l'IP du client
function getClientIP() {
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
    
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

try {
    // Récupérer les données JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Données JSON invalides');
    }
    
    // Validation des données
    $product_id = cleanInput($data['product_id'] ?? '');
    $rating = (int)($data['rating'] ?? 0);
    $comment = cleanInput($data['comment'] ?? '');
    $user_ip = getClientIP();
    
    // Vérifications
    if (empty($product_id)) {
        throw new Exception('ID produit manquant');
    }
    
    if ($rating < 1 || $rating > 5) {
        throw new Exception('Note invalide (doit être entre 1 et 5)');
    }
    
    // Connexion DB
    $db = getDB('main');
    
    // Vérifier si le produit existe
    $stmt = $db->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Produit introuvable');
    }
    
    // Vérifier si l'utilisateur a déjà noté ce produit (limiter à 1 avis par IP par produit)
    $stmt = $db->prepare("SELECT id FROM product_ratings WHERE product_id = ? AND user_ip = ?");
    $stmt->execute([$product_id, $user_ip]);
    
    if ($stmt->fetch()) {
        throw new Exception('Vous avez déjà évalué ce produit');
    }
    
    // Insérer le rating
    $stmt = $db->prepare("
        INSERT INTO product_ratings (product_id, user_ip, rating, comment, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $success = $stmt->execute([
        $product_id,
        $user_ip,
        $rating,
        $comment ?: null
    ]);
    
    if (!$success) {
        throw new Exception('Erreur lors de l\'enregistrement');
    }
    
    // Calculer la nouvelle moyenne et le nombre total
    $stmt = $db->prepare("
        SELECT 
            ROUND(AVG(rating), 1) as average,
            COUNT(*) as total
        FROM product_ratings 
        WHERE product_id = ?
    ");
    $stmt->execute([$product_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Log de succès
    error_log("Rating ajouté: Produit $product_id, Note $rating, IP $user_ip");
    
    // Réponse de succès
    echo json_encode([
        'success' => true,
        'message' => 'Votre avis a été enregistré avec succès',
        'newAverage' => (float)$stats['average'],
        'totalCount' => (int)$stats['total']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    error_log("Erreur submit rating: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}