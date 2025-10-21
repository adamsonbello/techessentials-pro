<?php
// admin-blog/comments/index.php - Gestion des commentaires
session_start();

// Vérification auth
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

// Connexion BDD
try {
    $dsn = "mysql:host={$DB_CONFIG['host']};dbname={$DB_CONFIG['dbname']};charset={$DB_CONFIG['charset']}";
    $blogDB = new PDO($dsn, $DB_CONFIG['username'], $DB_CONFIG['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Erreur connexion BDD: " . $e->getMessage());
}

$message = '';
$error = '';

// Actions sur les commentaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'approve':
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) throw new Exception("ID invalide");
                
                $stmt = $blogDB->prepare("UPDATE comments SET status = 'approved', moderated_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                
                $message = "Commentaire approuvé";
                break;
                
            case 'reject':
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) throw new Exception("ID invalide");
                
                $stmt = $blogDB->prepare("UPDATE comments SET status = 'rejected', moderated_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                
                $message = "Commentaire rejeté";
                break;
                
            case 'spam':
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) throw new Exception("ID invalide");
                
                $stmt = $blogDB->prepare("UPDATE comments SET status = 'spam', moderated_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                
                $message = "Commentaire marqué comme spam";
                break;
                
            case 'delete':
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) throw new Exception("ID invalide");
                
                $stmt = $blogDB->prepare("DELETE FROM comments WHERE id = ?");
                $stmt->execute([$id]);
                
                $message = "Commentaire supprimé définitivement";
                break;
                
            case 'bulk_action':
                $bulk_action = $_POST['bulk_action'] ?? '';
                $comment_ids = $_POST['comment_ids'] ?? [];
                
                if (empty($bulk_action) || empty($comment_ids)) {
                    throw new Exception("Sélectionnez une action et des commentaires");
                }
                
                $placeholders = str_repeat('?,', count($comment_ids) - 1) . '?';
                
                switch ($bulk_action) {
                    case 'approve':
                        $stmt = $blogDB->prepare("UPDATE comments SET status = 'approved', moderated_at = NOW() WHERE id IN ($placeholders)");
                        $stmt->execute($comment_ids);
                        $message = count($comment_ids) . " commentaire(s) approuvé(s)";
                        break;
                        
                    case 'reject':
                        $stmt = $blogDB->prepare("UPDATE comments SET status = 'rejected', moderated_at = NOW() WHERE id IN ($placeholders)");
                        $stmt->execute($comment_ids);
                        $message = count($comment_ids) . " commentaire(s) rejeté(s)";
                        break;
                        
                    case 'spam':
                        $stmt = $blogDB->prepare("UPDATE comments SET status = 'spam', moderated_at = NOW() WHERE id IN ($placeholders)");
                        $stmt->execute($comment_ids);
                        $message = count($comment_ids) . " commentaire(s) marqué(s) comme spam";
                        break;
                        
                    case 'delete':
                        $stmt = $blogDB->prepare("DELETE FROM comments WHERE id IN ($placeholders)");
                        $stmt->execute($comment_ids);
                        $message = count($comment_ids) . " commentaire(s) supprimé(s)";
                        break;
                }
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Filtres
$status_filter = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Construction de la requête
$where_conditions = [];
$params = [];

if ($status_filter !== 'all') {
    $where_conditions[] = "c.status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(c.author_name LIKE ? OR c.content LIKE ? OR a.title LIKE ?)";
    $search_param = "%{$search}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Récupération des commentaires
$comments_query = "
    SELECT c.*, 
           a.title as article_title,
           a.slug as article_slug
    FROM comments c
    LEFT JOIN articles a ON c.article_id = a.id
    {$where_clause}
    ORDER BY c.created_at DESC
    LIMIT {$per_page} OFFSET {$offset}
";

$comments = $blogDB->prepare($comments_query);
$comments->execute($params);
$comments = $comments->fetchAll();

// Comptage total pour pagination
$count_query = "
    SELECT COUNT(*)
    FROM comments c
    LEFT JOIN articles a ON c.article_id = a.id
    {$where_clause}
";
$count_stmt = $blogDB->prepare($count_query);
$count_stmt->execute($params);
$total_comments = $count_stmt->fetchColumn();
$total_pages = ceil($total_comments / $per_page);

// Statistiques
$stats = $blogDB->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'spam' THEN 1 ELSE 0 END) as spam
    FROM comments
")->fetch();

$admin_user = $_SESSION['blog_admin_user'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Commentaires - Blog Admin</title>
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
            --warning-color: #ff9800;
            --error-color: #f44336;
            --info-color: #2196F3;
            --text-color: #333;
            --text-light: #666;
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
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .stat-pending { color: var(--warning-color); }
        .stat-approved { color: var(--success-color); }
        .stat-rejected { color: var(--error-color); }
        .stat-spam { color: #9e9e9e; }

        .filters {
            background: var(--white);
            padding: 20px;
            border-radius: 8px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-group label {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            font-family: inherit;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-warning {
            background: var(--warning-color);
            color: white;
        }

        .btn-danger {
            background: var(--error-color);
            color: white;
        }

        .btn-small {
            padding: 4px 8px;
            font-size: 0.8rem;
        }

        .comments-list {
            background: var(--white);
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .list-header {
            background: var(--background-light);
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .bulk-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .comment-item {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            transition: background 0.3s ease;
        }

        .comment-item:hover {
            background: var(--background-light);
        }

        .comment-item:last-child {
            border-bottom: none;
        }

        .comment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .comment-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.9rem;
            color: var(--text-light);
        }

        .comment-author {
            font-weight: 600;
            color: var(--text-color);
        }

        .comment-content {
            margin: 15px 0;
            line-height: 1.6;
            padding: 15px;
            background: var(--background-light);
            border-radius: 6px;
            border-left: 4px solid var(--primary-color);
        }

        .comment-article {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 15px;
        }

        .comment-article a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .comment-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: rgba(255, 152, 0, 0.1);
            color: var(--warning-color);
        }

        .status-approved {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
        }

        .status-rejected {
            background: rgba(244, 67, 54, 0.1);
            color: var(--error-color);
        }

        .status-spam {
            background: rgba(158, 158, 158, 0.1);
            color: #9e9e9e;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            text-decoration: none;
            color: var(--text-color);
        }

        .pagination a:hover {
            background: var(--primary-color);
            color: white;
        }

        .pagination .current {
            background: var(--primary-color);
            color: white;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .message.success {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .message.error {
            background: rgba(244, 67, 54, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
                align-items: stretch;
            }

            .comment-header {
                flex-direction: column;
                gap: 10px;
            }

            .comment-actions {
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        /* TON CSS ICI - Garde celui que tu as déjà */
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a href="../dashboard.php" class="back-btn">← Dashboard</a>
            <h1>💬 Gestion des Commentaires</h1>
        </div>
        <div>Connecté: <?php echo htmlspecialchars($admin_user); ?></div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total commentaires</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-pending"><?php echo $stats['pending']; ?></div>
                <div class="stat-label">En attente</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-approved"><?php echo $stats['approved']; ?></div>
                <div class="stat-label">Approuvés</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-rejected"><?php echo $stats['rejected']; ?></div>
                <div class="stat-label">Rejetés</div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="filters">
            <form method="GET" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap; width: 100%;">
                <div class="filter-group">
                    <label for="status">Statut:</label>
                    <select name="status" id="status">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Tous</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                        <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approuvés</option>
                        <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejetés</option>
                        <option value="spam" <?php echo $status_filter === 'spam' ? 'selected' : ''; ?>>Spam</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="search">Recherche:</label>
                    <input type="text" name="search" id="search" placeholder="Auteur, contenu, article..." value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <button type="submit" class="btn btn-primary">🔍 Filtrer</button>
                
                <?php if (!empty($search) || $status_filter !== 'all'): ?>
                    <a href="?" class="btn btn-warning">🔄 Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Liste des commentaires -->
        <div class="comments-list">
            <div class="list-header">
                <h3>📝 Commentaires (<?php echo $total_comments; ?>)</h3>
                
                <div class="bulk-actions">
                    <form method="POST" style="display: flex; align-items: center; gap: 10px;">
                        <input type="hidden" name="action" value="bulk_action">
                        <select name="bulk_action" required>
                            <option value="">Actions groupées</option>
                            <option value="approve">✅ Approuver</option>
                            <option value="reject">❌ Rejeter</option>
                            <option value="spam">🚫 Marquer spam</option>
                            <option value="delete">🗑️ Supprimer</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-small">Appliquer</button>
                    </form>
                </div>
            </div>
            
            <?php if (empty($comments)): ?>
                <div class="empty-state">
                    <div class="empty-icon">💬</div>
                    <h3>Aucun commentaire</h3>
                    <p>
                        <?php if (!empty($search) || $status_filter !== 'all'): ?>
                            Aucun commentaire ne correspond à vos critères
                        <?php else: ?>
                            Aucun commentaire pour le moment
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item">
                        <div class="comment-header">
                            <div class="comment-meta">
                                <input type="checkbox" name="comment_ids[]" value="<?php echo $comment['id']; ?>" form="bulk-form">
                                <span class="comment-author"><?php echo htmlspecialchars($comment['author_name']); ?></span>
                                <span><?php echo htmlspecialchars($comment['author_email']); ?></span>
                                <span><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></span>
                                <span class="status-badge status-<?php echo $comment['status']; ?>">
                                    <?php echo ucfirst($comment['status']); ?>
                                </span>
                            </div>
                        </div>

                        <?php if (!empty($comment['article_title'])): ?>
                            <div class="comment-article">
                                💬 Sur l'article: <a href="../articles/edit.php?id=<?php echo $comment['article_id']; ?>"><?php echo htmlspecialchars($comment['article_title']); ?></a>
                            </div>
                        <?php endif; ?>

                        <div class="comment-content">
                            <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                        </div>

                        <div class="comment-actions">
                            <?php if ($comment['status'] !== 'approved'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="id" value="<?php echo $comment['id']; ?>">
                                    <button type="submit" class="btn btn-success btn-small">✅ Approuver</button>
                                </form>
                            <?php endif; ?>

                            <?php if ($comment['status'] !== 'rejected'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="reject">
                                    <input type="hidden" name="id" value="<?php echo $comment['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-small">❌ Rejeter</button>
                                </form>
                            <?php endif; ?>

                            <?php if ($comment['status'] !== 'spam'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="spam">
                                    <input type="hidden" name="id" value="<?php echo $comment['id']; ?>">
                                    <button type="submit" class="btn btn-warning btn-small">🚫 Spam</button>
                                </form>
                            <?php endif; ?>

                            <a href="edit.php?id=<?php echo $comment['id']; ?>" class="btn btn-small" style="background: #2196F3; color: white;">✏️ Modifier</a>

                            <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer définitivement ce commentaire ?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $comment['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-small">🗑️ Supprimer</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">← Précédent</a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>">Suivant →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Sélection multiple pour actions groupées
        document.addEventListener('DOMContentLoaded', function() {
            const bulkForm = document.createElement('form');
            bulkForm.id = 'bulk-form';
            bulkForm.method = 'POST';
            bulkForm.style.display = 'none';
            document.body.appendChild(bulkForm);

            const checkboxes = document.querySelectorAll('input[name="comment_ids[]"]');
            
            const listHeader = document.querySelector('.list-header h3');
            if (listHeader && checkboxes.length > 0) {
                const selectAllCheckbox = document.createElement('input');
                selectAllCheckbox.type = 'checkbox';
                selectAllCheckbox.id = 'select-all';
                selectAllCheckbox.style.marginRight = '10px';
                
                selectAllCheckbox.addEventListener('change', function() {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                });
                
                listHeader.insertBefore(selectAllCheckbox, listHeader.firstChild);
            }

            const bulkActionSelect = document.querySelector('select[name="bulk_action"]');
            const bulkSubmitBtn = document.querySelector('.bulk-actions button[type="submit"]');
            
            if (bulkSubmitBtn) {
                bulkSubmitBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const selectedComments = Array.from(checkboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);
                    
                    if (selectedComments.length === 0) {
                        alert('Veuillez sélectionner au moins un commentaire');
                        return;
                    }
                    
                    if (!bulkActionSelect.value) {
                        alert('Veuillez sélectionner une action');
                        return;
                    }
                    
                    const actionText = bulkActionSelect.options[bulkActionSelect.selectedIndex].text;
                    if (!confirm(`${actionText} ${selectedComments.length} commentaire(s) ?`)) {
                        return;
                    }
                    
                    const form = document.createElement('form');
                    form.method = 'POST';
                    
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'bulk_action';
                    form.appendChild(actionInput);
                    
                    const bulkActionInput = document.createElement('input');
                    bulkActionInput.type = 'hidden';
                    bulkActionInput.name = 'bulk_action';
                    bulkActionInput.value = bulkActionSelect.value;
                    form.appendChild(bulkActionInput);
                    
                    selectedComments.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'comment_ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });
                    
                    document.body.appendChild(form);
                    form.submit();
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                setTimeout(() => {
                    message.style.opacity = '0';
                    message.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        message.remove();
                    }, 300);
                }, 4000);
            });
        });

        document.getElementById('status').addEventListener('change', function() {
            this.form.submit();
        });

        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'a' && document.activeElement.tagName !== 'INPUT') {
                e.preventDefault();
                const selectAll = document.getElementById('select-all');
                if (selectAll) {
                    selectAll.checked = true;
                    selectAll.dispatchEvent(new Event('change'));
                }
            }
            
            if (e.key === 'Escape') {
                const checkboxes = document.querySelectorAll('input[name="comment_ids[]"]');
                checkboxes.forEach(cb => cb.checked = false);
                const selectAll = document.getElementById('select-all');
                if (selectAll) selectAll.checked = false;
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[name="comment_ids[]"]');
            const bulkActions = document.querySelector('.bulk-actions');
            
            function updateSelectedCount() {
                const selectedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
                
                const oldIndicator = document.querySelector('.selected-indicator');
                if (oldIndicator) oldIndicator.remove();
                
                if (selectedCount > 0) {
                    const indicator = document.createElement('span');
                    indicator.className = 'selected-indicator';
                    indicator.style.cssText = `
                        background: var(--info-color);
                        color: white;
                        padding: 4px 8px;
                        border-radius: 12px;
                        font-size: 0.8rem;
                        font-weight: 600;
                        margin-right: 10px;
                    `;
                    indicator.textContent = `${selectedCount} sélectionné(s)`;
                    bulkActions.insertBefore(indicator, bulkActions.firstChild);
                }
            }
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
            });
            
            const selectAll = document.getElementById('select-all');
            if (selectAll) {
                selectAll.addEventListener('change', updateSelectedCount);
            }
        });
    </script>
</body>
</html>