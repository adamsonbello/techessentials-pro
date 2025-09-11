<?php
// comments/submit.php - Soumission de commentaires côté public
header('Content-Type: application/json');

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
        'data' => $data
    ]);
    exit;
}

// Vérification de la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Méthode non autorisée');
}

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

// Validations
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
        jsonResponse(false, 'URL du site web invalide');
    }
}

// Protection anti-spam basique
$spam_keywords = ['viagra', 'casino', 'poker', 'loan', 'mortgage', 'porn', 'sex'];
$content_lower = strtolower($content);
foreach ($spam_keywords as $keyword) {
    if (strpos($content_lower, $keyword) !== false) {
        jsonResponse(false, 'Votre commentaire contient du contenu non autorisé');
    }
}

// Vérification de limite de temps (éviter le spam)
session_start();
$time_limit = 30; // secondes entre deux commentaires
if (isset($_SESSION['last_comment_time'])) {
    $time_diff = time() - $_SESSION['last_comment_time'];
    if ($time_diff < $time_limit) {
        $remaining = $time_limit - $time_diff;
        jsonResponse(false, "Veuillez attendre {$remaining} secondes avant de commenter à nouveau");
    }
}

try {
    // Connexion BDD
    $dsn = "mysql:host={$DB_CONFIG['host']};dbname={$DB_CONFIG['dbname']};charset={$DB_CONFIG['charset']}";
    $pdo = new PDO($dsn, $DB_CONFIG['username'], $DB_CONFIG['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Vérifier que l'article existe
    $stmt = $pdo->prepare("SELECT id FROM articles WHERE id = ? AND status = 'published'");
    $stmt->execute([$article_id]);
    if (!$stmt->fetch()) {
        jsonResponse(false, 'Article non trouvé');
    }

    // Vérifier le commentaire parent si spécifié
    if ($parent_id) {
        $stmt = $pdo->prepare("SELECT id FROM comments WHERE id = ? AND article_id = ?");
        $stmt->execute([$parent_id, $article_id]);
        if (!$stmt->fetch()) {
            jsonResponse(false, 'Commentaire parent non trouvé');
        }
    }

    // Déterminer le statut initial (auto-approval pour les emails de confiance)
    $trusted_domains = ['gmail.com', 'outlook.com', 'yahoo.com', 'hotmail.com'];
    $email_domain = substr($author_email, strpos($author_email, '@') + 1);
    $initial_status = in_array($email_domain, $trusted_domains) ? 'approved' : 'pending';

    // Insérer le commentaire
    $stmt = $pdo->prepare("
        INSERT INTO comments (
            article_id, parent_id, author_name, author_email, author_website, 
            content, author_ip, user_agent, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $article_id,
        $parent_id,
        $author_name,
        $author_email,
        $author_website,
        $content,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? '',
        $initial_status
    ]);

    // Enregistrer le timestamp pour la protection anti-spam
    $_SESSION['last_comment_time'] = time();

    // Message de succès selon le statut
    if ($initial_status === 'approved') {
        $message = 'Commentaire publié avec succès !';
    } else {
        $message = 'Commentaire soumis avec succès ! Il sera visible après modération.';
    }

    jsonResponse(true, $message, [
        'status' => $initial_status,
        'comment_id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    error_log("Erreur commentaire: " . $e->getMessage());
    jsonResponse(false, 'Erreur lors de l\'enregistrement du commentaire');
} catch (Exception $e) {
    error_log("Erreur commentaire: " . $e->getMessage());
    jsonResponse(false, 'Une erreur est survenue');
}
?>

<!-- comments/display.php - Affichage des commentaires -->
<?php
// comments/display.php - Affichage des commentaires avec formulaire
function displayComments($article_id, $parent_id = null, $level = 0) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM comments 
        WHERE article_id = ? AND parent_id " . ($parent_id ? "= ?" : "IS NULL") . " AND status = 'approved'
        ORDER BY created_at ASC
    ");
    
    if ($parent_id) {
        $stmt->execute([$article_id, $parent_id]);
    } else {
        $stmt->execute([$article_id]);
    }
    
    $comments = $stmt->fetchAll();
    
    if (empty($comments) && $level === 0) {
        echo '<div class="no-comments">
                <p>Aucun commentaire pour le moment. Soyez le premier à commenter !</p>
              </div>';
        return;
    }
    
    foreach ($comments as $comment) {
        $margin_left = $level * 30;
        $reply_class = $level > 0 ? 'reply-comment' : 'main-comment';
        
        echo '<div class="comment-item ' . $reply_class . '" style="margin-left: ' . $margin_left . 'px;" data-comment-id="' . $comment['id'] . '">';
        
        // Header du commentaire
        echo '<div class="comment-header">';
        echo '<div class="comment-author">';
        
        if (!empty($comment['author_website'])) {
            echo '<a href="' . htmlspecialchars($comment['author_website']) . '" target="_blank" rel="nofollow">';
            echo htmlspecialchars($comment['author_name']);
            echo '</a>';
        } else {
            echo htmlspecialchars($comment['author_name']);
        }
        
        echo '</div>';
        echo '<div class="comment-date">' . date('d/m/Y à H:i', strtotime($comment['created_at'])) . '</div>';
        echo '</div>';
        
        // Contenu du commentaire
        echo '<div class="comment-content">';
        echo nl2br(htmlspecialchars($comment['content']));
        echo '</div>';
        
        // Bouton répondre (limité à 3 niveaux)
        if ($level < 3) {
            echo '<div class="comment-actions">';
            echo '<button class="reply-btn" onclick="showReplyForm(' . $comment['id'] . ')">💬 Répondre</button>';
            echo '</div>';
            
            // Formulaire de réponse (caché par défaut)
            echo '<div class="reply-form-container" id="reply-form-' . $comment['id'] . '" style="display: none;">';
            displayCommentForm($article_id, $comment['id']);
            echo '</div>';
        }
        
        echo '</div>';
        
        // Commentaires enfants (récursif)
        displayComments($article_id, $comment['id'], $level + 1);
    }
}

function displayCommentForm($article_id, $parent_id = null) {
    $form_id = $parent_id ? "reply-form-{$parent_id}" : "main-comment-form";
    $title = $parent_id ? "Répondre" : "Laisser un commentaire";
    
    echo '<div class="comment-form-section">';
    echo '<h3>' . $title . '</h3>';
    echo '<form class="comment-form" data-article-id="' . $article_id . '" data-parent-id="' . ($parent_id ?? '') . '">';
    
    echo '<div class="form-row">';
    echo '<div class="form-group">';
    echo '<label for="author_name">Nom *</label>';
    echo '<input type="text" name="author_name" required maxlength="100" placeholder="Votre nom">';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label for="author_email">Email *</label>';
    echo '<input type="email" name="author_email" required maxlength="255" placeholder="votre@email.com">';
    echo '<small>Votre email ne sera pas publié</small>';
    echo '</div>';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label for="author_website">Site web (optionnel)</label>';
    echo '<input type="url" name="author_website" maxlength="255" placeholder="https://votre-site.com">';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label for="content">Commentaire *</label>';
    echo '<textarea name="content" required minlength="10" maxlength="2000" rows="5" placeholder="Votre commentaire..."></textarea>';
    echo '<small><span class="char-count">0</span>/2000 caractères</small>';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<button type="submit" class="submit-btn">📝 Publier le commentaire</button>';
    if ($parent_id) {
        echo '<button type="button" class="cancel-btn" onclick="hideReplyForm(' . $parent_id . ')">Annuler</button>';
    }
    echo '</div>';
    
    echo '<div class="form-message" style="display: none;"></div>';
    echo '</form>';
    echo '</div>';
}

// CSS et JavaScript pour les commentaires
echo '<style>
.comments-section {
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

.comments-header {
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e1e5e9;
}

.comments-count {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
}

.comment-item {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-left: 4px solid #667eea;
}

.reply-comment {
    background: #f8f9fa;
    border-left-color: #6c757d;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.comment-author {
    font-weight: 600;
    color: #667eea;
}

.comment-author a {
    color: inherit;
    text-decoration: none;
}

.comment-author a:hover {
    text-decoration: underline;
}

.comment-date {
    font-size: 0.9rem;
    color: #6c757d;
}

.comment-content {
    line-height: 1.6;
    color: #333;
    margin-bottom: 15px;
}

.comment-actions {
    display: flex;
    gap: 10px;
}

.reply-btn {
    background: none;
    border: 1px solid #667eea;
    color: #667eea;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.reply-btn:hover {
    background: #667eea;
    color: white;
}

.comment-form-section {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 8px;
    margin: 30px 0;
}

.comment-form-section h3 {
    margin-bottom: 20px;
    color: #333;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 2px solid #e1e5e9;
    border-radius: 6px;
    font-family: inherit;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #667eea;
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #6c757d;
    font-size: 0.85rem;
}

.submit-btn {
    background: #667eea;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-right: 10px;
}

.submit-btn:hover {
    background: #5a67d8;
    transform: translateY(-1px);
}

.submit-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
}

.cancel-btn {
    background: #6c757d;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
}

.cancel-btn:hover {
    background: #545b62;
}

.form-message {
    margin-top: 15px;
    padding: 10px;
    border-radius: 4px;
    font-weight: 500;
}

.form-message.success {
    background: rgba(76, 175, 80, 0.1);
    color: #4caf50;
    border: 1px solid rgba(76, 175, 80, 0.3);
}

.form-message.error {
    background: rgba(244, 67, 54, 0.1);
    color: #f44336;
    border: 1px solid rgba(244, 67, 54, 0.3);
}

.no-comments {
    text-align: center;
    padding: 40px;
    color: #6c757d;
}

.char-count {
    font-weight: 600;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .comment-item {
        margin-left: 0 !important;
    }
    
    .reply-comment {
        margin-left: 15px !important;
    }
}
</style>';

echo '<script>
// Gestion des formulaires de commentaires
document.addEventListener("DOMContentLoaded", function() {
    // Gestion de tous les formulaires de commentaires
    document.querySelectorAll(".comment-form").forEach(form => {
        // Compteur de caractères
        const textarea = form.querySelector("textarea[name=\"content\"]");
        const charCount = form.querySelector(".char-count");
        
        if (textarea && charCount) {
            textarea.addEventListener("input", function() {
                charCount.textContent = this.value.length;
                
                if (this.value.length > 1800) {
                    charCount.style.color = "#f44336";
                } else if (this.value.length > 1500) {
                    charCount.style.color = "#ff9800";
                } else {
                    charCount.style.color = "#4caf50";
                }
            });
        }
        
        // Soumission du formulaire
        form.addEventListener("submit", function(e) {
            e.preventDefault();
            submitComment(this);
        });
    });
});

function submitComment(form) {
    const submitBtn = form.querySelector(".submit-btn");
    const messageDiv = form.querySelector(".form-message");
    const formData = new FormData(form);
    
    // Ajouter les données du formulaire
    formData.append("article_id", form.dataset.articleId);
    if (form.dataset.parentId) {
        formData.append("parent_id", form.dataset.parentId);
    }
    
    // Désactiver le bouton
    submitBtn.disabled = true;
    submitBtn.textContent = "📤 Envoi en cours...";
    
    // Cacher les messages précédents
    messageDiv.style.display = "none";
    
    fetch("comments/submit.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        messageDiv.style.display = "block";
        messageDiv.className = "form-message " + (data.success ? "success" : "error");
        messageDiv.textContent = data.message;
        
        if (data.success) {
            form.reset();
            // Actualiser la page après 2 secondes pour voir le nouveau commentaire
            if (data.data && data.data.status === "approved") {
                setTimeout(() => location.reload(), 2000);
            }
            
            // Masquer le formulaire de réponse
            if (form.dataset.parentId) {
                setTimeout(() => {
                    hideReplyForm(form.dataset.parentId);
                }, 3000);
            }
        }
    })
    .catch(error => {
        console.error("Erreur:", error);
        messageDiv.style.display = "block";
        messageDiv.className = "form-message error";
        messageDiv.textContent = "Erreur de connexion. Veuillez réessayer.";
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = "📝 Publier le commentaire";
    });
}

function showReplyForm(commentId) {
    const replyForm = document.getElementById("reply-form-" + commentId);
    if (replyForm) {
        replyForm.style.display = "block";
        replyForm.querySelector("textarea").focus();
    }
}

function hideReplyForm(commentId) {
    const replyForm = document.getElementById("reply-form-" + commentId);
    if (replyForm) {
        replyForm.style.display = "none";
        // Reset du formulaire
        const form = replyForm.querySelector("form");
        if (form) {
            form.reset();
            const messageDiv = form.querySelector(".form-message");
            if (messageDiv) messageDiv.style.display = "none";
        }
    }
}
</script>';
?>