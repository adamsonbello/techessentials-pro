<?php
// admin-blog/comments/edit.php - Édition d'un commentaire
session_start();

// Vérification auth (même variable que index.php)
if (!isset($_SESSION['blog_admin_logged']) || $_SESSION['blog_admin_logged'] !== true) {
    header('Location: ../index.php');
    exit;
}

// Configuration BDD
$DB_CONFIG = [
    'host' => 'localhost',
    'dbname' => 'techessentials_blog',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

try {
    $pdo = new PDO(
        "mysql:host={$DB_CONFIG['host']};dbname={$DB_CONFIG['dbname']};charset={$DB_CONFIG['charset']}",
        $DB_CONFIG['username'],
        $DB_CONFIG['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}

$comment_id = $_GET['id'] ?? null;
$message = '';
$error = '';

if (!$comment_id) {
    header('Location: index.php');
    exit;
}

// Récupérer le commentaire
$stmt = $pdo->prepare("
    SELECT c.*, a.title as article_title 
    FROM comments c
    LEFT JOIN articles a ON c.article_id = a.id
    WHERE c.id = ?
");
$stmt->execute([$comment_id]);
$comment = $stmt->fetch();

if (!$comment) {
    header('Location: index.php');
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author_name = trim($_POST['author_name'] ?? '');
    $author_email = trim($_POST['author_email'] ?? '');
    $author_website = trim($_POST['author_website'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = $_POST['status'] ?? 'pending';
    
    // Validation
    if (empty($author_name) || empty($author_email) || empty($content)) {
        $error = 'Tous les champs obligatoires doivent être remplis.';
    } elseif (!filter_var($author_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE comments 
                SET author_name = ?, 
                    author_email = ?, 
                    author_website = ?, 
                    content = ?,
                    status = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $author_name,
                $author_email,
                $author_website,
                $content,
                $status,
                $comment_id
            ]);
            
            $message = '✅ Commentaire mis à jour avec succès !';
            
            // Recharger le commentaire
            $stmt = $pdo->prepare("
                SELECT c.*, a.title as article_title 
                FROM comments c
                LEFT JOIN articles a ON c.article_id = a.id
                WHERE c.id = ?
            ");
            $stmt->execute([$comment_id]);
            $comment = $stmt->fetch();
            
        } catch (PDOException $e) {
            $error = 'Erreur lors de la mise à jour : ' . $e->getMessage();
        }
    }
}

$admin_user = $_SESSION['blog_admin_user'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le commentaire - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #4CAF50;
            --error-color: #f44336;
            --text-color: #333;
            --border-color: #e1e5e9;
            --background-light: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background-light);
            color: var(--text-color);
        }

        .header {
            background: var(--white);
            padding: 15px 30px;
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .back-btn {
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: var(--secondary-color);
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .edit-form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: var(--shadow);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-color);
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            font-family: inherit;
            font-size: 1rem;
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
        }

        .btn-secondary {
            background: var(--border-color);
            color: var(--text-color);
        }

        .btn-secondary:hover {
            background: #d1d5d9;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .alert-error {
            background: rgba(244, 67, 54, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        .comment-meta {
            background: var(--background-light);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.9em;
            color: #666;
        }

        .comment-meta strong {
            color: var(--text-color);
        }

        .char-count {
            text-align: right;
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a href="index.php" class="back-btn">← Retour à la liste</a>
            <h1>✏️ Modifier le commentaire #<?= $comment['id'] ?></h1>
        </div>
        <div>Connecté: <?php echo htmlspecialchars($admin_user); ?></div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="edit-form">
            <!-- Infos commentaire -->
            <div class="comment-meta">
                <strong>📝 Article :</strong> <?= htmlspecialchars($comment['article_title']) ?><br>
                <strong>📅 Posté le :</strong> <?= date('d/m/Y à H:i', strtotime($comment['created_at'])) ?><br>
                <strong>🌐 IP :</strong> <?= htmlspecialchars($comment['author_ip']) ?><br>
                <?php if ($comment['updated_at']): ?>
                    <strong>🔄 Modifié le :</strong> <?= date('d/m/Y à H:i', strtotime($comment['updated_at'])) ?>
                <?php endif; ?>
            </div>

            <!-- Formulaire -->
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="author_name">Nom de l'auteur *</label>
                        <input 
                            type="text" 
                            id="author_name" 
                            name="author_name" 
                            value="<?= htmlspecialchars($comment['author_name']) ?>"
                            required
                            maxlength="100"
                        >
                    </div>

                    <div class="form-group">
                        <label for="author_email">Email *</label>
                        <input 
                            type="email" 
                            id="author_email" 
                            name="author_email" 
                            value="<?= htmlspecialchars($comment['author_email']) ?>"
                            required
                            maxlength="255"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="author_website">Site web</label>
                    <input 
                        type="url" 
                        id="author_website" 
                        name="author_website" 
                        value="<?= htmlspecialchars($comment['author_website'] ?? '') ?>"
                        maxlength="255"
                        placeholder="https://"
                    >
                </div>

                <div class="form-group">
                    <label for="content">Contenu du commentaire *</label>
                    <textarea 
                        id="content" 
                        name="content" 
                        required
                        maxlength="2000"
                    ><?= htmlspecialchars($comment['content']) ?></textarea>
                    <div class="char-count">
                        <span id="char-count">0</span> / 2000 caractères
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status">
                        <option value="pending" <?= $comment['status'] === 'pending' ? 'selected' : '' ?>>En attente</option>
                        <option value="approved" <?= $comment['status'] === 'approved' ? 'selected' : '' ?>>Approuvé</option>
                        <option value="spam" <?= $comment['status'] === 'spam' ? 'selected' : '' ?>>Spam</option>
                        <option value="rejected" <?= $comment['status'] === 'rejected' ? 'selected' : '' ?>>Rejeté</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Enregistrer les modifications</button>
                    <a href="index.php" class="btn btn-secondary">❌ Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Compteur de caractères
        const textarea = document.getElementById('content');
        const charCount = document.getElementById('char-count');
        
        function updateCount() {
            charCount.textContent = textarea.value.length;
        }
        
        textarea.addEventListener('input', updateCount);
        updateCount(); // Initial count
    </script>
</body>
</html>