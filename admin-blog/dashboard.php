<?php
// admin-blog/dashboard.php - Dashboard principal du blog admin
session_start();

// Vérification de l'authentification
if (!isset($_SESSION['blog_admin_logged']) || $_SESSION['blog_admin_logged'] !== true) {
    header('Location: index.php');
    exit;
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Configuration base de données
$DB_CONFIG = [
    'host' => 'localhost',
    'dbname' => 'techessentials_blog',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

// Connexion à la base de données blog
try {
    $dsn = "mysql:host={$DB_CONFIG['host']};dbname={$DB_CONFIG['dbname']};charset={$DB_CONFIG['charset']}";
    $blogDB = new PDO($dsn, $DB_CONFIG['username'], $DB_CONFIG['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion à la base blog: " . $e->getMessage());
}

// Récupération des statistiques
function getBlogStats($db) {
    try {
        // Stats principales
        $mainStats = $db->query("
            SELECT 
                COUNT(*) as total_articles,
                COUNT(CASE WHEN status = 'published' THEN 1 END) as published_articles,
                COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_articles,
                COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_articles,
                COALESCE(SUM(views_count), 0) as total_views,
                COALESCE(AVG(views_count), 0) as avg_views
            FROM articles
        ")->fetch();
        
        // Stats commentaires
        $commentStats = $db->query("
            SELECT 
                COUNT(*) as total_comments,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_comments,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_comments
            FROM comments
        ")->fetch();
        
        // Stats abonnés newsletter
        $subscriberStats = $db->query("
            SELECT 
                COUNT(*) as total_subscribers,
                COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_subscribers
            FROM newsletter_subscribers
        ")->fetch();
        
        // Stats par catégorie
        $categoryStats = $db->query("
            SELECT 
                c.name,
                c.icon,
                COUNT(a.id) as article_count
            FROM categories c
            LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published'
            GROUP BY c.id, c.name, c.icon
            ORDER BY article_count DESC
        ")->fetchAll();
        
        // Articles récents
        $recentArticles = $db->query("
            SELECT 
                a.id,
                a.title,
                a.status,
                a.views_count,
                a.comments_count,
                a.created_at,
                a.published_at,
                c.name as category_name,
                c.icon as category_icon
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            ORDER BY a.created_at DESC
            LIMIT 5
        ")->fetchAll();
        
        // Commentaires en attente
        $pendingComments = $db->query("
            SELECT 
                cm.id,
                cm.author_name,
                cm.content,
                cm.created_at,
                a.title as article_title
            FROM comments cm
            LEFT JOIN articles a ON cm.article_id = a.id
            WHERE cm.status = 'pending'
            ORDER BY cm.created_at DESC
            LIMIT 5
        ")->fetchAll();
        
        return [
            'main' => $mainStats,
            'comments' => $commentStats,
            'subscribers' => $subscriberStats,
            'categories' => $categoryStats,
            'recent_articles' => $recentArticles,
            'pending_comments' => $pendingComments
        ];
        
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

$stats = getBlogStats($blogDB);
$admin_user = $_SESSION['blog_admin_user'] ?? 'Blog Admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Blog - TechEssentials Pro</title>
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
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background-light);
            color: var(--text-color);
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            overflow-y: auto;
            z-index: 100;
            box-shadow: var(--shadow);
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .sidebar-subtitle {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-section {
            margin-bottom: 25px;
        }

        .nav-section-title {
            padding: 0 20px 10px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.7;
            font-weight: 600;
        }

        .nav-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-item:hover,
        .nav-item.active {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: white;
            color: white;
        }

        .nav-item-icon {
            font-size: 1.2rem;
            margin-right: 12px;
        }

        .nav-item-text {
            flex: 1;
        }

        .badge {
            background: var(--error-color);
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 500;
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* TOPBAR */
        .topbar {
            background: var(--white);
            padding: 15px 30px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-left h1 {
            font-size: 1.6rem;
            color: var(--text-color);
            font-weight: 600;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .quick-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .user-profile:hover {
            background: var(--background-light);
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--text-light);
        }

        /* CONTENT AREA */
        .content {
            padding: 30px;
        }

        /* STATS CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.articles { background: var(--primary-color); }
        .stat-icon.comments { background: var(--info-color); }
        .stat-icon.views { background: var(--success-color); }
        .stat-icon.subscribers { background: var(--warning-color); }

        .stat-content h3 {
            font-size: 1.8rem;
            margin-bottom: 5px;
            font-weight: 700;
        }

        .stat-content p {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .stat-change {
            font-size: 0.8rem;
            padding: 2px 8px;
            border-radius: 4px;
            margin-top: 5px;
            font-weight: 500;
        }

        .stat-change.positive {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
        }

        /* CONTENT SECTIONS */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .content-section {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .section-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .section-content {
            padding: 20px;
        }

        /* ARTICLES LIST */
        .article-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .article-item:last-child {
            border-bottom: none;
        }

        .article-status {
            width: 4px;
            border-radius: 2px;
            flex-shrink: 0;
        }

        .article-status.published { background: var(--success-color); }
        .article-status.draft { background: var(--warning-color); }
        .article-status.scheduled { background: var(--info-color); }

        .article-info {
            flex: 1;
        }

        .article-info h4 {
            font-size: 0.95rem;
            margin-bottom: 8px;
            line-height: 1.4;
            font-weight: 600;
        }

        .article-meta {
            font-size: 0.8rem;
            color: var(--text-light);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .status-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-badge.published {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
        }

        .status-badge.draft {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning-color);
        }

        .status-badge.scheduled {
            background: rgba(33, 150, 243, 0.1);
            color: var(--info-color);
        }

        /* COMMENTS */
        .comment-item {
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .comment-item:last-child {
            border-bottom: none;
        }

        .comment-author {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: var(--text-color);
        }

        .comment-content {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 8px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .comment-meta {
            font-size: 0.75rem;
            color: var(--text-light);
        }

        /* EMPTY STATES */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.1rem;
            margin-bottom: 8px;
            color: var(--text-color);
        }

        /* RESPONSIVE */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .topbar {
                padding: 15px;
            }

            .content {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">📝 TechEssentials Pro</div>
            <div class="sidebar-subtitle">Administration Blog</div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">Principal</div>
                <a href="dashboard.php" class="nav-item active">
                    <div class="nav-item-icon">🏠</div>
                    <div class="nav-item-text">Dashboard</div>
                </a>
                <a href="analytics.php" class="nav-item">
                    <div class="nav-item-icon">📊</div>
                    <div class="nav-item-text">Analytics</div>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Contenu</div>
                <a href="articles/" class="nav-item">
                    <div class="nav-item-icon">📝</div>
                    <div class="nav-item-text">Articles</div>
                    <span class="badge"><?php echo $stats['main']['draft_articles'] ?? 0; ?></span>
                </a>
                <a href="categories/" class="nav-item">
                    <div class="nav-item-icon">📁</div>
                    <div class="nav-item-text">Catégories</div>
                </a>
                <a href="tags/" class="nav-item">
                    <div class="nav-item-icon">🏷️</div>
                    <div class="nav-item-text">Tags</div>
                </a>
                <a href="media/" class="nav-item">
                    <div class="nav-item-icon">🖼️</div>
                    <div class="nav-item-text">Médiathèque</div>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Engagement</div>
                <a href="comments/" class="nav-item">
                    <div class="nav-item-icon">💬</div>
                    <div class="nav-item-text">Commentaires</div>
                    <span class="badge"><?php echo $stats['comments']['pending_comments'] ?? 0; ?></span>
                </a>
                <a href="newsletter/" class="nav-item">
                    <div class="nav-item-icon">💌</div>
                    <div class="nav-item-text">Newsletter</div>
                </a>
                <a href="subscribers/" class="nav-item">
                    <div class="nav-item-icon">👥</div>
                    <div class="nav-item-text">Abonnés</div>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Système</div>
                <a href="settings/" class="nav-item">
                    <div class="nav-item-icon">⚙️</div>
                    <div class="nav-item-text">Paramètres</div>
                </a>
                <a href="users/" class="nav-item">
                    <div class="nav-item-icon">👤</div>
                    <div class="nav-item-text">Utilisateurs</div>
                </a>
                <a href="../admin/" class="nav-item" target="_blank">
                    <div class="nav-item-icon">🔗</div>
                    <div class="nav-item-text">Admin Principal</div>
                </a>
            </div>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <!-- TOPBAR -->
        <header class="topbar">
            <div class="topbar-left">
                <h1>Dashboard Blog</h1>
            </div>
            <div class="topbar-right">
                <div class="quick-actions">
                    <a href="articles/new.php" class="btn btn-primary">
                        ✏️ Nouvel Article
                    </a>
                    <a href="newsletter/send.php" class="btn btn-success">
                        💌 Newsletter
                    </a>
                </div>
                <div class="user-profile">
                    <div class="user-avatar"><?php echo strtoupper(substr($admin_user, 0, 1)); ?></div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($admin_user); ?></div>
                        <div class="user-role">Blog Admin</div>
                    </div>
                </div>
                <a href="?logout=1" class="btn" style="background: #dc3545; color: white;">Déconnexion</a>
            </div>
        </header>

        <!-- CONTENT -->
        <div class="content">
            <!-- STATS CARDS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon articles">📝</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['main']['published_articles'] ?? 0; ?></h3>
                        <p>Articles publiés</p>
                        <?php if (($stats['main']['draft_articles'] ?? 0) > 0): ?>
                            <div class="stat-change positive">+<?php echo $stats['main']['draft_articles']; ?> brouillons</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon comments">💬</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['comments']['approved_comments'] ?? 0; ?></h3>
                        <p>Commentaires</p>
                        <?php if (($stats['comments']['pending_comments'] ?? 0) > 0): ?>
                            <div class="stat-change positive"><?php echo $stats['comments']['pending_comments']; ?> en attente</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon views">👁️</div>
                    <div class="stat-content">
                        <h3><?php echo number_format($stats['main']['total_views'] ?? 0); ?></h3>
                        <p>Vues totales</p>
                        <div class="stat-change positive">Moy: <?php echo round($stats['main']['avg_views'] ?? 0); ?> par article</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon subscribers">📬</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['subscribers']['active_subscribers'] ?? 0; ?></h3>
                        <p>Abonnés newsletter</p>
                        <div class="stat-change positive">Base blog autonome</div>
                    </div>
                </div>
            </div>

            <!-- CONTENT GRID -->
            <div class="content-grid">
                <!-- ARTICLES RÉCENTS -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Articles récents</h2>
                        <a href="articles/new.php" class="btn btn-primary">
                            ✏️ Nouveau
                        </a>
                    </div>
                    <div class="section-content">
                        <?php if (!empty($stats['recent_articles'])): ?>
                            <?php foreach ($stats['recent_articles'] as $article): ?>
                                <div class="article-item">
                                    <div class="article-status <?php echo $article['status']; ?>"></div>
                                    <div class="article-info">
                                        <h4><?php echo htmlspecialchars($article['title']); ?></h4>
                                        <div class="article-meta">
                                            <span><?php echo $article['category_icon']; ?> <?php echo htmlspecialchars($article['category_name']); ?></span>
                                            <span><?php echo date('d/m/Y', strtotime($article['created_at'])); ?></span>
                                            <span>👁️ <?php echo $article['views_count']; ?> vues</span>
                                            <span class="status-badge <?php echo $article['status']; ?>"><?php echo $article['status']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">📝</div>
                                <h3>Aucun article</h3>
                                <p>Créez votre premier article pour commencer</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- COMMENTAIRES EN ATTENTE -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">Commentaires en attente</h2>
                        <span style="color: #666; font-size: 0.9rem;"><?php echo count($stats['pending_comments'] ?? []); ?> nouveaux</span>
                    </div>
                    <div class="section-content">
                        <?php if (!empty($stats['pending_comments'])): ?>
                            <?php foreach ($stats['pending_comments'] as $comment): ?>
                                <div class="comment-item">
                                    <div class="comment-author"><?php echo htmlspecialchars($comment['author_name']); ?></div>
                                    <div class="comment-content"><?php echo htmlspecialchars($comment['content']); ?></div>
                                    <div class="comment-meta">
                                        Sur: <?php echo htmlspecialchars($comment['article_title']); ?> • 
                                        <?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div style="text-align: center; padding: 15px;">
                                <a href="comments/" class="btn btn-primary">Voir tous les commentaires</a>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">💬</div>
                                <h3>Aucun commentaire</h3>
                                <p>Les commentaires en attente apparaîtront ici</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Navigation active
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                // Ne pas changer l'active pour les liens externes ou les vrais liens
                if (!this.getAttribute('href').startsWith('http') && !this.getAttribute('target')) {
                    document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });

        // Mobile sidebar toggle (si nécessaire)
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
        }

        // Auto-refresh des stats toutes les 5 minutes
        setTimeout(function() {
            location.reload();
        }, 5 * 60 * 1000);
    </script>
</body>
</html>