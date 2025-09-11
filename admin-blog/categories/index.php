<?php
// admin-blog/categories/index.php - Gestion des catégories et tags
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

// Actions CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add_category':
                $name = trim($_POST['name'] ?? '');
                $slug = trim($_POST['slug'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $icon = trim($_POST['icon'] ?? '📝');
                $color = trim($_POST['color'] ?? '#667eea');
                
                if (empty($name)) throw new Exception("Le nom est requis");
                
                // Auto-génération slug
                if (empty($slug)) {
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
                }
                
                $stmt = $blogDB->prepare("
                    INSERT INTO categories (name, slug, description, icon, color, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$name, $slug, $description, $icon, $color]);
                
                $message = "Catégorie '{$name}' créée avec succès";
                break;
                
            case 'update_category':
                $id = (int)($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $slug = trim($_POST['slug'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $icon = trim($_POST['icon'] ?? '📝');
                $color = trim($_POST['color'] ?? '#667eea');
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                if ($id <= 0 || empty($name)) throw new Exception("Données invalides");
                
                $stmt = $blogDB->prepare("
                    UPDATE categories 
                    SET name = ?, slug = ?, description = ?, icon = ?, color = ?, is_active = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$name, $slug, $description, $icon, $color, $is_active, $id]);
                
                $message = "Catégorie '{$name}' mise à jour";
                break;
                
            case 'delete_category':
                $id = (int)($_POST['id'] ?? 0);
                if ($id <= 0) throw new Exception("ID invalide");
                
                // Vérifier s'il y a des articles
                $stmt = $blogDB->prepare("SELECT COUNT(*) FROM articles WHERE category_id = ?");
                $stmt->execute([$id]);
                $articleCount = $stmt->fetchColumn();
                
                if ($articleCount > 0) {
                    throw new Exception("Impossible de supprimer : {$articleCount} article(s) utilisent cette catégorie");
                }
                
                $stmt = $blogDB->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                
                $message = "Catégorie supprimée";
                break;
                
            case 'add_tag':
                $name = trim($_POST['tag_name'] ?? '');
                $slug = trim($_POST['tag_slug'] ?? '');
                $description = trim($_POST['tag_description'] ?? '');
                $color = trim($_POST['tag_color'] ?? '#64748b');
                
                if (empty($name)) throw new Exception("Le nom du tag est requis");
                
                if (empty($slug)) {
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
                }
                
                $stmt = $blogDB->prepare("
                    INSERT INTO tags (name, slug, description, color, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$name, $slug, $description, $color]);
                
                $message = "Tag '{$name}' créé avec succès";
                break;
                
            case 'update_tag':
                $id = (int)($_POST['tag_id'] ?? 0);
                $name = trim($_POST['tag_name'] ?? '');
                $slug = trim($_POST['tag_slug'] ?? '');
                $description = trim($_POST['tag_description'] ?? '');
                $color = trim($_POST['tag_color'] ?? '#64748b');
                
                if ($id <= 0 || empty($name)) throw new Exception("Données invalides");
                
                $stmt = $blogDB->prepare("
                    UPDATE tags 
                    SET name = ?, slug = ?, description = ?, color = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $slug, $description, $color, $id]);
                
                $message = "Tag '{$name}' mis à jour";
                break;
                
            case 'delete_tag':
                $id = (int)($_POST['tag_id'] ?? 0);
                if ($id <= 0) throw new Exception("ID invalide");
                
                // Supprimer d'abord les liaisons
                $stmt = $blogDB->prepare("DELETE FROM article_tags WHERE tag_id = ?");
                $stmt->execute([$id]);
                
                // Puis le tag
                $stmt = $blogDB->prepare("DELETE FROM tags WHERE id = ?");
                $stmt->execute([$id]);
                
                $message = "Tag supprimé";
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Récupération des données
$categories = $blogDB->query("
    SELECT c.*, 
           COUNT(a.id) as article_count
    FROM categories c
    LEFT JOIN articles a ON c.id = a.category_id
    GROUP BY c.id
    ORDER BY c.sort_order, c.name
")->fetchAll();

$tags = $blogDB->query("
    SELECT t.*, 
           COUNT(at.article_id) as usage_count
    FROM tags t
    LEFT JOIN article_tags at ON t.id = at.tag_id
    GROUP BY t.id
    ORDER BY usage_count DESC, t.name
")->fetchAll();

$admin_user = $_SESSION['blog_admin_user'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Catégories & Tags - Blog Admin</title>
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

        .tabs {
            display: flex;
            background: var(--white);
            border-radius: 8px 8px 0 0;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .tab {
            flex: 1;
            padding: 15px 20px;
            background: var(--white);
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }

        .tab.active {
            background: var(--primary-color);
            color: white;
        }

        .tab-content {
            display: none;
            background: var(--white);
            border-radius: 0 0 8px 8px;
            box-shadow: var(--shadow);
            padding: 30px;
        }

        .tab-content.active {
            display: block;
        }

        .section-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .form-section {
            background: var(--background-light);
            padding: 25px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .form-section h3 {
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            font-family: inherit;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .color-input {
            width: 60px !important;
            height: 40px;
            padding: 2px;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-1px);
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn-warning {
            background: var(--warning-color);
            color: white;
            font-size: 0.8rem;
            padding: 6px 12px;
        }

        .btn-danger {
            background: var(--error-color);
            color: white;
            font-size: 0.8rem;
            padding: 6px 12px;
        }

        .items-list {
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
        }

        .list-header {
            background: var(--background-light);
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .items-grid {
            display: grid;
            gap: 1px;
            background: var(--border-color);
        }

        .item-row {
            background: var(--white);
            padding: 20px;
            display: grid;
            grid-template-columns: auto 1fr auto auto auto;
            gap: 20px;
            align-items: center;
            transition: background 0.3s ease;
        }

        .item-row:hover {
            background: var(--background-light);
        }

        .item-icon {
            font-size: 1.5rem;
            width: 40px;
            text-align: center;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-weight: 600;
            margin-bottom: 3px;
        }

        .item-meta {
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .item-stats {
            text-align: center;
            font-size: 0.9rem;
        }

        .item-stats strong {
            display: block;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .item-actions {
            display: flex;
            gap: 8px;
        }

        .status-toggle {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .status-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .status-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            border-radius: 24px;
            transition: .3s;
        }

        .status-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            border-radius: 50%;
            transition: .3s;
        }

        input:checked + .status-slider {
            background-color: var(--success-color);
        }

        input:checked + .status-slider:before {
            transform: translateX(26px);
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

        .tag-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin: 2px;
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

        /* Modal pour édition */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: var(--white);
            margin: 5% auto;
            padding: 0;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            overflow: hidden;
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-close {
            color: white;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            background: none;
        }

        .modal-body {
            padding: 30px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .section-grid {
                grid-template-columns: 1fr;
            }

            .item-row {
                grid-template-columns: auto 1fr;
                gap: 15px;
            }

            .item-actions {
                grid-column: 1 / -1;
                justify-content: center;
                margin-top: 10px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a href="../dashboard.php" class="back-btn">← Dashboard</a>
            <h1>Gestion Catégories & Tags</h1>
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

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="showTab('categories')">📁 Catégories (<?php echo count($categories); ?>)</button>
            <button class="tab" onclick="showTab('tags')">🏷️ Tags (<?php echo count($tags); ?>)</button>
        </div>

        <!-- Tab Catégories -->
        <div id="categories" class="tab-content active">
            <div class="section-grid">
                <!-- Formulaire nouvelle catégorie -->
                <div class="form-section">
                    <h3>➕ Nouvelle catégorie</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_category">
                        
                        <div class="form-group">
                            <label>Nom *</label>
                            <input type="text" name="name" required placeholder="Ex: Tests & Reviews">
                        </div>

                        <div class="form-group">
                            <label>Slug URL</label>
                            <input type="text" name="slug" placeholder="tests-reviews (auto si vide)">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Icône</label>
                                <input type="text" name="icon" value="📝" placeholder="📝">
                            </div>
                            <div class="form-group">
                                <label>Couleur</label>
                                <input type="color" name="color" value="#667eea" class="color-input">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" rows="3" placeholder="Description de la catégorie..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">➕ Créer catégorie</button>
                    </form>
                </div>

                <!-- Liste des catégories -->
                <div class="items-list">
                    <div class="list-header">
                        <h3>📁 Catégories existantes</h3>
                    </div>
                    
                    <?php if (empty($categories)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">📁</div>
                            <h3>Aucune catégorie</h3>
                            <p>Créez votre première catégorie</p>
                        </div>
                    <?php else: ?>
                        <div class="items-grid">
                            <?php foreach ($categories as $cat): ?>
                                <div class="item-row">
                                    <div class="item-icon" style="color: <?php echo htmlspecialchars($cat['color']); ?>">
                                        <?php echo htmlspecialchars($cat['icon']); ?>
                                    </div>
                                    <div class="item-info">
                                        <div class="item-name"><?php echo htmlspecialchars($cat['name']); ?></div>
                                        <div class="item-meta">
                                            /<strong><?php echo htmlspecialchars($cat['slug']); ?></strong>
                                            <?php if ($cat['description']): ?>
                                                • <?php echo htmlspecialchars(substr($cat['description'], 0, 50)) . (strlen($cat['description']) > 50 ? '...' : ''); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="item-stats">
                                        <strong><?php echo $cat['article_count']; ?></strong>
                                        articles
                                    </div>
                                    <div>
                                        <label class="status-toggle">
                                            <input type="checkbox" <?php echo $cat['is_active'] ? 'checked' : ''; ?> 
                                                   onchange="toggleCategoryStatus(<?php echo $cat['id']; ?>, this.checked)">
                                            <span class="status-slider"></span>
                                        </label>
                                    </div>
                                    <div class="item-actions">
                                        <button class="btn btn-warning" onclick="editCategory(<?php echo htmlspecialchars(json_encode($cat)); ?>)">
                                            ✏️ Modifier
                                        </button>
                                        <?php if ($cat['article_count'] == 0): ?>
                                            <button class="btn btn-danger" onclick="deleteCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['name']); ?>')">
                                                🗑️ Supprimer
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tab Tags -->
        <div id="tags" class="tab-content">
            <div class="section-grid">
                <!-- Formulaire nouveau tag -->
                <div class="form-section">
                    <h3>🏷️ Nouveau tag</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_tag">
                        
                        <div class="form-group">
                            <label>Nom *</label>
                            <input type="text" name="tag_name" required placeholder="Ex: iPhone">
                        </div>

                        <div class="form-group">
                            <label>Slug URL</label>
                            <input type="text" name="tag_slug" placeholder="iphone (auto si vide)">
                        </div>

                        <div class="form-group">
                            <label>Couleur</label>
                            <input type="color" name="tag_color" value="#64748b" class="color-input">
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="tag_description" rows="2" placeholder="Description du tag..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">🏷️ Créer tag</button>
                    </form>
                </div>

                <!-- Liste des tags -->
                <div class="items-list">
                    <div class="list-header">
                        <h3>🏷️ Tags existants</h3>
                    </div>
                    
                    <?php if (empty($tags)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">🏷️</div>
                            <h3>Aucun tag</h3>
                            <p>Créez votre premier tag</p>
                        </div>
                    <?php else: ?>
                        <div class="items-grid">
                            <?php foreach ($tags as $tag): ?>
                                <div class="item-row">
                                    <div class="item-icon">
                                        <div class="tag-item" style="background-color: <?php echo htmlspecialchars($tag['color']); ?>20; color: <?php echo htmlspecialchars($tag['color']); ?>;">
                                            🏷️
                                        </div>
                                    </div>
                                    <div class="item-info">
                                        <div class="item-name"><?php echo htmlspecialchars($tag['name']); ?></div>
                                        <div class="item-meta">
                                            /<strong><?php echo htmlspecialchars($tag['slug']); ?></strong>
                                            <?php if ($tag['description']): ?>
                                                • <?php echo htmlspecialchars(substr($tag['description'], 0, 40)) . (strlen($tag['description']) > 40 ? '...' : ''); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="item-stats">
                                        <strong><?php echo $tag['usage_count']; ?></strong>
                                        utilisations
                                    </div>
                                    <div></div>
                                    <div class="item-actions">
                                        <button class="btn btn-warning" onclick="editTag(<?php echo htmlspecialchars(json_encode($tag)); ?>)">
                                            ✏️ Modifier
                                        </button>
                                        <button class="btn btn-danger" onclick="deleteTag(<?php echo $tag['id']; ?>, '<?php echo htmlspecialchars($tag['name']); ?>')">
                                            🗑️ Supprimer
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal édition catégorie -->
    <div id="editCategoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Modifier catégorie</h3>
                <button class="modal-close" onclick="closeModal('editCategoryModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="editCategoryForm">
                    <input type="hidden" name="action" value="update_category">
                    <input type="hidden" name="id" id="edit_cat_id">
                    
                    <div class="form-group">
                        <label>Nom *</label>
                        <input type="text" name="name" id="edit_cat_name" required>
                    </div>

                    <div class="form-group">
                        <label>Slug URL</label>
                        <input type="text" name="slug" id="edit_cat_slug">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Icône</label>
                            <input type="text" name="icon" id="edit_cat_icon">
                        </div>
                        <div class="form-group">
                            <label>Couleur</label>
                            <input type="color" name="color" id="edit_cat_color" class="color-input">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="edit_cat_description" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" id="edit_cat_active"> Catégorie active
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">💾 Sauvegarder</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal édition tag -->
    <div id="editTagModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Modifier tag</h3>
                <button class="modal-close" onclick="closeModal('editTagModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" id="editTagForm">
                    <input type="hidden" name="action" value="update_tag">
                    <input type="hidden" name="tag_id" id="edit_tag_id">
                    
                    <div class="form-group">
                        <label>Nom *</label>
                        <input type="text" name="tag_name" id="edit_tag_name" required>
                    </div>

                    <div class="form-group">
                        <label>Slug URL</label>
                        <input type="text" name="tag_slug" id="edit_tag_slug">
                    </div>

                    <div class="form-group">
                        <label>Couleur</label>
                        <input type="color" name="tag_color" id="edit_tag_color" class="color-input">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="tag_description" id="edit_tag_description" rows="2"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">💾 Sauvegarder</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Gestion des tabs avec support de l'ancre URL
        function showTab(tabName) {
            // Masquer tous les contenus
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Masquer tous les tabs actifs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Afficher le contenu sélectionné
            document.getElementById(tabName).classList.add('active');
            
            // Activer le tab sélectionné
            if (event && event.target) {
                event.target.classList.add('active');
            } else {
                // Si appelé programmatiquement, trouver le bon bouton
                const tabButton = Array.from(document.querySelectorAll('.tab')).find(tab => 
                    tab.onclick && tab.onclick.toString().includes(tabName)
                );
                if (tabButton) {
                    tabButton.classList.add('active');
                }
            }
        }

        // Vérifier l'ancre au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash.substring(1); // Enlever le #
            if (hash === 'tags') {
                // Activer l'onglet tags
                document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Activer le tab tags
                const tagsTab = document.querySelector('.tab:nth-child(2)'); // Deuxième tab = tags
                const tagsContent = document.getElementById('tags');
                
                if (tagsTab && tagsContent) {
                    tagsTab.classList.add('active');
                    tagsContent.classList.add('active');
                }
            }
        });

        // Toggle statut catégorie
        function toggleCategoryStatus(categoryId, isActive) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'toggle_category_status';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = categoryId;
            
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'is_active';
            statusInput.value = isActive ? '1' : '0';
            
            form.appendChild(actionInput);
            form.appendChild(idInput);
            form.appendChild(statusInput);
            
            document.body.appendChild(form);
            form.submit();
        }

        // Édition catégorie
        function editCategory(category) {
            document.getElementById('edit_cat_id').value = category.id;
            document.getElementById('edit_cat_name').value = category.name;
            document.getElementById('edit_cat_slug').value = category.slug;
            document.getElementById('edit_cat_icon').value = category.icon;
            document.getElementById('edit_cat_color').value = category.color;
            document.getElementById('edit_cat_description').value = category.description || '';
            document.getElementById('edit_cat_active').checked = category.is_active == 1;
            
            document.getElementById('editCategoryModal').style.display = 'block';
        }

        // Édition tag
        function editTag(tag) {
            document.getElementById('edit_tag_id').value = tag.id;
            document.getElementById('edit_tag_name').value = tag.name;
            document.getElementById('edit_tag_slug').value = tag.slug;
            document.getElementById('edit_tag_color').value = tag.color;
            document.getElementById('edit_tag_description').value = tag.description || '';
            
            document.getElementById('editTagModal').style.display = 'block';
        }

        // Suppression catégorie
        function deleteCategory(categoryId, categoryName) {
            if (confirm(`Êtes-vous sûr de vouloir supprimer la catégorie "${categoryName}" ?\n\nCette action est irréversible.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_category';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = categoryId;
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Suppression tag
        function deleteTag(tagId, tagName) {
            if (confirm(`Êtes-vous sûr de vouloir supprimer le tag "${tagName}" ?\n\nCette action supprimera également toutes les associations avec les articles.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_tag';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'tag_id';
                idInput.value = tagId;
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Fermeture modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Fermeture modal en cliquant à l'extérieur
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Auto-génération des slugs
        document.addEventListener('DOMContentLoaded', function() {
            // Pour les catégories
            const categoryNameInput = document.querySelector('input[name="name"]');
            const categorySlugInput = document.querySelector('input[name="slug"]');
            
            if (categoryNameInput && categorySlugInput) {
                categoryNameInput.addEventListener('input', function() {
                    if (categorySlugInput.value === '') {
                        const slug = this.value
                            .toLowerCase()
                            .trim()
                            .replace(/[^a-z0-9-]/g, '-')
                            .replace(/-+/g, '-')
                            .replace(/^-|-$/g, '');
                        categorySlugInput.value = slug;
                    }
                });
            }

            // Pour les tags
            const tagNameInput = document.querySelector('input[name="tag_name"]');
            const tagSlugInput = document.querySelector('input[name="tag_slug"]');
            
            if (tagNameInput && tagSlugInput) {
                tagNameInput.addEventListener('input', function() {
                    if (tagSlugInput.value === '') {
                        const slug = this.value
                            .toLowerCase()
                            .trim()
                            .replace(/[^a-z0-9-]/g, '-')
                            .replace(/-+/g, '-')
                            .replace(/^-|-$/g, '');
                        tagSlugInput.value = slug;
                    }
                });
            }
        });

        // Animation des messages
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

        // Prévisualisation couleurs en temps réel
        document.addEventListener('DOMContentLoaded', function() {
            const colorInputs = document.querySelectorAll('input[type="color"]');
            colorInputs.forEach(input => {
                input.addEventListener('change', function() {
                    // Ajouter effet visuel ou prévisualisation si nécessaire
                    this.style.boxShadow = `0 0 10px ${this.value}40`;
                });
            });
        });
    </script>
</body>
</html>