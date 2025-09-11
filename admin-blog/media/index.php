<?php
// admin-blog/media/index.php - Médiathèque complète
session_start();

// Vérification auth
if (!isset($_SESSION['blog_admin_logged']) || $_SESSION['blog_admin_logged'] !== true) {
    header('Location: ../index.php');
    exit;
}

// Configuration
$DB_CONFIG = [
    'host' => 'localhost',
    'dbname' => 'techessentials_blog',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

$UPLOAD_DIR = '../uploads/';
$UPLOAD_URL = 'uploads/';

// Créer le dossier d'upload si nécessaire
if (!is_dir($UPLOAD_DIR)) {
    mkdir($UPLOAD_DIR, 0755, true);
}

// Connexion BDD
try {
    $dsn = "mysql:host={$DB_CONFIG['host']};dbname={$DB_CONFIG['dbname']};charset={$DB_CONFIG['charset']}";
    $pdo = new PDO($dsn, $DB_CONFIG['username'], $DB_CONFIG['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Erreur BDD: " . $e->getMessage());
}

$message = '';
$error = '';

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'delete_media':
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) throw new Exception("ID invalide");
                
                // Récupérer les infos du fichier
                $stmt = $pdo->prepare("SELECT filename FROM media WHERE id = ?");
                $stmt->execute([$id]);
                $media = $stmt->fetch();
                
                if (!$media) throw new Exception("Média non trouvé");
                
                // Supprimer le fichier physique
                $file_path = $UPLOAD_DIR . $media['filename'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                
                // Supprimer de la BDD
                $stmt = $pdo->prepare("DELETE FROM media WHERE id = ?");
                $stmt->execute([$id]);
                
                $message = "Média supprimé avec succès";
                break;
                
            case 'update_media':
                $id = (int)($_POST['id'] ?? 0);
                $alt_text = trim($_POST['alt_text'] ?? '');
                $description = trim($_POST['description'] ?? '');
                
                if ($id <= 0) throw new Exception("ID invalide");
                
                $stmt = $pdo->prepare("
                    UPDATE media 
                    SET alt_text = ?, description = ?
                    WHERE id = ?
                ");
                $stmt->execute([$alt_text, $description, $id]);
                
                $message = "Média mis à jour";
                break;
                
            case 'bulk_delete':
                $media_ids = $_POST['media_ids'] ?? [];
                if (empty($media_ids)) throw new Exception("Aucun média sélectionné");
                
                $placeholders = str_repeat('?,', count($media_ids) - 1) . '?';
                
                // Récupérer les noms de fichiers
                $stmt = $pdo->prepare("SELECT filename FROM media WHERE id IN ($placeholders)");
                $stmt->execute($media_ids);
                $files = $stmt->fetchAll();
                
                // Supprimer les fichiers physiques
                foreach ($files as $file) {
                    $file_path = $UPLOAD_DIR . $file['filename'];
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
                
                // Supprimer de la BDD
                $stmt = $pdo->prepare("DELETE FROM media WHERE id IN ($placeholders)");
                $stmt->execute($media_ids);
                
                $message = count($media_ids) . " média(s) supprimé(s)";
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Filtres et pagination
$search = trim($_GET['search'] ?? '');
$type_filter = $_GET['type'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 24;
$offset = ($page - 1) * $per_page;

// Construction de la requête
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(original_name LIKE ? OR alt_text LIKE ? OR description LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($type_filter)) {
    $where_conditions[] = "mime_type LIKE ?";
    $params[] = $type_filter . '%';
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Récupération des médias
$media_query = "
    SELECT * FROM media
    {$where_clause}
    ORDER BY created_at DESC
    LIMIT {$per_page} OFFSET {$offset}
";

$media_stmt = $pdo->prepare($media_query);
$media_stmt->execute($params);
$media_items = $media_stmt->fetchAll();

// Comptage total
$count_query = "SELECT COUNT(*) FROM media {$where_clause}";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_media = $count_stmt->fetchColumn();
$total_pages = ceil($total_media / $per_page);

// Statistiques
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN mime_type LIKE 'image/%' THEN 1 END) as images,
        COUNT(CASE WHEN mime_type LIKE 'video/%' THEN 1 END) as videos,
        COUNT(CASE WHEN mime_type LIKE 'application/%' THEN 1 END) as documents,
        ROUND(SUM(file_size)/1024/1024, 2) as total_size_mb
    FROM media
")->fetch();

$admin_user = $_SESSION['blog_admin_user'] ?? 'Admin';

// Fonction pour formater la taille
function formatFileSize($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
    return round($bytes / 1073741824, 1) . ' GB';
}

// Fonction pour obtenir l'icône du type de fichier
function getFileTypeIcon($mime_type) {
    if (strpos($mime_type, 'image/') === 0) return '🖼️';
    if (strpos($mime_type, 'video/') === 0) return '🎥';
    if (strpos($mime_type, 'audio/') === 0) return '🎵';
    if (strpos($mime_type, 'application/pdf') === 0) return '📄';
    return '📎';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Médiathèque - Blog Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #4CAF50;
            --warning-color: #ff9800;
            --error-color: #f44336;
            --info-color: #2196F3;
            --text-color: #333;
            --text-light: #666;
            --border-color: #e1e5e9;
            --background-light: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background-light);
            color: var(--text-color);
        }

        .header {
            background: var(--white);
            padding: 15px 30px;
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .back-btn {
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: var(--secondary-color);
        }

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .controls-section {
            background: var(--white);
            padding: 25px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 25px;
        }

        .controls-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .controls-left {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            min-width: 300px;
        }

        .search-input {
            width: 100%;
            padding: 10px 40px 10px 15px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: var(--text-light);
        }

        .filter-select {
            padding: 10px 15px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            background: var(--white);
            cursor: pointer;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .upload-btn {
            background: var(--success-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .upload-btn:hover {
            background: #45a049;
        }

        .bulk-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-danger {
            background: var(--error-color);
            color: white;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .media-item {
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            position: relative;
        }

        .media-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .media-item.selected {
            border: 3px solid var(--primary-color);
        }

        .media-preview {
            width: 100%;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--background-light);
            position: relative;
            overflow: hidden;
        }

        .media-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .media-preview .file-icon {
            font-size: 3rem;
            color: var(--text-light);
        }

        .media-checkbox {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 2;
            transform: scale(1.2);
        }

        .media-info {
            padding: 15px;
        }

        .media-name {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 0.9rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .media-meta {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-bottom: 10px;
        }

        .media-actions {
            display: flex;
            gap: 5px;
            justify-content: center;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            text-decoration: none;
            color: var(--text-color);
        }

        .pagination a:hover {
            background: var(--primary-color);
            color: white;
        }

        .pagination .current {
            background: var(--primary-color);
            color: white;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .message.success {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .message.error {
            background: rgba(244, 67, 54, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--text-light);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: var(--white);
            margin: 5% auto;
            padding: 0;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-close {
            color: white;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            background: none;
        }

        .modal-body {
            padding: 30px;
            overflow-y: auto;
            flex: 1;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .preview-image {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
            margin-bottom: 20px;
            border-radius: 6px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .controls-top {
                flex-direction: column;
                align-items: stretch;
            }

            .controls-left {
                justify-content: center;
            }

            .search-box {
                min-width: 100%;
            }

            .media-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 15px;
            }

            .bulk-controls {
                justify-content: center;
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a href="../dashboard.php" class="back-btn">← Dashboard</a>
            <h1>📁 Médiathèque</h1>
        </div>
        <div>Connecté: <?php echo htmlspecialchars($admin_user); ?></div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="stats-section">
            <div class="stat-card">
                <div class="stat-number" style="color: var(--info-color);"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total fichiers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: var(--success-color);"><?php echo $stats['images']; ?></div>
                <div class="stat-label">Images</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: var(--warning-color);"><?php echo $stats['videos']; ?></div>
                <div class="stat-label">Vidéos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: var(--error-color);"><?php echo $stats['total_size_mb']; ?> MB</div>
                <div class="stat-label">Espace utilisé</div>
            </div>
        </div>

        <!-- Contrôles -->
        <div class="controls-section">
            <div class="controls-top">
                <div class="controls-left">
                    <!-- Recherche -->
                    <form method="GET" class="search-box">
                        <input type="text" 
                               name="search" 
                               class="search-input" 
                               placeholder="Rechercher dans la médiathèque..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="search-btn">🔍</button>
                        <?php if (!empty($type_filter)): ?>
                            <input type="hidden" name="type" value="<?php echo htmlspecialchars($type_filter); ?>">
                        <?php endif; ?>
                    </form>

                    <!-- Filtre par type -->
                    <form method="GET">
                        <select name="type" class="filter-select" onchange="this.form.submit()">
                            <option value="">Tous les types</option>
                            <option value="image" <?php echo $type_filter === 'image' ? 'selected' : ''; ?>>🖼️ Images</option>
                            <option value="video" <?php echo $type_filter === 'video' ? 'selected' : ''; ?>>🎥 Vidéos</option>
                            <option value="application" <?php echo $type_filter === 'application' ? 'selected' : ''; ?>>📄 Documents</option>
                        </select>
                        <?php if (!empty($search)): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>
                    </form>
                </div>

                <a href="upload.php" class="upload-btn">📤 Nouveau fichier</a>
            </div>

            <!-- Actions groupées -->
            <div class="bulk-controls">
                <form method="POST" style="display: flex; align-items: center; gap: 10px;">
                    <input type="hidden" name="action" value="bulk_delete">
                    <button type="button" id="select-all" class="btn btn-primary btn-small">
                        ☑️ Tout sélectionner
                    </button>
                    <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Supprimer les médias sélectionnés ?')">
                        🗑️ Supprimer sélection
                    </button>
                    <span id="selection-count" style="color: var(--text-light); font-size: 0.9rem;"></span>
                </form>
            </div>
        </div>

        <!-- Grille des médias -->
        <?php if (empty($media_items)): ?>
            <div class="empty-state">
                <div class="empty-icon">📁</div>
                <h3>Aucun média trouvé</h3>
                <p>
                    <?php if (!empty($search) || !empty($type_filter)): ?>
                        Aucun fichier ne correspond à vos critères de recherche.
                    <?php else: ?>
                        Uploadez votre premier fichier pour commencer.
                    <?php endif; ?>
                </p>
                <?php if (!empty($search) || !empty($type_filter)): ?>
                    <br>
                    <a href="?" style="color: var(--primary-color); text-decoration: none;">
                        ← Voir tous les médias
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="media-grid">
                <?php foreach ($media_items as $media): ?>
                    <div class="media-item" data-id="<?php echo $media['id']; ?>">
                        <input type="checkbox" name="media_ids[]" value="<?php echo $media['id']; ?>" class="media-checkbox">
                        
                        <div class="media-preview">
                            <?php if (strpos($media['mime_type'], 'image/') === 0): ?>
                                <img src="<?php echo htmlspecialchars($UPLOAD_URL . $media['filename']); ?>" 
                                     alt="<?php echo htmlspecialchars($media['alt_text']); ?>"
                                     onclick="openPreview(<?php echo htmlspecialchars(json_encode($media)); ?>)">
                            <?php else: ?>
                                <div class="file-icon" onclick="openPreview(<?php echo htmlspecialchars(json_encode($media)); ?>)">
                                    <?php echo getFileTypeIcon($media['mime_type']); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="media-info">
                            <div class="media-name" title="<?php echo htmlspecialchars($media['original_name']); ?>">
                                <?php echo htmlspecialchars($media['original_name']); ?>
                            </div>
                            <div class="media-meta">
                                <?php echo formatFileSize($media['file_size']); ?> • 
                                <?php echo date('d/m/Y', strtotime($media['created_at'])); ?>
                                <?php if ($media['width'] && $media['height']): ?>
                                    <br><?php echo $media['width']; ?>×<?php echo $media['height']; ?>px
                                <?php endif; ?>
                            </div>
                            <div class="media-actions">
                                <button class="btn btn-primary btn-small" onclick="editMedia(<?php echo htmlspecialchars(json_encode($media)); ?>)">
                                    ✏️ Modifier
                                </button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete_media">
                                    <input type="hidden" name="id" value="<?php echo $media['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-small" 
                                            onclick="return confirm('Supprimer ce média ?')">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type_filter); ?>">
                            ← Précédent
                        </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type_filter); ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type_filter); ?>">
                            Suivant →
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Modal de prévisualisation -->
    <div id="previewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📷 Prévisualisation</h3>
                <button class="modal-close" onclick="closeModal('previewModal')">&times;</button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Contenu dynamique -->
            </div>
        </div>
    </div>

    <!-- Modal d'édition -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Modifier le média</h3>
                <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="editForm">
                    <input type="hidden" name="action" value="update_media">
                    <input type="hidden" name="id" id="edit_media_id">
                    
                    <div class="form-group">
                        <label>Nom du fichier</label>
                        <input type="text" id="edit_filename" disabled style="background: #f0f0f0;">
                    </div>

                    <div class="form-group">
                        <label>Texte alternatif (ALT)</label>
                        <input type="text" name="alt_text" id="edit_alt_text" placeholder="Description de l'image pour l'accessibilité">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="edit_description" rows="3" placeholder="Description du média"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">💾 Sauvegarder</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Gestion de la sélection multiple
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllBtn = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.media-checkbox');
            const selectionCount = document.getElementById('selection-count');
            
            function updateSelectionCount() {
                const selectedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
                if (selectedCount > 0) {
                    selectionCount.textContent = `${selectedCount} média(s) sélectionné(s)`;
                    selectionCount.style.color = 'var(--primary-color)';
                } else {
                    selectionCount.textContent = '';
                }
            }
            
            selectAllBtn.addEventListener('click', function() {
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                checkboxes.forEach(cb => {
                    cb.checked = !allChecked;
                    cb.closest('.media-item').classList.toggle('selected', cb.checked);
                });
                updateSelectionCount();
            });
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    this.closest('.media-item').classList.toggle('selected', this.checked);
                    updateSelectionCount();
                });
            });
            
            // Mise à jour du formulaire de suppression groupée
            const bulkForm = document.querySelector('form[method="POST"]');
            if (bulkForm) {
                bulkForm.addEventListener('submit', function(e) {
                    const selectedCheckboxes = Array.from(checkboxes).filter(cb => cb.checked);
                    if (selectedCheckboxes.length === 0) {
                        e.preventDefault();
                        alert('Veuillez sélectionner au moins un média');
                        return;
                    }
                    
                    // Ajouter les IDs sélectionnés au formulaire
                    selectedCheckboxes.forEach(cb => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'media_ids[]';
                        input.value = cb.value;
                        this.appendChild(input);
                    });
                });
            }
        });

        // Modal de prévisualisation
        function openPreview(media) {
            const modal = document.getElementById('previewModal');
            const content = document.getElementById('previewContent');
            
            let html = '';
            
            if (media.mime_type.startsWith('image/')) {
                html = `
                    <img src="${media.filename}" alt="${media.alt_text || ''}" class="preview-image">
                `;
            } else {
                html = `
                    <div style="text-align: center; padding: 40px;">
                        <div style="font-size: 4rem; margin-bottom: 20px;">
                            ${getFileTypeIcon(media.mime_type)}
                        </div>
                        <h3>${media.original_name}</h3>
                        <p>Type: ${media.mime_type}</p>
                        <p>Taille: ${formatFileSize(media.file_size)}</p>
                        <br>
                        <a href="${media.filename}" download="${media.original_name}" class="btn btn-primary">
                            📥 Télécharger
                        </a>
                    </div>
                `;
            }
            
            html += `
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                    <p><strong>Nom:</strong> ${media.original_name}</p>
                    <p><strong>Taille:</strong> ${formatFileSize(media.file_size)}</p>
                    <p><strong>Type:</strong> ${media.mime_type}</p>
                    ${media.width && media.height ? `<p><strong>Dimensions:</strong> ${media.width}×${media.height}px</p>` : ''}
                    <p><strong>Uploadé le:</strong> ${new Date(media.created_at).toLocaleDateString('fr-FR')}</p>
                    ${media.alt_text ? `<p><strong>Alt:</strong> ${media.alt_text}</p>` : ''}
                    ${media.description ? `<p><strong>Description:</strong> ${media.description}</p>` : ''}
                </div>
            `;
            
            content.innerHTML = html;
            modal.style.display = 'block';
        }

        // Modal d'édition
        function editMedia(media) {
            document.getElementById('edit_media_id').value = media.id;
            document.getElementById('edit_filename').value = media.original_name;
            document.getElementById('edit_alt_text').value = media.alt_text || '';
            document.getElementById('edit_description').value = media.description || '';
            
            document.getElementById('editModal').style.display = 'block';
        }

        // Fermeture des modals
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Fermeture en cliquant à l'extérieur
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Fonctions utilitaires (côté client)
        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return Math.round(bytes / 1024 * 10) / 10 + ' KB';
            if (bytes < 1073741824) return Math.round(bytes / 1048576 * 10) / 10 + ' MB';
            return Math.round(bytes / 1073741824 * 10) / 10 + ' GB';
        }

        function getFileTypeIcon(mimeType) {
            if (mimeType.includes('image/')) return '🖼️';
            if (mimeType.includes('video/')) return '🎥';
            if (mimeType.includes('audio/')) return '🎵';
            if (mimeType.includes('pdf')) return '📄';
            return '📎';
        }
    </script>
</body>
</html>