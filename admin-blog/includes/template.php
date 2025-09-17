<?php
// admin-blog/includes/template.php - Template de base factorisé (VERSION CORRIGÉE)
class BlogAdminTemplate {
    
    private $db;
    private $page_title;
    private $active_nav;
    private $admin_user;
    
    public function __construct($page_title = 'Dashboard Blog', $active_nav = 'dashboard') {
        session_start();
        
        // Vérification auth
        if (!isset($_SESSION['blog_admin_logged']) || $_SESSION['blog_admin_logged'] !== true) {
            header('Location: /techessentialspro/admin-blog/index.php');
            exit;
        }
        
        $this->page_title = $page_title;
        $this->active_nav = $active_nav;
        $this->admin_user = $_SESSION['blog_admin_user'] ?? 'Blog Admin';
        $this->initDatabase();
    }
    
    private function initDatabase() {
        $config = [
            'host' => 'localhost',
            'dbname' => 'techessentials_blog',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4'
        ];
        
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $this->db = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die("Erreur BDD: " . $e->getMessage());
        }
    }
    
    public function getDB() {
        return $this->db;
    }
    
    // NOUVELLE FONCTION : Retourne toujours le chemin absolu correct
    private function getAdminPath() {
        return '/techessentialspro/admin-blog/';
    }
    
    public function getBlogStats() {
        try {
            $mainStats = $this->db->query("
                SELECT 
                    COUNT(*) as total_articles,
                    COUNT(CASE WHEN status = 'published' THEN 1 END) as published_articles,
                    COUNT(CASE WHEN status = 'draft' THEN 1 END) as draft_articles,
                    COALESCE(SUM(views_count), 0) as total_views
                FROM articles
            ")->fetch();
            
            $commentStats = $this->db->query("
                SELECT 
                    COUNT(*) as total_comments,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_comments
                FROM comments
            ")->fetch();
            
            $subscriberStats = $this->db->query("
                SELECT 
                    COUNT(*) as total_subscribers,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_subscribers
                FROM newsletter_subscribers
            ")->fetch();
            
            return [
                'main' => $mainStats,
                'comments' => $commentStats,
                'subscribers' => $subscriberStats
            ];
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function renderHeader() {
        $stats = $this->getBlogStats();
        // UTILISATION DU CHEMIN ABSOLU
        $adminPath = $this->getAdminPath();
        
        echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($this->page_title) . ' - TechEssentials Pro</title>
    <style>' . $this->getCSS() . '</style>
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
                <a href="' . $adminPath . 'dashboard.php" class="nav-item ' . ($this->active_nav === 'dashboard' ? 'active' : '') . '">
                    <div class="nav-item-icon">🏠</div>
                    <div class="nav-item-text">Dashboard</div>
                </a>
                <a href="' . $adminPath . 'analytics/" class="nav-item ' . ($this->active_nav === 'analytics' ? 'active' : '') . '">
                    <div class="nav-item-icon">📊</div>
                    <div class="nav-item-text">Analytics</div>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Contenu</div>
                <a href="' . $adminPath . 'articles/" class="nav-item ' . ($this->active_nav === 'articles' ? 'active' : '') . '">
                    <div class="nav-item-icon">📝</div>
                    <div class="nav-item-text">Articles</div>
                    <span class="badge">' . ($stats['main']['draft_articles'] ?? 0) . '</span>
                </a>
                <a href="' . $adminPath . 'categories/" class="nav-item ' . ($this->active_nav === 'categories' ? 'active' : '') . '">
                    <div class="nav-item-icon">📁</div>
                    <div class="nav-item-text">Catégories</div>
                </a>
                <a href="' . $adminPath . 'tags/" class="nav-item ' . ($this->active_nav === 'tags' ? 'active' : '') . '">
                    <div class="nav-item-icon">🏷️</div>
                    <div class="nav-item-text">Tags</div>
                </a>
                <a href="' . $adminPath . 'media/" class="nav-item ' . ($this->active_nav === 'media' ? 'active' : '') . '">
                    <div class="nav-item-icon">🖼️</div>
                    <div class="nav-item-text">Médiathèque</div>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Engagement</div>
                <a href="' . $adminPath . 'comments/" class="nav-item ' . ($this->active_nav === 'comments' ? 'active' : '') . '">
                    <div class="nav-item-icon">💬</div>
                    <div class="nav-item-text">Commentaires</div>
                    <span class="badge">' . ($stats['comments']['pending_comments'] ?? 0) . '</span>
                </a>
                <a href="' . $adminPath . 'newsletter/" class="nav-item ' . ($this->active_nav === 'newsletter' ? 'active' : '') . '">
                    <div class="nav-item-icon">💌</div>
                    <div class="nav-item-text">Newsletter</div>
                </a>
                <a href="' . $adminPath . 'subscribers/" class="nav-item ' . ($this->active_nav === 'subscribers' ? 'active' : '') . '">
                    <div class="nav-item-icon">👥</div>
                    <div class="nav-item-text">Abonnés</div>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Système</div>
                <a href="' . $adminPath . 'settings/" class="nav-item ' . ($this->active_nav === 'settings' ? 'active' : '') . '">
                    <div class="nav-item-icon">⚙️</div>
                    <div class="nav-item-text">Paramètres</div>
                </a>
                <a href="' . $adminPath . 'users/" class="nav-item ' . ($this->active_nav === 'users' ? 'active' : '') . '">
                    <div class="nav-item-icon">👤</div>
                    <div class="nav-item-text">Utilisateurs</div>
                </a>
                <a href="/techessentialspro/admin/" class="nav-item" target="_blank">
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
                <h1>' . htmlspecialchars($this->page_title) . '</h1>
            </div>
            <div class="topbar-right">
                <div class="quick-actions">
                    <a href="' . $adminPath . 'articles/editor.php" class="btn btn-primary">
                        ✏️ Nouvel Article
                    </a>
                    <a href="' . $adminPath . 'newsletter/templates.php" class="btn btn-success">
                        🎨 Templates
                    </a>
                </div>
                <div class="user-profile">
                    <div class="user-avatar">' . strtoupper(substr($this->admin_user, 0, 1)) . '</div>
                    <div class="user-info">
                        <div class="user-name">' . htmlspecialchars($this->admin_user) . '</div>
                        <div class="user-role">Blog Admin</div>
                    </div>
                </div>
                <a href="' . $adminPath . 'logout.php" class="btn" style="background: #dc3545; color: white;">Déconnexion</a>
            </div>
        </header>

        <div class="content">';
    }
    
    public function renderFooter() {
        echo '
        </div>
    </main>
    
    <script>
        // Gestion active nav - améliorée
        document.addEventListener("DOMContentLoaded", function() {
            // Ne pas modifier les liens avec target="_blank"
            document.querySelectorAll(".nav-item").forEach(item => {
                if (!item.getAttribute("target")) {
                    item.addEventListener("click", function(e) {
                        // Laisser le comportement par défaut pour la navigation
                        // Juste pour effet visuel temporaire
                        document.querySelectorAll(".nav-item").forEach(nav => nav.classList.remove("active"));
                        this.classList.add("active");
                    });
                }
            });
        });
    </script>
</body>
</html>';
    }
    
    private function getCSS() {
        return '
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
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
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

        /* COMMON COMPONENTS */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .content-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .card-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-color);
        }

        .card-content {
            padding: 20px;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: var(--text-color);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .table th {
            background: var(--background-light);
            font-weight: 600;
            color: var(--text-color);
        }

        .table tbody tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-badge.published { background: rgba(76, 175, 80, 0.1); color: var(--success-color); }
        .status-badge.draft { background: rgba(255, 152, 0, 0.1); color: var(--warning-color); }
        .status-badge.pending { background: rgba(255, 193, 7, 0.1); color: #856404; }
        .status-badge.active { background: rgba(40, 167, 69, 0.1); color: var(--success-color); }
        .status-badge.inactive { background: rgba(108, 117, 125, 0.1); color: #495057; }

        .btn-group {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* RESPONSIVE */
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

            .topbar {
                padding: 15px;
            }

            .content {
                padding: 20px 15px;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
        ';
    }
}
?>