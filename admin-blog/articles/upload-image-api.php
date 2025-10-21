<?php
/**
 * API endpoint pour upload d'images via AJAX
 * Fichier : admin-blog/articles/upload-image-api.php
 */

session_start();
header('Content-Type: application/json');

// Vérifier authentification
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Non autorisé']);
    exit;
}

require_once '../includes/config.php';
require_once '../includes/image-optimizer.php';

// Vérifier que c'est une requête POST avec fichier
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Requête invalide']);
    exit;
}

try {
    // Créer l'optimiseur
    $optimizer = new ImageOptimizer();
    
    // Optimiser l'image
    $result = $optimizer->optimize($_FILES['image']);
    
    if ($result['success']) {
        // Enregistrer dans la base de données (optionnel)
        $stmt = $pdo->prepare("
            INSERT INTO blog_images 
            (unique_name, original_name, original_size, optimized_data, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $result['unique_name'],
            $result['original_name'],
            $result['original_size'],
            json_encode($result)
        ]);
        
        $result['image_id'] = $pdo->lastInsertId();
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur : ' . $e->getMessage()
    ]);
}
?>