<?php
// admin-blog/articles/editor.php - Éditeur unifié (création + édition)
session_start();

// Vérification auth
if (!isset($_SESSION['blog_admin_logged']) || $_SESSION['blog_admin_logged'] !== true) {
    header('Location: ../index.php');
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
    die("Erreur connexion BDD: " . $e->getMessage());
}

// Déterminer le mode (création ou édition)
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$article = null;
$selected_tags = [];
$mode = 'create'; // Par défaut création

// Si ID fourni, charger l'article pour édition
if ($article_id) {
    $stmt = $blogDB->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();
    
    if (!$article) {
        header('Location: index.php');
        exit;
    }
    
    $mode = 'edit';
    
    // Charger les tags de l'article
    $stmt = $blogDB->prepare("SELECT tag_id FROM article_tags WHERE article_id = ?");
    $stmt->execute([$article_id]);
    $selected_tags = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Récupérer catégories et tags
$categories = $blogDB->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name")->fetchAll();
$tags = $blogDB->query("SELECT * FROM tags ORDER BY usage_count DESC, name")->fetchAll();

$message = '';
$error = '';

// Traitement du formulaire (création ou mise à jour)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Auto-save AJAX
    if (isset($_POST['autosave'])) {
        header('Content-Type: application/json');
        
        try {
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $excerpt = trim($_POST['excerpt'] ?? '');
            
            if ($article_id) {
                $stmt = $blogDB->prepare("
                    UPDATE articles 
                    SET title = ?, content = ?, excerpt = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute([$title, $content, $excerpt, $article_id]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Auto-saved']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
    
    // Sauvegarde normale
    if (isset($_POST['save_article'])) {
        try {
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            $excerpt = trim($_POST['excerpt'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $featured_image = trim($_POST['featured_image'] ?? '');  // ← AJOUTER ICI
            if (empty($featured_image) && !empty($content)) {
                if (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $match)) {
                        $featured_image = $match[1];
                    }
            }

            

            $category_id = (int)($_POST['category_id'] ?? 0);
            $meta_title = trim($_POST['meta_title'] ?? '');
            $meta_description = trim($_POST['meta_description'] ?? '');
            $status = $_POST['status'] ?? 'draft';
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $read_time = (int)($_POST['read_time'] ?? 5);
            $post_tags = $_POST['tags'] ?? [];
            
            // Validation
            if (empty($title)) throw new Exception("Le titre est requis");
            if (empty($content)) throw new Exception("Le contenu est requis");
            if ($category_id <= 0) throw new Exception("Une catégorie est requise");
            
            // Génération auto du slug si vide
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
            }
            
            // Vérifier unicité du slug
            $stmt = $blogDB->prepare("SELECT id FROM articles WHERE slug = ? AND id != ?");
            $stmt->execute([$slug, $article_id ?: 0]);
            if ($stmt->fetch()) {
                $slug = $slug . '-' . time();
            }
            
            // Début transaction
            $blogDB->beginTransaction();
            
            if ($mode === 'edit') {
            // UPDATE article existant
               $stmt = $blogDB->prepare("
            UPDATE articles SET
            title = ?, slug = ?, excerpt = ?, content = ?, featured_image = ?,
            category_id = ?, meta_title = ?, meta_description = ?,
            status = ?, is_featured = ?, read_time_minutes = ?,
            updated_at = NOW()
            " . ($status === 'published' && $article['status'] !== 'published' ? ", published_at = NOW()" : "") . "
            WHERE id = ?
           ");
    
            $stmt->execute([
            $title, $slug, $excerpt, $content, $featured_image,
            $category_id, $meta_title, $meta_description, $status, $is_featured, $read_time,
            $article_id
           ]);
                
                // Supprimer les anciens tags
                $stmt = $blogDB->prepare("DELETE FROM article_tags WHERE article_id = ?");
                $stmt->execute([$article_id]);
                
                $message = "Article mis à jour avec succès !";
                
          } else {
                // INSERT nouvel article
                 $stmt = $blogDB->prepare("
                INSERT INTO articles (
                title, slug, excerpt, content, featured_image, category_id, author_name, author_email,
                meta_title, meta_description, status, is_featured, read_time_minutes,
                published_at, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
           ");
    
              $published_at = ($status === 'published') ? date('Y-m-d H:i:s') : null;
              $author_name = $_SESSION['blog_admin_user'] ?? 'Admin';
              $author_email = 'admin@techessentialspro.com';
    
              $stmt->execute([
              $title, $slug, $excerpt, $content, $featured_image, $category_id, $author_name, $author_email,
              $meta_title, $meta_description, $status, $is_featured, $read_time,
              $published_at
            ]);
    
             $article_id = $blogDB->lastInsertId();
             $message = "Article créé avec succès ! ID: " . $article_id;
          }
            
            // Gestion des tags (pour les deux modes)
            if (!empty($post_tags)) {
                $stmt = $blogDB->prepare("INSERT IGNORE INTO article_tags (article_id, tag_id) VALUES (?, ?)");
                foreach ($post_tags as $tag_id) {
                    $stmt->execute([$article_id, (int)$tag_id]);
                }
                
                // Mettre à jour le compteur d'usage des tags
                $blogDB->exec("
                    UPDATE tags t 
                    SET usage_count = (
                        SELECT COUNT(*) FROM article_tags WHERE tag_id = t.id
                    )
                ");
            }
            
           $blogDB->commit();
$_SESSION['success_message'] = "Article sauvegardé avec succès";

if ($status === 'published') {
    $_SESSION['success_message'] .= " (Article publié)";
}

// AJOUT : Redirection si "Sauvegarder et Fermer"
if (isset($_POST['save_and_close']) && $_POST['save_and_close'] == '1') {
    header('Location: list.php?message=' . urlencode($_SESSION['success_message']));
    exit;
}

// Si création, passer en mode édition
if ($mode === 'create') {
    header('Location: editor.php?id=' . $article_id . '&message=' . urlencode($_SESSION['success_message']));
    exit;
}

// Rester sur l'éditeur (mode edit)
header('Location: editor.php?id=' . $article_id . '&message=' . urlencode($_SESSION['success_message']));
exit;

} catch (Exception $e) {
    $blogDB->rollBack();
    $error_message = $e->getMessage();
}
            
            if ($status === 'published') {
                $message .= " (Article publié)";
            }
            
            // Si save_and_close, rediriger vers la liste
            if (isset($_POST['save_and_close'])) {
                header('Location: index.php?message=' . urlencode($message));
                exit;
            }
            
            // Si création, passer en mode édition
            if ($mode === 'create') {
                header('Location: editor.php?id=' . $article_id . '&message=' . urlencode($message));
                exit;
            }
            
      
    }
}

// Préparer les valeurs pour le formulaire
$form_data = [
    'title' => $article['title'] ?? ($_POST['title'] ?? ''),
    'slug' => $article['slug'] ?? ($_POST['slug'] ?? ''),
    'excerpt' => $article['excerpt'] ?? ($_POST['excerpt'] ?? ''),
    'content' => $article['content'] ?? ($_POST['content'] ?? ''),
    'category_id' => $article['category_id'] ?? ($_POST['category_id'] ?? 0),
    'meta_title' => $article['meta_title'] ?? ($_POST['meta_title'] ?? ''),
    'meta_description' => $article['meta_description'] ?? ($_POST['meta_description'] ?? ''),
    'status' => $article['status'] ?? ($_POST['status'] ?? 'draft'),
    'is_featured' => $article['is_featured'] ?? (isset($_POST['is_featured']) ? 1 : 0),
    'read_time' => $article['read_time_minutes'] ?? ($_POST['read_time'] ?? 5),
    'tags' => !empty($_POST['tags']) ? $_POST['tags'] : $selected_tags
];

$admin_user = $_SESSION['blog_admin_user'] ?? 'Admin';
$page_title = $mode === 'edit' ? 'Éditer l\'article' : 'Nouvel Article';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Blog Admin</title>
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
            line-height: 1.6;
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
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
        }

        .header h1 {
            color: var(--text-color);
            font-size: 1.5rem;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-published {
            background: #c6f6d5;
            color: #22543d;
        }

        .status-draft {
            background: #fed7d7;
            color: #742a2a;
        }

        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 30px;
        }

        .main-editor {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .editor-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 30px;
        }

        .editor-header h2 {
            font-size: 1.3rem;
            margin-bottom: 5px;
        }

        .editor-content {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .form-group textarea {
            min-height: 400px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* Editor Toolbar */
        .editor-toolbar {
            display: flex;
            gap: 8px;
            margin-bottom: 10px;
            padding: 10px;
            background: var(--background-light);
            border-radius: 6px;
            flex-wrap: wrap;
        }

        .toolbar-btn {
            padding: 6px 12px;
            border: 1px solid var(--border-color);
            background: var(--white);
            color: var(--text-color);
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .toolbar-btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* Media Library Modal */
        .media-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
        }

        .media-modal-content {
            background-color: var(--white);
            margin: 2% auto;
            border-radius: 12px;
            width: 90%;
            max-width: 1000px;
            max-height: 90%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .media-modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .media-modal-close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            background: none;
        }

        .media-modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .media-upload-zone {
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .media-upload-zone:hover,
        .media-upload-zone.dragover {
            border-color: var(--primary-color);
            background: rgba(102, 126, 234, 0.05);
        }

        .media-grid-modal {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .media-item-modal {
            border: 2px solid transparent;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .media-item-modal:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .media-item-modal.selected {
            border-color: var(--success-color);
            background: rgba(76, 175, 80, 0.1);
        }

        .media-preview-modal {
            width: 100%;
            height: 120px;
            background: var(--background-light);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .media-preview-modal img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .media-info-modal {
            padding: 10px;
            font-size: 0.8rem;
        }

        .media-name-modal {
            font-weight: 600;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .media-actions {
            display: flex;
            gap: 10px;
            padding: 20px;
            border-top: 1px solid var(--border-color);
            justify-content: space-between;
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sidebar-section {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .sidebar-header {
            background: var(--background-light);
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-header h3 {
            font-size: 1rem;
            color: var(--text-color);
        }

        .sidebar-content {
            padding: 20px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
        }

        .tags-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .tag-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: var(--background-light);
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .tag-item input[type="checkbox"] {
            width: auto;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-secondary {
            background: var(--text-light);
            color: white;
        }

        .actions {
            display: flex;
            gap: 15px;
            padding: 20px;
            background: var(--background-light);
            border-top: 1px solid var(--border-color);
        }

        .message {
            padding: 15px 20px;
            border-radius: 8px;
            margin: 20px 30px;
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

        .help-text {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-top: 5px;
        }

        .autosave-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 8px;
            background: #48bb78;
            color: white;
            z-index: 1000;
            display: none;
        }

        /* Article info pour mode édition */
        .article-info {
            background: rgba(102, 126, 234, 0.1);
            border: 1px solid rgba(102, 126, 234, 0.3);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .article-info p {
            margin: 5px 0;
            font-size: 0.9rem;
            color: var(--text-color);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .container {
                padding: 0 15px;
            }

            .editor-content {
                padding: 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }
        }

        
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a href="index.php" class="back-btn">← Retour aux articles</a>
            <h1><?= $page_title ?></h1>
            <?php if ($mode === 'edit'): ?>
                <span class="status-badge status-<?= $article['status'] ?>"><?= $article['status'] ?></span>
            <?php endif; ?>
        </div>
        <div>Connecté: <?= htmlspecialchars($admin_user) ?></div>
    </div>

    <div class="container">
        <div class="main-editor">
            <div class="editor-header">
                <h2><?= $mode === 'edit' ? '✏️ Modifier l\'article' : '✏️ Créer un nouvel article' ?></h2>
                <p><?= $mode === 'edit' ? 'Modifiez et mettez à jour votre contenu' : 'Rédigez et publiez votre contenu sur TechEssentials Pro' ?></p>
            </div>

            <?php if ($message || isset($_GET['message'])): ?>
                <div class="message success"><?= htmlspecialchars($message ?: $_GET['message']) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="editor-content" id="articleForm">
                <?php if ($mode === 'edit'): ?>
                <div class="article-info">
                    <p><strong>📅 Créé le:</strong> <?= date('d/m/Y à H:i', strtotime($article['created_at'])) ?></p>
                    <?php if ($article['updated_at']): ?>
                    <p><strong>🔄 Dernière modification:</strong> <?= date('d/m/Y à H:i', strtotime($article['updated_at'])) ?></p>
                    <?php endif; ?>
                    <?php if ($article['published_at']): ?>
                    <p><strong>📤 Publié le:</strong> <?= date('d/m/Y à H:i', strtotime($article['published_at'])) ?></p>
                    <?php endif; ?>
                    <p><strong>👁️ Vues:</strong> <?= number_format($article['views'] ?? 0) ?></p>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="title">📝 Titre de l'article *</label>
                    <input type="text" id="title" name="title" required placeholder="Ex: Test complet iPhone 16 : Notre verdict" value="<?= htmlspecialchars($form_data['title']) ?>">
                    <div class="help-text">Le titre principal qui apparaîtra sur votre site</div>
                </div>

                <div class="form-group">
                    <label for="slug">🔗 URL (slug)</label>
                    <input type="text" id="slug" name="slug" placeholder="test-iphone-16-verdict" value="<?= htmlspecialchars($form_data['slug']) ?>">
                    <div class="help-text">Laisser vide pour génération automatique</div>
                </div>

                <div class="form-group">
                    <label for="excerpt">📄 Extrait</label>
                    <textarea id="excerpt" name="excerpt" rows="3" placeholder="Résumé de l'article qui apparaîtra dans les listes et partages..."><?= htmlspecialchars($form_data['excerpt']) ?></textarea>
                    <div class="help-text">Résumé affiché sur la page d'accueil et réseaux sociaux</div>
                </div>

                <div class="form-group">
                    <label for="content">📖 Contenu de l'article *</label>
                    <div class="editor-toolbar">
                        <button type="button" class="toolbar-btn" onclick="openMediaLibrary()">
                            🖼️ Insérer Image
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertHTML('<h2>', '</h2>')">
                            H2
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertHTML('<strong>', '</strong>')">
                            <strong>B</strong>
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertHTML('<em>', '</em>')">
                            <em>I</em>
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertHTML('<ul><li>', '</li></ul>')">
                            Liste
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertLink()">
                            🔗 Lien
                        </button>
                    </div>
                    <textarea id="content" name="content" required placeholder="Rédigez votre article ici..."><?= htmlspecialchars($form_data['content']) ?></textarea>
                  
                    <div class="help-text">HTML supporté. Utilisez la barre d'outils pour insérer des éléments. Ctrl+S pour sauvegarder</div>
                   </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="meta_title">🔍 Titre SEO</label>
                        <input type="text" id="meta_title" name="meta_title" placeholder="Titre optimisé pour Google" value="<?= htmlspecialchars($form_data['meta_title']) ?>">
                        <div class="help-text">Titre affiché dans les résultats Google</div>
                    </div>
                    <div class="form-group">
                        <label for="read_time">⏱️ Temps de lecture (min)</label>
                        <input type="number" id="read_time" name="read_time" value="<?= (int)$form_data['read_time'] ?>" min="1" max="60">
                    </div>
                </div>

                <div class="form-group">
                    <label for="meta_description">📋 Description SEO</label>
                    <textarea id="meta_description" name="meta_description" rows="2" placeholder="Description pour les moteurs de recherche et réseaux sociaux"><?= htmlspecialchars($form_data['meta_description']) ?></textarea>
                    <div class="help-text">200 caractères max recommandés - <span id="meta_desc_count">0</span>/200</div>
                </div>

                <div class="actions">
                    <button type="submit" name="save_article" value="draft" class="btn btn-secondary" onclick="document.querySelector('[name=status]').value='draft'">
                        💾 Enregistrer Brouillon
                    </button>
                    <button type="submit" name="save_article" value="published" class="btn btn-success" onclick="document.querySelector('[name=status]').value='published'">
                        🚀 <?= $mode === 'edit' && $article['status'] === 'published' ? 'Mettre à jour' : 'Publier' ?> Article
                    </button>
                   <button type="submit" name="save_article" value="1" class="btn btn-primary">
                          ✅ Sauvegarder et Fermer
                   </button>
                    <input type="hidden" name="status" value="draft">
                </div>
            </form>
        </div>

        <div class="sidebar">
            <!-- Catégorie -->
            <div class="sidebar-section">
                <div class="sidebar-header">
                    <h3>📁 Catégorie</h3>
                </div>
                <div class="sidebar-content">
                    <select name="category_id" form="articleForm" required>
                        <option value="">Choisir une catégorie</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($form_data['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['icon'] . ' ' . $cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Options -->
            <div class="sidebar-section">
                <div class="sidebar-header">
                    <h3>⚙️ Options</h3>
                </div>
                <div class="sidebar-content">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_featured" name="is_featured" form="articleForm" <?= $form_data['is_featured'] ? 'checked' : '' ?>>
                        <label for="is_featured">⭐ Article à la une</label>
                    </div>
                    <div class="help-text">L'article apparaîtra en premier sur la page d'accueil</div>
                </div>
            </div>

            <!-- Tags -->
            <div class="sidebar-section">
                <div class="sidebar-header">
                    <h3>🏷️ Tags</h3>
                </div>
                <div class="sidebar-content">
                    <div class="tags-grid">
                        <?php foreach ($tags as $tag): ?>
                            <div class="tag-item">
                                <input type="checkbox" id="tag_<?= $tag['id'] ?>" name="tags[]" value="<?= $tag['id'] ?>" form="articleForm" <?= in_array($tag['id'], $form_data['tags']) ? 'checked' : '' ?>>
                                <label for="tag_<?= $tag['id'] ?>"><?= htmlspecialchars($tag['name']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <?php if ($mode === 'edit'): ?>
            <div class="sidebar-section">
                <div class="sidebar-header">
                    <h3>🚀 Actions rapides</h3>
                </div>
                <div class="sidebar-content">
                    <a href="../view-article.php?id=<?= $article_id ?>" target="_blank" class="btn btn-secondary" style="width: 100%; margin-bottom: 10px;">
                        👁️ Prévisualiser
                    </a>
                    <a href="index.php" class="btn btn-secondary" style="width: 100%;">
                        📋 Retour à la liste
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Aide -->
            <div class="sidebar-section">
                <div class="sidebar-header">
                    <h3>💡 Aide & Raccourcis</h3>
                </div>
                <div class="sidebar-content">
                    <p><strong>Ctrl+S :</strong> Sauvegarde rapide</p>
                    <p><strong>Ctrl+B :</strong> Texte en gras</p>
                    <p><strong>Ctrl+I :</strong> Texte en italique</p>
                    <br>
                    <p><strong>Brouillon :</strong> Article sauvé mais non visible</p>
                    <p><strong>Publié :</strong> Article visible sur le site</p>
                    <br>
                    <p><strong>Auto-save :</strong> Sauvegarde automatique toutes les 180 secondes</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Media Library Modal -->
    <div id="mediaModal" class="media-modal">
        <div class="media-modal-content">
            <div class="media-modal-header">
                <h3>🖼️ Médiathèque</h3>
                <button class="media-modal-close" onclick="closeMediaLibrary()">&times;</button>
            </div>
            <div class="media-modal-body">
                <!-- Upload rapide -->
                <div class="media-upload-zone" onclick="document.getElementById('quickUpload').click()">
                    <div>📷 Upload rapide - Cliquez ou glissez une image</div>
                    <input type="file" id="quickUpload" accept="image/*" style="display: none;" onchange="quickUploadImage()">
                </div>
                
                <!-- Message upload -->
                <div id="uploadStatus" style="display: none; padding: 10px; border-radius: 4px; margin-bottom: 15px;"></div>
                
                <!-- Galerie d'images -->
                <div id="mediaGrid" class="media-grid-modal">
                    <div style="text-align: center; padding: 40px; color: #666;">
                        Chargement des images...
                    </div>
                </div>
            </div>
            <div class="media-actions">
                <div>
                    <button class="btn btn-secondary" onclick="closeMediaLibrary()">Annuler</button>
                </div>
                <div>
                    <button id="insertImageBtn" class="btn btn-primary" onclick="insertSelectedImage()" disabled>
                        Insérer l'image sélectionnée
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicateur auto-save -->
    <div id="autosaveIndicator" class="autosave-indicator">💾 Sauvegarde automatique...</div>

    <script>
        // Variables globales
        let selectedImage = null;
        let mediaList = [];
        let formModified = false;
        let autoSaveTimer = null;
        const articleId = <?= $article_id ? $article_id : 'null' ?>;

        // Auto-génération du slug depuis le titre
        document.getElementById('title').addEventListener('input', function() {
            const slugField = document.getElementById('slug');
            if (slugField.value === '' || <?= $mode === 'create' ? 'true' : 'false' ?>) {
                let slug = this.value
                    .toLowerCase()
                    .replace(/[àáäâ]/g, 'a')
                    .replace(/[èéëê]/g, 'e')
                    .replace(/[ìíïî]/g, 'i')
                    .replace(/[òóöô]/g, 'o')
                    .replace(/[ùúüû]/g, 'u')
                    .replace(/[ñ]/g, 'n')
                    .replace(/[ç]/g, 'c')
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
                slugField.value = slug;
            }
        });

        // Auto-génération titre SEO
        document.getElementById('title').addEventListener('input', function() {
            const metaTitleField = document.getElementById('meta_title');
            if (metaTitleField.value === '') {
                metaTitleField.value = this.value;
            }
        });

        // Compteur de caractères pour meta description
        const metaDesc = document.getElementById('meta_description');
        function updateMetaDescCount() {
            const length = metaDesc.value.length;
            const counter = document.getElementById('meta_desc_count');
            counter.textContent = length;
            counter.style.color = length > 200 ? 'red' : (length > 160 ? 'orange' : 'green');
        }
        metaDesc.addEventListener('input', updateMetaDescCount);
        updateMetaDescCount();

        // AUTO-SAVE (seulement en mode édition)
        <?php if ($mode === 'edit'): ?>
        function autoSave() {
            if (!formModified) return;
            
            const indicator = document.getElementById('autosaveIndicator');
            indicator.style.display = 'block';
            
            const formData = new FormData();
            formData.append('autosave', 'true');
            formData.append('title', document.getElementById('title').value);
            formData.append('content', document.getElementById('content').value);
            formData.append('excerpt', document.getElementById('excerpt').value);
            
            fetch('editor.php?id=<?= $article_id ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                indicator.textContent = '✅ Sauvegardé automatiquement';
                setTimeout(() => {
                    indicator.style.display = 'none';
                }, 2000);
                formModified = false;
            })
            .catch(error => {
                indicator.textContent = '❌ Erreur de sauvegarde';
                indicator.style.background = '#f44336';
                setTimeout(() => {
                    indicator.style.display = 'none';
                }, 60000);
            });
        }

        // Déclencher auto-save
        function triggerAutoSave() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(autoSave, 60000); // 60 secondes
        }

        // Écouter les modifications
        document.querySelectorAll('#title, #content, #excerpt').forEach(field => {
            field.addEventListener('input', () => {
                formModified = true;
                triggerAutoSave();
            });
        });

        // Auto-save toutes les 60000 secondes si modifications
        setInterval(() => {
            if (formModified) autoSave();
        }, 240000);
        <?php endif; ?>

        // Confirmation avant quitter si contenu modifié
        window.addEventListener('beforeunload', function(e) {
            if (formModified) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Désactiver confirmation après soumission
        document.querySelectorAll('button[type="submit"]').forEach(btn => {
            btn.addEventListener('click', () => formModified = false);
        });

        // Raccourcis clavier
        document.addEventListener('keydown', function(e) {
            // Ctrl+S ou Cmd+S pour sauvegarder
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                <?php if ($mode === 'edit'): ?>
                autoSave();
                <?php else: ?>
                document.querySelector('button[name="save_article"]').click();
                <?php endif; ?>
            }
            
            // Ctrl+B pour gras
            if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                e.preventDefault();
                insertHTML('<strong>', '</strong>');
            }
            
            // Ctrl+I pour italique
            if ((e.ctrlKey || e.metaKey) && e.key === 'i') {
                e.preventDefault();
                insertHTML('<em>', '</em>');
            }
        });

        // === FONCTIONS MÉDIATHÈQUE ===

        // Ouvrir la médiathèque
        async function openMediaLibrary() {
            document.getElementById('mediaModal').style.display = 'block';
            await loadMediaLibrary();
        }

        // Fermer la médiathèque
        function closeMediaLibrary() {
            document.getElementById('mediaModal').style.display = 'none';
            selectedImage = null;
            document.getElementById('insertImageBtn').disabled = true;
        }

        // Charger la liste des médias
        async function loadMediaLibrary() {
            try {
                const response = await fetch('../media/api.php?action=getMedia');
                const data = await response.json();
                
                mediaList = data.success ? data.media : [];
                renderMediaGrid();
                
            } catch (error) {
                console.error('Erreur chargement médias:', error);
                document.getElementById('mediaGrid').innerHTML = '<div style="text-align: center; color: red; padding: 40px;">Erreur de chargement</div>';
            }
        }

        // Afficher la grille de médias
        function renderMediaGrid() {
            const grid = document.getElementById('mediaGrid');
            
            if (mediaList.length === 0) {
                grid.innerHTML = '<div style="text-align: center; padding: 40px; color: #666;"><div style="font-size: 3rem; margin-bottom: 15px;">📷</div><h3>Aucune image</h3><p>Uploadez votre première image</p></div>';
                return;
            }

            const uploadUrl = '/techessentialspro/uploads/blog/';
            
            grid.innerHTML = mediaList.map(media => `
                <div class="media-item-modal" onclick="selectImage(${media.id})" data-id="${media.id}">
                    <div class="media-preview-modal">
                        <img src="${uploadUrl}${media.filename}" alt="${media.alt_text || ''}" loading="lazy">
                    </div>
                    <div class="media-info-modal">
                        <div class="media-name-modal">${media.original_name}</div>
                        <div style="color: #666;">${media.width}×${media.height}</div>
                    </div>
                </div>
            `).join('');
        }

        // Sélectionner une image
        function selectImage(mediaId) {
            document.querySelectorAll('.media-item-modal').forEach(item => {
                item.classList.remove('selected');
            });
            
            const selectedItem = document.querySelector(`[data-id="${mediaId}"]`);
            if (selectedItem) {
                selectedItem.classList.add('selected');
                selectedImage = mediaList.find(m => m.id == mediaId);
                document.getElementById('insertImageBtn').disabled = false;
            }
        }

        // Insérer l'image sélectionnée
        function insertSelectedImage() {
            if (!selectedImage) return;

            const uploadUrl = '/techessentialspro/uploads/blog/';
            const imageUrl = uploadUrl + selectedImage.filename;
            const altText = selectedImage.alt_text || selectedImage.original_name;
            
            const imageHTML = `<img src="${imageUrl}" alt="${altText}" style="max-width: 100%; height: auto;">`;
            
            insertTextAtCursor(document.getElementById('content'), imageHTML);
            closeMediaLibrary();
            formModified = true;
        }

        // Upload rapide d'image
        async function quickUploadImage() {
            const fileInput = document.getElementById('quickUpload');
            const file = fileInput.files[0];
            
            if (!file) return;
            
            const statusDiv = document.getElementById('uploadStatus');
            statusDiv.style.display = 'block';
            statusDiv.innerHTML = '📤 Upload en cours...';
            statusDiv.style.background = '#e7f3ff';
            statusDiv.style.color = '#0066cc';
            
            const formData = new FormData();
            formData.append('media_file', file);
            formData.append('alt_text', file.name.replace(/\.[^/.]+$/, ''));
            
            try {
                const response = await fetch('../media/api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    statusDiv.innerHTML = '✅ Image uploadée avec succès !';
                    statusDiv.style.background = '#d4edda';
                    statusDiv.style.color = '#155724';
                    
                    await loadMediaLibrary();
                    
                    if (result.media_id) {
                        selectImage(result.media_id);
                    }
                } else {
                    throw new Error(result.error || 'Erreur upload');
                }
                
            } catch (error) {
                statusDiv.innerHTML = '❌ Erreur: ' + error.message;
                statusDiv.style.background = '#f8d7da';
                statusDiv.style.color = '#721c24';
            }
            
            setTimeout(() => {
                statusDiv.style.display = 'none';
            }, 5000);
            
            fileInput.value = '';
        }

        // Fonctions utilitaires pour l'éditeur
        function insertHTML(openTag, closeTag) {
            const textarea = document.getElementById('content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            
            const replacement = openTag + selectedText + closeTag;
            insertTextAtCursor(textarea, replacement);
        }

        function insertLink() {
            const url = prompt('URL du lien:');
            if (url) {
                const textarea = document.getElementById('content');
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const selectedText = textarea.value.substring(start, end) || 'Texte du lien';
                
                const linkHTML = `<a href="${url}" target="_blank">${selectedText}</a>`;
                insertTextAtCursor(textarea, linkHTML);
            }
        }

        function insertTextAtCursor(textarea, text) {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            
            textarea.value = textarea.value.substring(0, start) + text + textarea.value.substring(end);
            
            const newCursorPos = start + text.length;
            textarea.setSelectionRange(newCursorPos, newCursorPos);
            textarea.focus();
            
            formModified = true;
        }

        // Drag & drop sur la zone d'upload
        const uploadZone = document.querySelector('.media-upload-zone');
        if (uploadZone) {
            uploadZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadZone.classList.add('dragover');
            });

            uploadZone.addEventListener('dragleave', () => {
                uploadZone.classList.remove('dragover');
            });

            uploadZone.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadZone.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    document.getElementById('quickUpload').files = files;
                    quickUploadImage();
                }
            });
        }

        // Fermeture modal avec Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeMediaLibrary();
            }
        });

        </script>

    <script>
<!-- Modal HTML -->
<div id="image-modal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.7);">
    <div class="modal-content" style="background:white;border-radius:12px;width:90%;max-width:800px;margin:5% auto;padding:20px;">
        <div class="modal-header" style="display:flex;justify-content:space-between;margin-bottom:20px;">
            <h3>Gestionnaire d'images</h3>
            <button class="close-modal" style="background:none;border:none;font-size:28px;cursor:pointer;">&times;</button>
        </div>
        <div class="modal-body">
            <div class="upload-zone" id="quick-upload-zone" style="border:2px dashed #3498db;padding:40px;text-align:center;cursor:pointer;border-radius:8px;">
                <div style="font-size:48px;">📁</div>
                <div>Glissez une image ou cliquez</div>
                <input type="file" id="quick-file-input" accept="image/*" style="display:none;">
            </div>
            <div style="margin-top:20px;">
                <label>Texte alternatif :</label>
                <input type="text" id="image-alt" style="width:100%;padding:10px;margin:5px 0;">
            </div>
            <button id="upload-insert-btn" style="width:100%;background:#3498db;color:white;padding:12px;border:none;border-radius:6px;margin-top:10px;cursor:pointer;">
                📤 Optimiser et insérer
            </button>
        </div>
    </div>
</div>

<script>
class ImageManager {
    constructor() {
        this.modal = document.getElementById('image-modal');
        this.setupEvents();
    }
    
    setupEvents() {
        // Bouton ouvrir
        const btn = document.getElementById('insert-image-btn');
        if(btn) btn.addEventListener('click', () => this.open());
        
        // Bouton fermer
        document.querySelector('.close-modal').addEventListener('click', () => this.close());
        
        // Upload zone
        const zone = document.getElementById('quick-upload-zone');
        const input = document.getElementById('quick-file-input');
        
        zone.addEventListener('click', () => input.click());
        input.addEventListener('change', (e) => {
            if(e.target.files[0]) this.selectedFile = e.target.files[0];
        });
        
        // Bouton upload
        document.getElementById('upload-insert-btn').addEventListener('click', () => this.upload());
    }
    
    open() {
        this.modal.style.display = 'flex';
    }
    
    close() {
        this.modal.style.display = 'none';
    }
    
    async upload() {
        if(!this.selectedFile) {
            alert('Sélectionnez une image');
            return;
        }
        
        const formData = new FormData();
        formData.append('image', this.selectedFile);
        
        try {
            const response = await fetch('upload-image-api.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if(result.success) {
                const alt = document.getElementById('image-alt').value;
                const html = `<img src="${result.urls.medium.jpeg}" alt="${alt}" loading="lazy">`;
                
                // Insérer dans le textarea
                const textarea = document.querySelector('textarea[name="content"]');
                textarea.value += '\n' + html + '\n';
                
                this.close();
                alert('✅ Image insérée !');
            } else {
                alert('Erreur : ' + result.error);
            }
        } catch(e) {
            alert('Erreur upload : ' + e.message);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new ImageManager();
});
</script>


<!-- Gestionnaire d'images -->
<script src="image-manager.js"></script>

<script>
// Auto-save pour brouillons
let formModified = false;
const articleId = <?= $article_id ?? 'null' ?>;

// Détecter modifications
document.getElementById('articleForm')?.addEventListener('input', () => {
    formModified = true;
});

// Auto-save toutes les 30 secondes
if (articleId) {
    setInterval(async () => {
        if (formModified) {
            const formData = new FormData(document.getElementById('articleForm'));
            formData.set('status', 'draft');
            
            await fetch('', { method: 'POST', body: formData });
            formModified = false;
            
            // Notification discrète
            const notif = document.createElement('div');
            notif.textContent = '💾 Sauvegardé';
            notif.style.cssText = 'position:fixed;top:20px;right:20px;background:#10b981;color:white;padding:10px 20px;border-radius:8px;z-index:9999';
            document.body.appendChild(notif);
            setTimeout(() => notif.remove(), 2000);
        }
    }, 180000);
}

// Ctrl+S pour sauvegarder
document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        document.querySelector('[name=save_article][value=draft]')?.click();
    }
});
</script>


</body>
</html>


