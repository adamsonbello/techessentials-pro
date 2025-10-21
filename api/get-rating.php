<?php
/**
 * TechEssentials Pro - API Get Rating
 * Récupère les avis et statistiques d'un produit
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Configuration
define('TECHESSENTIALS_PRO', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
    exit;
}

try {
    // Récupérer l'ID du produit
    $product_id = $_GET['product_id'] ?? '';
    
    if (empty($product_id)) {
        throw new Exception('ID produit manquant');
    }
    
    // Nettoyage
    $product_id = htmlspecialchars(strip_tags(trim($product_id)), ENT_QUOTES, 'UTF-8');
    
    // 🆕 PARAMÈTRES DE PAGINATION
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    
    // Limites de sécurité (entre 1 et 20 avis par requête)
    $limit = min(max($limit, 1), 20);
    $offset = max($offset, 0);
    
    // Connexion DB
    $db = getDB('main');
    
    // Récupérer les statistiques globales (inchangé)
    $stmt = $db->prepare("
        SELECT 
            COALESCE(ROUND(AVG(rating), 1), 0) as average,
            COUNT(*) as total,
            SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as stars_5,
            SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as stars_4,
            SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as stars_3,
            SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as stars_2,
            SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as stars_1
        FROM product_ratings 
        WHERE product_id = ?
    ");
    $stmt->execute([$product_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 🆕 RÉCUPÉRER LES AVIS AVEC PAGINATION
    $stmt = $db->prepare("
        SELECT 
            rating,
            comment,
            DATE_FORMAT(created_at, '%d/%m/%Y') as date,
            TIMESTAMPDIFF(DAY, created_at, NOW()) as days_ago,
            TIMESTAMPDIFF(HOUR, created_at, NOW()) as hours_ago
        FROM product_ratings 
        WHERE product_id = ?
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$product_id, $limit, $offset]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formater les avis (inchangé)
    $formatted_reviews = [];
    foreach ($reviews as $review) {
        // Formater la date relative
        if ($review['days_ago'] == 0) {
            if ($review['hours_ago'] == 0) {
                $time_ago = "À l'instant";
            } elseif ($review['hours_ago'] == 1) {
                $time_ago = "Il y a 1 heure";
            } else {
                $time_ago = "Il y a {$review['hours_ago']} heures";
            }
        } elseif ($review['days_ago'] == 1) {
            $time_ago = "Hier";
        } elseif ($review['days_ago'] < 7) {
            $time_ago = "Il y a {$review['days_ago']} jours";
        } elseif ($review['days_ago'] < 30) {
            $weeks = floor($review['days_ago'] / 7);
            $time_ago = $weeks == 1 ? "Il y a 1 semaine" : "Il y a {$weeks} semaines";
        } else {
            $time_ago = $review['date'];
        }
        
        $formatted_reviews[] = [
            'rating' => (int)$review['rating'],
            'comment' => $review['comment'],
            'date' => $time_ago,
            'hasComment' => !empty($review['comment'])
        ];
    }
    
    // Calculer les pourcentages de distribution (inchangé)
    $total = (int)$stats['total'];
    $distribution = [];
    
    if ($total > 0) {
        for ($i = 5; $i >= 1; $i--) {
            $count = (int)$stats["stars_$i"];
            $distribution[$i] = [
                'count' => $count,
                'percentage' => round(($count / $total) * 100, 1)
            ];
        }
    }
    
    // 🆕 VÉRIFIER S'IL RESTE DES AVIS À CHARGER
    $hasMoreReviews = ($offset + $limit) < $total;
    
    // 🆕 RÉPONSE AVEC INFORMATIONS DE PAGINATION
    echo json_encode([
        'success' => true,
        'average' => (float)$stats['average'],
        'count' => $total,
        'distribution' => $distribution,
        'reviews' => $formatted_reviews,
        'hasReviews' => $total > 0,
        // 🆕 Nouvelles données de pagination
        'pagination' => [
            'currentOffset' => $offset,
            'limit' => $limit,
            'hasMore' => $hasMoreReviews,
            'loaded' => count($formatted_reviews),
            'total' => $total
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    error_log("Erreur get rating: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'average' => 0,
        'count' => 0,
        'reviews' => [],
        'pagination' => [
            'hasMore' => false
        ]
    ]);
}