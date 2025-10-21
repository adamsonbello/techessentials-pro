<?php
// admin-blog/media/api.php - VERSION CORRIGÉE FINALE
ob_start();

error_reporting(0);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['blog_admin_logged']) || $_SESSION['blog_admin_logged'] !== true) {
    ob_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

require_once '../includes/image-optimizer.php';

$DB_CONFIG = [
    'host' => 'localhost',
    'dbname' => 'techessentials_blog',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

try {
    $dsn = "mysql:host={$DB_CONFIG['host']};dbname={$DB_CONFIG['dbname']};charset={$DB_CONFIG['charset']}";
    $blogDB = new PDO($dsn, $DB_CONFIG['username'], $DB_CONFIG['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur BDD']);
    exit;
}

$upload_dir = '../../uploads/blog/';
$upload_url = '/TechEssentialsPro/uploads/blog/';
$allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$max_file_size = 10 * 1024 * 1024;

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

ob_clean();
header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'getMedia':
            $media = $blogDB->query("
                SELECT id, filename, original_name, mime_type, file_size, width, height, 
                       alt_text, description, created_at, uploaded_by
                FROM media 
                ORDER BY created_at DESC 
                LIMIT 100
            ")->fetchAll();

            echo json_encode(['success' => true, 'media' => $media]);
            break;

        case 'uploadMedia':
        default:
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['media_file'])) {
                throw new Exception("Aucun fichier reçu");
            }

            $file = $_FILES['media_file'];
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Erreur upload: " . $file['error']);
            }
            
            if ($file['size'] > $max_file_size) {
                throw new Exception("Fichier trop volumineux (max 10MB)");
            }
            
            $file_info = pathinfo($file['name']);
            $extension = strtolower($file_info['extension']);
            
            if (!in_array($extension, $allowed_types)) {
                throw new Exception("Type non autorisé");
            }
            
            $image_info = getimagesize($file['tmp_name']);
            if (!$image_info) {
                throw new Exception("Le fichier n'est pas une image valide");
            }
            
            // OPTIMISATION
            $optimizer = new ImageOptimizer();
            $optimization_result = $optimizer->optimize($file);
            
            if (!$optimization_result['success']) {
                throw new Exception("Erreur optimisation");
            }
            
            // DEBUG
            file_put_contents('../../debug_result.txt', print_r($optimization_result, true));
            
            // Trouver le fichier optimisé - ORDRE DE PRIORITÉ
            $optimized_jpeg_path = null;
            $size_used = null;
            $priority_order = ['medium', 'full', 'large', 'thumbnail'];
            
            foreach ($priority_order as $size) {
                if (isset($optimization_result['urls'][$size]['jpeg'])) {
                    $optimized_jpeg_url = $optimization_result['urls'][$size]['jpeg'];
                    
                    // Construire le chemin en normalisant les slashes pour Windows
                    $optimized_jpeg_path = $_SERVER['DOCUMENT_ROOT'] . $optimized_jpeg_url;
                    $optimized_jpeg_path = str_replace('/', DIRECTORY_SEPARATOR, $optimized_jpeg_path);
                    
                    $size_used = $size;
                    break;
                }
            }
            
            if (!$optimized_jpeg_path) {
                throw new Exception("Aucune version optimisée trouvée dans le résultat");
            }
            
            if (!file_exists($optimized_jpeg_path)) {
                throw new Exception("Fichier introuvable: " . $optimized_jpeg_path);
            }
            
            if (is_dir($optimized_jpeg_path)) {
                throw new Exception("Le chemin est un dossier: " . $optimized_jpeg_path);
            }
            
            // Copier
            $dest_filename = $optimization_result['unique_name'] . '.jpg';
            $dest_path = $upload_dir . $dest_filename;
            
            if (!copy($optimized_jpeg_path, $dest_path)) {
                throw new Exception("Erreur copie de " . $optimized_jpeg_path . " vers " . $dest_path);
            }
            
            // BDD
            $alt_text = trim($_POST['alt_text'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $caption = trim($_POST['caption'] ?? '');
            
            $optimized_info = $optimization_result['optimized_sizes'][$size_used] ?? [];
            $final_size = filesize($dest_path);
            $final_image_info = getimagesize($dest_path);
            
            // INSERTION 1 : Table media
            $stmt = $blogDB->prepare("
                INSERT INTO media (
                    filename, original_name, file_path, file_type, mime_type, file_size, 
                    width, height, alt_text, description, caption,
                    uploaded_by, created_at
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $dest_filename,
                $file['name'],
                $upload_url . $dest_filename,
                'image',
                'image/jpeg',
                $final_size,
                $optimized_info['width'] ?? $final_image_info[0],
                $optimized_info['height'] ?? $final_image_info[1],
                $alt_text,
                $description,
                $caption,
                1
            ]);
            
            $media_id = $blogDB->lastInsertId();
            
            // INSERTION 2 : Table blog_images pour tracking optimisation
            $stmt = $blogDB->prepare("
                INSERT INTO blog_images (
                    unique_name, original_name, original_size, 
                    optimized_data, alt_text, created_by, 
                    article_id, used_in_article, 
                    created_at, updated_at
                )
                VALUES (?, ?, ?, ?, ?, ?, NULL, 0, NOW(), NOW())
            ");
            
            $stmt->execute([
                $optimization_result['unique_name'],
                $optimization_result['original_name'],
                $optimization_result['original_size'],
                json_encode($optimization_result),
                $alt_text,
                'admin@techessentialspro.com'
            ]);
            
            $blog_image_id = $blogDB->lastInsertId();
            
            // RÉPONSE JSON UNIQUE
            echo json_encode([
                'success' => true,
                'message' => '✅ Image uploadée et optimisée',
                'media_id' => $media_id,
                'blog_image_id' => $blog_image_id,
                'filename' => $dest_filename,
                'url' => $upload_url . $dest_filename,
                'optimization' => [
                    'size_used' => $size_used,
                    'original_size' => number_format($optimization_result['original_size'] / 1024, 2) . ' KB',
                    'optimized_size' => number_format($final_size / 1024, 2) . ' KB',
                    'saving_percent' => $optimization_result['total_saving_percent'] . '%',
                    'versions_created' => array_keys($optimization_result['optimized_sizes'])
                ]
            ]);
            break;
    }

} catch (Exception $e) {
    ob_clean();
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}