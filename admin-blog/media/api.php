<?php
// admin-blog/media/api.php - API pour la médiathèque
session_start();

// Vérification auth
if (!isset($_SESSION['blog_admin_logged']) || $_SESSION['blog_admin_logged'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

// Configuration BDD
$DB_CONFIG = [
    'host' => 'localhost',
    'dbname' => 'techessentials_blog',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

// Connexion BDD
try {
    $dsn = "mysql:host={$DB_CONFIG['host']};dbname={$DB_CONFIG['dbname']};charset={$DB_CONFIG['charset']}";
    $blogDB = new PDO($dsn, $DB_CONFIG['username'], $DB_CONFIG['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur BDD: ' . $e->getMessage()]);
    exit;
}

// Configuration upload
$upload_dir = '../../uploads/blog/';
$upload_url = '/techessentialspro/uploads/blog/';
$allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$max_file_size = 5 * 1024 * 1024; // 5MB

// Créer dossier s'il n'existe pas
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Headers JSON
header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'getMedia':
            // Récupérer la liste des médias
            $media = $blogDB->query("
                SELECT id, filename, original_name, mime_type, file_size, width, height, 
                       alt_text, description, created_at, uploaded_by
                FROM media 
                ORDER BY created_at DESC 
                LIMIT 100
            ")->fetchAll();

            echo json_encode([
                'success' => true,
                'media' => $media
            ]);
            break;

        case 'uploadMedia':
        default:
            // Upload de média
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['media_file'])) {
                throw new Exception("Aucun fichier reçu");
            }

            $file = $_FILES['media_file'];
            
            // Vérifications
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Erreur upload: " . $file['error']);
            }
            
            if ($file['size'] > $max_file_size) {
                throw new Exception("Fichier trop volumineux (max 5MB)");
            }
            
            // Vérification type
            $file_info = pathinfo($file['name']);
            $extension = strtolower($file_info['extension']);
            
            if (!in_array($extension, $allowed_types)) {
                throw new Exception("Type non autorisé. Autorisés: " . implode(', ', $allowed_types));
            }
            
            // Vérification image
            $image_info = getimagesize($file['tmp_name']);
            if (!$image_info) {
                throw new Exception("Le fichier n'est pas une image valide");
            }
            
            // Génération nom unique
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $file_path = $upload_dir . $filename;
            
            // Upload
            if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                throw new Exception("Erreur sauvegarde fichier");
            }
            
            // Enregistrement BDD
            $alt_text = trim($_POST['alt_text'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            $stmt = $blogDB->prepare("
                INSERT INTO media (filename, original_name, mime_type, file_size, width, height, alt_text, description, uploaded_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
           $stmt->execute([
               $filename,
               $file['name'],
               $image_info['mime'],
               $file['size'],
               $image_info[0], // width
               $image_info[1], // height
               $alt_text,
               $description,
               1  // ← ID de l'admin par défaut
            ]);
            
            $media_id = $blogDB->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => 'Image uploadée avec succès',
                'media_id' => $media_id,
                'filename' => $filename,
                'url' => $upload_url . $filename
            ]);
            break;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>