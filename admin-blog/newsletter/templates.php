<?php
// admin-blog/newsletter/templates.php - Gestionnaire de templates unifié
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
            case 'upload_template':
                if (!isset($_FILES['template_file']) || $_FILES['template_file']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Erreur lors de l'upload du fichier");
                }
                
                $name = trim($_POST['template_name'] ?? '');
                
                if (empty($name)) throw new Exception("Le nom du template est requis");
                
                // Lecture du fichier HTML
                $file_content = file_get_contents($_FILES['template_file']['tmp_name']);
                if ($file_content === false) {
                    throw new Exception("Impossible de lire le fichier");
                }
                
                // Validation HTML basique
                if (strpos($file_content, '<html') === false && strpos($file_content, '<HTML') === false) {
                    throw new Exception("Le fichier ne semble pas être un template HTML valide");
                }
                
                // Génération preview
                $preview_path = generateTemplatePreview($file_content, $name);
                
                // Insertion en BDD (adapté à la vraie structure)
                $stmt = $pdo->prepare("
                    INSERT INTO newsletter_templates 
                    (name, html_content, preview_image, is_active) 
                    VALUES (?, ?, ?, 1)
                ");
                $stmt->execute([$name, $file_content, $preview_path]);
                
                $message = "Template '$name' importé avec succès !";
                break;
                
            case 'create_template':
                $name = trim($_POST['template_name'] ?? '');
                $html_content = $_POST['html_content'] ?? '';
                
                if (empty($name)) throw new Exception("Le nom du template est requis");
                if (empty($html_content)) throw new Exception("Le contenu HTML est requis");
                
                // Génération preview
                $preview_path = generateTemplatePreview($html_content, $name);
                
                $stmt = $pdo->prepare("
                    INSERT INTO newsletter_templates 
                    (name, html_content, preview_image, is_active) 
                    VALUES (?, ?, ?, 1)
                ");
                $stmt->execute([$name, $html_content, $preview_path]);
                
                $message = "Template '$name' créé avec succès !";
                break;
                
            case 'edit_template':
                $id = (int)$_POST['template_id'];
                $name = trim($_POST['template_name'] ?? '');
                $html_content = $_POST['html_content'] ?? '';
                
                if (empty($name)) throw new Exception("Le nom du template est requis");
                
                // Nouvelle preview
                $preview_path = generateTemplatePreview($html_content, $name);
                
                $stmt = $pdo->prepare("
                    UPDATE newsletter_templates 
                    SET name = ?, html_content = ?, preview_image = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $html_content, $preview_path, $id]);
                
                $message = "Template mis à jour !";
                break;
                
            case 'toggle_template':
                $id = (int)$_POST['template_id'];
                $stmt = $pdo->prepare("UPDATE newsletter_templates SET is_active = 1 - is_active WHERE id = ?");
                $stmt->execute([$id]);
                $message = "Statut du template mis à jour";
                break;
                
            case 'delete_template':
                $id = (int)$_POST['template_id'];
                
                // Supprimer l'image preview
                $stmt = $pdo->prepare("SELECT preview_image FROM newsletter_templates WHERE id = ?");
                $stmt->execute([$id]);
                $template = $stmt->fetch();
                if ($template && $template['preview_image'] && file_exists($template['preview_image'])) {
                    unlink($template['preview_image']);
                }
                
                $stmt = $pdo->prepare("DELETE FROM newsletter_templates WHERE id = ?");
                $stmt->execute([$id]);
                $message = "Template supprimé";
                break;
                
            case 'duplicate_template':
                $id = (int)$_POST['template_id'];
                $stmt = $pdo->prepare("SELECT * FROM newsletter_templates WHERE id = ?");
                $stmt->execute([$id]);
                $template = $stmt->fetch();
                
                if ($template) {
                    $new_name = $template['name'] . ' (Copie)';
                    $preview_path = generateTemplatePreview($template['html_content'], $new_name);
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO newsletter_templates 
                        (name, html_content, preview_image, is_active) 
                        VALUES (?, ?, ?, 1)
                    ");
                    $stmt->execute([$new_name, $template['html_content'], $preview_path]);
                    
                    $message = "Template dupliqué !";
                }
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Récupération des templates et statistiques
$tab = $_GET['tab'] ?? 'list';
$edit_id = (int)($_GET['edit'] ?? 0);

$templates = getTemplates($pdo);
$stats = getTemplateStats($pdo);
$edit_template = null;

if ($edit_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM newsletter_templates WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_template = $stmt->fetch();
}

// ==================== FONCTIONS UTILITAIRES ====================

/**
 * Récupère tous les templates
 */
function getTemplates($pdo) {
    $stmt = $pdo->query("
        SELECT *, 
               CASE WHEN is_active = 1 THEN 'Actif' ELSE 'Inactif' END as status_text
        FROM newsletter_templates 
        ORDER BY created_at DESC
    ");
    return $stmt->fetchAll();
}

/**
 * Récupère les statistiques des templates
 */
function getTemplateStats($pdo) {
    $stats = [];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM newsletter_templates");
    $stats['total_templates'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as active FROM newsletter_templates WHERE is_active = 1");
    $stats['active_templates'] = $stmt->fetchColumn();
    
    return $stats;
}

/**
 * Génère un aperçu de template
 */
function generateTemplatePreview($html_content, $name) {
    // Créer le dossier de previews s'il n'existe pas
    $preview_dir = '../../../uploads/newsletter-previews/';
    if (!is_dir($preview_dir)) {
        mkdir($preview_dir, 0755, true);
    }
    
    // Générer une preview HTML avec des données d'exemple
    $sample_data = getSampleNewsletterData();
    $preview_html = processTemplateWithSampleData($html_content, $sample_data);
    
    $preview_filename = 'preview_' . sanitizeFilename($name) . '_' . time() . '.html';
    $preview_path = $preview_dir . $preview_filename;
    
    if (file_put_contents($preview_path, $preview_html) !== false) {
        return 'uploads/newsletter-previews/' . $preview_filename;
    }
    
    return null;
}

/**
 * Données d'exemple pour la prévisualisation
 */
function getSampleNewsletterData() {
    return [
        'SITE_NAME' => 'TechEssentials Pro',
        'NEWSLETTER_TITLE' => 'Newsletter de démonstration',
        'ARTICLES' => [
            [
                'title' => 'Les nouvelles tendances tech 2025',
                'excerpt' => 'Découvrez les innovations qui vont révolutionner cette année...',
                'url' => '#article-1',
                'category' => 'Innovation',
                'date' => date('d/m/Y'),
                'image' => 'https://via.placeholder.com/600x300/4f46e5/ffffff?text=Article+Demo'
            ],
            [
                'title' => 'Guide complet: Intelligence Artificielle',
                'excerpt' => 'Tout ce que vous devez savoir sur l\'IA moderne et ses applications...',
                'url' => '#article-2',
                'category' => 'IA',
                'date' => date('d/m/Y'),
                'image' => 'https://via.placeholder.com/600x300/059669/ffffff?text=IA+Demo'
            ]
        ],
        'UNSUBSCRIBE_URL' => '#unsubscribe',
        'WEBSITE_URL' => 'https://techessentialspro.com',
        'CURRENT_YEAR' => date('Y')
    ];
}

/**
 * Traite un template avec des données d'exemple
 */
function processTemplateWithSampleData($html_content, $data) {
    // Remplacer les variables simples
    foreach ($data as $key => $value) {
        if (!is_array($value)) {
            $html_content = str_replace(
                ['{{' . $key . '}}', '{' . $key . '}', '%' . $key . '%'], 
                $value, 
                $html_content
            );
        }
    }
    
    // Traitement spécial pour les articles
    if (isset($data['ARTICLES']) && is_array($data['ARTICLES'])) {
        $articles_html = generateSampleArticlesHtml($data['ARTICLES']);
        
        $html_content = preg_replace(
            '/\{\{ARTICLES\}\}|\{ARTICLES\}|\%ARTICLES\%/i',
            $articles_html,
            $html_content
        );
    }
    
    return $html_content;
}

/**
 * Génère le HTML des articles d'exemple
 */
function generateSampleArticlesHtml($articles) {
    $articles_html = '';
    
    foreach ($articles as $article) {
        $articles_html .= '
        <div style="margin-bottom: 30px; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
            <h2 style="color: #333; margin: 0 0 10px 0;">' . htmlspecialchars($article['title']) . '</h2>
            <p style="color: #666; font-size: 14px; margin: 0 0 15px 0;">
                <strong>' . htmlspecialchars($article['category']) . '</strong> • ' . $article['date'] . '
            </p>
            <p style="color: #555; line-height: 1.6; margin: 0 0 15px 0;">' . htmlspecialchars($article['excerpt']) . '</p>
            <a href="' . $article['url'] . '" style="background: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Lire la suite</a>
        </div>';
    }
    
    return $articles_html;
}

/**
 * Nettoie un nom de fichier
 */
function sanitizeFilename($filename) {
    return preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $filename));
}

// Création de la table si elle n'existe pas (adaptée à la structure réelle)
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS newsletter_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            html_content LONGTEXT NOT NULL,
            preview_image VARCHAR(255),
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_active (is_active),
            INDEX idx_name (name)
        )
    ");
} catch (PDOException $e) {
    // Table existe déjà
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionnaire de Templates - Newsletter</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            color: #333;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #8e44ad;
            margin-bottom: 0.5rem;
        }
        
        .tabs {
            display: flex;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .tab {
            flex: 1;
            padding: 1rem 1.5rem;
            text-align: center;
            text-decoration: none;
            color: #666;
            font-weight: 500;
            transition: all 0.3s ease;
            border-right: 1px solid #eee;
        }
        
        .tab:last-child { border-right: none; }
        .tab.active { background: #8e44ad; color: white; }
        .tab:hover:not(.active) { background: #f8f9fa; }
        
        .content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        
        .templates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .template-card {
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            background: white;
        }
        
        .template-card:hover {
            border-color: #8e44ad;
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .template-preview {
            height: 200px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            position: relative;
            overflow: hidden;
        }
        
        .template-preview iframe {
            width: 100%;
            height: 400px;
            transform: scale(0.5);
            transform-origin: top left;
            border: none;
        }
        
        .template-info {
            padding: 1.5rem;
        }
        
        .template-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .template-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        
        .btn {
            background: #8e44ad;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #7d3c9a;
            transform: translateY(-2px);
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
        
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #229954; }
        
        .btn-warning { background: #f39c12; }
        .btn-warning:hover { background: #e67e22; }
        
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        
        .btn-info { background: #3498db; }
        .btn-info:hover { background: #2980b9; }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        input[type="text"],
        input[type="file"],
        textarea,
        select {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #8e44ad;
            box-shadow: 0 0 0 3px rgba(142, 68, 173, 0.1);
        }
        
        textarea {
            min-height: 400px;
            font-family: 'Courier New', monospace;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        
        .upload-area {
            border: 3px dashed #ddd;
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-area:hover {
            border-color: #8e44ad;
            background: #f8f4ff;
        }
        
        .upload-area.dragover {
            border-color: #8e44ad;
            background: #f8f4ff;
        }
        
        .code-editor {
            position: relative;
        }
        
        .editor-toolbar {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            flex-wrap: wrap;
        }
        
        .variables-help {
            background: #e8f4f8;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 4px solid #3498db;
        }
        
        .variables-help h4 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .variables-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.5rem;
        }
        
        .variable-tag {
            background: #3498db;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .variable-tag:hover {
            background: #2980b9;
            transform: translateY(-1px);
        }
        
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .integration-status {
            background: #e8f5e8;
            border-left: 4px solid #27ae60;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .two-columns {
                grid-template-columns: 1fr;
            }
            
            .templates-grid {
                grid-template-columns: 1fr;
            }
            
            .editor-toolbar {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation améliorée -->
        <div style="margin-bottom: 1rem;">
            <a href="../dashboard.php" class="btn" style="background: #6c757d; margin-right: 1rem;">
                ← Dashboard
            </a>
            <a href="../" class="btn" style="background: #17a2b8; margin-right: 1rem;">
                Articles
            </a>
            <a href="index.php" class="btn" style="background: #667eea;">
                Newsletter
                <?php if ($stats['active_templates'] > 0): ?>
                    <small style="background: rgba(255,255,255,0.3); padding: 2px 6px; border-radius: 10px; margin-left: 5px;">
                        <?= $stats['active_templates'] ?> disponibles
                    </small>
                <?php endif; ?>
            </a>
        </div>
        
        <!-- Header -->
        <div class="header">
            <h1>Gestionnaire de Templates</h1>
            <p>Créez et gérez vos templates de newsletter</p>
        </div>
        
        <!-- Statut d'intégration -->
        <?php if ($stats['active_templates'] > 0): ?>
            <div class="integration-status">
                <strong>Intégration réussie !</strong> 
                Vos <?= $stats['active_templates'] ?> template(s) actif(s) sont maintenant disponibles dans le composeur de newsletter.
            </div>
        <?php endif; ?>
        
        <!-- Statistiques -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['total_templates']) ?></div>
                <div class="stat-label">Templates total</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['active_templates']) ?></div>
                <div class="stat-label">Templates actifs</div>
            </div>
        </div>
        
        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- Onglets -->
        <div class="tabs">
            <a href="?tab=list" class="tab <?= $tab === 'list' ? 'active' : '' ?>">
                Mes Templates
            </a>
            <a href="?tab=upload" class="tab <?= $tab === 'upload' ? 'active' : '' ?>">
                Importer
            </a>
            <a href="?tab=create" class="tab <?= $tab === 'create' ? 'active' : '' ?>">
                Créer
            </a>
        </div>
        
        <!-- Contenu -->
        <div class="content">
            <?php if ($tab === 'list'): ?>
                <!-- Liste des templates -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h2>Mes Templates Newsletter</h2>
                    <div>
                        <a href="?tab=upload" class="btn btn-success">+ Importer Template</a>
                        <a href="?tab=create" class="btn">+ Créer Template</a>
                    </div>
                </div>
                
                <?php if (empty($templates)): ?>
                    <div style="text-align: center; padding: 4rem; color: #666;">
                        <h3>Aucun template personnalisé</h3>
                        <p>Commencez par importer ou créer votre premier template !</p>
                        <div style="margin-top: 2rem;">
                            <a href="?tab=upload" class="btn btn-success">Importer un template</a>
                            <a href="?tab=create" class="btn">Créer un template</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="templates-grid">
                        <?php foreach ($templates as $template): ?>
                            <div class="template-card">
                                <div class="template-preview">
                                    <?php if ($template['preview_image'] && file_exists('../../../' . $template['preview_image'])): ?>
                                        <iframe src="../../../<?= htmlspecialchars($template['preview_image']) ?>" 
                                                scrolling="no"></iframe>
                                    <?php else: ?>
                                        <div style="text-align: center;">
                                            <div style="font-size: 3rem; margin-bottom: 1rem;">📧</div>
                                            <div>Aperçu non disponible</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="template-info">
                                    <div class="template-name">
                                        <?= htmlspecialchars($template['name']) ?>
                                        <?php if ($template['is_active']): ?>
                                            <span class="badge badge-success">Actif</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactif</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div style="font-size: 0.8rem; color: #888; margin-bottom: 1rem;">
                                        Créé le <?= date('d/m/Y', strtotime($template['created_at'])) ?>
                                    </div>
                                    
                                    <?php if ($template['is_active']): ?>
                                        <div style="background: #e8f5e8; color: #155724; padding: 0.5rem; border-radius: 4px; margin-bottom: 1rem; font-size: 0.85rem;">
                                            Template disponible dans le composeur newsletter
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="template-actions">
                                        <a href="?tab=create&edit=<?= $template['id'] ?>" class="btn btn-sm btn-info">
                                            Modifier
                                        </a>
                                        
                                        <?php if ($template['preview_image'] && file_exists('../../../' . $template['preview_image'])): ?>
                                            <a href="../../../<?= htmlspecialchars($template['preview_image']) ?>" 
                                               target="_blank" class="btn btn-sm btn-warning">
                                                Aperçu
                                            </a>
                                        <?php endif; ?>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="duplicate_template">
                                            <input type="hidden" name="template_id" value="<?= $template['id'] ?>">
                                            <button type="submit" class="btn btn-sm" style="background: #8e44ad;">
                                                Dupliquer
                                            </button>
                                        </form>
                                        
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_template">
                                            <input type="hidden" name="template_id" value="<?= $template['id'] ?>">
                                            <button type="submit" class="btn btn-sm <?= $template['is_active'] ? 'btn-warning' : 'btn-success' ?>">
                                                <?= $template['is_active'] ? 'Désactiver' : 'Activer' ?>
                                            </button>
                                        </form>
                                        
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Supprimer ce template ?')">
                                            <input type="hidden" name="action" value="delete_template">
                                            <input type="hidden" name="template_id" value="<?= $template['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
            <?php elseif ($tab === 'upload'): ?>
                <!-- Import de template -->
                <h2>Importer un Template</h2>
                <p style="color: #666; margin-bottom: 2rem;">
                    Importez un fichier HTML de template newsletter depuis votre ordinateur
                </p>
                
                <div class="two-columns">
                    <div>
                        <h3>Upload depuis un fichier</h3>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="upload_template">
                            
                            <div class="form-group">
                                <label for="template_name">Nom du template *</label>
                                <input type="text" id="template_name" name="template_name" 
                                       placeholder="Ex: Template Promotions Black Friday" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="template_file">Fichier HTML *</label>
                                <div class="upload-area" id="upload-area">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">📤</div>
                                    <div style="font-size: 1.2rem; margin-bottom: 0.5rem;">
                                        Glissez votre fichier HTML ici
                                    </div>
                                    <div style="color: #666;">ou cliquez pour parcourir</div>
                                    <input type="file" id="template_file" name="template_file" 
                                           accept=".html,.htm" required style="display: none;">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success">
                                Importer le Template
                            </button>
                        </form>
                    </div>
                    
                    <div>
                        <h3>Conseils d'importation</h3>
                        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px;">
                            <h4 style="color: #333; margin-bottom: 1rem;">Variables supportées :</h4>
                            <div class="variables-list">
                                <span class="variable-tag">{{SITE_NAME}}</span>
                                <span class="variable-tag">{{NEWSLETTER_TITLE}}</span>
                                <span class="variable-tag">{{ARTICLES}}</span>
                                <span class="variable-tag">{{UNSUBSCRIBE_URL}}</span>
                                <span class="variable-tag">{{WEBSITE_URL}}</span>
                                <span class="variable-tag">{{CURRENT_YEAR}}</span>
                            </div>
                            
                            <h4 style="color: #333; margin: 1.5rem 0 1rem 0;">Sources recommandées :</h4>
                            <ul style="margin-left: 1.5rem; color: #666;">
                                <li><strong>Litmus Community</strong> - Templates gratuits</li>
                                <li><strong>Email on Acid</strong> - Collection professionnelle</li>
                                <li><strong>GitHub</strong> - Templates open-source</li>
                                <li><strong>CodePen</strong> - Templates créatifs</li>
                                <li><strong>Themeforest</strong> - Templates premium</li>
                            </ul>
                            
                            <div style="background: #e8f5e8; padding: 1rem; border-radius: 4px; margin-top: 1rem;">
                                <strong>Intégration automatique :</strong> Une fois importé et activé, 
                                votre template apparaîtra dans le menu déroulant du composeur de newsletter.
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($tab === 'create' || $edit_template): ?>
                <!-- Créer/Modifier template -->
                <h2><?= $edit_template ? 'Modifier Template' : 'Créer un Template' ?></h2>
                <p style="color: #666; margin-bottom: 2rem;">
                    <?= $edit_template ? 'Modifiez votre template existant' : 'Créez un nouveau template de newsletter personnalisé' ?>
                </p>
                
                <form method="POST">
                    <input type="hidden" name="action" value="<?= $edit_template ? 'edit_template' : 'create_template' ?>">
                    <?php if ($edit_template): ?>
                        <input type="hidden" name="template_id" value="<?= $edit_template['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="two-columns">
                        <div>
                            <div class="form-group">
                                <label for="template_name">Nom du template *</label>
                                <input type="text" id="template_name" name="template_name" 
                                       value="<?= htmlspecialchars($edit_template['name'] ?? '') ?>"
                                       placeholder="Ex: Template Promotions Spéciales" required>
                            </div>
                        </div>
                        
                        <div class="variables-help">
                            <h4>Variables disponibles</h4>
                            <div class="variables-list">
                                <span class="variable-tag" onclick="insertVariable('{{SITE_NAME}}')">{{SITE_NAME}}</span>
                                <span class="variable-tag" onclick="insertVariable('{{NEWSLETTER_TITLE}}')">{{NEWSLETTER_TITLE}}</span>
                                <span class="variable-tag" onclick="insertVariable('{{ARTICLES}}')">{{ARTICLES}}</span>
                                <span class="variable-tag" onclick="insertVariable('{{UNSUBSCRIBE_URL}}')">{{UNSUBSCRIBE_URL}}</span>
                                <span class="variable-tag" onclick="insertVariable('{{WEBSITE_URL}}')">{{WEBSITE_URL}}</span>
                                <span class="variable-tag" onclick="insertVariable('{{CURRENT_YEAR}}')">{{CURRENT_YEAR}}</span>
                            </div>
                            <small style="color: #666; margin-top: 0.5rem; display: block;">
                                Cliquez sur une variable pour l'insérer dans l'éditeur
                            </small>
                        </div>
                    </div>
                    
                    <div class="form-group code-editor">
                        <label for="html_content">Code HTML du template *</label>
                        <div class="editor-toolbar">
                            <button type="button" onclick="insertTemplate('basic')" class="btn btn-sm">
                                Template de base
                            </button>
                            <button type="button" onclick="insertTemplate('promo')" class="btn btn-sm">
                                Template promotionnel
                            </button>
                            <button type="button" onclick="insertTemplate('minimal')" class="btn btn-sm">
                                Template minimal
                            </button>
                            <button type="button" onclick="previewTemplate()" class="btn btn-sm btn-info">
                                Aperçu
                            </button>
                        </div>
                        
                        <textarea id="html_content" name="html_content" required 
                                  placeholder="Collez votre code HTML ici..."><?= htmlspecialchars($edit_template['html_content'] ?? '') ?></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-success">
                            <?= $edit_template ? 'Mettre à jour' : 'Créer le Template' ?>
                        </button>
                        
                        <?php if ($edit_template): ?>
                            <a href="?tab=list" class="btn" style="background: #6c757d;">
                                ← Retour à la liste
                            </a>
                        <?php endif; ?>
                        
                        <button type="button" onclick="previewTemplate()" class="btn btn-info">
                            Prévisualiser
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        <!-- Modal de prévisualisation -->
        <div id="preview-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 1000;">
            <div style="position: absolute; top: 5%; left: 5%; right: 5%; bottom: 5%; background: white; border-radius: 12px; overflow: hidden;">
                <div style="padding: 1rem; background: #f8f9fa; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                    <h3>Aperçu du Template</h3>
                    <button onclick="closePreview()" class="btn btn-danger">Fermer</button>
                </div>
                <iframe id="preview-frame" style="width: 100%; height: calc(100% - 70px); border: none;"></iframe>
            </div>
        </div>
    </div>
    
    <script>
        // Upload par glisser-déposer
        document.addEventListener('DOMContentLoaded', function() {
            const uploadArea = document.getElementById('upload-area');
            const fileInput = document.getElementById('template_file');
            
            if (uploadArea && fileInput) {
                uploadArea.addEventListener('click', () => fileInput.click());
                
                uploadArea.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    uploadArea.classList.add('dragover');
                });
                
                uploadArea.addEventListener('dragleave', () => {
                    uploadArea.classList.remove('dragover');
                });
                
                uploadArea.addEventListener('drop', (e) => {
                    e.preventDefault();
                    uploadArea.classList.remove('dragover');
                    
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        fileInput.files = files;
                        uploadArea.innerHTML = `
                            <div style="color: #27ae60;">
                                <div style="font-size: 2rem;">✅</div>
                                <div>${files[0].name}</div>
                            </div>
                        `;
                    }
                });
                
                fileInput.addEventListener('change', (e) => {
                    if (e.target.files.length > 0) {
                        uploadArea.innerHTML = `
                            <div style="color: #27ae60;">
                                <div style="font-size: 2rem;">✅</div>
                                <div>${e.target.files[0].name}</div>
                            </div>
                        `;
                    }
                });
            }
        });
        
        // Insertion de variables
        function insertVariable(variable) {
            const textarea = document.getElementById('html_content');
            if (textarea) {
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const value = textarea.value;
                
                textarea.value = value.substring(0, start) + variable + value.substring(end);
                textarea.focus();
                textarea.setSelectionRange(start + variable.length, start + variable.length);
            }
        }
        
        // Templates prédéfinis
        function insertTemplate(type) {
            const textarea = document.getElementById('html_content');
            if (!textarea) return;
            
            let template = '';
            
            switch (type) {
                case 'basic':
                    template = `<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{NEWSLETTER_TITLE}}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; }
        .header { background: #333; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { background: #f8f8f8; padding: 20px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{SITE_NAME}}</h1>
        </div>
        <div class="content">
            <h2>{{NEWSLETTER_TITLE}}</h2>
            {{ARTICLES}}
        </div>
        <div class="footer">
            <p><a href="{{UNSUBSCRIBE_URL}}">Se désabonner</a> | <a href="{{WEBSITE_URL}}">Visiter le site</a></p>
            <p>&copy; {{CURRENT_YEAR}} {{SITE_NAME}}</p>
        </div>
    </div>
</body>
</html>`;
                    break;
                    
                case 'promo':
                    template = `<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{NEWSLETTER_TITLE}}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #000; }
        .container { max-width: 600px; margin: 0 auto; background: linear-gradient(45deg, #ff6b6b, #4ecdc4); }
        .header { padding: 30px; text-align: center; color: white; }
        .header h1 { font-size: 2.5rem; margin: 0; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); }
        .promo-banner { background: #ff3333; color: white; text-align: center; padding: 15px; font-size: 1.2rem; font-weight: bold; }
        .content { padding: 30px; background: white; }
        .cta-button { display: inline-block; background: #ff3333; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="promo-banner">🔥 OFFRE LIMITÉE - Ne manquez pas cette occasion ! 🔥</div>
        <div class="header">
            <h1>{{SITE_NAME}}</h1>
            <p style="font-size: 1.2rem;">{{NEWSLETTER_TITLE}}</p>
        </div>
        <div class="content">
            {{ARTICLES}}
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{WEBSITE_URL}}" class="cta-button">🛍️ PROFITEZ MAINTENANT</a>
            </div>
        </div>
        <div style="background: #333; color: white; padding: 20px; text-align: center;">
            <p><a href="{{UNSUBSCRIBE_URL}}" style="color: #ccc;">Se désabonner</a></p>
        </div>
    </div>
</body>
</html>`;
                    break;
                    
                case 'minimal':
                    template = `<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{NEWSLETTER_TITLE}}</title>
    <style>
        body { font-family: Georgia, serif; margin: 0; padding: 40px 20px; background: white; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 1px solid #eee; padding-bottom: 30px; margin-bottom: 30px; }
        .header h1 { font-size: 2rem; margin: 0; font-weight: normal; }
        .footer { border-top: 1px solid #eee; padding-top: 20px; margin-top: 40px; text-align: center; font-size: 14px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{SITE_NAME}}</h1>
            <p>{{NEWSLETTER_TITLE}}</p>
        </div>
        
        {{ARTICLES}}
        
        <div class="footer">
            <p><a href="{{WEBSITE_URL}}">{{SITE_NAME}}</a> • <a href="{{UNSUBSCRIBE_URL}}">Se désabonner</a></p>
            <p>© {{CURRENT_YEAR}} Tous droits réservés</p>
        </div>
    </div>
</body>
</html>`;
                    break;
            }
            
            if (confirm('Remplacer le contenu actuel par ce template ?')) {
                textarea.value = template;
            }
        }
        
        // Prévisualisation
        function previewTemplate() {
            const htmlContent = document.getElementById('html_content').value;
            
            if (!htmlContent.trim()) {
                alert('Veuillez d\'abord saisir du contenu HTML');
                return;
            }
            
            // Remplacer les variables par des données de test
            let previewHTML = htmlContent
                .replace(/\{\{SITE_NAME\}\}/g, 'TechEssentials Pro')
                .replace(/\{\{NEWSLETTER_TITLE\}\}/g, 'Newsletter de Démonstration')
                .replace(/\{\{WEBSITE_URL\}\}/g, '#')
                .replace(/\{\{UNSUBSCRIBE_URL\}\}/g, '#unsubscribe')
                .replace(/\{\{CURRENT_YEAR\}\}/g, new Date().getFullYear())
                .replace(/\{\{ARTICLES\}\}/g, `
                    <div style="margin-bottom: 30px; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
                        <h2>Article de démonstration 1</h2>
                        <p style="color: #666; font-size: 14px;"><strong>Tech</strong> • ${new Date().toLocaleDateString()}</p>
                        <p>Ceci est un extrait d'article de démonstration pour tester votre template...</p>
                        <a href="#" style="background: #4f46e5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Lire la suite</a>
                    </div>
                    <div style="margin-bottom: 30px; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
                        <h2>Article de démonstration 2</h2>
                        <p style="color: #666; font-size: 14px;"><strong>Innovation</strong> • ${new Date().toLocaleDateString()}</p>
                        <p>Un autre exemple d'article pour voir le rendu de votre design...</p>
                        <a href="#" style="background: #059669; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Lire la suite</a>
                    </div>
                `);
            
            const frame = document.getElementById('preview-frame');
            frame.srcdoc = previewHTML;
            
            document.getElementById('preview-modal').style.display = 'block';
        }
        
        function closePreview() {
            document.getElementById('preview-modal').style.display = 'none';
        }
        
        // Fermer la modal en cliquant à l'extérieur
        document.getElementById('preview-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePreview();
            }
        });
        
        // Raccourcis clavier
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                previewTemplate();
            }
            if (e.key === 'Escape') {
                closePreview();
            }
        });
    </script>
</body>
</html>