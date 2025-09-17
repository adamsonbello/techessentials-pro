<?php
// admin-blog/articles/index.php - Gestion des articles
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

// Gestion des actions
$message = '';
$error = '';

// Suppression d'article
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // Vérifier que l'article existe
        $stmt = $pdo->prepare("SELECT title FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        $article = $stmt->fetch();
        
        if ($article) {
            $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
            $stmt->execute([$id]);
            $message = "Article \"" . htmlspecialchars($article['title']) . "\" supprimé avec succès!";
        } else {
            $error = "Article introuvable.";
        }
    } catch (PDOException $e) {
        $error = "Erreur lors de la suppression: " . $e->getMessage();
    }
}

// Changement de statut
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = $_GET['toggle'];
    try {
        // Basculer entre published et draft
        $stmt = $pdo->prepare("
            UPDATE articles 
            SET status = CASE 
                WHEN status = 'published' THEN 'draft' 
                ELSE 'published' 
            END 
            WHERE id = ?
        ");
        $stmt->execute([$id]);
        $message = "Statut de l'article modifié!";
    } catch (PDOException $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

// Filtres et recherche
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'DESC';

// Construction de la requête
$where_clauses = [];
$params = [];

if ($search) {
    $where_clauses[] = "(a.title LIKE ? OR a.content LIKE ? OR a.author LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($filter_status) {
    $where_clauses[] = "a.status = ?";
    $params[] = $filter_status;
}

if ($filter_category) {
    $where_clauses[] = "a.category_id = ?";
    $params[] = $filter_category;
}

$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Pagination
$page = max(1, $_GET['page'] ?? 1);
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Compter le total
$count_sql = "SELECT COUNT(*) as total FROM articles a $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total = $stmt->fetch()['total'];
$total_pages = ceil($total / $per_page);

// Récupérer les articles
$sql = "
    SELECT 
        a.*,
        c.name as category_name,
        COALESCE(a.views, 0) as views
    FROM articles a
    LEFT JOIN categories c ON a.category_id = c.id
    $where_sql
    ORDER BY a.$sort $order
    LIMIT $per_page OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll();

// Récupérer les catégories pour le filtre
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

// Statistiques
$stats = [];
$stmt = $pdo->query("SELECT COUNT(*) as total FROM articles WHERE status = 'published'");
$stats['published'] = $stmt->fetch()['total'];
$stmt = $pdo->query("SELECT COUNT(*) as total FROM articles WHERE status = 'draft'");
$stats['draft'] = $stmt->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Articles - Blog Admin</title>
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
            min-height: 100vh;
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
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #2d3748;
            font-size: 28px;
        }
        
        .btn-new {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.3s;
        }
        
        .btn-new:hover {
            transform: translateY(-2px);
        }
        
        /* Stats */
        .stats-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            flex: 1;
            background: rgba(102, 126, 234, 0.05);
            border-radius: 10px;
            padding: 15px;
            border: 1px solid rgba(102, 126, 234, 0.2);
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            color: #718096;
            font-size: 14px;
            margin-top: 5px;
        }
        
        /* Filters */
        .filters {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .filter-group input,
        .filter-group select {
            padding: 8px 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-search {
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-search:hover {
            background: #5a67d8;
        }
        
        /* Table */
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 12px;
            color: #718096;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
            font-size: 14px;
            text-transform: uppercase;
        }
        
        td {
            padding: 15px 12px;
            border-bottom: 1px solid #f7fafc;
            color: #4a5568;
        }
        
        tr:hover {
            background: #f7fafc;
        }
        
        .article-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
        }
        
        .article-meta {
            font-size: 13px;
            color: #a0aec0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
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
        
        .category-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            background: #edf2f7;
            color: #4a5568;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        .btn-edit {
            background: #4299e1;
            color: white;
        }
        
        .btn-edit:hover {
            background: #3182ce;
        }
        
        .btn-toggle {
            background: #f6ad55;
            color: white;
        }
        
        .btn-toggle:hover {
            background: #ed8936;
        }
        
        .btn-delete {
            background: #fc8181;
            color: white;
        }
        
        .btn-delete:hover {
            background: #f56565;
        }
        
        /* Pagination */
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
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            color: #4a5568;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .pagination a:hover {
            border-color: #667eea;
            color: #667eea;
        }
        
        .pagination .active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        /* Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        
        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #fc8181;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .dashboard {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
            }
            
            .filters {
                flex-direction: column;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .header-top {
                flex-direction: column;
                gap: 15px;
            }
            
            .table-container {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>📝 Blog Admin</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="../dashboard.php">🏠 Dashboard</a>
                <a href="../articles/" class="active">📝 Articles</a>
                <a href="../categories/">📁 Catégories</a>
                <a href="../tags/">🏷️ Tags</a>
                <a href="../newsletter/">📧 Newsletter</a>
                <a href="../users/">👥 Utilisateurs</a>
                <a href="../analytics/">📊 Analytics</a>
                <a href="../settings/">⚙️ Paramètres</a>
                <a href="../logout.php">🚪 Déconnexion</a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Messages -->
            <?php if ($message): ?>
            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                ❌ <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            
            <!-- Header -->
            <div class="header">
                <div class="header-top">
                    <h1>📝 Gestion des Articles</h1>
                    <a href="editor.php" class="btn-new">
                        ➕ Nouvel Article
                    </a>
                </div>
                
                <!-- Stats -->
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-value"><?= $stats['published'] ?></div>
                        <div class="stat-label">Articles publiés</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= $stats['draft'] ?></div>
                        <div class="stat-label">Brouillons</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?= $total ?></div>
                        <div class="stat-label">Total articles</div>
                    </div>
                </div>
                
                <!-- Filters -->
                <form method="GET" class="filters">
                    <div class="filter-group">
                        <input type="text" 
                               name="search" 
                               placeholder="Rechercher un article..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                    
                    <div class="filter-group">
                        <select name="status">
                            <option value="">Tous les statuts</option>
                            <option value="published" <?= $filter_status === 'published' ? 'selected' : '' ?>>Publié</option>
                            <option value="draft" <?= $filter_status === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <select name="category">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $filter_category == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <select name="sort">
                            <option value="created_at" <?= $sort === 'created_at' ? 'selected' : '' ?>>Date création</option>
                            <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Titre</option>
                            <option value="views" <?= $sort === 'views' ? 'selected' : '' ?>>Vues</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-search">🔍 Filtrer</button>
                </form>
            </div>
            
            <!-- Table -->
            <div class="table-container">
                <?php if (empty($articles)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📄</div>
                    <h3>Aucun article trouvé</h3>
                    <p>Commencez par créer votre premier article!</p>
                </div>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th>Statut</th>
                            <th>Vues</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): ?>
                        <tr>
                            <td>
                                <div class="article-title">
                                    <?= htmlspecialchars(substr($article['title'], 0, 50)) ?>
                                    <?= strlen($article['title']) > 50 ? '...' : '' ?>
                                </div>
                                <div class="article-meta">
                                    Par <?= htmlspecialchars($article['author'] ?: 'Anonyme') ?>
                                </div>
                            </td>
                            <td>
                                <?php if ($article['category_name']): ?>
                                <span class="category-badge">
                                    <?= htmlspecialchars($article['category_name']) ?>
                                </span>
                                <?php else: ?>
                                <span style="color: #a0aec0;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $article['status'] ?>">
                                    <?= $article['status'] ?>
                                </span>
                            </td>
                            <td>
                                <?= number_format($article['views']) ?>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="editor.php?id=<?= $article['id'] ?>" class="btn-action btn-edit">
                                        ✏️ Éditer
                                    </a>
                                    <a href="?toggle=<?= $article['id'] ?>&page=<?= $page ?>" 
                                       class="btn-action btn-toggle"
                                       onclick="return confirm('Changer le statut de cet article?')">
                                        🔄 <?= $article['status'] === 'published' ? 'Dépublier' : 'Publier' ?>
                                    </a>
                                    <a href="?delete=<?= $article['id'] ?>&page=<?= $page ?>" 
                                       class="btn-action btn-delete"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article?')">
                                        🗑️ Supprimer
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?page=1<?= $search ? '&search=' . urlencode($search) : '' ?><?= $filter_status ? '&status=' . $filter_status : '' ?><?= $filter_category ? '&category=' . $filter_category : '' ?>">
                    ⏮️ Début
                </a>
                <a href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $filter_status ? '&status=' . $filter_status : '' ?><?= $filter_category ? '&category=' . $filter_category : '' ?>">
                    ◀️ Précédent
                </a>
                <?php endif; ?>
                
                <span class="active">Page <?= $page ?> sur <?= $total_pages ?></span>
                
                <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $filter_status ? '&status=' . $filter_status : '' ?><?= $filter_category ? '&category=' . $filter_category : '' ?>">
                    Suivant ▶️
                </a>
                <a href="?page=<?= $total_pages ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $filter_status ? '&status=' . $filter_status : '' ?><?= $filter_category ? '&category=' . $filter_category : '' ?>">
                    Fin ⏭️
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 50);
            });
        });
        
        // Auto-hide messages
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>