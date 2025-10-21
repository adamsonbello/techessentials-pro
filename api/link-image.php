<?php
// Lier une image à un article
session_start();

if (!isset($_SESSION['blog_admin_logged'])) {
    http_response_code(401);
    exit;
}

require_once '../includes/db.php';

$article_id = $_POST['article_id'] ?? null;
$media_id = $_POST['media_id'] ?? null;

if (!$article_id || !$media_id) {
    echo json_encode(['success' => false, 'error' => 'Paramètres manquants']);
    exit;
}

try {
    $stmt = $blogDB->prepare("
        INSERT INTO blog_articles_images (article_id, image_id, created_at)
        VALUES (?, ?, NOW())
        ON DUPLICATE KEY UPDATE created_at = NOW()
    ");
    
    $stmt->execute([$article_id, $media_id]);
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}