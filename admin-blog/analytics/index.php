<?php
// admin-blog/analytics/index.php - Tableau de bord Analytics
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

// Période sélectionnée (par défaut 30 jours)
$period = $_GET['period'] ?? '30';
$start_date = date('Y-m-d', strtotime("-{$period} days"));
$end_date = date('Y-m-d');

// Statistiques générales
$stats = [];

// Total articles
$stmt = $pdo->query("SELECT COUNT(*) as total FROM articles WHERE status = 'published'");
$stats['total_articles'] = $stmt->fetch()['total'];

// Articles cette période
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM articles WHERE status = 'published' AND created_at >= ?");
$stmt->execute([$start_date]);
$stats['period_articles'] = $stmt->fetch()['total'];

// Total vues (si la colonne existe)
try {
    $stmt = $pdo->query("SELECT COALESCE(SUM(views), 0) as total FROM articles");
    $stats['total_views'] = $stmt->fetch()['total'];
} catch (PDOException $e) {
    $stats['total_views'] = 0;
}

// Abonnés newsletter
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = 'active'");
    $stats['subscribers'] = $stmt->fetch()['total'];
} catch (PDOException $e) {
    $stats['subscribers'] = 0;
}

// Top articles (les plus vus)
$top_articles = [];
try {
    $stmt = $pdo->query("
        SELECT id, title, COALESCE(views, 0) as views, created_at 
        FROM articles 
        WHERE status = 'published' 
        ORDER BY views DESC 
        LIMIT 10
    ");
    $top_articles = $stmt->fetchAll();
} catch (PDOException $e) {
    // La colonne views n'existe peut-être pas
}

// Articles récents
$recent_articles = [];
try {
    $stmt = $pdo->query("
        SELECT id, title, status, created_at 
        FROM articles 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $recent_articles = $stmt->fetchAll();
} catch (PDOException $e) {}

// Statistiques par jour pour le graphique
$daily_stats = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as articles_count
        FROM articles 
        WHERE created_at >= ? AND status = 'published'
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ");
    $stmt->execute([$start_date]);
    $daily_stats = $stmt->fetchAll();
} catch (PDOException $e) {}

// Catégories populaires
$popular_categories = [];
try {
    $stmt = $pdo->query("
        SELECT 
            c.name,
            COUNT(a.id) as article_count
        FROM categories c
        LEFT JOIN articles a ON a.category_id = c.id
        WHERE a.status = 'published'
        GROUP BY c.id, c.name
        ORDER BY article_count DESC
        LIMIT 5
    ");
    $popular_categories = $stmt->fetchAll();
} catch (PDOException $e) {}

// Auteurs actifs
$active_authors = [];
try {
    $stmt = $pdo->prepare("
        SELECT 
            author,
            COUNT(*) as article_count,
            MAX(created_at) as last_post
        FROM articles
        WHERE created_at >= ? AND status = 'published'
        GROUP BY author
        ORDER BY article_count DESC
        LIMIT 5
    ");
    $stmt->execute([$start_date]);
    $active_authors = $stmt->fetchAll();
} catch (PDOException $e) {}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Dashboard Blog</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .dashboard {
            display: flex;
            height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 25px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .sidebar-header h2 {
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-nav {
            padding: 20px 0;
        }
        
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #4a5568;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-nav a:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            transform: translateX(5px);
        }
        
        .sidebar-nav a.active {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border-left: 3px solid #667eea;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }
        
        .header {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #2d3748;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .period-selector {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .period-btn {
            padding: 8px 16px;
            border: 2px solid #e2e8f0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #4a5568;
        }
        
        .period-btn:hover {
            border-color: #667eea;
            color: #667eea;
        }
        
        .period-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .stat-icon.articles { background: rgba(102, 126, 234, 0.1); color: #667eea; }
        .stat-icon.views { background: rgba(72, 187, 120, 0.1); color: #48bb78; }
        .stat-icon.subscribers { background: rgba(237, 137, 54, 0.1); color: #ed8936; }
        .stat-icon.new { background: rgba(159, 122, 234, 0.1); color: #9f7aea; }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #718096;
            font-size: 14px;
        }
        
        /* Charts Section */
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .chart-card h3 {
            color: #2d3748;
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        /* Tables */
        .tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }
        
        .table-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .table-card h3 {
            color: #2d3748;
            margin-bottom: 20px;
            font-size: 18px;
        }
        
        .simple-table {
            width: 100%;
        }
        
        .simple-table tr {
            border-bottom: 1px solid #e2e8f0;
        }
        
        .simple-table tr:last-child {
            border-bottom: none;
        }
        
        .simple-table td {
            padding: 12px 0;
            color: #4a5568;
        }
        
        .simple-table td:last-child {
            text-align: right;
            color: #718096;
            font-size: 14px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge.published { background: #c6f6d5; color: #22543d; }
        .badge.draft { background: #fed7d7; color: #742a2a; }
        
        /* Chart Styles */
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .chart-bar {
            display: flex;
            align-items: flex-end;
            height: 250px;
            gap: 10px;
            padding: 10px 0;
        }
        
        .bar {
            flex: 1;
            background: linear-gradient(to top, #667eea, #9f7aea);
            border-radius: 5px 5px 0 0;
            min-height: 5px;
            position: relative;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .bar:hover {
            opacity: 0.8;
        }
        
        .bar-tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #2d3748;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
            margin-bottom: 5px;
        }
        
        .bar:hover .bar-tooltip {
            opacity: 1;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
            }
            
            .charts-grid,
            .tables-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>📊 Blog Admin</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="../dashboard.php">🏠 Dashboard</a>
                <a href="../articles/">📝 Articles</a>
                <a href="../categories/">📁 Catégories</a>
                <a href="../tags/">🏷️ Tags</a>
                <a href="../newsletter/">📧 Newsletter</a>
                <a href="../users/">👥 Utilisateurs</a>
                <a href="index.php" class="active">📊 Analytics</a>
                <a href="../settings/">⚙️ Paramètres</a>
                <a href="../logout.php">🚪 Déconnexion</a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h1>📊 Analytics Dashboard</h1>
                <p style="color: #718096;">Vue d'ensemble des performances de votre blog</p>
                
                <div class="period-selector">
                    <a href="?period=7" class="period-btn <?= $period == '7' ? 'active' : '' ?>">7 jours</a>
                    <a href="?period=30" class="period-btn <?= $period == '30' ? 'active' : '' ?>">30 jours</a>
                    <a href="?period=90" class="period-btn <?= $period == '90' ? 'active' : '' ?>">3 mois</a>
                    <a href="?period=365" class="period-btn <?= $period == '365' ? 'active' : '' ?>">1 an</a>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon articles">📝</div>
                    <div class="stat-value"><?= number_format($stats['total_articles']) ?></div>
                    <div class="stat-label">Articles publiés</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon views">👁️</div>
                    <div class="stat-value"><?= number_format($stats['total_views']) ?></div>
                    <div class="stat-label">Vues totales</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon subscribers">📧</div>
                    <div class="stat-value"><?= number_format($stats['subscribers']) ?></div>
                    <div class="stat-label">Abonnés newsletter</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon new">✨</div>
                    <div class="stat-value"><?= number_format($stats['period_articles']) ?></div>
                    <div class="stat-label">Nouveaux articles (<?= $period ?> jours)</div>
                </div>
            </div>
            
            <!-- Charts -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3>📈 Publications sur la période</h3>
                    <div class="chart-container">
                        <div class="chart-bar">
                            <?php
                            // Créer un tableau avec tous les jours de la période
                            $all_days = [];
                            for ($i = 0; $i < min($period, 30); $i++) {
                                $date = date('Y-m-d', strtotime("-$i days"));
                                $all_days[$date] = 0;
                            }
                            
                            // Remplir avec les vraies données
                            foreach ($daily_stats as $stat) {
                                if (isset($all_days[$stat['date']])) {
                                    $all_days[$stat['date']] = $stat['articles_count'];
                                }
                            }
                            
                            // Inverser pour avoir l'ordre chronologique
                            $all_days = array_reverse($all_days);
                            
                            $max_value = max(array_values($all_days)) ?: 1;
                            foreach ($all_days as $date => $count):
                                $height = ($count / $max_value) * 100;
                            ?>
                            <div class="bar" style="height: <?= $height ?>%">
                                <div class="bar-tooltip">
                                    <?= date('d/m', strtotime($date)) ?>: <?= $count ?> article(s)
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="chart-card">
                    <h3>📁 Catégories populaires</h3>
                    <?php if (empty($popular_categories)): ?>
                        <p style="color: #718096;">Aucune donnée disponible</p>
                    <?php else: ?>
                        <?php foreach ($popular_categories as $cat): ?>
                        <div style="margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: #4a5568;"><?= htmlspecialchars($cat['name']) ?></span>
                                <span style="color: #718096; font-size: 14px;"><?= $cat['article_count'] ?> articles</span>
                            </div>
                            <div style="background: #e2e8f0; border-radius: 5px; height: 8px; overflow: hidden;">
                                <div style="background: linear-gradient(to right, #667eea, #9f7aea); height: 100%; width: <?= ($cat['article_count'] / $stats['total_articles']) * 100 ?>%;"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Tables -->
            <div class="tables-grid">
                <!-- Top Articles -->
                <div class="table-card">
                    <h3>🔥 Top Articles (plus vus)</h3>
                    <table class="simple-table">
                        <?php if (empty($top_articles)): ?>
                            <tr><td colspan="2" style="text-align: center; color: #718096;">Aucun article avec des vues</td></tr>
                        <?php else: ?>
                            <?php foreach ($top_articles as $article): ?>
                            <tr>
                                <td>
                                    <div><?= htmlspecialchars(substr($article['title'], 0, 40)) ?><?= strlen($article['title']) > 40 ? '...' : '' ?></div>
                                    <div style="color: #718096; font-size: 12px; margin-top: 4px;">
                                        <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                                    </div>
                                </td>
                                <td><?= number_format($article['views']) ?> vues</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </table>
                </div>
                
                <!-- Recent Articles -->
                <div class="table-card">
                    <h3>🕐 Articles récents</h3>
                    <table class="simple-table">
                        <?php if (empty($recent_articles)): ?>
                            <tr><td colspan="2" style="text-align: center; color: #718096;">Aucun article</td></tr>
                        <?php else: ?>
                            <?php foreach ($recent_articles as $article): ?>
                            <tr>
                                <td>
                                    <div><?= htmlspecialchars(substr($article['title'], 0, 40)) ?><?= strlen($article['title']) > 40 ? '...' : '' ?></div>
                                    <div style="margin-top: 4px;">
                                        <span class="badge <?= $article['status'] ?>"><?= $article['status'] ?></span>
                                    </div>
                                </td>
                                <td><?= date('d/m/Y', strtotime($article['created_at'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </table>
                </div>
                
                <!-- Active Authors -->
                <div class="table-card">
                    <h3>✍️ Auteurs actifs</h3>
                    <table class="simple-table">
                        <?php if (empty($active_authors)): ?>
                            <tr><td colspan="2" style="text-align: center; color: #718096;">Aucun auteur actif</td></tr>
                        <?php else: ?>
                            <?php foreach ($active_authors as $author): ?>
                            <tr>
                                <td>
                                    <div><?= htmlspecialchars($author['author'] ?: 'Anonyme') ?></div>
                                    <div style="color: #718096; font-size: 12px; margin-top: 4px;">
                                        Dernier: <?= date('d/m/Y', strtotime($author['last_post'])) ?>
                                    </div>
                                </td>
                                <td><?= $author['article_count'] ?> articles</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
            
            <!-- Export Button -->
            <div style="margin-top: 30px; text-align: center;">
                <button onclick="exportData()" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 12px 30px; border-radius: 8px; font-size: 16px; cursor: pointer; transition: transform 0.3s;">
                    📥 Exporter les données
                </button>
            </div>
        </div>
    </div>
    
    <script>
        // Export function
        function exportData() {
            if (confirm('Voulez-vous exporter les données Analytics en CSV ?')) {
                window.location.href = 'export.php?period=<?= $period ?>';
            }
        }
        
        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card, .chart-card, .table-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>