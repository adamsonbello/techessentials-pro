<?php
// blog/submit_comment.php - Soumission de commentaires côté public
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration BDD
$DB_CONFIG = [
    'host' => 'localhost',
    'dbname' => 'techessentials_blog',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

// Fonction de réponse JSON
function jsonResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// Vérification de la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Méthode non autorisée');
}

// Démarrage session pour protection anti-spam
session_start();

// Vérification des données requises
$required_fields = ['article_id', 'author_name', 'author_email', 'content'];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        jsonResponse(false, "Le champ '{$field}' est requis");
    }
}

// Récupération et validation des données
$article_id = (int)$_POST['article_id'];
$author_name = trim($_POST['author_name']);
$author_email = trim($_POST['author_email']);
$content = trim($_POST['content']);
$author_website = trim($_POST['author_website'] ?? '');
$parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

// Validations de base
if ($article_id <= 0) {
    jsonResponse(false, 'ID d\'article invalide');
}

if (!filter_var($author_email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(false, 'Adresse email invalide');
}

if (strlen($author_name) < 2 || strlen($author_name) > 100) {
    jsonResponse(false, 'Le nom doit contenir entre 2 et 100 caractères');
}

if (strlen($content) < 10 || strlen($content) > 2000) {
    jsonResponse(false, 'Le commentaire doit contenir entre 10 et 2000 caractères');
}

// Validation du site web si fourni
if (!empty($author_website)) {
    if (!filter_var($author_website, FILTER_VALIDATE_URL)) {
        // Essayer d'ajouter http:// si pas de protocole
        $author_website = 'http://' . $author_website;
        if (!filter_var($author_website, FILTER_VALIDATE_URL)) {
            jsonResponse(false, 'URL du site web invalide');
        }
    }
}

// Normalisation du nom (pas de HTML, caractères spéciaux)
$author_name = strip_tags($author_name);
$author_name = preg_replace('/[<>"\']/', '', $author_name);

// Normalisation du contenu (pas de HTML malveillant)
$content = strip_tags($content);
$content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

// Protection anti-spam avancée
$spam_score = 0;

// Vérification mots-clés spam
$spam_keywords = [
    'viagra', 'casino', 'poker', 'loan', 'mortgage', 'porn', 'sex', 'pills', 'pharmacy',
    'discount', 'cheap', 'free money', 'make money', 'bitcoin', 'cryptocurrency',
    'weight loss', 'diet pills', 'enlargement', 'enhancement'
];

$content_lower = strtolower($content);
$name_lower = strtolower($author_name);

foreach ($spam_keywords as $keyword) {
    if (strpos($content_lower, $keyword) !== false || strpos($name_lower, $keyword) !== false) {
        $spam_score += 10;
    }
}

// Vérification liens suspects
if (preg_match_all('/https?:\/\//', $content, $matches) > 2) {
    $spam_score += 15;
}

// Vérification répétition de caractères
if (preg_match('/(.)\1{4,}/', $content)) {
    $spam_score += 5;
}

// Vérification majuscules excessives
$uppercase_ratio = 0;
if (strlen($content) > 0) {
    $uppercase_count = strlen(preg_replace('/[^A-Z]/', '', $content));
    $uppercase_ratio = $uppercase_count / strlen($content);
}
if ($uppercase_ratio > 0.5) {
    $spam_score += 10;
}

// Rejeter si score de spam trop élevé
if ($spam_score >= 20) {
    jsonResponse(false, 'Votre commentaire a été détecté comme spam');
}

// Vérification de limite de temps (éviter le spam)
$time_limit = 30; // secondes entre deux commentaires
if (isset($_SESSION['last_comment_time'])) {
    $time_diff = time() - $_SESSION['last_comment_time'];
    if ($time_diff < $time_limit) {
        $remaining = $time_limit - $time_diff;
        jsonResponse(false, "Veuillez attendre {$remaining} secondes avant de commenter à nouveau");
    }
}

// Vérification du nombre de commentaires par IP
$user_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$daily_limit = 10; // Maximum 10 commentaires par IP par jour

try {
    // Connexion BDD
    $dsn = "mysql:host={$DB_CONFIG['host']};dbname={$DB_CONFIG['dbname']};charset={$DB_CONFIG['charset']}";
    $pdo = new PDO($dsn, $DB_CONFIG['username'], $DB_CONFIG['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Vérifier le limite quotidienne par IP
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM comments 
        WHERE author_ip = ? AND DATE(created_at) = CURDATE()
    ");
    $stmt->execute([$user_ip]);
    $daily_count = $stmt->fetchColumn();

    if ($daily_count >= $daily_limit) {
        jsonResponse(false, 'Limite quotidienne de commentaires atteinte');
    }

    // Vérifier que l'article existe et est publié
    $stmt = $pdo->prepare("SELECT id, title FROM articles WHERE id = ? AND status = 'published'");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();
    
    if (!$article) {
        jsonResponse(false, 'Article non trouvé ou non publié');
    }

    // Vérifier le commentaire parent si spécifié
    if ($parent_id) {
        $stmt = $pdo->prepare("
            SELECT id FROM comments 
            WHERE id = ? AND article_id = ? AND status = 'approved'
        ");
        $stmt->execute([$parent_id, $article_id]);
        if (!$stmt->fetch()) {
            jsonResponse(false, 'Commentaire parent non trouvé');
        }
    }

    // Vérification de doublon (même contenu dans les dernières 24h)
    $stmt = $pdo->prepare("
        SELECT id FROM comments 
        WHERE content = ? AND author_email = ? 
        AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $stmt->execute([$content, $author_email]);
    if ($stmt->fetch()) {
        jsonResponse(false, 'Commentaire identique déjà posté récemment');
    }

    // Déterminer le statut initial
    $initial_status = 'pending'; // Par défaut en modération

    // Auto-approval pour certains critères
    $auto_approve = false;

    // 1. Domaines de confiance
    $trusted_domains = [
        'gmail.com', 'outlook.com', 'yahoo.com', 'hotmail.com', 
        'protonmail.com', 'icloud.com', 'live.com'
    ];
    $email_domain = substr($author_email, strpos($author_email, '@') + 1);
    
    if (in_array($email_domain, $trusted_domains)) {
        $auto_approve = true;
    }

    // 2. Utilisateur ayant déjà commenté et approuvé
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM comments 
        WHERE author_email = ? AND status = 'approved'
    ");
    $stmt->execute([$author_email]);
    $approved_count = $stmt->fetchColumn();
    
    if ($approved_count >= 1) {
        $auto_approve = true;
    }

    // 3. Commentaire de qualité (long, pas de fautes évidentes)
    if (strlen($content) > 100 && $spam_score < 5) {
        $auto_approve = true;
    }

    // Appliquer le statut
    if ($auto_approve && $spam_score < 10) {
        $initial_status = 'approved';
    }

    // Insérer le commentaire
    $stmt = $pdo->prepare("
        INSERT INTO comments (
            article_id, parent_id, author_name, author_email, author_website, 
            content, author_ip, user_agent, status, spam_score, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500); // Limiter la taille
    
    $stmt->execute([
        $article_id,
        $parent_id,
        $author_name,
        $author_email,
        $author_website,
        $content,
        $user_ip,
        $user_agent,
        $initial_status,
        $spam_score
    ]);

    $comment_id = $pdo->lastInsertId();

    // Enregistrer le timestamp pour la protection anti-spam
    $_SESSION['last_comment_time'] = time();

    // Notification par email à l'admin (optionnel)
    if ($initial_status === 'pending') {
        // Ici vous pouvez ajouter l'envoi d'email à l'admin
        // mail('admin@techessentialspro.com', 'Nouveau commentaire en attente', ...);
    }

    // Réponse selon le statut
    $response_data = [
        'status' => $initial_status,
        'comment_id' => $comment_id,
        'article_title' => $article['title']
    ];

    if ($initial_status === 'approved') {
        $message = '✅ Commentaire publié avec succès ! Il est maintenant visible.';
    } else {
        $message = '📝 Commentaire soumis avec succès ! Il sera visible après modération.';
    }

    jsonResponse(true, $message, $response_data);

} catch (PDOException $e) {
    error_log("Erreur commentaire DB: " . $e->getMessage());
    jsonResponse(false, 'Erreur lors de l\'enregistrement du commentaire');
} catch (Exception $e) {
    error_log("Erreur commentaire: " . $e->getMessage());
    jsonResponse(false, 'Une erreur est survenue. Veuillez réessayer.');
}
?>