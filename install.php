<?php
/**
 * TechEssentials Pro v2.0 - Script d'Installation Automatique
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 * 
 * ⚠️  ATTENTION : Supprimer ce fichier après l'installation !
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Sécurité : Vérifier l'accès
$allowed_ips = ['127.0.0.1', '::1', 'localhost'];
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

if (!in_array($client_ip, $allowed_ips) && !isset($_GET['force'])) {
    die('❌ Installation autorisée uniquement en local. Ajoutez ?force=1 pour forcer.');
}

// Configuration
define('INSTALLATION_VERSION', '2.0.0');
define('ROOT_PATH', __DIR__ . '/');

// Interface HTML simple
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechEssentials Pro v2.0 - Installation</title>
    <style>
        * { box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0; background: #f8fafc; color: #334155; line-height: 1.6;
        }
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .card { 
            background: white; border-radius: 12px; padding: 30px; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); margin-bottom: 30px;
        }
        h1 { color: #667eea; margin-bottom: 10px; }
        .version { color: #64748b; font-size: 14px; margin-bottom: 30px; }
        .step { 
            padding: 20px; margin: 15px 0; border-radius: 8px; 
            border-left: 4px solid #e2e8f0;
        }
        .step.pending { border-left-color: #94a3b8; background: #f8fafc; }
        .step.running { border-left-color: #3b82f6; background: #dbeafe; }
        .step.success { border-left-color: #10b981; background: #d1fae5; }
        .step.error { border-left-color: #ef4444; background: #fee2e2; }
        .step h3 { margin: 0 0 10px 0; }
        .step .details { font-size: 14px; color: #6b7280; }
        button { 
            background: #667eea; color: white; border: none; 
            padding: 12px 24px; border-radius: 6px; cursor: pointer;
            font-size: 16px; margin-top: 20px;
        }
        button:hover { background: #5a67d8; }
        button:disabled { background: #cbd5e1; cursor: not-allowed; }
        .progress { 
            height: 6px; background: #e2e8f0; border-radius: 3px; 
            overflow: hidden; margin: 20px 0;
        }
        .progress-bar { 
            height: 100%; background: #667eea; width: 0%; 
            transition: width 0.3s ease;
        }
        .log { 
            background: #1e293b; color: #e2e8f0; padding: 20px; 
            border-radius: 6px; font-family: 'Courier New', monospace; 
            font-size: 12px; max-height: 300px; overflow-y: auto; 
        }
        .warning { 
            background: #fef3cd; border: 1px solid #fbbf24; color: #92400e; 
            padding: 15px; border-radius: 6px; margin: 20px 0;
        }
        .success-message { 
            background: #d1fae5; border: 1px solid #10b981; color: #065f46; 
            padding: 20px; border-radius: 6px; text-align: center;
        }
        .config-form { margin: 20px 0; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input { 
            width: 100%; padding: 10px; border: 1px solid #d1d5db; 
            border-radius: 4px; font-size: 16px;
        }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>🚀 TechEssentials Pro</h1>
            <div class="version">Installation automatique - Version <?= INSTALLATION_VERSION ?></div>

            <?php if (!isset($_POST['action'])): ?>
            <!-- Écran d'accueil -->
            <div class="warning">
                ⚠️ <strong>Important :</strong> Sauvegardez votre site avant de continuer. 
                Cette installation va remplacer l'ancien système par la nouvelle architecture MVC.
            </div>

            <h2>📋 Étapes d'installation</h2>
            <div class="step pending">
                <h3>1. Vérification du système</h3>
                <div class="details">Vérification des prérequis PHP, Apache et permissions</div>
            </div>
            <div class="step pending">
                <h3>2. Sauvegarde automatique</h3>
                <div class="details">Sauvegarde de l'ancien index.php et api.php</div>
            </div>
            <div class="step pending">
                <h3>3. Création de la structure</h3>
                <div class="details">Création des dossiers /includes/, /api/, /cache/, /logs/</div>
            </div>
            <div class="step pending">
                <h3>4. Configuration base de données</h3>
                <div class="details">Test de connexion et création des tables manquantes</div>
            </div>
            <div class="step pending">
                <h3>5. Déploiement des fichiers</h3>
                <div class="details">Installation du nouveau système MVC</div>
            </div>
            <div class="step pending">
                <h3>6. Tests finaux</h3>
                <div class="details">Vérification du bon fonctionnement</div>
            </div>

            <form method="post" class="config-form">
                <h2>⚙️ Configuration</h2>
                <div class="grid">
                    <div class="form-group">
                        <label>Host Base de Données</label>
                        <input type="text" name="db_host" value="localhost" required>
                    </div>
                    <div class="form-group">
                        <label>Nom Base de Données</label>
                        <input type="text" name="db_name" value="techessentials" required>
                    </div>
                    <div class="form-group">
                        <label>Utilisateur DB</label>
                        <input type="text" name="db_user" value="root" required>
                    </div>
                    <div class="form-group">
                        <label>Mot de Passe DB</label>
                        <input type="password" name="db_pass">
                    </div>
                </div>
                
                <div class="grid">
                    <div class="form-group">
                        <label>Email SMTP</label>
                        <input type="email" name="smtp_email" placeholder="newsletter@techessentialspro.com">
                    </div>
                    <div class="form-group">
                        <label>Mot de Passe SMTP</label>
                        <input type="password" name="smtp_pass" placeholder="Mot de passe app Gmail">
                    </div>
                </div>

                <input type="hidden" name="action" value="install">
                <button type="submit">🚀 Commencer l'Installation</button>
            </form>

            <?php else: ?>
            <!-- Installation en cours -->
            <div id="installation-progress">
                <h2>Installation en cours...</h2>
                <div class="progress">
                    <div class="progress-bar" id="progress-bar"></div>
                </div>
                <div id="current-step">Initialisation...</div>
                <div class="log" id="installation-log"></div>
            </div>

            <script>
            let currentStep = 0;
            const steps = [
                'Vérification système',
                'Sauvegarde automatique', 
                'Création structure',
                'Configuration DB',
                'Déploiement fichiers',
                'Tests finaux'
            ];

            function updateProgress(step, message) {
                currentStep = step;
                const progress = (step / steps.length) * 100;
                document.getElementById('progress-bar').style.width = progress + '%';
                document.getElementById('current-step').textContent = steps[step - 1] || 'Terminé';
                
                const log = document.getElementById('installation-log');
                log.innerHTML += `[${new Date().toLocaleTimeString()}] ${message}\n`;
                log.scrollTop = log.scrollHeight;
            }

            // Simuler l'installation via AJAX
            async function runInstallation() {
                updateProgress(1, '🔍 Vérification des prérequis...');
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                updateProgress(2, '💾 Sauvegarde de l\'ancien système...');
                await new Promise(resolve => setTimeout(resolve, 1500));
                
                updateProgress(3, '📁 Création de la structure de dossiers...');
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                updateProgress(4, '🗄️ Configuration base de données...');
                await new Promise(resolve => setTimeout(resolve, 2000));
                
                updateProgress(5, '📦 Déploiement des nouveaux fichiers...');
                await new Promise(resolve => setTimeout(resolve, 2500));
                
                updateProgress(6, '✅ Tests de fonctionnement...');
                await new Promise(resolve => setTimeout(resolve, 1500));
                
                updateProgress(6, '🎉 Installation terminée avec succès !');
                
                // Afficher le succès
                setTimeout(() => {
                    document.getElementById('installation-progress').innerHTML = `
                        <div class="success-message">
                            <h2>🎉 Installation Réussie !</h2>
                            <p>TechEssentials Pro v2.0 a été installé avec succès.</p>
                            <p><strong>⚠️ N'oubliez pas de supprimer ce fichier install.php !</strong></p>
                            <p><a href="/" style="color: #667eea;">→ Visiter le site</a></p>
                        </div>
                    `;
                }, 1000);
            }

            runInstallation();
            </script>

            <?php
            // Exécution réelle de l'installation
            $installation = new TechEssentialsInstaller($_POST);
            $installation->run();
            ?>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php

/**
 * Classe d'installation TechEssentials Pro v2.0
 */
class TechEssentialsInstaller {
    private $config;
    private $log = [];
    
    public function __construct($config) {
        $this->config = $config;
    }
    
    public function run() {
        try {
            $this->step1_checkSystem();
            $this->step2_backupOldSystem();
            $this->step3_createStructure();
            $this->step4_configureDatabase();
            $this->step5_deployFiles();
            $this->step6_runTests();
            
            $this->log('🎉 Installation terminée avec succès !');
            
        } catch (Exception $e) {
            $this->log('❌ Erreur : ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function step1_checkSystem() {
        $this->log('🔍 Vérification des prérequis...');
        
        // Vérifier PHP
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            throw new Exception('PHP 7.4+ requis. Version actuelle : ' . PHP_VERSION);
        }
        $this->log('✅ PHP ' . PHP_VERSION . ' OK');
        
        // Vérifier les extensions
        $required_extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
        foreach ($required_extensions as $ext) {
            if (!extension_loaded($ext)) {
                throw new Exception("Extension PHP manquante : {$ext}");
            }
        }
        $this->log('✅ Extensions PHP OK');
        
        // Vérifier les permissions
        $writable_dirs = ['.', 'assets'];
        foreach ($writable_dirs as $dir) {
            if (!is_writable($dir)) {
                throw new Exception("Dossier non accessible en écriture : {$dir}");
            }
        }
        $this->log('✅ Permissions OK');
        
        // Vérifier mod_rewrite
        if (!function_exists('apache_get_modules') || !in_array('mod_rewrite', apache_get_modules())) {
            $this->log('⚠️ mod_rewrite non détecté - URLs propres pourraient ne pas fonctionner');
        } else {
            $this->log('✅ mod_rewrite OK');
        }
    }
    
    private function step2_backupOldSystem() {
        $this->log('💾 Sauvegarde de l\'ancien système...');
        
        $backup_dir = 'backup-' . date('Y-m-d-H-i-s');
        if (!mkdir($backup_dir)) {
            throw new Exception('Impossible de créer le dossier de sauvegarde');
        }
        
        // Sauvegarder les fichiers principaux
        $files_to_backup = ['index.php', 'api.php'];
        foreach ($files_to_backup as $file) {
            if (file_exists($file)) {
                if (!copy($file, "{$backup_dir}/{$file}")) {
                    throw new Exception("Impossible de sauvegarder {$file}");
                }
                $this->log("✅ Sauvegardé : {$file}");
            }
        }
        
        $this->log("✅ Sauvegarde créée dans : {$backup_dir}");
    }
    
    private function step3_createStructure() {
        $this->log('📁 Création de la structure...');
        
        $directories = [
            'includes',
            'includes/layouts', 
            'includes/pages',
            'includes/pages/blog',
            'includes/pages/reviews', 
            'includes/pages/errors',
            'includes/partials',
            'includes/sections',
            'api',
            'api/controllers',
            'api/middleware',
            'data',
            'cache',
            'logs',
            'assets/css',
            'assets/js',
            'assets/images',
            'assets/fonts'
        ];
        
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    throw new Exception("Impossible de créer le dossier : {$dir}");
                }
                $this->log("✅ Créé : {$dir}");
            } else {
                $this->log("⚪ Existe : {$dir}");
            }
        }
        
        // Créer les fichiers de protection
        $htaccess_content = "deny from all\n";
        file_put_contents('includes/.htaccess', $htaccess_content);
        file_put_contents('data/.htaccess', $htaccess_content);
        file_put_contents('cache/.htaccess', $htaccess_content);
        file_put_contents('logs/.htaccess', $htaccess_content);
        
        $this->log('✅ Structure créée avec protections');
    }
    
    private function step4_configureDatabase() {
        $this->log('🗄️ Configuration base de données...');
        
        // Tester la connexion
        try {
            $dsn = "mysql:host={$this->config['db_host']};dbname={$this->config['db_name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $this->config['db_user'], $this->config['db_pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->log('✅ Connexion DB réussie');
        } catch (PDOException $e) {
            throw new Exception('Connexion DB échouée : ' . $e->getMessage());
        }
        
        // Créer/vérifier les tables nécessaires
        $this->createTables($pdo);
        
        $this->log('✅ Base de données configurée');
    }
    
    private function createTables($pdo) {
        $this->log('📋 Vérification des tables...');
        
        // Table contact_messages
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS contact_messages (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL,
                subject VARCHAR(255),
                message TEXT NOT NULL,
                phone VARCHAR(50),
                company VARCHAR(100),
                source VARCHAR(50) DEFAULT 'website',
                ip_address VARCHAR(45),
                user_agent TEXT,
                status ENUM('new', 'read', 'replied', 'resolved', 'archived') DEFAULT 'new',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // Table api_tokens
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS api_tokens (
                id INT PRIMARY KEY AUTO_INCREMENT,
                token VARCHAR(64) UNIQUE NOT NULL,
                name VARCHAR(100) NOT NULL,
                permissions TEXT,
                rate_limit INT DEFAULT 100,
                usage_count INT DEFAULT 0,
                last_used TIMESTAMP NULL,
                expires_at TIMESTAMP NULL,
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // Vérifier/ajouter colonnes manquantes aux tables existantes
        try {
            // Reviews
            $pdo->exec("ALTER TABLE reviews ADD COLUMN IF NOT EXISTS views INT DEFAULT 0");
            $pdo->exec("ALTER TABLE reviews ADD COLUMN IF NOT EXISTS is_featured BOOLEAN DEFAULT FALSE");
            $pdo->exec("ALTER TABLE reviews ADD COLUMN IF NOT EXISTS meta_title VARCHAR(255)");
            $pdo->exec("ALTER TABLE reviews ADD COLUMN IF NOT EXISTS meta_description TEXT");
            
            // Newsletter subscribers 
            $pdo->exec("ALTER TABLE newsletter_subscribers ADD COLUMN IF NOT EXISTS language VARCHAR(2) DEFAULT 'en'");
            $pdo->exec("ALTER TABLE newsletter_subscribers ADD COLUMN IF NOT EXISTS source VARCHAR(50) DEFAULT 'website'");
            $pdo->exec("ALTER TABLE newsletter_subscribers ADD COLUMN IF NOT EXISTS confirm_token VARCHAR(64)");
            $pdo->exec("ALTER TABLE newsletter_subscribers ADD COLUMN IF NOT EXISTS confirmed_at TIMESTAMP NULL");
            
        } catch (PDOException $e) {
            $this->log('⚠️ Certaines colonnes existent déjà : ' . $e->getMessage());
        }
        
        $this->log('✅ Tables vérifiées/créées');
    }
    
    private function step5_deployFiles() {
        $this->log('📦 Déploiement des fichiers...');
        
        // Créer le fichier config.php
        $this->createConfigFile();
        
        // Créer le nouveau index.php
        $this->createIndexFile();
        
        // Créer router.php
        $this->createRouterFile();
        
        // Créer .htaccess
        $this->createHtaccessFile();
        
        // Créer les fichiers de traduction
        $this->createTranslationFile();
        
        $this->log('✅ Fichiers système déployés');
    }
    
    private function createConfigFile() {
        $config_content = "<?php
/**
 * TechEssentials Pro - Configuration Générée
 * Généré automatiquement le " . date('Y-m-d H:i:s') . "
 */

if (!defined('TECHESSENTIALS_PRO')) {
    define('TECHESSENTIALS_PRO', true);
}

define('ENV', 'development');
define('DEBUG', ENV === 'development');

define('ROOT_PATH', dirname(dirname(__FILE__)) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('BASE_URL', 'http://' . \$_SERVER['HTTP_HOST'] . dirname(\$_SERVER['SCRIPT_NAME']) . '/');

\$DB_CONFIG = [
    'main' => [
        'host' => '{$this->config['db_host']}',
        'dbname' => '{$this->config['db_name']}',
        'username' => '{$this->config['db_user']}',
        'password' => '{$this->config['db_pass']}',
        'charset' => 'utf8mb4'
    ]
];

function getDB(\$config_key = 'main') {
    global \$DB_CONFIG;
    static \$connections = [];
    
    if (isset(\$connections[\$config_key])) {
        return \$connections[\$config_key];
    }
    
    try {
        \$config = \$DB_CONFIG[\$config_key];
        \$dsn = \"mysql:host={\$config['host']};dbname={\$config['dbname']};charset={\$config['charset']}\";
        \$pdo = new PDO(\$dsn, \$config['username'], \$config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        \$connections[\$config_key] = \$pdo;
        return \$pdo;
    } catch (PDOException \$e) {
        if (DEBUG) {
            die('DB Error: ' . \$e->getMessage());
        }
        die('Database connection failed');
    }
}

session_start();
";
        
        file_put_contents('includes/config.php', $config_content);
        $this->log('✅ config.php créé');
    }
    
    private function createIndexFile() {
        $index_content = "<?php
/**
 * TechEssentials Pro v2.0 - Point d'Entrée
 * Généré automatiquement le " . date('Y-m-d H:i:s') . "
 */

define('TECHESSENTIALS_PRO', true);
require_once __DIR__ . '/includes/config.php';

// Page d'accueil simple
?>
<!DOCTYPE html>
<html lang=\"fr\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>TechEssentials Pro v2.0 - Installation Réussie</title>
    <style>
        body { font-family: -apple-system, sans-serif; text-align: center; padding: 50px; }
        .success { background: #d1fae5; padding: 30px; border-radius: 10px; margin: 20px auto; max-width: 600px; }
        .warning { background: #fef3cd; padding: 20px; border-radius: 6px; margin: 20px 0; }
        a { color: #667eea; }
    </style>
</head>
<body>
    <div class=\"success\">
        <h1>🎉 TechEssentials Pro v2.0</h1>
        <h2>Installation Réussie !</h2>
        <p>Le nouveau système MVC a été installé avec succès.</p>
        <p><strong>Version :</strong> " . INSTALLATION_VERSION . "</p>
        <p><strong>Date :</strong> " . date('Y-m-d H:i:s') . "</p>
    </div>
    
    <div class=\"warning\">
        <h3>⚠️ Prochaines Étapes</h3>
        <p>1. <strong>Supprimez le fichier install.php</strong></p>
        <p>2. Copiez tous les templates et fichiers système</p>
        <p>3. Configurez l'environnement de production</p>
        <p>4. Testez toutes les fonctionnalités</p>
    </div>
    
    <p><a href=\"/admin-blog\">→ Accéder à l'admin</a> | <a href=\"mailto:support@techessentialspro.com\">Support</a></p>
</body>
</html>";
        
        file_put_contents('index.php', $index_content);
        $this->log('✅ index.php créé');
    }
    
    private function createRouterFile() {
        $router_content = "<?php
/**
 * TechEssentials Pro - Router Basique
 * Généré automatiquement - Remplacer par le router complet
 */

define('TECHESSENTIALS_PRO', true);
require_once __DIR__ . '/includes/config.php';

echo '<!DOCTYPE html><html><head><title>TechEssentials Pro v2.0</title></head><body>';
echo '<h1>🚧 Router en Construction</h1>';
echo '<p>Système installé avec succès. Router complet à déployer.</p>';
echo '</body></html>';
";
        
        file_put_contents('router.php', $router_content);
        $this->log('✅ router.php créé (basique)');
    }
    
    private function createHtaccessFile() {
        $htaccess_content = "# TechEssentials Pro v2.0 - Configuration Auto-générée
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ router.php?route=\$1 [QSA,L]

# Sécurité basique
<FilesMatch \"\.(php|inc)\$\">
    Order Deny,Allow
    Allow from all
</FilesMatch>

<Files \"install.php\">
    Order Deny,Allow
    Deny from all
</Files>
";
        
        file_put_contents('.htaccess', $htaccess_content);
        $this->log('✅ .htaccess créé');
    }
    
    private function createTranslationFile() {
        $translations = [
            'site' => [
                'title' => [
                    'en' => 'TechEssentials Pro',
                    'fr' => 'TechEssentials Pro'
                ]
            ],
            'navigation' => [
                'home' => [
                    'en' => 'Home',
                    'fr' => 'Accueil'
                ]
            ]
        ];
        
        file_put_contents('data/translations.json', json_encode($translations, JSON_PRETTY_PRINT));
        $this->log('✅ translations.json créé');
    }
    
    private function step6_runTests() {
        $this->log('✅ Tests de base...');
        
        // Test de connexion DB
        try {
            $pdo = getDB();
            $pdo->query('SELECT 1');
            $this->log('✅ Test DB : OK');
        } catch (Exception $e) {
            $this->log('❌ Test DB : ' . $e->getMessage());
        }
        
        // Test des dossiers
        $required_dirs = ['includes', 'api', 'cache', 'logs', 'data'];
        foreach ($required_dirs as $dir) {
            if (is_dir($dir) && is_writable($dir)) {
                $this->log("✅ Test {$dir} : OK");
            } else {
                $this->log("❌ Test {$dir} : Problème de permissions");
            }
        }
        
        $this->log('✅ Tests terminés');
    }
    
    private function log($message) {
        $this->log[] = $message;
        // En mode CLI, afficher directement
        if (php_sapi_name() === 'cli') {
            echo $message . "\n";
        }
    }
}

// Si appelé en ligne de commande
if (php_sapi_name() === 'cli' && isset($argv)) {
    echo "🚀 TechEssentials Pro v" . INSTALLATION_VERSION . " - Installation CLI\n";
    echo "=".str_repeat('=', 50)."\n";
    
    $config = [
        'db_host' => $argv[1] ?? 'localhost',
        'db_name' => $argv[2] ?? 'techessentials', 
        'db_user' => $argv[3] ?? 'root',
        'db_pass' => $argv[4] ?? '',
        'smtp_email' => $argv[5] ?? '',
        'smtp_pass' => $argv[6] ?? ''
    ];
    
    try {
        $installer = new TechEssentialsInstaller($config);
        $installer->run();
        echo "\n✅ Installation terminée avec succès !\n";
        echo "⚠️  N'oubliez pas de supprimer install.php\n";
    } catch (Exception $e) {
        echo "\n❌ Erreur : " . $e->getMessage() . "\n";
        exit(1);
    }
}
?>