<?php
// blog/index.php - Page principale du blog TechEssentials Pro
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

// Paramètres de filtrage
$category_filter = $_GET['category'] ?? '';
$tag_filter = $_GET['tag'] ?? '';
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 12; // Articles par page
$offset = ($page - 1) * $per_page;

// Construction des conditions WHERE
$where_conditions = ["a.status = 'published'"];
$params = [];

if (!empty($category_filter)) {
    $where_conditions[] = "c.slug = ?";
    $params[] = $category_filter;
}

if (!empty($tag_filter)) {
    $where_conditions[] = "EXISTS (
        SELECT 1 FROM article_tags at 
        INNER JOIN tags t ON at.tag_id = t.id 
        WHERE at.article_id = a.id AND t.slug = ?
    )";
    $params[] = $tag_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(a.title LIKE ? OR a.content LIKE ? OR a.excerpt LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Requête principale pour les articles
$articles_query = "
    SELECT 
        a.id, a.title, a.slug, a.excerpt, a.content, a.featured_image, 
        a.published_at, a.views, a.reading_time,
        c.name as category_name, c.slug as category_slug, c.color as category_color, c.icon as category_icon,
        (SELECT COUNT(*) FROM comments WHERE article_id = a.id AND status = 'approved') as comments_count
    FROM articles a
    LEFT JOIN categories c ON a.category_id = c.id
    {$where_clause}
    ORDER BY a.published_at DESC
    LIMIT {$per_page} OFFSET {$offset}
";

$articles_stmt = $pdo->prepare($articles_query);
$articles_stmt->execute($params);
$articles = $articles_stmt->fetchAll();

// Comptage total pour pagination
$count_query = "
    SELECT COUNT(DISTINCT a.id)
    FROM articles a
    LEFT JOIN categories c ON a.category_id = c.id
    {$where_clause}
";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($params);
$total_articles = $count_stmt->fetchColumn();
$total_pages = ceil($total_articles / $per_page);

// Récupération des catégories pour le menu
$categories = $pdo->query("
    SELECT c.*, COUNT(a.id) as article_count
    FROM categories c
    LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published'
    WHERE c.is_active = 1
    GROUP BY c.id
    ORDER BY c.sort_order, c.name
")->fetchAll();

// Récupération des tags populaires
$popular_tags = $pdo->query("
    SELECT t.name, t.slug, t.color, COUNT(at.article_id) as usage_count
    FROM tags t
    INNER JOIN article_tags at ON t.id = at.tag_id
    INNER JOIN articles a ON at.article_id = a.id
    WHERE a.status = 'published'
    GROUP BY t.id
    ORDER BY usage_count DESC
    LIMIT 15
")->fetchAll();

// Articles récents pour la sidebar
$recent_articles = $pdo->query("
    SELECT id, title, slug, published_at, featured_image
    FROM articles
    WHERE status = 'published'
    ORDER BY published_at DESC
    LIMIT 5
")->fetchAll();

// Statistiques globales
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_articles,
        SUM(views) as total_views,
        (SELECT COUNT(*) FROM comments WHERE status = 'approved') as total_comments
    FROM articles 
    WHERE status = 'published'
")->fetch();

// Fonction pour générer l'excerpt
function generateExcerpt($content, $length = 150) {
    $content = strip_tags($content);
    if (strlen($content) <= $length) {
        return $content;
    }
    return substr($content, 0, $length) . '...';
}

// Fonction pour calculer le temps de lecture
function getReadingTime($minutes) {
    if ($minutes <= 1) return '1 min';
    if ($minutes >= 60) {
        $hours = floor($minutes / 60);
        $remaining_minutes = $minutes % 60;
        return $hours . 'h' . ($remaining_minutes > 0 ? ' ' . $remaining_minutes . 'min' : '');
    }
    return $minutes . ' min';
}

// Fonction pour formater les dates
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'À l\'instant';
    if ($time < 3600) return floor($time/60) . ' min';
    if ($time < 86400) return floor($time/3600) . ' h';
    if ($time < 2592000) return floor($time/86400) . ' j';
    if ($time < 31536000) return floor($time/2592000) . ' mois';
    
    return floor($time/31536000) . ' an' . (floor($time/31536000) > 1 ? 's' : '');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php 
        if (!empty($search)) {
            echo "Recherche: " . htmlspecialchars($search) . " - ";
        } elseif (!empty($category_filter)) {
            $current_cat = array_filter($categories, function($cat) use ($category_filter) {
                return $cat['slug'] === $category_filter;
            });
            if ($current_cat) {
                echo htmlspecialchars(current($current_cat)['name']) . " - ";
            }
        }
        ?>
        Blog TechEssentials Pro
    </title>
    <meta name="description" content="Découvrez les dernières actualités tech, tests produits, tutoriels et guides d'achat sur TechEssentials Pro. <?php echo $stats['total_articles']; ?> articles de qualité vous attendent.">
    <meta name="keywords" content="technologie, tests, reviews, tutoriels, guides, high-tech, smartphones, ordinateurs">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Blog TechEssentials Pro">
    <meta property="og:description" content="Votre source d'informations tech de référence">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo "https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}"; ?>">
    
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
            --gradient: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background: var(--background-light);
        }

        /* Header */
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
            background: var(--gradient);
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

        .nav-menu a:hover,
        .nav-menu a.active {
            background: var(--primary-color);
            color: white;
        }

        /* Hero Section */
        .hero-section {
            background: var(--gradient);
            color: white;
            padding: 60px 20px;
            text-align: center;
            margin-bottom: 40px;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero-title {
            font-size: 3rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Filtres et recherche */
        .filters-section {
            max-width: 1200px;
            margin: 0 auto 40px;
            padding: 0 20px;
        }

        .filters-container {
            background: var(--white);
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 20px;
            align-items: center;
        }

        .search-container {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 25px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 20px;
            cursor: pointer;
        }

        .filter-dropdown {
            padding: 10px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            background: var(--white);
            cursor: pointer;
            min-width: 150px;
        }

        .filter-dropdown:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .active-filters {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .filter-tag {
            background: var(--primary-color);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-tag .remove {
            cursor: pointer;
            font-weight: bold;
        }

        /* Layout principal */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 40px;
        }

        /* Grille des articles */
        .articles-section h2 {
            margin-bottom: 25px;
            color: var(--text-color);
            font-size: 1.8rem;
        }

        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .article-card {
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .article-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: var(--background-light);
        }

        .article-content {
            padding: 20px;
        }

        .article-category {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .article-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
            line-height: 1.3;
            color: var(--text-color);
        }

        .article-excerpt {
            color: var(--text-light);
            font-size: 0.95rem;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .article-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: var(--text-light);
            border-top: 1px solid var(--border-color);
            padding-top: 12px;
        }

        .meta-left {
            display: flex;
            gap: 15px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Sidebar */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .sidebar-widget {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        .widget-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .categories-list {
            list-style: none;
        }

        .categories-list li {
            margin-bottom: 8px;
        }

        .categories-list a {
            text-decoration: none;
            color: var(--text-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .categories-list a:hover {
            background: var(--background-light);
            color: var(--primary-color);
        }

        .category-count {
            background: var(--border-color);
            color: var(--text-light);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.8rem;
        }

        .tags-cloud {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .tag-item {
            background: var(--background-light);
            border: 1px solid var(--border-color);
            color: var(--text-color);
            padding: 6px 12px;
            border-radius: 15px;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .tag-item:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .recent-articles-list {
            list-style: none;
        }

        .recent-article-item {
            display: flex;
            gap: 12px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .recent-article-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .recent-article-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            background: var(--background-light);
        }

        .recent-article-content {
            flex: 1;
        }

        .recent-article-title {
            font-size: 0.9rem;
            font-weight: 600;
            line-height: 1.3;
            margin-bottom: 4px;
        }

        .recent-article-title a {
            text-decoration: none;
            color: var(--text-color);
        }

        .recent-article-title a:hover {
            color: var(--primary-color);
        }

        .recent-article-date {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin: 40px 0;
        }

        .pagination a,
        .pagination span {
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination .current {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination .disabled {
            color: var(--text-light);
            cursor: not-allowed;
        }

        /* États vides */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-title {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--text-color);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .main-container {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .sidebar {
                order: -1;
            }

            .articles-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            }
        }

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

            .hero-title {
                font-size: 2.2rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .hero-stats {
                gap: 20px;
            }

            .filters-container {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .articles-grid {
                grid-template-columns: 1fr;
            }

            .article-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .sidebar {
                order: 0;
            }
        }

        @media (max-width: 480px) {
            .hero-section {
                padding: 40px 20px;
            }

            .filters-container {
                padding: 20px;
            }

            .main-container {
                padding: 0 15px;
            }

            .pagination {
                flex-wrap: wrap;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .article-card {
            animation: fadeInUp 0.5s ease-out;
        }

        .article-card:nth-child(1) { animation-delay: 0.1s; }
        .article-card:nth-child(2) { animation-delay: 0.2s; }
        .article-card:nth-child(3) { animation-delay: 0.3s; }
        .article-card:nth-child(4) { animation-delay: 0.4s; }
    </style>
</head>
<body>
    
<!-- 1. HEADER CORRIGÉ POUR LE BLOG (à mettre dans blog/index.php) -->

<header class="site-header">
    <div class="header-content">
        <link rel="stylesheet" href="../assets/css/header-unified.css">
        <a href="../index.php?lang=<?= $lang ?? 'fr' ?>" class="logo">TechEssentials Pro</a>
        <nav>
            <ul class="nav-menu">
                <li><a href="../index.php?lang=<?= $lang ?? 'fr' ?>">🏠 Accueil</a></li>
                <li><a href="../products.php?lang=<?= $lang ?? 'fr' ?>">📱 Produits</a></li>
                <li><a href="../reviews.php?lang=<?= $lang ?? 'fr' ?>">⭐ Tests</a></li>
                <li><a href="index.php?lang=<?= $lang ?? 'fr' ?>" class="active">📝 Blog</a></li>
                <li><a href="../deals.php?lang=<?= $lang ?? 'fr' ?>">💰 Bons Plans</a></li>
                <li><a href="../contact.php?lang=<?= $lang ?? 'fr' ?>">📞 Contact</a></li>
            </ul>
        </nav>
        
        <!-- Ajout du switch langue -->
        <div class="lang-switch">
            <a href="?lang=fr<?= isset($_GET) && count($_GET) > 1 ? '&' . http_build_query(array_diff_key($_GET, ['lang' => ''])) : '' ?>" 
               class="<?= ($lang ?? 'fr') === 'fr' ? 'active' : '' ?>">FR</a>
            <a href="?lang=en<?= isset($_GET) && count($_GET) > 1 ? '&' . http_build_query(array_diff_key($_GET, ['lang' => ''])) : '' ?>" 
               class="<?= ($lang ?? 'fr') === 'en' ? 'active' : '' ?>">EN</a>
        </div>
    </div>
</header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">📝 Blog TechEssentials Pro</h1>
            <p class="hero-subtitle">
                Découvrez les dernières actualités tech, tests approfondis et guides d'experts
            </p>
            
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo number_format($stats['total_articles']); ?></span>
                    <span class="stat-label">Articles</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo number_format($stats['total_views']); ?></span>
                    <span class="stat-label">Lectures</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo number_format($stats['total_comments']); ?></span>
                    <span class="stat-label">Commentaires</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Filtres et recherche -->
    <div class="filters-section">
        <form method="GET" class="filters-container">
            <div class="search-container">
                <input type="text" 
                       name="search" 
                       class="search-input" 
                       placeholder="Rechercher dans les articles..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn">🔍</button>
            </div>
            
            <select name="category" class="filter-dropdown" onchange="this.form.submit()">
                <option value="">🗂️ Toutes les catégories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['slug']); ?>" 
                            <?php echo $category_filter === $category['slug'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['icon'] . ' ' . $category['name']); ?>
                        (<?php echo $category['article_count']; ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="filter-dropdown" style="border: none; background: var(--primary-color); color: white;">
                Filtrer
            </button>
        </form>
        
        <!-- Filtres actifs -->
        <?php if (!empty($category_filter) || !empty($tag_filter) || !empty($search)): ?>
            <div class="active-filters">
                <?php if (!empty($search)): ?>
                    <div class="filter-tag">
                        🔍 "<?php echo htmlspecialchars($search); ?>"
                        <a href="?<?php echo http_build_query(array_filter(['category' => $category_filter, 'tag' => $tag_filter])); ?>" 
                           class="remove">×</a>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($category_filter)): ?>
                    <?php 
                    $current_cat = array_filter($categories, function($cat) use ($category_filter) {
                        return $cat['slug'] === $category_filter;
                    });
                    if ($current_cat): 
                        $cat = current($current_cat);
                    ?>
                        <div class="filter-tag">
                            <?php echo htmlspecialchars($cat['icon'] . ' ' . $cat['name']); ?>
                            <a href="?<?php echo http_build_query(array_filter(['search' => $search, 'tag' => $tag_filter])); ?>" 
                               class="remove">×</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if (!empty($tag_filter)): ?>
                    <div class="filter-tag">
                        🏷️ <?php echo htmlspecialchars($tag_filter); ?>
                        <a href="?<?php echo http_build_query(array_filter(['search' => $search, 'category' => $category_filter])); ?>" 
                           class="remove">×</a>
                    </div>
                <?php endif; ?>
                
                <a href="?" class="filter-tag" style="background: var(--error-color);">
                    ❌ Effacer tous les filtres
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Contenu principal -->
    <div class="main-container">
        <!-- Articles -->
        <main class="articles-section">
            <h2>
                <?php 
                if (!empty($search)) {
                    echo "Résultats pour: \"" . htmlspecialchars($search) . "\"";
                } elseif (!empty($category_filter)) {
                    $current_cat = array_filter($categories, function($cat) use ($category_filter) {
                        return $cat['slug'] === $category_filter;
                    });
                    if ($current_cat) {
                        $cat = current($current_cat);
                        echo htmlspecialchars($cat['icon'] . ' ' . $cat['name']);
                    }
                } elseif (!empty($tag_filter)) {
                    echo "🏷️ Tag: " . htmlspecialchars($tag_filter);
                } else {
                    echo "📰 Derniers articles";
                }
                ?>
                <small style="color: var(--text-light); font-weight: normal;">
                    (<?php echo number_format($total_articles); ?> article<?php echo $total_articles > 1 ? 's' : ''; ?>)
                </small>
            </h2>
            
            <?php if (empty($articles)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📝</div>
                    <h3 class="empty-title">Aucun article trouvé</h3>
                    <p>
                        <?php if (!empty($search) || !empty($category_filter) || !empty($tag_filter)): ?>
                            Essayez de modifier vos filtres ou votre recherche.
                        <?php else: ?>
                            Les articles seront bientôt disponibles.
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($search) || !empty($category_filter) || !empty($tag_filter)): ?>
                        <br>
                        <a href="?" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">
                            ← Voir tous les articles
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="articles-grid">
                    <?php foreach ($articles as $article): ?>
                        <article class="article-card">
                            <a href="article.php?id=<?php echo $article['id']; ?>" style="text-decoration: none; color: inherit;">
                                <?php if (!empty($article['featured_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($article['title']); ?>" 
                                         class="article-image">
                                <?php else: ?>
                                    <div class="article-image" style="display: flex; align-items: center; justify-content: center; font-size: 3rem; background: var(--gradient); color: white;">
                                        📱
                                    </div>
                                <?php endif; ?>
                                
                                <div class="article-content">
                                    <?php if (!empty($article['category_name'])): ?>
                                        <div class="article-category" 
                                             style="background-color: <?php echo htmlspecialchars($article['category_color']); ?>20; color: <?php echo htmlspecialchars($article['category_color']); ?>;">
                                            <span><?php echo htmlspecialchars($article['category_icon']); ?></span>
                                            <span><?php echo htmlspecialchars($article['category_name']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <h3 class="article-title">
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    </h3>
                                    
                                    <p class="article-excerpt">
                                        <?php 
                                        $excerpt = !empty($article['excerpt']) 
                                            ? $article['excerpt'] 
                                            : generateExcerpt($article['content']);
                                        echo htmlspecialchars($excerpt);
                                        ?>
                                    </p>
                                    
                                    <div class="article-meta">
                                        <div class="meta-left">
                                            <div class="meta-item">
                                                <span>📅</span>
                                                <span><?php echo timeAgo($article['published_at']); ?></span>
                                            </div>
                                            <div class="meta-item">
                                                <span>👁️</span>
                                                <span><?php echo number_format($article['views']); ?></span>
                                            </div>
                                            <div class="meta-item">
                                                <span>💬</span>
                                                <span><?php echo $article['comments_count']; ?></span>
                                            </div>
                                        </div>
                                        <div class="meta-item">
                                            <span>⏱️</span>
                                            <span><?php echo getReadingTime($article['reading_time']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                ← Précédent
                            </a>
                        <?php else: ?>
                            <span class="disabled">← Précédent</span>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        
                        if ($start > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">1</a>
                            <?php if ($start > 2): ?>
                                <span>...</span>
                            <?php endif; ?>
                        <?php endif;

                        for ($i = $start; $i <= $end; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor;

                        if ($end < $total_pages): ?>
                            <?php if ($end < $total_pages - 1): ?>
                                <span>...</span>
                            <?php endif; ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>">
                                <?php echo $total_pages; ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                Suivant →
                            </a>
                        <?php else: ?>
                            <span class="disabled">Suivant →</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>

        <!-- Sidebar -->
        <aside class="sidebar">
            <!-- Catégories -->
            <div class="sidebar-widget">
                <h3 class="widget-title">🗂️ Catégories</h3>
                <ul class="categories-list">
                    <li>
                        <a href="?">
                            <span>📰 Tous les articles</span>
                            <span class="category-count"><?php echo $stats['total_articles']; ?></span>
                        </a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="?category=<?php echo urlencode($category['slug']); ?>"
                               <?php echo $category_filter === $category['slug'] ? 'style="background: var(--primary-color); color: white;"' : ''; ?>>
                                <span><?php echo htmlspecialchars($category['icon'] . ' ' . $category['name']); ?></span>
                                <span class="category-count"><?php echo $category['article_count']; ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Tags populaires -->
            <?php if (!empty($popular_tags)): ?>
                <div class="sidebar-widget">
                    <h3 class="widget-title">🏷️ Tags populaires</h3>
                    <div class="tags-cloud">
                        <?php foreach ($popular_tags as $tag): ?>
                            <a href="?tag=<?php echo urlencode($tag['slug']); ?>" 
                               class="tag-item"
                               <?php echo $tag_filter === $tag['slug'] ? 'style="background: var(--primary-color); color: white; border-color: var(--primary-color);"' : ''; ?>>
                                <?php echo htmlspecialchars($tag['name']); ?>
                                <small>(<?php echo $tag['usage_count']; ?>)</small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Articles récents -->
            <?php if (!empty($recent_articles)): ?>
                <div class="sidebar-widget">
                    <h3 class="widget-title">📝 Articles récents</h3>
                    <ul class="recent-articles-list">
                        <?php foreach ($recent_articles as $recent): ?>
                            <li class="recent-article-item">
                                <?php if (!empty($recent['featured_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($recent['featured_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($recent['title']); ?>" 
                                         class="recent-article-image">
                                <?php else: ?>
                                    <div class="recent-article-image" style="display: flex; align-items: center; justify-content: center; background: var(--gradient); color: white; font-size: 1.5rem;">
                                        📱
                                    </div>
                                <?php endif; ?>
                                
                                <div class="recent-article-content">
                                    <div class="recent-article-title">
                                        <a href="article.php?id=<?php echo $recent['id']; ?>">
                                            <?php echo htmlspecialchars($recent['title']); ?>
                                        </a>
                                    </div>
                                    <div class="recent-article-date">
                                        <?php echo timeAgo($recent['published_at']); ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Newsletter -->
      
        </aside>
       </div>
        <?php 
      // Include newsletter
        include '../includes/layouts/newsletter.php';
        ?>
        
    

    <script>
        // Animation au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.article-card').forEach(card => {
            card.style.animationPlayState = 'paused';
            observer.observe(card);
        });

        // Recherche en temps réel (optionnel)
        let searchTimeout;
        const searchInput = document.querySelector('.search-input');
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    if (this.value.length >= 3 || this.value.length === 0) {
                        // Auto-submit après 500ms de pause dans la frappe
                        // this.form.submit();
                    }
                }, 500);
            });
        }

        // Raccourci clavier pour la recherche
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
            }
        });
    </script>
</body>
</html>