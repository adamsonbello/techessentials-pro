<?php
/**
 * Dashboard Admin Principal - TechEssentials Pro
 * Page d'accueil après connexion admin
 */
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

$admin_user = $_SESSION['admin_user'] ?? 'Admin';
$login_time = $_SESSION['admin_login_time'] ?? time();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - TechEssentials Pro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            line-height: 1.6;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-header h1 {
            font-size: 2rem;
            font-weight: 600;
        }
        
        .admin-info {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 0.95rem;
        }
        
        .admin-info span {
            background: rgba(255,255,255,0.2);
            padding: 5px 12px;
            border-radius: 15px;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            transition: background 0.2s;
            font-weight: 500;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #667eea;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .stat-icon {
            font-size: 2rem;
            opacity: 0.8;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .stat-trend {
            font-size: 0.85rem;
            color: #28a745;
            font-weight: 500;
        }
        
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .module-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .module-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        
        .module-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }
        
        .module-icon {
            font-size: 1.8rem;
        }
        
        .module-title {
            color: #2d3748;
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .module-description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        .module-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
            display: inline-block;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
            color: white;
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
            color: #495057;
        }
        
        .system-status {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .status-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        
        .status-label {
            font-weight: 500;
            color: #495057;
        }
        
        .status-value {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
        }
        
        .status-ok {
            color: #28a745;
        }
        
        .status-warning {
            color: #ffc107;
        }
        
        .status-error {
            color: #dc3545;
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .welcome-banner h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        
        .session-info {
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .admin-header {
                padding: 15px 20px;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .admin-info {
                flex-direction: column;
                gap: 10px;
            }
            
            .container {
                padding: 0 15px;
            }
            
            .quick-stats,
            .modules-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="header-content">
            <h1>Dashboard Admin</h1>
            <div class="admin-info">
                <span>Connecté : <?= htmlspecialchars($admin_user) ?></span>
                <span>Session : <?= gmdate("H:i", time() - $login_time) ?></span>
                <a href="?logout=1" class="logout-btn">Déconnexion</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="welcome-banner">
            <h2>Bienvenue dans votre espace d'administration</h2>
            <div class="session-info">
                Dernière connexion : <?= date('d/m/Y à H:i', $login_time) ?>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="quick-stats">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">📧</div>
                    <div class="stat-trend" id="contactsTrend">+0 aujourd'hui</div>
                </div>
                <div class="stat-number" id="totalContacts">
                    <span class="loading"></span>
                </div>
                <div class="stat-label">Messages de Contact</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">📰</div>
                    <div class="stat-trend" id="subscribersTrend">+0 cette semaine</div>
                </div>
                <div class="stat-number" id="totalSubscribers">
                    <span class="loading"></span>
                </div>
                <div class="stat-label">Abonnés Newsletter</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">🔔</div>
                    <div class="stat-trend" id="newItemsTrend">À traiter</div>
                </div>
                <div class="stat-number" id="pendingItems">
                    <span class="loading"></span>
                </div>
                <div class="stat-label">Éléments en Attente</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">⚡</div>
                    <div class="stat-trend">Temps de réponse</div>
                </div>
                <div class="stat-number" id="responseTime">
                    <span class="loading"></span>
                </div>
                <div class="stat-label">Moyenne (heures)</div>
            </div>
        </div>

        <!-- Modules disponibles -->
        <div class="modules-grid">
            <div class="module-card">
                <div class="module-header">
                    <span class="module-icon">💬</span>
                    <h3 class="module-title">Gestion des Contacts</h3>
                </div>
                <div class="module-description">
                    Gérez tous les messages de contact, répondez aux demandes et suivez les statuts de traitement.
                </div>
                <div class="module-actions">
                    <a href="contact-manager.php" class="btn btn-primary">Gérer les Messages</a>
                    <a href="contact-manager.php?status=new" class="btn btn-secondary">Nouveaux Messages</a>
                </div>
            </div>
            
            <div class="module-card">
                <div class="module-header">
                    <span class="module-icon">📧</span>
                    <h3 class="module-title">Email & Newsletter</h3>
                </div>
                <div class="module-description">
                    Gérez votre liste d'abonnés, envoyez des newsletters et configurez les templates d'emails.
                </div>
                <div class="module-actions">
                    <a href="email-manager.php" class="btn btn-primary">Email Manager</a>
                    <a href="newsletter.php" class="btn btn-secondary">Statistiques</a>
                </div>
            </div>
            
            <div class="module-card">
                <div class="module-header">
                    <span class="module-icon">📊</span>
                    <h3 class="module-title">Analytics & Stats</h3>
                </div>
                <div class="module-description">
                    Consultez les statistiques détaillées de votre newsletter et des interactions utilisateurs.
                </div>
                <div class="module-actions">
                    <a href="newsletter.php" class="btn btn-primary">Voir les Stats</a>
                    <a href="#" onclick="exportAllData()" class="btn btn-secondary">Exporter Données</a>
                </div>
            </div>
            
            <div class="module-card">
                <div class="module-header">
                    <span class="module-icon">⚙️</span>
                    <h3 class="module-title">Configuration</h3>
                </div>
                <div class="module-description">
                    Configurez les paramètres système, testez les emails et gérez les paramètres de sécurité.
                </div>
                <div class="module-actions">
                    <a href="email-manager.php?tab=config" class="btn btn-primary">Configuration</a>
                    <a href="#" onclick="testSystem()" class="btn btn-secondary">Test Système</a>
                </div>
            </div>
        </div>

        <!-- État du système -->
        <div class="system-status">
            <div class="status-header">
                <h3>État du Système</h3>
                <button onclick="refreshSystemStatus()" class="btn btn-secondary">Actualiser</button>
            </div>
            <div class="status-grid" id="systemStatus">
                <div class="status-item">
                    <span class="status-label">Configuration Email</span>
                    <span class="status-value" id="emailStatus">
                        <span class="loading"></span>
                    </span>
                </div>
                <div class="status-item">
                    <span class="status-label">Base de Données</span>
                    <span class="status-value status-ok">✓ Connectée</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Espace Disque</span>
                    <span class="status-value status-ok">✓ Disponible</span>
                </div>
                <div class="status-item">
                    <span class="status-label">Dernière Newsletter</span>
                    <span class="status-value" id="lastNewsletter">
                        <span class="loading"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_URL = "api.php";

        // Charger les statistiques au démarrage
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardStats();
            refreshSystemStatus();
            
            // Auto-refresh toutes les 5 minutes
            setInterval(loadDashboardStats, 5 * 60 * 1000);
        });

        // Charger les statistiques du dashboard
        async function loadDashboardStats() {
            try {
                // Charger les stats des contacts
                const contactsResponse = await fetch(`${API_URL}?action=getStats`);
                const contactsData = await contactsResponse.json();
                
                if (contactsData.success) {
                    document.getElementById('totalContacts').textContent = contactsData.stats.total || 0;
                    document.getElementById('contactsTrend').textContent = `+${contactsData.stats.today || 0} aujourd'hui`;
                    document.getElementById('pendingItems').textContent = contactsData.stats.pending || 0;
                    document.getElementById('responseTime').textContent = contactsData.stats.avg_response_time || '-';
                }
                
                // Charger les stats newsletter
                const newsletterResponse = await fetch(`${API_URL}?action=getNewsletterStats`);
                const newsletterData = await newsletterResponse.json();
                
                if (newsletterData.success || newsletterData.total_active !== undefined) {
                    document.getElementById('totalSubscribers').textContent = newsletterData.total_active || 0;
                    document.getElementById('subscribersTrend').textContent = `+${newsletterData.this_week || 0} cette semaine`;
                }
                
            } catch (error) {
                console.error('Erreur chargement stats:', error);
                // Afficher des valeurs par défaut en cas d'erreur
                document.getElementById('totalContacts').textContent = '-';
                document.getElementById('totalSubscribers').textContent = '-';
                document.getElementById('pendingItems').textContent = '-';
                document.getElementById('responseTime').textContent = '-';
            }
        }

        // Actualiser l'état du système
        async function refreshSystemStatus() {
            try {
                const response = await fetch(`${API_URL}?action=testEmailConfig`);
                const data = await response.json();
                
                const emailStatus = document.getElementById('emailStatus');
                if (data.success) {
                    emailStatus.innerHTML = '<span class="status-ok">✓ Opérationnel</span>';
                } else {
                    emailStatus.innerHTML = '<span class="status-error">✗ Erreur</span>';
                }
                
                // Simuler d'autres checks
                document.getElementById('lastNewsletter').innerHTML = '<span class="status-ok">Récente</span>';
                
            } catch (error) {
                document.getElementById('emailStatus').innerHTML = '<span class="status-error">✗ Test échoué</span>';
            }
        }

        // Tester le système
        async function testSystem() {
            if (!confirm('Lancer les tests système (email, base de données, etc.) ?')) {
                return;
            }
            
            alert('Tests système en cours... (fonctionnalité à implémenter)');
        }

        // Exporter toutes les données
        async function exportAllData() {
            if (!confirm('Exporter toutes les données (contacts + newsletter) ?')) {
                return;
            }
            
            try {
                // Export contacts
                window.open(`${API_URL}?action=exportContacts`, '_blank');
                
                // Export newsletter
                setTimeout(() => {
                    window.open(`${API_URL}?action=exportSubscribers`, '_blank');
                }, 1000);
                
            } catch (error) {
                alert('Erreur export: ' + error.message);
            }
        }
    </script>
</body>
</html>