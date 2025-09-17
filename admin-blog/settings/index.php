<?php
// admin-blog/settings/index.php - Paramètres du blog
require_once '../includes/template.php';

$template = new BlogAdminTemplate('Paramètres', 'settings');
$db = $template->getDB();

$message = '';
$error = '';

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'update_general':
                $site_name = trim($_POST['site_name'] ?? '');
                $site_description = trim($_POST['site_description'] ?? '');
                $contact_email = trim($_POST['contact_email'] ?? '');
                $posts_per_page = (int)$_POST['posts_per_page'];
                $allow_comments = isset($_POST['allow_comments']) ? 1 : 0;
                $moderate_comments = isset($_POST['moderate_comments']) ? 1 : 0;
                
                if (empty($site_name)) throw new Exception("Le nom du site est requis");
                if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Email de contact invalide");
                }
                if ($posts_per_page < 1 || $posts_per_page > 50) {
                    throw new Exception("Le nombre d'articles par page doit être entre 1 et 50");
                }
                
                // Mise à jour ou insertion des paramètres
                $settings = [
                    'site_name' => $site_name,
                    'site_description' => $site_description,
                    'contact_email' => $contact_email,
                    'posts_per_page' => $posts_per_page,
                    'allow_comments' => $allow_comments,
                    'moderate_comments' => $moderate_comments
                ];
                
                foreach ($settings as $key => $value) {
                    $stmt = $db->prepare("
                        INSERT INTO blog_settings (setting_key, setting_value) 
                        VALUES (?, ?) 
                        ON DUPLICATE KEY UPDATE 
                        setting_value = VALUES(setting_value), 
                        updated_at = CURRENT_TIMESTAMP
                    ");
                    $stmt->execute([$key, $value]);
                }
                
                $message = "Paramètres généraux mis à jour";
                break;
                
            case 'update_seo':
                $meta_title = trim($_POST['meta_title'] ?? '');
                $meta_description = trim($_POST['meta_description'] ?? '');
                $meta_keywords = trim($_POST['meta_keywords'] ?? '');
                $google_analytics = trim($_POST['google_analytics'] ?? '');
                $google_search_console = trim($_POST['google_search_console'] ?? '');
                
                $seo_settings = [
                    'meta_title' => $meta_title,
                    'meta_description' => $meta_description,
                    'meta_keywords' => $meta_keywords,
                    'google_analytics' => $google_analytics,
                    'google_search_console' => $google_search_console
                ];
                
                foreach ($seo_settings as $key => $value) {
                    $stmt = $db->prepare("
                        INSERT INTO blog_settings (setting_key, setting_value) 
                        VALUES (?, ?) 
                        ON DUPLICATE KEY UPDATE 
                        setting_value = VALUES(setting_value), 
                        updated_at = CURRENT_TIMESTAMP
                    ");
                    $stmt->execute([$key, $value]);
                }
                
                $message = "Paramètres SEO mis à jour";
                break;
                
            case 'update_newsletter':
                $newsletter_enabled = isset($_POST['newsletter_enabled']) ? 1 : 0;
                $smtp_host = trim($_POST['smtp_host'] ?? '');
                $smtp_port = (int)$_POST['smtp_port'];
                $smtp_username = trim($_POST['smtp_username'] ?? '');
                $smtp_password = trim($_POST['smtp_password'] ?? '');
                $from_email = trim($_POST['from_email'] ?? '');
                $from_name = trim($_POST['from_name'] ?? '');
                
                $newsletter_settings = [
                    'newsletter_enabled' => $newsletter_enabled,
                    'smtp_host' => $smtp_host,
                    'smtp_port' => $smtp_port,
                    'smtp_username' => $smtp_username,
                    'smtp_password' => $smtp_password, // En production, il faudrait chiffrer
                    'newsletter_from_email' => $from_email,
                    'newsletter_from_name' => $from_name
                ];
                
                foreach ($newsletter_settings as $key => $value) {
                    $stmt = $db->prepare("
                        INSERT INTO blog_settings (setting_key, setting_value) 
                        VALUES (?, ?) 
                        ON DUPLICATE KEY UPDATE 
                        setting_value = VALUES(setting_value), 
                        updated_at = CURRENT_TIMESTAMP
                    ");
                    $stmt->execute([$key, $value]);
                }
                
                $message = "Paramètres newsletter mis à jour";
                break;
                
            case 'test_email':
                $test_email = trim($_POST['test_email'] ?? '');
                if (empty($test_email) || !filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Email de test invalide");
                }
                
                // Test d'envoi simple
                $test_subject = "Test depuis TechEssentials Pro Blog";
                $test_content = "Ceci est un email de test envoyé depuis votre blog TechEssentials Pro.\n\nSi vous recevez ce message, la configuration email fonctionne correctement.";
                
                if (mail($test_email, $test_subject, $test_content)) {
                    $message = "Email de test envoyé avec succès à $test_email";
                } else {
                    throw new Exception("Échec de l'envoi du test email");
                }
                break;
                
            case 'clear_cache':
                // Simulation de nettoyage du cache
                $cache_files = 0;
                $cache_dirs = ['../cache/', '../tmp/', '../logs/'];
                
                foreach ($cache_dirs as $dir) {
                    if (is_dir($dir)) {
                        $files = glob($dir . '*');
                        foreach ($files as $file) {
                            if (is_file($file) && basename($file) !== '.htaccess') {
                                unlink($file);
                                $cache_files++;
                            }
                        }
                    }
                }
                
                $message = "Cache nettoyé ($cache_files fichiers supprimés)";
                break;
                
            case 'backup_database':
                // Simulation de sauvegarde
                $backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
                // Ici vous pourriez implémenter une vraie sauvegarde MySQL
                $message = "Sauvegarde créée: $backup_file (fonctionnalité à implémenter)";
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Récupération des paramètres actuels
function getSettings($db) {
    $settings = [];
    $stmt = $db->query("SELECT setting_key, setting_value FROM blog_settings");
    
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    // Valeurs par défaut
    $defaults = [
        'site_name' => 'TechEssentials Pro Blog',
        'site_description' => 'Blog tech et innovations',
        'contact_email' => 'contact@techessentialspro.com',
        'posts_per_page' => 10,
        'allow_comments' => 1,
        'moderate_comments' => 1,
        'meta_title' => '',
        'meta_description' => '',
        'meta_keywords' => '',
        'google_analytics' => '',
        'google_search_console' => '',
        'newsletter_enabled' => 1,
        'smtp_host' => '',
        'smtp_port' => 587,
        'smtp_username' => '',
        'smtp_password' => '',
        'newsletter_from_email' => '',
        'newsletter_from_name' => ''
    ];
    
    return array_merge($defaults, $settings);
}


// Créer la table blog_settings AVANT toute utilisation
//createSettingsTable($db);

$settings = getSettings($db);

// Statistiques système
$system_stats = [
    'php_version' => phpversion(),
    'mysql_version' => $db->query("SELECT VERSION() as version")->fetch()['version'],
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'max_execution_time' => ini_get('max_execution_time') . 's',
    'memory_limit' => ini_get('memory_limit')
];

// La table blog_settings est maintenant créée automatiquement dans getSettings()

// Rendu de la page
$template->renderHeader();
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <h2 class="page-title">Paramètres du Blog</h2>
    <div class="btn-group">
        <button onclick="exportSettings()" class="btn btn-outline">📤 Exporter</button>
        <button onclick="backupDatabase()" class="btn btn-success">💾 Sauvegarder</button>
    </div>
</div>

<!-- MESSAGES -->
<?php if ($message): ?>
    <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<!-- ONGLETS -->
<div style="display: flex; background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 30px; overflow: hidden;">
    <button class="tab-btn active" onclick="showTab('general')">🏠 Général</button>
    <button class="tab-btn" onclick="showTab('seo')">🔍 SEO</button>
    <button class="tab-btn" onclick="showTab('newsletter')">📧 Newsletter</button>
    <button class="tab-btn" onclick="showTab('system')">⚙️ Système</button>
</div>

<!-- ONGLET GÉNÉRAL -->
<div id="general-tab" class="tab-content active">
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">Paramètres généraux</h3>
        </div>
        <div class="card-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_general">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <div>
                        <div class="form-group">
                            <label class="form-label">Nom du site *</label>
                            <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name']) ?>" required class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Description du site</label>
                            <textarea name="site_description" rows="3" class="form-control"><?= htmlspecialchars($settings['site_description']) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email de contact *</label>
                            <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email']) ?>" required class="form-control">
                        </div>
                    </div>
                    
                    <div>
                        <div class="form-group">
                            <label class="form-label">Articles par page</label>
                            <input type="number" name="posts_per_page" value="<?= $settings['posts_per_page'] ?>" min="1" max="50" class="form-control">
                            <small style="color: var(--text-light);">Entre 1 et 50 articles</small>
                        </div>
                        
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="allow_comments" <?= $settings['allow_comments'] ? 'checked' : '' ?>>
                                <span>Autoriser les commentaires</span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                <input type="checkbox" name="moderate_comments" <?= $settings['moderate_comments'] ? 'checked' : '' ?>>
                                <span>Modération des commentaires</span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">💾 Sauvegarder</button>
            </form>
        </div>
    </div>
</div>

<!-- ONGLET SEO -->
<div id="seo-tab" class="tab-content" style="display: none;">
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">Référencement (SEO)</h3>
        </div>
        <div class="card-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_seo">
                
                <div class="form-group">
                    <label class="form-label">Titre META (balise title)</label>
                    <input type="text" name="meta_title" value="<?= htmlspecialchars($settings['meta_title']) ?>" class="form-control" placeholder="Titre SEO de votre blog">
                    <small style="color: var(--text-light);">Recommandé: 50-60 caractères</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description META</label>
                    <textarea name="meta_description" rows="3" class="form-control" placeholder="Description de votre blog pour les moteurs de recherche"><?= htmlspecialchars($settings['meta_description']) ?></textarea>
                    <small style="color: var(--text-light);">Recommandé: 150-160 caractères</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Mots-clés META</label>
                    <input type="text" name="meta_keywords" value="<?= htmlspecialchars($settings['meta_keywords']) ?>" class="form-control" placeholder="tech, blog, innovations, gadgets">
                    <small style="color: var(--text-light);">Séparez par des virgules</small>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Google Analytics ID</label>
                        <input type="text" name="google_analytics" value="<?= htmlspecialchars($settings['google_analytics']) ?>" class="form-control" placeholder="G-XXXXXXXXXX">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Google Search Console</label>
                        <input type="text" name="google_search_console" value="<?= htmlspecialchars($settings['google_search_console']) ?>" class="form-control" placeholder="Code de vérification">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">💾 Sauvegarder SEO</button>
            </form>
        </div>
    </div>
</div>

<!-- ONGLET NEWSLETTER -->
<div id="newsletter-tab" class="tab-content" style="display: none;">
    <div class="content-card">
        <div class="card-header">
            <h3 class="card-title">Configuration Newsletter</h3>
        </div>
        <div class="card-content">
            <form method="POST">
                <input type="hidden" name="action" value="update_newsletter">
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="newsletter_enabled" <?= $settings['newsletter_enabled'] ? 'checked' : '' ?>>
                        <span><strong>Activer la newsletter</strong></span>
                    </label>
                    <small style="color: var(--text-light);">Permet l'abonnement et l'envoi de newsletters</small>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <h4 style="margin: 20px 0 15px; color: var(--text-color);">Configuration SMTP</h4>
                        
                        <div class="form-group">
                            <label class="form-label">Serveur SMTP</label>
                            <input type="text" name="smtp_host" value="<?= htmlspecialchars($settings['smtp_host']) ?>" class="form-control" placeholder="smtp.gmail.com">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Port SMTP</label>
                            <input type="number" name="smtp_port" value="<?= $settings['smtp_port'] ?>" class="form-control" placeholder="587">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Nom d'utilisateur</label>
                            <input type="text" name="smtp_username" value="<?= htmlspecialchars($settings['smtp_username']) ?>" class="form-control" placeholder="votre@email.com">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Mot de passe</label>
                            <input type="password" name="smtp_password" value="<?= htmlspecialchars($settings['smtp_password']) ?>" class="form-control" placeholder="••••••••">
                        </div>
                    </div>
                    
                    <div>
                        <h4 style="margin: 20px 0 15px; color: var(--text-color);">Informations expéditeur</h4>
                        
                        <div class="form-group">
                            <label class="form-label">Email expéditeur</label>
                            <input type="email" name="from_email" value="<?= htmlspecialchars($settings['newsletter_from_email']) ?>" class="form-control" placeholder="newsletter@techessentialspro.com">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Nom expéditeur</label>
                            <input type="text" name="from_name" value="<?= htmlspecialchars($settings['newsletter_from_name']) ?>" class="form-control" placeholder="TechEssentials Pro">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Test d'envoi</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="email" name="test_email" class="form-control" placeholder="test@example.com">
                                <button type="button" onclick="testEmail()" class="btn btn-outline">📧 Tester</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">💾 Sauvegarder Newsletter</button>
            </form>
        </div>
    </div>
</div>

<!-- ONGLET SYSTÈME -->
<div id="system-tab" class="tab-content" style="display: none;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        
        <!-- Informations système -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Informations système</h3>
            </div>
            <div class="card-content">
                <table class="table">
                    <tr>
                        <td><strong>Version PHP</strong></td>
                        <td><?= $system_stats['php_version'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Version MySQL</strong></td>
                        <td><?= $system_stats['mysql_version'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Serveur web</strong></td>
                        <td><?= $system_stats['server_software'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Taille max upload</strong></td>
                        <td><?= $system_stats['upload_max_filesize'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Temps d'exécution max</strong></td>
                        <td><?= $system_stats['max_execution_time'] ?></td>
                    </tr>
                    <tr>
                        <td><strong>Limite mémoire</strong></td>
                        <td><?= $system_stats['memory_limit'] ?></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Actions système -->
        <div class="content-card">
            <div class="card-header">
                <h3 class="card-title">Maintenance</h3>
            </div>
            <div class="card-content">
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="clear_cache">
                        <button type="submit" class="btn btn-outline" style="width: 100%; justify-content: flex-start;" onclick="return confirm('Vider le cache ?')">
                            🗑️ Vider le cache
                        </button>
                    </form>
                    
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="backup_database">
                        <button type="submit" class="btn btn-success" style="width: 100%; justify-content: flex-start;" onclick="return confirm('Créer une sauvegarde ?')">
                            💾 Sauvegarder la BDD
                        </button>
                    </form>
                    
                    <button onclick="optimizeDatabase()" class="btn btn-outline" style="width: 100%; justify-content: flex-start;">
                        ⚡ Optimiser la BDD
                    </button>
                    
                    <button onclick="checkUpdates()" class="btn btn-info" style="width: 100%; justify-content: flex-start;">
                        🔄 Vérifier les mises à jour
                    </button>
                    
                    <div style="margin-top: 20px; padding: 15px; background: var(--background-light); border-radius: 8px;">
                        <strong>⚠️ Zone de danger</strong>
                        <p style="color: var(--text-light); font-size: 0.9rem; margin: 8px 0;">Actions irréversibles</p>
                        <button onclick="resetSettings()" class="btn" style="background: var(--error-color); color: white; width: 100%; justify-content: flex-start;">
                            🔥 Reset paramètres
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.tab-btn {
    flex: 1;
    padding: 15px 20px;
    border: none;
    background: white;
    color: var(--text-light);
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
}

.tab-btn.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
    background: rgba(102, 126, 234, 0.05);
}

.tab-btn:hover:not(.active) {
    background: var(--background-light);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}
</style>

<script>
function showTab(tabName) {
    // Masquer tous les onglets
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.style.display = 'none';
        tab.classList.remove('active');
    });
    
    // Retirer la classe active de tous les boutons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Afficher l'onglet sélectionné
    document.getElementById(tabName + '-tab').style.display = 'block';
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Activer le bouton correspondant
    event.target.classList.add('active');
}

function testEmail() {
    const email = document.querySelector('input[name="test_email"]').value;
    if (!email) {
        alert('Veuillez saisir un email de test');
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="test_email">
        <input type="hidden" name="test_email" value="${email}">
    `;
    document.body.appendChild(form);
    form.submit();
}

function exportSettings() {
    alert('Fonctionnalité d\'export en développement');
}

function backupDatabase() {
    if (confirm('Créer une sauvegarde de la base de données ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="backup_database">';
        document.body.appendChild(form);
        form.submit();
    }
}

function optimizeDatabase() {
    if (confirm('Optimiser la base de données ? Cette opération peut prendre quelques minutes.')) {
        alert('Optimisation en cours... (fonctionnalité à implémenter)');
    }
}

function checkUpdates() {
    alert('Vérification des mises à jour... (fonctionnalité à implémenter)');
}

function resetSettings() {
    if (confirm('⚠️ ATTENTION ⚠️\n\nCette action va remettre tous les paramètres aux valeurs par défaut.\nCette action est IRRÉVERSIBLE.\n\nÊtes-vous absolument certain ?')) {
        if (confirm('Dernière confirmation : Supprimer TOUS les paramètres ?')) {
            alert('Reset des paramètres (fonctionnalité à implémenter avec sécurité renforcée)');
        }
    }
}

// Sauvegarde automatique des onglets dans localStorage
document.addEventListener('DOMContentLoaded', function() {
    const activeTab = localStorage.getItem('settings_active_tab') || 'general';
    const tabBtn = document.querySelector(`[onclick="showTab('${activeTab}')"]`);
    if (tabBtn) {
        tabBtn.click();
    }
    
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.getAttribute('onclick').match(/'([^']+)'/)[1];
            localStorage.setItem('settings_active_tab', tabName);
        });
    });
});
</script>

<?php $template->renderFooter(); ?>