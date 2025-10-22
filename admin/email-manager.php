<?php
// admin/email-manager.php - Gestionnaire d'emails avancé
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

$admin_user = isset($_SESSION['admin_user']) ? $_SESSION['admin_user'] : 'Admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Manager - TechEssentials Pro</title>
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
        
        .logout-btn {
            background: #dc3545;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.2s;
            margin: 5px;
        }
        
        .btn:hover {
            background: #5a6fd8;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .status-indicator {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9rem;
            margin-left: 10px;
        }
        
        .status-online {
            background: #d4edda;
            color: #155724;
        }
        
        .status-offline {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-testing {
            background: #fff3cd;
            color: #856404;
        }
        
        .results-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }
        
        .newsletter-preview {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            margin-top: 15px;
            background: white;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        
        .tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>📧 Email Manager</h1>
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
        <div class="dashboard-cards">
            <!-- Configuration Email -->
            <div class="card">
                <h3>⚙️ Configuration Email</h3>
                <p>Testez votre configuration SMTP</p>
                <button onclick="testEmailConfig()" class="btn">🔧 Tester Configuration</button>
                <button onclick="sendTestEmail()" class="btn btn-success">📤 Envoyer Email de Test</button>
                <div id="configStatus" class="status-indicator status-offline">Non testé</div>
                <div id="configResults" class="results-box" style="display: none;"></div>
            </div>

            <!-- Statistiques Email -->
            <div class="card">
                <h3>📈 Statistiques Email</h3>
                <div id="emailStats">
                    <p>Chargement...</p>
                </div>
                <button onclick="loadEmailStats()" class="btn">🔄 Actualiser</button>
            </div>

            <!-- Actions Rapides -->
            <div class="card">
                <h3>⚡ Actions Rapides</h3>
                <button onclick="sendWelcomeToAll()" class="btn btn-warning">📬 Bienvenue à tous les nouveaux</button>
                <button onclick="exportSubscribers()" class="btn">📥 Exporter Abonnés CSV</button>
                <div class="form-group" style="margin-top: 15px;">
                    <label>Email de test :</label>
                    <input type="email" id="testEmailAddress" value="admin@example.com" placeholder="votre-email@example.com">
                </div>
            </div>
        </div>

        <!-- Onglets -->
        <div class="tabs">
            <div class="tab active" onclick="showTab('newsletter')">📰 Envoyer Newsletter</div>
            <div class="tab" onclick="showTab('templates')">📝 Templates</div>
            <div class="tab" onclick="showTab('subscribers')">👥 Abonnés</div>
        </div>

        <!-- Contenu Newsletter -->
        <div id="newsletter" class="tab-content active">
            <div class="card">
                <h3>📰 Envoyer Newsletter</h3>
                <form id="newsletterForm">
                    <div class="form-group">
                        <label>Langue :</label>
                        <select id="newsletterLanguage">
                            <option value="">Toutes les langues</option>
                            <option value="en">🇺🇸 Anglais</option>
                            <option value="fr">🇫🇷 Français</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Sujet :</label>
                        <input type="text" id="newsletterSubject" placeholder="Ex: Weekly Tech Deals & Reviews" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Contenu HTML :</label>
                        <textarea id="newsletterContent" placeholder="Votre contenu HTML ici...">
<h2>🔥 Cette semaine chez TechEssentials Pro</h2>

<p>Bonjour {{email}},</p>

<p>Voici les meilleures offres tech de cette semaine :</p>

<h3>📱 Deals de la semaine</h3>
<ul>
<li><strong>Webcam Logitech C920</strong> - 30% de réduction</li>
<li><strong>Casque Sony WH-1000XM4</strong> - Meilleur prix de l'année</li>
<li><strong>Clavier mécanique</strong> - Livraison gratuite</li>
</ul>

<h3>📖 Nos derniers avis</h3>
<p>Découvrez notre test du nouveau MacBook Pro M3 et pourquoi il révolutionne le télétravail.</p>

<p>À bientôt,<br>L'équipe TechEssentials Pro</p>
                        </textarea>
                    </div>
                    
                    <button type="button" onclick="previewNewsletter()" class="btn">👁️ Aperçu</button>
                    <button type="button" onclick="sendNewsletter()" class="btn btn-success">📤 Envoyer Newsletter</button>
                </form>
                
                <div id="newsletterPreview" class="newsletter-preview" style="display: none;">
                    <h4>Aperçu de la newsletter :</h4>
                    <div id="previewContent"></div>
                </div>
                
                <div id="newsletterResults" class="results-box" style="display: none;"></div>
            </div>
        </div>

        <!-- Contenu Templates -->
        <div id="templates" class="tab-content">
            <div class="card">
                <h3>📝 Gestion des Templates</h3>
                <p>Templates disponibles :</p>
                <ul>
                    <li><strong>welcome_en.html</strong> - Template de bienvenue anglais</li>
                    <li><strong>welcome_fr.html</strong> - Template de bienvenue français</li>
                </ul>
                
                <div class="form-group">
                    <label>Tester template de bienvenue :</label>
                    <select id="templateLanguage">
                        <option value="en">🇺🇸 Anglais</option>
                        <option value="fr">🇫🇷 Français</option>
                    </select>
                    <button onclick="previewWelcomeTemplate()" class="btn">👁️ Aperçu Template</button>
                </div>
                
                <div id="templatePreview" class="newsletter-preview" style="display: none;">
                    <h4>Aperçu du template :</h4>
                    <div id="templateContent"></div>
                </div>
            </div>
        </div>

        <!-- Contenu Abonnés -->
        <div id="subscribers" class="tab-content">
            <div class="card">
                <h3>👥 Liste des Abonnés</h3>
                <button onclick="loadSubscribers()" class="btn">🔄 Charger Liste</button>
                <button onclick="exportSubscribers()" class="btn btn-success">📥 Exporter CSV</button>
                
                <div id="subscribersTable" style="margin-top: 20px;">
                    <p>Cliquez sur "Charger Liste" pour voir les abonnés.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_URL = "api.php";

        // Gestion des onglets
        function showTab(tabName) {
            // Masquer tous les contenus
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Désactiver tous les onglets
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Activer l'onglet et le contenu sélectionnés
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        // Tester la configuration email
        async function testEmailConfig() {
            const statusEl = document.getElementById('configStatus');
            const resultsEl = document.getElementById('configResults');
            
            statusEl.textContent = 'Test en cours...';
            statusEl.className = 'status-indicator status-testing';
            
            try {
                const response = await fetch(`${API_URL}?action=testEmailConfig`);
                const data = await response.json();
                
                if (data.success) {
                    statusEl.textContent = 'Configuration OK';
                    statusEl.className = 'status-indicator status-online';
                    resultsEl.textContent = `✅ ${data.message}`;
                } else {
                    statusEl.textContent = 'Erreur Configuration';
                    statusEl.className = 'status-indicator status-offline';
                    resultsEl.textContent = `❌ ${data.error}`;
                }
                
                resultsEl.style.display = 'block';
                
            } catch (error) {
                statusEl.textContent = 'Erreur Réseau';
                statusEl.className = 'status-indicator status-offline';
                resultsEl.textContent = `❌ Erreur réseau: ${error.message}`;
                resultsEl.style.display = 'block';
            }
        }

        // Envoyer email de test
        async function sendTestEmail() {
            const testEmail = document.getElementById('testEmailAddress').value;
            const resultsEl = document.getElementById('configResults');
            
            if (!testEmail) {
                alert('Veuillez saisir un email de test');
                return;
            }
            
            resultsEl.textContent = 'Envoi en cours...';
            resultsEl.style.display = 'block';
            
            try {
                const response = await fetch(`${API_URL}?action=sendTestEmail`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        email: testEmail,
                        language: 'fr'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    resultsEl.textContent = `✅ Email de test envoyé à ${testEmail}`;
                } else {
                    resultsEl.textContent = `❌ Erreur: ${data.error}`;
                }
                
            } catch (error) {
                resultsEl.textContent = `❌ Erreur réseau: ${error.message}`;
            }
        }

        // Charger statistiques email
        async function loadEmailStats() {
            const statsEl = document.getElementById('emailStats');
            statsEl.innerHTML = 'Chargement...';
            
            try {
                const response = await fetch(`${API_URL}?action=getNewsletterStats`);
                const data = await response.json();
                
                if (data.error) {
                    statsEl.innerHTML = `❌ ${data.error}`;
                    return;
                }
                
                const enCount = data.by_language?.find(l => l.language === 'en')?.count || 0;
                const frCount = data.by_language?.find(l => l.language === 'fr')?.count || 0;
                
                statsEl.innerHTML = `
                    <p><strong>📊 Total abonnés actifs:</strong> ${data.total_active || 0}</p>
                    <p><strong>🇺🇸 Anglais:</strong> ${enCount}</p>
                    <p><strong>🇫🇷 Français:</strong> ${frCount}</p>
                    <p><strong>📅 Cette semaine:</strong> ${data.this_week || 0}</p>
                    <p><strong>📆 Aujourd'hui:</strong> ${data.today || 0}</p>
                `;
                
            } catch (error) {
                statsEl.innerHTML = `❌ Erreur: ${error.message}`;
            }
        }

        // Aperçu newsletter
        function previewNewsletter() {
            const subject = document.getElementById('newsletterSubject').value;
            const content = document.getElementById('newsletterContent').value;
            const previewEl = document.getElementById('newsletterPreview');
            const previewContent = document.getElementById('previewContent');
            
            if (!subject || !content) {
                alert('Veuillez remplir le sujet et le contenu');
                return;
            }
            
            const processedContent = content
                .replace(/{{email}}/g, 'exemple@email.com')
                .replace(/{{unsubscribe_url}}/g, '#unsubscribe-link');
            
            previewContent.innerHTML = `
                <h4>Sujet: ${subject}</h4>
                <hr>
                <div style="border: 1px solid #ddd; padding: 15px; background: white;">
                    ${processedContent}
                </div>
            `;
            
            previewEl.style.display = 'block';
        }

    
        // SEND WELCOME TO ALL
        async function sendWelcomeToAll() {
            if (!confirm('Envoyer un email de bienvenue à tous les nouveaux abonnés (dernières 24h) via Brevo ?')) {
                return;
           }
    
           try {
               const response = await fetch(`${API_URL}?action=sendWelcomeToAll`, {
            method: 'POST'
        });
        
               const data = await response.json();
        
          if (data.success) {
            alert(`✅ Emails de bienvenue envoyés via Brevo !\n\nTotal: ${data.results.total}\nEnvoyés: ${data.results.sent}\nÉchecs: ${data.results.failed}`);
        } else {
            alert('❌ Erreur: ' + (data.error || 'Erreur inconnue'));
        }
          } catch (error) {
              alert('❌ Erreur réseau: ' + error.message);
    }
}
          

           


        // Envoyer newsletter
        async function sendNewsletter() {
            const subject = document.getElementById('newsletterSubject').value;
            const content = document.getElementById('newsletterContent').value;
            const language = document.getElementById('newsletterLanguage').value;
            const resultsEl = document.getElementById('newsletterResults');
            
            if (!subject || !content) {
                alert('Veuillez remplir le sujet et le contenu');
                return;
            }
            
            if (!confirm('Êtes-vous sûr de vouloir envoyer cette newsletter à tous les abonnés ?')) {
                return;
            }
            
            resultsEl.textContent = 'Envoi en cours...';
            resultsEl.style.display = 'block';
            
            try {
                const response = await fetch(`${API_URL}?action=sendNewsletterBroadcast`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        subject: subject,
                        content: content,
                        language: language || null
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Succès: ${data.results.sent || 0}
                    Échecs: ${data.results.failed || 0}
                    ${data.results.errors ? '\nErreurs:\n' + data.results.errors.join('\n') : ''}`;
                    resultsEl.textContent = `✅ Newsletter envoyée !
                } else {
                    resultsEl.textContent = `❌ Erreur: ${data.error}`;
                }
                
            } catch (error) {
                resultsEl.textContent = `❌ Erreur réseau: ${error.message}`;
            }
        }

        // Charger liste des abonnés
        async function loadSubscribers() {
            const tableEl = document.getElementById('subscribersTable');
            tableEl.innerHTML = 'Chargement...';
            
            try {
                const response = await fetch(`${API_URL}?action=getRecentSubscribers&limit=100`);
                const data = await response.json();
                
                if (data.error) {
                    tableEl.innerHTML = `❌ ${data.error}`;
                    return;
                }
                
                if (!Array.isArray(data) || data.length === 0) {
                    tableEl.innerHTML = 'Aucun abonné trouvé.';
                    return;
                }
                
                let tableHTML = `
                    <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="padding: 10px; border: 1px solid #ddd;">Email</th>
                                <th style="padding: 10px; border: 1px solid #ddd;">Langue</th>
                                <th style="padding: 10px; border: 1px solid #ddd;">Statut</th>
                                <th style="padding: 10px; border: 1px solid #ddd;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                data.forEach(subscriber => {
                    const langFlag = subscriber.language === 'fr' ? '🇫🇷' : '🇺🇸';
                    const statusColor = subscriber.status === 'active' ? 'green' : 'red';
                    
                    tableHTML += `
                        <tr>
                            <td style="padding: 8px; border: 1px solid #ddd;">${subscriber.email}</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">${langFlag} ${subscriber.language.toUpperCase()}</td>
                            <td style="padding: 8px; border: 1px solid #ddd; color: ${statusColor};">${subscriber.status}</td>
                            <td style="padding: 8px; border: 1px solid #ddd;">${new Date(subscriber.subscribed_at).toLocaleDateString()}</td>
                        </tr>
                    `;
                });
                
                tableHTML += '</tbody></table>';
                tableEl.innerHTML = tableHTML;
                
            } catch (error) {
                tableEl.innerHTML = `❌ Erreur: ${error.message}`;
            }
        }

        // Exporter abonnés CSV
        async function exportSubscribers() {
            try {
                window.location.href = `${API_URL}?action=exportSubscribers`;
            } catch (error) {
                alert(`Erreur export: ${error.message}`);
            }
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            loadEmailStats();
            
            // Auto-actualisation des stats toutes les 5 minutes
            setInterval(loadEmailStats, 5 * 60 * 1000);
        });

        // Aperçu template de bienvenue
async function previewWelcomeTemplate() {
    const language = document.getElementById('templateLanguage').value;
    const previewEl = document.getElementById('templatePreview');
    const contentEl = document.getElementById('templateContent');
    
    previewEl.style.display = 'block';
    contentEl.innerHTML = 'Chargement...';
    
    try {
        const response = await fetch(`newsletters/templates/welcome_${language}.html`);
        
        if (!response.ok) {
            throw new Error('Template non trouvé');
        }
        
        const template = await response.text();
        
        // Remplacer les variables pour l'aperçu
        const preview = template
            .replace(/{{email}}/g, 'exemple@email.com')
            .replace(/{{unsubscribe_url}}/g, '#unsubscribe')
            .replace(/{{year}}/g, new Date().getFullYear());
        
        contentEl.innerHTML = preview;
    } catch (error) {
        contentEl.innerHTML = '❌ Template introuvable dans admin/newsletters/templates/welcome_' + language + '.html';
    }
}
    </script>
</body>
</html>