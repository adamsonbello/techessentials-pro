<?php
// admin-blog/view-article.php - Prévisualisation articles (brouillons inclus)
session_start();

// Vérification auth admin
if (!isset($_SESSION['blog_admin_logged']) || $_SESSION['blog_admin_logged'] !== true) {
    header('Location: index.php');
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

// Récupérer l'ID
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id <= 0) {
    header('Location: articles/index.php');
    exit;
}

// Charger l'article (brouillon ou publié)
$stmt = $blogDB->prepare("
    SELECT a.*, c.name as category_name, c.icon as category_icon, c.color as category_color
    FROM articles a
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE a.id = ?
");
$stmt->execute([$article_id]);
$article = $stmt->fetch();

if (!$article) {
    die("Article introuvable");
}

// Charger les tags
$stmt = $blogDB->prepare("
    SELECT t.* FROM tags t
    INNER JOIN article_tags at ON t.id = at.tag_id
    WHERE at.article_id = ?
");
$stmt->execute([$article_id]);
$tags = $stmt->fetchAll();

$admin_user = $_SESSION['blog_admin_user'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prévisualisation : <?= htmlspecialchars($article['title']) ?></title>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --text-color: #333;
            --text-light: #666;
            --border-color: #e1e5e9;
            --background-light: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background: var(--background-light);
        }

        /* Bandeau PREVIEW */
        .preview-banner {
            background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
            color: white;
            padding: 15px 20px;
            text-align: center;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .preview-info {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            background: rgba(255,255,255,0.2);
        }

        .back-admin {
            background: white;
            color: #ee5a6f;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        /* Container principal */
        .container {
            max-width: 800px;
            margin: 30px auto;
            background: var(--white);
            box-shadow: var(--shadow);
            border-radius: 12px;
            overflow: hidden;
        }

        /* En-tête de l'article */
        .article-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .article-category {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .article-title {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
            line-height: 1.2;
        }

        .article-excerpt {
            font-size: 1.1rem;
            opacity: 0.95;
            margin-bottom: 20px;
            font-style: italic;
        }

        .article-meta {
            display: flex;
            justify-content: center;
            gap: 20px;
            font-size: 0.9rem;
            opacity: 0.9;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Contenu de l'article */
        .article-content {
            padding: 40px 30px;
        }

        .article-content h2 {
            color: var(--primary-color);
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 1.8rem;
        }

        .article-content h3 {
            color: var(--text-color);
            margin-top: 25px;
            margin-bottom: 12px;
            font-size: 1.4rem;
        }

        .article-content p {
            margin-bottom: 20px;
            text-align: justify;
        }

        .article-content ul,
        .article-content ol {
            margin: 20px 0;
            padding-left: 30px;
        }

        .article-content li {
            margin-bottom: 8px;
        }

        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: var(--shadow);
        }

        .article-content blockquote {
            border-left: 4px solid var(--primary-color);
            padding: 15px 20px;
            margin: 20px 0;
            background: var(--background-light);
            font-style: italic;
        }

        .article-content code {
            background: var(--background-light);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }

        .article-content pre {
            background: var(--background-light);
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            margin: 20px 0;
        }

        /* Tags */
        .article-tags {
            padding: 20px 30px;
            border-top: 1px solid var(--border-color);
            background: var(--background-light);
        }

        .tags-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .tag-item {
            background: var(--white);
            border: 2px solid var(--border-color);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px 10px;
            }

            .article-title {
                font-size: 2rem;
            }

            .article-content {
                padding: 30px 20px;
            }

            .preview-banner {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Bandeau PREVIEW -->
    <div class="preview-banner">
        <div class="preview-info">
            <span>🔍 MODE PRÉVISUALISATION</span>
            <span class="status-badge">
                <?= strtoupper($article['status']) ?>
            </span>
            <span>👤 <?= htmlspecialchars($admin_user) ?></span>
        </div>
        <a href="articles/editor.php?id=<?= $article_id ?>" class="back-admin">
            ← Retour à l'éditeur
        </a>
    </div>

    <!-- Container principal -->
    <div class="container">
        <!-- En-tête de l'article -->
        <div class="article-header">
            <?php if ($article['category_name']): ?>
            <div class="article-category">
                <span><?= htmlspecialchars($article['category_icon']) ?></span>
                <span><?= htmlspecialchars($article['category_name']) ?></span>
            </div>
            <?php endif; ?>
            
            <h1 class="article-title"><?= htmlspecialchars($article['title']) ?></h1>
            
            <?php if ($article['excerpt']): ?>
            <div class="article-excerpt"><?= htmlspecialchars($article['excerpt']) ?></div>
            <?php endif; ?>
            
            <div class="article-meta">
                <div class="meta-item">
                    <span>📅</span>
                    <span>
                        <?php if ($article['published_at']): ?>
                            Publié le <?= date('d/m/Y', strtotime($article['published_at'])) ?>
                        <?php else: ?>
                            Créé le <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                        <?php endif; ?>
                    </span>
                </div>
                <?php if ($article['read_time_minutes']): ?>
                <div class="meta-item">
                    <span>⏱️</span>
                    <span><?= $article['read_time_minutes'] ?> min de lecture</span>
                </div>
                <?php endif; ?>
                <div class="meta-item">
                    <span>👁️</span>
                    <span><?= number_format($article['views']) ?> vues</span>
                </div>
            </div>
        </div>

        <!-- Contenu de l'article -->
        <div class="article-content">
            <?php 
            $content = $article['content'];
            
            // Ajouter target="_blank" aux liens externes
            $content = preg_replace('/(<a[^>]*href=["\']https?:\/\/(?!localhost)[^"\']*["\'][^>]*)>/i', '$1 target="_blank" rel="noopener noreferrer">', $content);
            
            echo $content;
            ?>
        </div>

        <!-- Tags -->
        <?php if (!empty($tags)): ?>
        <div class="article-tags">
            <div class="tags-list">
                <span style="font-weight: 600;">🏷️ Tags:</span>
                <?php foreach ($tags as $tag): ?>
                    <span class="tag-item"><?= htmlspecialchars($tag['name']) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>