<?php
// blog/article.php - Page publique pour afficher un article avec commentaires
// URL: yoursite.com/blog/article.php?id=1 ou yoursite.com/blog/article.php?slug=mon-article

// Configuration BDD
$DB_CONFIG = [
    'host' => 'localhost',
    'dbname' => 'techessentials_blog',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

try {
    $dsn = "mysql:host={$DB_CONFIG['host']};dbname={$DB_CONFIG['dbname']};charset={$DB_CONFIG['charset']}";
    $pdo = new PDO($dsn, $DB_CONFIG['username'], $DB_CONFIG['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

// Récupération de l'article
$article_id = $_GET['id'] ?? null;
$article_slug = $_GET['slug'] ?? null;

if ($article_id) {
    $stmt = $pdo->prepare("
        SELECT a.*, c.name as category_name, c.color as category_color, c.icon as category_icon
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.id = ? AND a.status = 'published'
    ");
    $stmt->execute([$article_id]);
} elseif ($article_slug) {
    $stmt = $pdo->prepare("
        SELECT a.*, c.name as category_name, c.color as category_color, c.icon as category_icon
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.slug = ? AND a.status = 'published'
    ");
    $stmt->execute([$article_slug]);
} else {
    header('Location: index.php');
    exit;
}

$article = $stmt->fetch();
if (!$article) {
    header('HTTP/1.0 404 Not Found');
    die("Article non trouvé");
}

// Récupération des tags
$stmt = $pdo->prepare("
    SELECT t.name, t.slug, t.color
    FROM tags t
    INNER JOIN article_tags at ON t.id = at.tag_id
    WHERE at.article_id = ?
    ORDER BY t.name
");
$stmt->execute([$article['id']]);
$tags = $stmt->fetchAll();

// Compter les commentaires approuvés
$stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE article_id = ? AND status = 'approved'");
$stmt->execute([$article['id']]);
$comments_count = $stmt->fetchColumn();

// Articles similaires (même catégorie)
$stmt = $pdo->prepare("
    SELECT id, title, slug, excerpt, featured_image, published_at
    FROM articles
    WHERE category_id = ? AND id != ? AND status = 'published'
    ORDER BY published_at DESC
    LIMIT 3
");
$stmt->execute([$article['category_id'], $article['id']]);
$related_articles = $stmt->fetchAll();

// Fonctions d'affichage des commentaires
function displayComments($article_id, $parent_id = null, $level = 0) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM comments 
        WHERE article_id = ? AND parent_id " . ($parent_id ? "= ?" : "IS NULL") . " AND status = 'approved'
        ORDER BY created_at ASC
    ");
    
    if ($parent_id) {
        $stmt->execute([$article_id, $parent_id]);
    } else {
        $stmt->execute([$article_id]);
    }
    
    $comments = $stmt->fetchAll();
    
    if (empty($comments) && $level === 0) {
        echo '<div class="no-comments">
                <div class="no-comments-icon">💬</div>
                <p>Aucun commentaire pour le moment.</p>
                <p>Soyez le premier à partager votre avis !</p>
              </div>';
        return;
    }
    
    foreach ($comments as $comment) {
        $margin_left = $level * 30;
        $reply_class = $level > 0 ? 'reply-comment' : 'main-comment';
        
        echo '<div class="comment-item ' . $reply_class . '" style="margin-left: ' . $margin_left . 'px;" data-comment-id="' . $comment['id'] . '">';
        
        // Avatar basé sur l'email
        $avatar_hash = md5(strtolower(trim($comment['author_email'])));
        $avatar_url = "https://www.gravatar.com/avatar/{$avatar_hash}?s=50&d=identicon";
        
        echo '<div class="comment-header">';
        echo '<div class="comment-author-info">';
        echo '<img src="' . $avatar_url . '" alt="Avatar" class="comment-avatar">';
        echo '<div>';
        
        if (!empty($comment['author_website'])) {
            echo '<a href="' . htmlspecialchars($comment['author_website']) . '" target="_blank" rel="nofollow" class="comment-author">';
            echo htmlspecialchars($comment['author_name']);
            echo '</a>';
        } else {
            echo '<span class="comment-author">' . htmlspecialchars($comment['author_name']) . '</span>';
        }
        
        echo '<div class="comment-date">' . date('d/m/Y à H:i', strtotime($comment['created_at'])) . '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        // Contenu du commentaire
        echo '<div class="comment-content">';
        echo nl2br(htmlspecialchars($comment['content']));
        echo '</div>';
        
        // Bouton répondre (limité à 3 niveaux)
        if ($level < 3) {
            echo '<div class="comment-actions">';
            echo '<button class="reply-btn" onclick="showReplyForm(' . $comment['id'] . ')">💬 Répondre</button>';
            echo '</div>';
            
            // Formulaire de réponse (caché par défaut)
            echo '<div class="reply-form-container" id="reply-form-' . $comment['id'] . '" style="display: none;">';
            displayCommentForm($article['id'], $comment['id']);
            echo '</div>';
        }
        
        echo '</div>';
        
        // Commentaires enfants (récursif)
        displayComments($article['id'], $comment['id'], $level + 1);
    }
}

function displayCommentForm($article_id, $parent_id = null) {
    $title = $parent_id ? "💬 Répondre" : "💬 Laisser un commentaire";
    
    echo '<div class="comment-form-section">';
    echo '<h3>' . $title . '</h3>';
    echo '<form class="comment-form" data-article-id="' . $article_id . '" data-parent-id="' . ($parent_id ?? '') . '">';
    
    echo '<div class="form-row">';
    echo '<div class="form-group">';
    echo '<label for="author_name">Nom *</label>';
    echo '<input type="text" name="author_name" required maxlength="100" placeholder="Votre nom">';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label for="author_email">Email *</label>';
    echo '<input type="email" name="author_email" required maxlength="255" placeholder="votre@email.com">';
    echo '<small>Votre email ne sera pas publié</small>';
    echo '</div>';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label for="author_website">Site web (optionnel)</label>';
    echo '<input type="url" name="author_website" maxlength="255" placeholder="https://votre-site.com">';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label for="content">Votre commentaire *</label>';
    echo '<textarea name="content" required minlength="10" maxlength="2000" rows="5" placeholder="Partagez votre avis, vos questions ou vos expériences..."></textarea>';
    echo '<small><span class="char-count">0</span>/2000 caractères</small>';
    echo '</div>';
    
    echo '<div class="form-actions">';
    echo '<button type="submit" class="submit-btn">📝 Publier le commentaire</button>';
    if ($parent_id) {
        echo '<button type="button" class="cancel-btn" onclick="hideReplyForm(' . $parent_id . ')">❌ Annuler</button>';
    }
    echo '</div>';
    
    echo '<div class="form-message" style="display: none;"></div>';
    echo '</form>';
    echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - TechEssentials Pro</title>
    <meta name="description" content="<?php echo htmlspecialchars($article['meta_description'] ?: $article['excerpt']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($article['meta_keywords']); ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($article['title']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($article['excerpt']); ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?php echo "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"; ?>">
    <?php if ($article['featured_image']): ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($article['featured_image']); ?>">
    <?php endif; ?>
    
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
            line-height: 1.6;
            color: var(--text-color);
            background: var(--background-light);
        }

        /* Header avec menu */
        .site-header {
            background: var(--white);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 20px;
        }

        .nav-menu a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .nav-menu a:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Container principal */
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--white);
            box-shadow: var(--shadow);
            border-radius: 12px;
            overflow: hidden;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        /* En-tête de l'article */
        .article-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        .breadcrumb {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .breadcrumb a {
            color: white;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
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
            align-items: center;
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

        /* Image mise en avant */
        .featured-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
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
            text-decoration: none;
            color: var(--text-color);
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .tag-item:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        /* Navigation article */
        .article-nav {
            padding: 20px 30px;
            border-top: 1px solid var(--border-color);
            text-align: center;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        /* Section commentaires */
        .comments-section {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .comments-header {
            margin-bottom: 30px;
            padding: 20px;
            background: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .comments-count {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        /* Styles des commentaires */
        .comment-item {
            background: var(--white);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary-color);
        }

        .reply-comment {
            background: var(--background-light);
            border-left-color: var(--text-light);
        }

        .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .comment-author-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
        }

        .comment-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid var(--border-color);
        }

        .comment-author {
            font-weight: 600;
            color: var(--primary-color);
            text-decoration: none;
        }

        .comment-author:hover {
            text-decoration: underline;
        }

        .comment-date {
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .comment-content {
            line-height: 1.6;
            color: var(--text-color);
            margin-bottom: 15px;
        }

        .comment-actions {
            display: flex;
            gap: 10px;
        }

        .reply-btn {
            background: none;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            padding: 6px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .reply-btn:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Formulaire de commentaire */
        .comment-form-section {
            background: var(--white);
            padding: 30px;
            border-radius: 8px;
            margin: 30px 0;
            box-shadow: var(--shadow);
        }

        .comment-form-section h3 {
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-family: inherit;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .form-group small {
            display: block;
            margin-top: 5px;
            color: var(--text-light);
            font-size: 0.85rem;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .submit-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .submit-btn:disabled {
            background: var(--text-light);
            cursor: not-allowed;
            transform: none;
        }

        .cancel-btn {
            background: var(--text-light);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
        }

        .cancel-btn:hover {
            background: #5a5a5a;
        }

        .form-message {
            margin-top: 15px;
            padding: 12px;
            border-radius: 6px;
            font-weight: 500;
        }

        .form-message.success {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .form-message.error {
            background: rgba(244, 67, 54, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        .no-comments {
            text-align: center;
            padding: 40px;
            color: var(--text-light);
            background: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .no-comments-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .char-count {
            font-weight: 600;
        }

        /* Articles similaires */
        .related-articles {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .related-header {
            background: var(--white);
            padding: 20px;
            border-radius: 8px 8px 0 0;
            box-shadow: var(--shadow);
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            background: var(--white);
            padding: 20px;
            border-radius: 0 0 8px 8px;
            box-shadow: var(--shadow);
        }

        .related-article {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .related-article:hover {
            transform: translateY(-5px);
        }

        .related-article img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }

        .related-content {
            padding: 15px;
        }

        .related-title {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-color);
            text-decoration: none;
        }

        .related-title:hover {
            color: var(--primary-color);
        }

        .related-date {
            font-size: 0.85rem;
            color: var(--text-light);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
                gap: 10px;
            }

            .container {
                margin: 20px 10px;
                border-radius: 8px;
            }

            .article-header {
                padding: 30px 20px;
            }

            .article-title {
                font-size: 2rem;
            }

            .article-meta {
                flex-direction: column;
                gap: 10px;
            }

            .article-content {
                padding: 30px 20px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .comment-item {
                margin-left: 0 !important;
            }

            .reply-comment {
                margin-left: 15px !important;
            }

            .related-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <!-- Header avec navigation -->
    <header class="site-header">
        <div class="header-content">
            <a href="../index.html" class="logo">TechEssentials Pro</a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="../index.html">🏠 Accueil</a></li>
                    <li><a href="../products.html">📱 Produits</a></li>
                    <li><a href="../reviews.html">⭐ Tests</a></li>
                    <li><a href="index.php">📝 Blog</a></li>
                    <li><a href="../deals.html">💰 Bons Plans</a></li>
                    <li><a href="../contact.html">📞 Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Container principal -->
    <div class="container">
        <!-- En-tête de l'article -->
        <div class="article-header">
            <div class="breadcrumb">
                <a href="index.php">Blog</a> → 
                <?php if ($article['category_name']): ?>
                    <span><?php echo htmlspecialchars($article['category_name']); ?></span> →
                <?php endif; ?>
                <span>Article</span>
            </div>

            <?php if ($article['category_name']): ?>
                <div class="article-category" style="background-color: <?php echo htmlspecialchars($article['category_color']); ?>20;">
                    <span><?php echo htmlspecialchars($article['category_icon']); ?></span>
                    <span><?php echo htmlspecialchars($article['category_name']); ?></span>
                </div>
            <?php endif; ?>

            <h1 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h1>
            
            <?php if ($article['excerpt']): ?>
                <div class="article-excerpt"><?php echo htmlspecialchars($article['excerpt']); ?></div>
            <?php endif; ?>

            <div class="article-meta">
                <div class="meta-item">
                    <span>📅</span>
                    <span>Publié le <?php echo date('d/m/Y', strtotime($article['published_at'])); ?></span>
                </div>
                <div class="meta-item">
                    <span>👁️</span>
                    <span><?php echo number_format($article['views']); ?> vue<?php echo $article['views'] > 1 ? 's' : ''; ?></span>
                </div>
                <div class="meta-item">
                    <span>💬</span>
                    <span><?php echo $comments_count; ?> commentaire<?php echo $comments_count > 1 ? 's' : ''; ?></span>
                </div>
                <?php if (!empty($tags)): ?>
                    <div class="meta-item">
                        <span>🏷️</span>
                        <span><?php echo count($tags); ?> tag<?php echo count($tags) > 1 ? 's' : ''; ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Image mise en avant -->
        <?php if (!empty($article['featured_image'])): ?>
            <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" 
                 alt="<?php echo htmlspecialchars($article['title']); ?>" 
                 class="featured-image">
        <?php endif; ?>

        <!-- Contenu de l'article -->
        <div class="article-content">
            <?php 
            // Affichage du contenu avec traitement HTML sécurisé
            $content = $article['content'];
            
            // Liste des balises autorisées pour un blog tech
            $allowed_tags = '<p><br><strong><b><em><i><h2><h3><h4><ul><li><ol><a><img><blockquote><code><pre><table><tr><td><th><thead><tbody>';
            $content = strip_tags($content, $allowed_tags);
            
            // Ajouter target="_blank" aux liens externes
            $content = preg_replace('/(<a[^>]*href=["\']https?:\/\/[^"\']*["\'][^>]*)>/i', '$1 target="_blank" rel="noopener">', $content);
            
            echo $content;
            ?>
        </div>

        <!-- Tags -->
        <?php if (!empty($tags)): ?>
            <div class="article-tags">
                <div class="tags-list">
                    <strong>🏷️ Tags:</strong>
                    <?php foreach ($tags as $tag): ?>
                        <a href="index.php?tag=<?php echo urlencode($tag['slug']); ?>" 
                           class="tag-item"
                           style="border-color: <?php echo htmlspecialchars($tag['color']); ?>;">
                            <?php echo htmlspecialchars($tag['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Navigation -->
        <div class="article-nav">
            <a href="index.php" class="back-btn">← Retour au blog</a>
        </div>
    </div>

    <!-- Articles similaires -->
    <?php if (!empty($related_articles)): ?>
        <div class="related-articles">
            <div class="related-header">
                <h3>📰 Articles similaires</h3>
            </div>
            <div class="related-grid">
                <?php foreach ($related_articles as $related): ?>
                    <article class="related-article">
                        <?php if (!empty($related['featured_image'])): ?>
                            <img src="<?php echo htmlspecialchars($related['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($related['title']); ?>">
                        <?php endif; ?>
                        <div class="related-content">
                            <a href="article.php?id=<?php echo $related['id']; ?>" class="related-title">
                                <?php echo htmlspecialchars($related['title']); ?>
                            </a>
                            <div class="related-date">
                                <?php echo date('d/m/Y', strtotime($related['published_at'])); ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Section des commentaires -->
    <div class="comments-section">
        <div class="comments-header">
            <h2 class="comments-count">
                💬 <?php echo $comments_count; ?> commentaire<?php echo $comments_count > 1 ? 's' : ''; ?>
            </h2>
            <p>Partagez votre avis, vos questions ou vos expériences sur cet article !</p>
        </div>

        <!-- Formulaire de commentaire principal -->
        <?php displayCommentForm($article['id']); ?>

        <!-- Liste des commentaires existants -->
        <div class="comments-list">
            <?php displayComments($article['id']); ?>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Mise à jour du compteur de vues (une seule fois par session)
        if (!sessionStorage.getItem('viewed_article_<?php echo $article['id']; ?>')) {
            fetch('update_views.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    article_id: <?php echo $article['id']; ?>
                })
            }).catch(error => {
                console.log('Erreur compteur vues:', error);
            });
            sessionStorage.setItem('viewed_article_<?php echo $article['id']; ?>', 'true');
        }

        // Gestion des formulaires de commentaires
        document.addEventListener("DOMContentLoaded", function() {
            // Gestion de tous les formulaires de commentaires
            document.querySelectorAll(".comment-form").forEach(form => {
                // Compteur de caractères
                const textarea = form.querySelector("textarea[name='content']");
                const charCount = form.querySelector(".char-count");
                
                if (textarea && charCount) {
                    textarea.addEventListener("input", function() {
                        charCount.textContent = this.value.length;
                        
                        if (this.value.length > 1800) {
                            charCount.style.color = "#f44336";
                        } else if (this.value.length > 1500) {
                            charCount.style.color = "#ff9800";
                        } else {
                            charCount.style.color = "#4caf50";
                        }
                    });
                }
                
                // Soumission du formulaire
                form.addEventListener("submit", function(e) {
                    e.preventDefault();
                    submitComment(this);
                });
            });
        });

        function submitComment(form) {
            const submitBtn = form.querySelector(".submit-btn");
            const messageDiv = form.querySelector(".form-message");
            const formData = new FormData(form);
            
            // Ajouter les données du formulaire
            formData.append("article_id", form.dataset.articleId);
            if (form.dataset.parentId) {
                formData.append("parent_id", form.dataset.parentId);
            }
            
            // Désactiver le bouton
            submitBtn.disabled = true;
            submitBtn.textContent = "📤 Envoi en cours...";
            
            // Cacher les messages précédents
            messageDiv.style.display = "none";
            
            fetch("submit_comment.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                messageDiv.style.display = "block";
                messageDiv.className = "form-message " + (data.success ? "success" : "error");
                messageDiv.textContent = data.message;
                
                if (data.success) {
                    form.reset();
                    // Réinitialiser le compteur de caractères
                    const charCount = form.querySelector(".char-count");
                    if (charCount) charCount.textContent = "0";
                    
                    // Actualiser la page après 2 secondes pour voir le nouveau commentaire
                    if (data.data && data.data.status === "approved") {
                        setTimeout(() => location.reload(), 2000);
                    }
                    
                    // Masquer le formulaire de réponse après succès
                    if (form.dataset.parentId) {
                        setTimeout(() => {
                            hideReplyForm(form.dataset.parentId);
                        }, 3000);
                    }
                }
            })
            .catch(error => {
                console.error("Erreur:", error);
                messageDiv.style.display = "block";
                messageDiv.className = "form-message error";
                messageDiv.textContent = "Erreur de connexion. Veuillez réessayer.";
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = "📝 Publier le commentaire";
            });
        }

        function showReplyForm(commentId) {
            // Fermer les autres formulaires de réponse ouverts
            document.querySelectorAll('.reply-form-container').forEach(container => {
                if (container.id !== "reply-form-" + commentId) {
                    container.style.display = "none";
                }
            });
            
            const replyForm = document.getElementById("reply-form-" + commentId);
            if (replyForm) {
                replyForm.style.display = "block";
                replyForm.querySelector("textarea").focus();
                
                // Scroll vers le formulaire
                replyForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        function hideReplyForm(commentId) {
            const replyForm = document.getElementById("reply-form-" + commentId);
            if (replyForm) {
                replyForm.style.display = "none";
                // Reset du formulaire
                const form = replyForm.querySelector("form");
                if (form) {
                    form.reset();
                    const messageDiv = form.querySelector(".form-message");
                    if (messageDiv) messageDiv.style.display = "none";
                    const charCount = form.querySelector(".char-count");
                    if (charCount) charCount.textContent = "0";
                }
            }
        }

        // Animation d'apparition des commentaires
        document.addEventListener("DOMContentLoaded", function() {
            const observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.style.opacity = '0';
                            entry.target.style.transform = 'translateY(20px)';
                            entry.target.style.transition = 'all 0.6s ease';
                            
                            setTimeout(() => {
                                entry.target.style.opacity = '1';
                                entry.target.style.transform = 'translateY(0)';
                            }, 100);
                            
                            observer.unobserve(entry.target);
                        }
                    });
                },
                { threshold: 0.1 }
            );

            document.querySelectorAll('.comment-item').forEach(comment => {
                observer.observe(comment);
            });
        });

        // Amélioration UX: auto-save du formulaire dans localStorage
        document.addEventListener("DOMContentLoaded", function() {
            const mainForm = document.querySelector('.comment-form[data-parent-id=""]');
            if (mainForm) {
                const inputs = mainForm.querySelectorAll('input, textarea');
                
                // Restaurer les données sauvées
                inputs.forEach(input => {
                    const savedValue = localStorage.getItem('comment_' + input.name);
                    if (savedValue) {
                        input.value = savedValue;
                        if (input.name === 'content') {
                            const charCount = mainForm.querySelector('.char-count');
                            if (charCount) charCount.textContent = savedValue.length;
                        }
                    }
                    
                    // Sauvegarder en temps réel
                    input.addEventListener('input', function() {
                        localStorage.setItem('comment_' + this.name, this.value);
                    });
                });
                
                // Nettoyer localStorage après soumission réussie
                mainForm.addEventListener('submit', function() {
                    setTimeout(() => {
                        inputs.forEach(input => {
                            localStorage.removeItem('comment_' + input.name);
                        });
                    }, 2000);
                });
            }
        });
    </script>
</body>
</html>