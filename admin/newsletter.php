<?php
// admin/newsletter.php - Dashboard newsletter sécurisé (version corrigée)
session_start();

// Vérifier l'authentification
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: index.php');
    exit;
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Récupérer le nom d'utilisateur
$admin_user = isset($_SESSION['admin_user']) ? $_SESSION['admin_user'] : 'Admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Admin - TechEssentials Pro</title>
    <!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
      new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
      j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
     'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
      })(window,document,'script','dataLayer','GTM-NXXH9Q4K');
</script>
<!-- End Google Tag Manager -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }
        
        .admin-header {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-header h1 {
            color: #667eea;
            font-size: 1.5rem;
        }
        
        .admin-nav {
            display: flex;
            gap: 15px;
        }
        
        .nav-link {
            color: #667eea;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background 0.2s;
        }
        
        .nav-link:hover, .nav-link.active {
            background: #e7f3ff;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-user span {
            color: #6c757d;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: background 0.2s;
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .dashboard-header h2 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 30px;
        }
        
        .stat-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            border-left: 4px solid #667eea;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin: 20px 30px;
            border-radius: 6px;
            border-left: 4px solid #dc3545;
        }
        
        .refresh-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            margin: 0 30px 20px 30px;
            transition: background 0.2s;
        }
        
        .refresh-btn:hover {
            background: #5a6fd8;
        }
        
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 30px;
        }
        
        .info-box h3 {
            color: #0066cc;
            margin-bottom: 10px;
        }
        
        .api-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
            margin-left: 10px;
        }
        
        .api-status.online {
            background: #d4edda;
            color: #155724;
        }
        
        .api-status.offline {
            background: #f8d7da;
            color: #721c24;
        }
        
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .admin-nav {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NXXH9Q4K"
        height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
    <div class="admin-header">
        <h1>📊 Newsletter Dashboard</h1>
        <div class="admin-nav">
            <a href="dashboard.php" class="nav-link">🏠 Dashboard</a>
            <a href="newsletter.php" class="nav-link active">📊 Stats</a>
            <a href="email-manager.php" class="nav-link">📧 Emails</a>
            <a href="contact-manager.php" class="nav-link">💬 Messages</a>
            <a href="verified-contacts.php" class="nav-link">✅ Contacts Vérifiés</a>
        </div>
        <div class="admin-user">
            <span>Connecté : <?php echo htmlspecialchars($admin_user); ?></span>
            <a href="?logout=1" class="logout-btn">Déconnexion</a>
        </div>
    </div>

    <div class="container">
        <div class="dashboard-header">
            <h2>📊 Statistiques Newsletter</h2>
            <p>Gestion des abonnés TechEssentials Pro</p>
        </div>

        <button onclick="loadStats()" class="refresh-btn">🔄 Actualiser les données</button>
        <span>API Status: <span id="apiStatus" class="api-status offline">Vérification...</span></span>

        <div class="info-box">
            <h3>ℹ️ Informations</h3>
            <p>Cette page d'administration vous permet de suivre les statistiques de votre newsletter en temps réel.</p>
            <p><strong>Prochaines étapes :</strong></p>
            <ul>
                <li>Testez l'inscription newsletter sur votre site</li>
                <li>Vérifiez que les données apparaissent ici</li>
                <li>Configurez l'envoi d'emails pour compléter le système</li>
            </ul>
        </div>

        <div class="stats-grid" id="statsGrid">
            <div class="loading">Chargement des statistiques...</div>
        </div>
    </div>

    <script>
        // API URL corrigé pour le système unifié
        const API_URL = "api.php";

        // Test de connectivité API
        async function testAPI() {
            try {
                const response = await fetch(`${API_URL}?action=getNewsletterStats`);
                const statusElement = document.getElementById('apiStatus');
                
                if (response.ok) {
                    statusElement.textContent = 'En ligne';
                    statusElement.className = 'api-status online';
                } else {
                    statusElement.textContent = 'Erreur HTTP';
                    statusElement.className = 'api-status offline';
                }
            } catch (error) {
                const statusElement = document.getElementById('apiStatus');
                statusElement.textContent = 'Hors ligne';
                statusElement.className = 'api-status offline';
            }
        }

        // Charger les statistiques
        async function loadStats() {
            try {
                const response = await fetch(`${API_URL}?action=getNewsletterStats`);
                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                displayStats(data);

            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('statsGrid').innerHTML = `
                    <div class="error">❌ Erreur de chargement: ${error.message}<br>
                    <small>Vérifiez que votre API est accessible et que les tables existent dans la base de données.</small></div>
                `;
            }
        }

        // Afficher les statistiques
        function displayStats(stats) {
            const statsHtml = `
                <div class="stat-card">
                    <div class="stat-number">${stats.total_active || 0}</div>
                    <div class="stat-label">Abonnés actifs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${stats.this_week || 0}</div>
                    <div class="stat-label">Cette semaine</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${getLanguageCount(stats.by_language, 'en')}</div>
                    <div class="stat-label">🇺🇸 Anglais</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${getLanguageCount(stats.by_language, 'fr')}</div>
                    <div class="stat-label">🇫🇷 Français</div>
                </div>
            `;

            document.getElementById('statsGrid').innerHTML = statsHtml;
        }

        // Helper pour compter par langue
        function getLanguageCount(byLanguage, lang) {
            if (!byLanguage || !Array.isArray(byLanguage)) return 0;
            const found = byLanguage.find(item => item.language === lang);
            return found ? found.count : 0;
        }

        // Auto-chargement
        document.addEventListener('DOMContentLoaded', function() {
            testAPI();
            loadStats();
        });
        
        // Auto-actualisation toutes les 2 minutes
        setInterval(function() {
            testAPI();
            loadStats();
        }, 2 * 60 * 1000);
    </script>
</body>
</html>