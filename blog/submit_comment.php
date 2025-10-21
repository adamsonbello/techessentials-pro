<?php
ob_start();
header('Content-Type: application/json');

// Vérifier méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Démarrer session
session_start();

// Vérifier données
if (empty($_POST['article_id']) || empty($_POST['author_name']) || empty($_POST['author_email']) || empty($_POST['content'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

// Récupérer données
$article_id = (int)$_POST['article_id'];
$author_name = trim($_POST['author_name']);
$author_email = trim($_POST['author_email']);
$content = trim($_POST['content']);
$author_website = trim($_POST['author_website'] ?? '');
$parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

// Validation basique
if (!filter_var($author_email, FILTER_VALIDATE_EMAIL)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Email invalide']);
    exit;
}

// Connexion BDD
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
    
    // Vérifier article existe
    $stmt = $pdo->prepare("SELECT id, title FROM articles WHERE id = ? AND status = 'published'");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();
    
    if (!$article) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Article non trouvé']);
        exit;
    }
    
    // Insérer commentaire
    $stmt = $pdo->prepare("
        INSERT INTO comments (
            article_id, parent_id, author_name, author_email, author_website, 
            content, author_ip, user_agent, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
    
    $stmt->execute([
        $article_id,
        $parent_id,
        $author_name,
        $author_email,
        $author_website,
        $content,
        $user_ip,
        $user_agent
    ]);
    
    $comment_id = $pdo->lastInsertId();
    
    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => '📝 Commentaire soumis avec succès ! Il sera visible après modération par l\'administrateur.',
        'data' => [
            'status' => 'approved',
            'comment_id' => $comment_id,
            'article_title' => $article['title']
        ]
    ]);

    $comment_id = $pdo->lastInsertId();

// ✅ NOTIFICATION EMAIL À L'ADMIN
$admin_email = 'ton-email@gmail.com'; // ← CHANGE ICI
$subject = '💬 Nouveau commentaire en attente de modération';
$message = "
Bonjour,

Un nouveau commentaire a été posté sur votre blog et nécessite votre approbation.

📝 Article : {$article['title']}
👤 Auteur : {$author_name}
📧 Email : {$author_email}
🌐 Site web : " . ($author_website ?: 'Non renseigné') . "

💬 Commentaire :
" . substr($content, 0, 200) . (strlen($content) > 200 ? '...' : '') . "

🔗 Modérer ce commentaire :
http://localhost/TechessentialsPro/admin/comments/index.php?status=pending

---
TechEssentials Pro - Système de gestion
";

$headers = "From: noreply@techessentialspro.com\r\n";
$headers .= "Reply-To: noreply@techessentialspro.com\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Envoi email (désactivé en local, activé en production)
// mail($admin_email, $subject, $message, $headers);

// Log pour debug (à retirer en production)

error_log("EMAIL ENVOYÉ À: $admin_email - Commentaire ID: $comment_id");
    
} catch (PDOException $e) {
    ob_end_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Erreur base de données',
        'error' => $e->getMessage()  // ← VÉRIFIE QUE CETTE LIGNE EST LÀ
    ]);
   
}
?>
