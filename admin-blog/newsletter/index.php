<?php
// admin-blog/newsletter/index.php - Système de newsletter unifié avec templates
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

// Configuration email
$EMAIL_CONFIG = [
    'from_email' => 'newsletter@techessentialspro.com',
    'from_name' => 'TechEssentials Pro',
    'reply_to' => 'contact@techessentialspro.com'
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

$message = '';
$error = '';

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'send_newsletter':
                $name = trim($_POST['name'] ?? '');
                $subject = trim($_POST['subject'] ?? '');
                $article_ids = $_POST['article_ids'] ?? [];
                $test_email = trim($_POST['test_email'] ?? '');
                $template = $_POST['template'] ?? 'default';
                
                if (empty($name)) throw new Exception("Le nom de la campagne est requis");
                if (empty($subject)) throw new Exception("Le sujet est requis");
                if (empty($article_ids)) throw new Exception("Sélectionnez au moins un article");
                
                // Récupération des articles sélectionnés
                $placeholders = str_repeat('?,', count($article_ids) - 1) . '?';
                $stmt = $pdo->prepare("
                    SELECT a.*, c.name as category_name, c.color as category_color
                    FROM articles a 
                    LEFT JOIN categories c ON a.category_id = c.id 
                    WHERE a.id IN ($placeholders) AND a.status = 'published'
                    ORDER BY a.created_at DESC
                ");
                $stmt->execute($article_ids);
                $articles = $stmt->fetchAll();
                
                if (empty($articles)) throw new Exception("Aucun article publié trouvé");
                
                // Création de la campagne
                $stmt = $pdo->prepare("
                    INSERT INTO newsletter_campaigns 
                    (name, subject, featured_articles, template, status) 
                    VALUES (?, ?, ?, ?, 'draft')
                ");
                $stmt->execute([
                    $name,
                    $subject, 
                    json_encode($article_ids),
                    $template
                ]);
                $campaign_id = $pdo->lastInsertId();
                
                // Génération du contenu HTML avec le template sélectionné
                $newsletter_content = generateNewsletterContent($articles, $subject, $template, $pdo);
                
                // Mise à jour du contenu de la campagne
                $stmt = $pdo->prepare("UPDATE newsletter_campaigns SET content = ? WHERE id = ?");
                $stmt->execute([$newsletter_content, $campaign_id]);
                
                if (!empty($test_email)) {
                    // Envoi de test
                    $success = sendTestEmail($test_email, $subject, $newsletter_content, $EMAIL_CONFIG);
                    if ($success) {
                        $message = "Email de test envoyé avec succès à $test_email";
                    } else {
                        $error = "Erreur lors de l'envoi du test";
                    }
                } else {
                    // Envoi à tous les abonnés actifs et confirmés
                    $stmt = $pdo->prepare("
                        SELECT email, name, unsubscribe_token 
                        FROM newsletter_subscribers 
                        WHERE is_active = 1 AND is_confirmed = 1
                    ");
                    $stmt->execute();
                    $subscribers = $stmt->fetchAll();
                    
                    $sent_count = 0;
                    foreach ($subscribers as $subscriber) {
                        $personalized_content = personalizeContent($newsletter_content, $subscriber);
                        if (sendEmail($subscriber['email'], $subject, $personalized_content, $EMAIL_CONFIG)) {
                            $sent_count++;
                            
                            // Mise à jour de l'activité de l'abonné
                            $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET last_activity_at = CURRENT_TIMESTAMP WHERE email = ?");
                            $stmt->execute([$subscriber['email']]);
                        }
                    }
                    
                    // Mise à jour de la campagne
                    $stmt = $pdo->prepare("
                        UPDATE newsletter_campaigns 
                        SET status = 'sent', sent_at = CURRENT_TIMESTAMP, 
                            subscribers_count = ?, sent_count = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([count($subscribers), $sent_count, $campaign_id]);
                    
                    $message = "Newsletter envoyée à $sent_count abonnés";
                }
                break;
                
            case 'add_subscriber':
                $email = trim($_POST['email'] ?? '');
                $name = trim($_POST['name'] ?? '');
                $source = $_POST['source'] ?? 'admin';
                
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Email valide requis");
                }
                
                // Génération des tokens
                $unsubscribe_token = bin2hex(random_bytes(32));
                $confirmation_token = bin2hex(random_bytes(32));
                
                $stmt = $pdo->prepare("
                    INSERT INTO newsletter_subscribers 
                    (email, name, source, unsubscribe_token, confirmation_token, 
                     consent_ip, is_confirmed) 
                    VALUES (?, ?, ?, ?, ?, ?, 1)
                ");
                $stmt->execute([
                    $email, $name, $source, $unsubscribe_token, 
                    $confirmation_token, $_SERVER['REMOTE_ADDR']
                ]);
                
                $message = "Abonné ajouté avec succès";
                break;
                
            case 'toggle_subscriber':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("
                    UPDATE newsletter_subscribers 
                    SET is_active = 1 - is_active 
                    WHERE id = ?
                ");
                $stmt->execute([$id]);
                $message = "Statut mis à jour";
                break;
                
            case 'delete_subscriber':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
                $stmt->execute([$id]);
                $message = "Abonné supprimé";
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Statistiques
$stats = getNewsletterStats($pdo);

// Récupération des données selon l'onglet
$tab = $_GET['tab'] ?? 'compose';

$articles = [];
$subscribers = [];
$campaigns = [];
$custom_templates = [];

if ($tab === 'compose') {
    // Articles récents pour la newsletter
    $stmt = $pdo->query("
        SELECT a.*, c.name as category_name, c.color as category_color
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.id 
        WHERE a.status = 'published' 
        ORDER BY a.created_at DESC 
        LIMIT 20
    ");
    $articles = $stmt->fetchAll();
    
    // Templates personnalisés disponibles
    $stmt = $pdo->query("SELECT id, name FROM newsletter_templates WHERE is_active = 1 ORDER BY name");
    $custom_templates = $stmt->fetchAll();
}

if ($tab === 'subscribers') {
    $subscribers = getSubscribers($pdo);
}

if ($tab === 'history') {
    $campaigns = getCampaigns($pdo);
}

// ==================== FONCTIONS UTILITAIRES ====================

/**
 * Récupère les statistiques de la newsletter
 */
function getNewsletterStats($pdo) {
    $stats = [];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM newsletter_subscribers");
    $stats['total_subscribers'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as active FROM newsletter_subscribers WHERE is_active = 1 AND is_confirmed = 1");
    $stats['active_subscribers'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as campaigns FROM newsletter_campaigns");
    $stats['total_campaigns'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) as sent FROM newsletter_campaigns WHERE status = 'sent'");
    $stats['sent_campaigns'] = $stmt->fetchColumn();
    
    return $stats;
}

/**
 * Récupère la liste des abonnés
 */
function getSubscribers($pdo) {
    $stmt = $pdo->query("
        SELECT *, 
               CASE WHEN is_active = 1 AND is_confirmed = 1 THEN 'Actif'
                    WHEN is_active = 1 AND is_confirmed = 0 THEN 'En attente'
                    ELSE 'Inactif' END as status_text
        FROM newsletter_subscribers 
        ORDER BY subscribed_at DESC
    ");
    return $stmt->fetchAll();
}

/**
 * Récupère l'historique des campagnes
 */
function getCampaigns($pdo) {
    $stmt = $pdo->query("
        SELECT *, 
               CASE status 
                   WHEN 'draft' THEN 'Brouillon'
                   WHEN 'scheduled' THEN 'Programmée'
                   WHEN 'sending' THEN 'En cours'
                   WHEN 'sent' THEN 'Envoyée'
                   WHEN 'cancelled' THEN 'Annulée'
               END as status_text
        FROM newsletter_campaigns 
        ORDER BY created_at DESC
    ");
    return $stmt->fetchAll();
}

/**
 * Fonction principale de génération de newsletter
 */
function generateNewsletterContent($articles, $subject, $template, $pdo) {
    if (strpos($template, 'custom_') === 0) {
        // Template personnalisé de la BDD
        $template_id = (int)str_replace('custom_', '', $template);
        return generateCustomTemplate($articles, $subject, $template_id, $pdo);
    } else {
        // Templates intégrés (default, modern, minimal)
        return generateBuiltinTemplate($articles, $subject, $template);
    }
}

/**
 * Génère une newsletter avec un template intégré
 */
function generateBuiltinTemplate($articles, $subject, $template) {
    $styles = getTemplateStyles($template);
    $content = getTemplateStructure($template);
    
    $newsletter_html = '
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($subject) . '</title>
        <style>' . $styles . '</style>
    </head>
    <body>' . $content . '</body>
    </html>';
    
    // Remplacer les variables
    $newsletter_html = str_replace('{{SUBJECT}}', htmlspecialchars($subject), $newsletter_html);
    $newsletter_html = str_replace('{{ARTICLES}}', generateArticlesHtml($articles, $template), $newsletter_html);
    
    return $newsletter_html;
}

/**
 * Génère une newsletter avec un template personnalisé
 */
function generateCustomTemplate($articles, $subject, $template_id, $pdo) {
    $stmt = $pdo->prepare("SELECT html_content FROM newsletter_templates WHERE id = ? AND is_active = 1");
    $stmt->execute([$template_id]);
    $template_data = $stmt->fetch();
    
    if (!$template_data) {
        return generateBuiltinTemplate($articles, $subject, 'default');
    }
    
    $html = $template_data['html_content'];
    
    // Remplacer les variables du système
    $replacements = [
        '{{SITE_NAME}}' => 'TechEssentials Pro',
        '{{NEWSLETTER_TITLE}}' => htmlspecialchars($subject),
        '{{WEBSITE_URL}}' => 'https://techessentialspro.com',
        '{{CURRENT_YEAR}}' => date('Y'),
        '{{ARTICLES}}' => generateArticlesHtml($articles, 'custom')
    ];
    
    foreach ($replacements as $placeholder => $value) {
        $html = str_replace($placeholder, $value, $html);
    }
    
    return $html;
}

/**
 * Retourne les styles CSS selon le template
 */
function getTemplateStyles($template) {
    $common = 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; padding: 0; }
               .container { max-width: 600px; margin: 0 auto; }
               .article { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
               .article:last-child { border-bottom: none; }
               .btn { display: inline-block; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: 500; }';
    
    switch ($template) {
        case 'modern':
            return $common . '
                body { background: #f8f9fa; }
                .container { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 20px; text-align: center; color: white; }
                .content { padding: 40px 30px; }
                .btn { background: #667eea; color: white; }
                .footer { background: #2c3e50; color: white; padding: 30px; text-align: center; }';
                
        case 'minimal':
            return $common . '
                body { background: white; color: #333; }
                .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 30px; margin-bottom: 30px; }
                .header h1 { font-family: Georgia, serif; font-weight: normal; }
                .content { padding: 0 20px; }
                .btn { background: #000; color: white; }
                .footer { border-top: 2px solid #000; padding-top: 20px; margin-top: 40px; text-align: center; }';
                
        default: // 'default'
            return $common . '
                body { background: #f8f9fa; }
                .container { background: white; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px 20px; text-align: center; color: white; }
                .content { padding: 30px 20px; }
                .btn { background: #667eea; color: white; }
                .footer { background: #2c3e50; color: white; padding: 20px; text-align: center; }';
    }
}

/**
 * Retourne la structure HTML selon le template
 */
function getTemplateStructure($template) {
    return '
    <div class="container">
        <div class="header">
            <h1>TechEssentials Pro</h1>
            <p>{{SUBJECT}}</p>
        </div>
        <div class="content">
            <h2>Nouveaux articles</h2>
            {{ARTICLES}}
        </div>
        <div class="footer">
            <p>Merci de votre abonnement !</p>
            <p><a href="https://techessentialspro.com">Visiter le site</a> • <a href="{{UNSUBSCRIBE_URL}}">Se désabonner</a></p>
        </div>
    </div>';
}

/**
 * Génère le HTML des articles selon le template
 */
function generateArticlesHtml($articles, $template) {
    $articles_html = '';
    
    foreach ($articles as $article) {
        $article_url = "https://techessentialspro.com/blog/article.php?id=" . $article['id'];
        $excerpt = substr(strip_tags($article['content']), 0, 200) . '...';
        
        $articles_html .= '
        <div class="article">
            <h2>' . htmlspecialchars($article['title']) . '</h2>
            <div style="color: #666; font-size: 14px; margin-bottom: 15px;">
                <span style="background: ' . htmlspecialchars($article['category_color'] ?? '#667eea') . '; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                    ' . htmlspecialchars($article['category_name'] ?? 'Tech') . '
                </span>
                • ' . date('d/m/Y', strtotime($article['created_at'])) . '
            </div>
            <div style="color: #555; line-height: 1.6; margin-bottom: 15px;">' . htmlspecialchars($excerpt) . '</div>
            <a href="' . $article_url . '" class="btn">Lire l\'article →</a>
        </div>';
    }
    
    return $articles_html;
}

/**
 * Personnalise le contenu pour un abonné
 */
function personalizeContent($content, $subscriber) {
    $unsubscribe_url = "https://techessentialspro.com/newsletter/unsubscribe.php?token=" . $subscriber['unsubscribe_token'];
    return str_replace('{{UNSUBSCRIBE_URL}}', $unsubscribe_url, $content);
}

/**
 * Envoi d'email de test
 */
function sendTestEmail($to, $subject, $content, $config) {
    return sendEmail($to, "[TEST] " . $subject, $content, $config);
}

/**
 * Envoi d'email
 */
function sendEmail($to, $subject, $content, $config) {
    $headers = [
        'From: ' . $config['from_name'] . ' <' . $config['from_email'] . '>',
        'Reply-To: ' . $config['reply_to'],
        'Content-Type: text/html; charset=UTF-8',
        'X-Mailer: TechEssentials Pro Newsletter'
    ];
    
    return mail($to, $subject, $content, implode("\r\n", $headers));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter - TechEssentials Pro</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f8f9fa;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .tabs {
            display: flex;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        .tab {
            flex: 1;
            padding: 1rem 1.5rem;
            text-align: center;
            text-decoration: none;
            color: #666;
            font-weight: 500;
            transition: all 0.3s ease;
            border-right: 1px solid #eee;
        }
        
        .tab:last-child { border-right: none; }
        .tab.active { background: #667eea; color: white; }
        .tab:hover:not(.active) { background: #f8f9fa; }
        
        .content {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }
        
        .required { color: #e74c3c; }
        
        input[type="text"],
        input[type="email"],
        select,
        textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            background: #667eea;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #e74c3c;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-success {
            background: #27ae60;
        }
        
        .btn-success:hover {
            background: #229954;
        }
        
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .article-card {
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .article-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
        
        .article-card.selected {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .article-title {
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .article-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .article-excerpt {
            color: #555;
            line-height: 1.6;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        
        .two-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .template-info {
            background: #e8f4f8;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #2c3e50;
        }
        
        @media (max-width: 768px) {
            .two-columns {
                grid-template-columns: 1fr;
            }
            
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .tabs {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navigation -->
        <div style="margin-bottom: 1rem;">
            <a href="../dashboard.php" class="btn" style="background: #6c757d; margin-right: 1rem;">
                ← Retour au Dashboard
            </a>
            <a href="../" class="btn" style="background: #17a2b8;">
                Articles
            </a>
        </div>
        
        <!-- Header -->
        <div class="header">
            <h1>Newsletter</h1>
            <p>Gérez vos campagnes et abonnés</p>
        </div>
        
        <!-- Statistiques -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['total_subscribers']) ?></div>
                <div class="stat-label">Total abonnés</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['active_subscribers']) ?></div>
                <div class="stat-label">Abonnés actifs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['total_campaigns']) ?></div>
                <div class="stat-label">Campagnes créées</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['sent_campaigns']) ?></div>
                <div class="stat-label">Campagnes envoyées</div>
            </div>
        </div>
        
        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- Onglets -->
        <div class="tabs">
            <a href="?tab=compose" class="tab <?= $tab === 'compose' ? 'active' : '' ?>">
                Composer
            </a>
            <a href="?tab=subscribers" class="tab <?= $tab === 'subscribers' ? 'active' : '' ?>">
                Abonnés (<?= $stats['total_subscribers'] ?>)
            </a>
            <a href="?tab=history" class="tab <?= $tab === 'history' ? 'active' : '' ?>">
                Historique
            </a>
        </div>
        
        <!-- Lien vers gestionnaire de templates -->
        <div style="margin-bottom: 1rem; text-align: right;">
            <a href="templates.php" class="btn" style="background: #8e44ad;">
                Gérer Templates
            </a>
        </div>
        
        <!-- Contenu -->
        <div class="content">
            <?php if ($tab === 'compose'): ?>
                <!-- Composer une newsletter -->
                <h2>Nouvelle Newsletter</h2>
                <p style="color: #666; margin-bottom: 2rem;">Sélectionnez les articles à inclure dans votre newsletter</p>
                
                <form method="POST">
                    <input type="hidden" name="action" value="send_newsletter">
                    
                    <div class="two-columns">
                        <div>
                            <div class="form-group">
                                <label for="name">Nom de la campagne <span class="required">*</span></label>
                                <input type="text" id="name" name="name" placeholder="Ex: Newsletter Tech Février 2025" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="subject">Sujet de l'email <span class="required">*</span></label>
                                <input type="text" id="subject" name="subject" placeholder="Nouveautés tech de la semaine" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="template">Template</label>
                                <select id="template" name="template">
                                    <option value="default">Template par défaut</option>
                                    <option value="modern">Template moderne</option>
                                    <option value="minimal">Template minimal</option>
                                    <?php if (!empty($custom_templates)): ?>
                                        <optgroup label="Templates personnalisés">
                                            <?php foreach ($custom_templates as $tmpl): ?>
                                                <option value="custom_<?= $tmpl['id'] ?>">
                                                    <?= htmlspecialchars($tmpl['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>
                                </select>
                                <?php if (!empty($custom_templates)): ?>
                                    <div class="template-info">
                                        Templates personnalisés disponibles: <?= count($custom_templates) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div>
                            <div class="form-group">
                                <label for="test_email">Email de test (optionnel)</label>
                                <input type="email" id="test_email" name="test_email" placeholder="votre-email@example.com">
                                <small style="color: #666;">Laissez vide pour envoyer directement à tous les abonnés</small>
                            </div>
                            
                            <div class="form-group">
                                <button type="button" id="select-all" class="btn btn-sm">Sélectionner tout</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Articles disponibles -->
                    <div class="form-group">
                        <label>Articles à inclure <span class="required">*</span></label>
                        <div class="articles-grid" id="articles-section">
                            <?php foreach ($articles as $article): ?>
                                <div class="article-card" onclick="toggleArticle(<?= $article['id'] ?>)">
                                    <input type="checkbox" name="article_ids[]" value="<?= $article['id'] ?>" style="display: none;">
                                    <div class="article-title"><?= htmlspecialchars($article['title']) ?></div>
                                    <div class="article-meta">
                                        <span style="background: <?= htmlspecialchars($article['category_color'] ?? '#667eea') ?>; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem;">
                                            <?= htmlspecialchars($article['category_name'] ?? 'Tech') ?>
                                        </span>
                                        • <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                                    </div>
                                    <div class="article-excerpt">
                                        <?= htmlspecialchars(substr(strip_tags($article['content']), 0, 150)) ?>...
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        Envoyer la Newsletter
                    </button>
                </form>
                
            <?php elseif ($tab === 'subscribers'): ?>
                <!-- Gestion des abonnés -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h2>Abonnés</h2>
                    <button onclick="showAddSubscriber()" class="btn">+ Ajouter un abonné</button>
                </div>
                
                <!-- Formulaire d'ajout (caché) -->
                <div id="add-subscriber-form" style="display: none; background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                    <h3>Ajouter un nouvel abonné</h3>
                    <form method="POST" style="display: flex; gap: 1rem; align-items: end;">
                        <input type="hidden" name="action" value="add_subscriber">
                        <div class="form-group" style="margin-bottom: 0; flex: 1;">
                            <label for="add_email">Email</label>
                            <input type="email" id="add_email" name="email" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 0; flex: 1;">
                            <label for="add_name">Nom (optionnel)</label>
                            <input type="text" id="add_name" name="name">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="add_source">Source</label>
                            <select id="add_source" name="source">
                                <option value="admin">Admin</option>
                                <option value="website">Site web</option>
                                <option value="import">Import</option>
                                <option value="manual">Manuel</option>
                            </select>
                        </div>
                        <button type="submit" class="btn">Ajouter</button>
                        <button type="button" onclick="hideAddSubscriber()" class="btn btn-danger">Annuler</button>
                    </form>
                </div>
                
                <!-- Liste des abonnés -->
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Email</th>
                                <th>Nom</th>
                                <th>Source</th>
                                <th>Statut</th>
                                <th>Abonné le</th>
                                <th>Dernière activité</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subscribers as $subscriber): ?>
                                <tr>
                                    <td><?= htmlspecialchars($subscriber['email']) ?></td>
                                    <td><?= htmlspecialchars($subscriber['name'] ?: '-') ?></td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?= htmlspecialchars($subscriber['source'] ?: 'blog') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($subscriber['is_active'] && $subscriber['is_confirmed']): ?>
                                            <span class="badge badge-success">Actif</span>
                                        <?php elseif ($subscriber['is_active'] && !$subscriber['is_confirmed']): ?>
                                            <span class="badge badge-warning">En attente</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($subscriber['subscribed_at'])) ?></td>
                                    <td>
                                        <?= $subscriber['last_activity_at'] ? date('d/m/Y', strtotime($subscriber['last_activity_at'])) : '-' ?>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_subscriber">
                                                <input type="hidden" name="id" value="<?= $subscriber['id'] ?>">
                                                <button type="submit" class="btn btn-sm <?= $subscriber['is_active'] ? 'btn-danger' : 'btn-success' ?>">
                                                    <?= $subscriber['is_active'] ? 'Désactiver' : 'Activer' ?>
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cet abonné ?')">
                                                <input type="hidden" name="action" value="delete_subscriber">
                                                <input type="hidden" name="id" value="<?= $subscriber['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($subscribers)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #666; padding: 2rem;">
                                        Aucun abonné pour le moment
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
            <?php elseif ($tab === 'history'): ?>
                <!-- Historique des campagnes -->
                <h2>Historique des Campagnes</h2>
                <p style="color: #666; margin-bottom: 2rem;">Toutes vos newsletters envoyées</p>
                
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Sujet</th>
                                <th>Statut</th>
                                <th>Abonnés</th>
                                <th>Envoyés</th>
                                <th>Ouvertures</th>
                                <th>Clics</th>
                                <th>Créée le</th>
                                <th>Envoyée le</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campaigns as $campaign): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($campaign['name']) ?></strong></td>
                                    <td><?= htmlspecialchars($campaign['subject']) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'draft' => 'badge-info',
                                            'scheduled' => 'badge-warning', 
                                            'sending' => 'badge-warning',
                                            'sent' => 'badge-success',
                                            'cancelled' => 'badge-danger'
                                        ][$campaign['status']] ?? 'badge-info';
                                        ?>
                                        <span class="badge <?= $statusClass ?>">
                                            <?= htmlspecialchars($campaign['status_text']) ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($campaign['subscribers_count']) ?></td>
                                    <td><?= number_format($campaign['sent_count']) ?></td>
                                    <td>
                                        <?= number_format($campaign['opens_count']) ?>
                                        <?php if ($campaign['sent_count'] > 0): ?>
                                            <small style="color: #666;">
                                                (<?= round(($campaign['opens_count'] / $campaign['sent_count']) * 100, 1) ?>%)
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= number_format($campaign['clicks_count']) ?>
                                        <?php if ($campaign['opens_count'] > 0): ?>
                                            <small style="color: #666;">
                                                (<?= round(($campaign['clicks_count'] / $campaign['opens_count']) * 100, 1) ?>%)
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($campaign['created_at'])) ?></td>
                                    <td>
                                        <?= $campaign['sent_at'] ? date('d/m/Y H:i', strtotime($campaign['sent_at'])) : '-' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($campaigns)): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; color: #666; padding: 2rem;">
                                        Aucune campagne créée pour le moment
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Gestion de la sélection d'articles
        function toggleArticle(id) {
            const card = event.currentTarget;
            const checkbox = card.querySelector('input[type="checkbox"]');
            
            checkbox.checked = !checkbox.checked;
            card.classList.toggle('selected', checkbox.checked);
        }
        
        // Sélectionner/désélectionner tous les articles
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllBtn = document.getElementById('select-all');
            
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    const checkboxes = document.querySelectorAll('#articles-section input[type="checkbox"]');
                    const cards = document.querySelectorAll('#articles-section .article-card');
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    
                    checkboxes.forEach((checkbox, index) => {
                        checkbox.checked = !allChecked;
                        cards[index].classList.toggle('selected', !allChecked);
                    });
                    
                    selectAllBtn.textContent = allChecked ? 'Sélectionner tout' : 'Désélectionner tout';
                });
            }
        });
        
        // Gestion du formulaire d'ajout d'abonné
        function showAddSubscriber() {
            document.getElementById('add-subscriber-form').style.display = 'block';
        }
        
        function hideAddSubscriber() {
            document.getElementById('add-subscriber-form').style.display = 'none';
        }
        
        // Auto-génération du nom de campagne
        document.addEventListener('DOMContentLoaded', function() {
            const subjectInput = document.getElementById('subject');
            const nameInput = document.getElementById('name');
            
            if (subjectInput && nameInput) {
                subjectInput.addEventListener('input', function() {
                    if (!nameInput.value) {
                        const now = new Date();
                        const dateStr = now.toLocaleDateString('fr-FR');
                        nameInput.value = `Newsletter ${dateStr} - ${this.value}`;
                    }
                });
            }
        });
        
        // Validation du formulaire
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[method="POST"]');
            
            if (form && form.querySelector('input[name="action"][value="send_newsletter"]')) {
                form.addEventListener('submit', function(e) {
                    const selectedArticles = document.querySelectorAll('#articles-section input[type="checkbox"]:checked');
                    
                    if (selectedArticles.length === 0) {
                        e.preventDefault();
                        alert('Veuillez sélectionner au moins un article pour la newsletter.');
                        return false;
                    }
                    
                    const testEmail = document.getElementById('test_email').value;
                    
                    if (!testEmail) {
                        const confirm = window.confirm(
                            `Êtes-vous sûr de vouloir envoyer cette newsletter à tous les abonnés actifs ?\n\n` +
                            `Articles sélectionnés: ${selectedArticles.length}\n` +
                            `Cette action ne peut pas être annulée.`
                        );
                        
                        if (!confirm) {
                            e.preventDefault();
                            return false;
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>                                                                                                                                                   