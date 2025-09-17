<?php
// admin-blog/subscribers/index.php - Gestion des abonnés newsletter
require_once '../includes/template.php';

$template = new BlogAdminTemplate('Abonnés Newsletter', 'subscribers');
$db = $template->getDB();

$message = '';
$error = '';

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add_subscriber':
                $email = trim($_POST['email'] ?? '');
                $name = trim($_POST['name'] ?? '');
                $source = $_POST['source'] ?? 'admin';
                
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Email valide requis");
                }
                
                // Vérifier si l'email existe déjà
                $stmt = $db->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    throw new Exception("Cet email est déjà abonné");
                }
                
                // Génération des tokens
                $unsubscribe_token = bin2hex(random_bytes(32));
                $confirmation_token = bin2hex(random_bytes(32));
                
                $stmt = $db->prepare("
                    INSERT INTO newsletter_subscribers 
                    (email, name, source, unsubscribe_token, confirmation_token, 
                     consent_ip, is_confirmed, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, 1, 1)
                ");
                $stmt->execute([
                    $email, $name, $source, $unsubscribe_token, 
                    $confirmation_token, $_SERVER['REMOTE_ADDR']
                ]);
                
                $message = "Abonné ajouté avec succès";
                break;
                
            case 'toggle_subscriber':
                $id = (int)$_POST['id'];
                $stmt = $db->prepare("UPDATE newsletter_subscribers SET is_active = 1 - is_active WHERE id = ?");
                $stmt->execute([$id]);
                $message = "Statut de l'abonné mis à jour";
                break;
                
            case 'delete_subscriber':
                $id = (int)$_POST['id'];
                $stmt = $db->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
                $stmt->execute([$id]);
                $message = "Abonné supprimé";
                break;
                
            case 'bulk_action':
                $selected_subscribers = $_POST['selected_subscribers'] ?? [];
                $bulk_action = $_POST['bulk_action'] ?? '';
                
                if (empty($selected_subscribers)) {
                    throw new Exception("Aucun abonné sélectionné");
                }
                
                switch ($bulk_action) {
                    case 'activate':
                        $placeholders = str_repeat('?,', count($selected_subscribers) - 1) . '?';
                        $db->prepare("UPDATE newsletter_subscribers SET is_active = 1 WHERE id IN ($placeholders)")
                           ->execute($selected_subscribers);
                        $message = count($selected_subscribers) . " abonnés activés";
                        break;
                        
                    case 'deactivate':
                        $placeholders = str_repeat('?,', count($selected_subscribers) - 1) . '?';
                        $db->prepare("UPDATE newsletter_subscribers SET is_active = 0 WHERE id IN ($placeholders)")
                           ->execute($selected_subscribers);
                        $message = count($selected_subscribers) . " abonnés désactivés";
                        break;
                        
                    case 'delete':
                        $placeholders = str_repeat('?,', count($selected_subscribers) - 1) . '?';
                        $db->prepare("DELETE FROM newsletter_subscribers WHERE id IN ($placeholders)")
                           ->execute($selected_subscribers);
                        $message = count($selected_subscribers) . " abonnés supprimés";
                        break;
                }
                break;
                
            case 'import_csv':
                if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Erreur lors de l'upload du fichier CSV");
                }
                
                $csv_content = file_get_contents($_FILES['csv_file']['tmp_name']);
                $lines = str_getcsv($csv_content, "\n");
                $imported = 0;
                $errors = [];
                
                foreach ($lines as $line_num => $line) {
                    if ($line_num === 0) continue; // Ignorer l'en-tête
                    
                    $data = str_getcsv($line);
                    if (empty($data[0])) continue;
                    
                    $email = trim($data[0]);
                    $name = trim($data[1] ?? '');
                    
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Ligne " . ($line_num + 1) . ": Email invalide ($email)";
                        continue;
                    }
                    
                    // Vérifier si existe déjà
                    $stmt = $db->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $errors[] = "Ligne " . ($line_num + 1) . ": Email déjà existant ($email)";
                        continue;
                    }
                    
                    // Insérer
                    $unsubscribe_token = bin2hex(random_bytes(32));
                    $confirmation_token = bin2hex(random_bytes(32));
                    
                    $stmt = $db->prepare("
                        INSERT INTO newsletter_subscribers 
                        (email, name, source, unsubscribe_token, confirmation_token, 
                         consent_ip, is_confirmed, is_active) 
                        VALUES (?, ?, 'import', ?, ?, ?, 1, 1)
                    ");
                    $stmt->execute([$email, $name, $unsubscribe_token, $confirmation_token, $_SERVER['REMOTE_ADDR']]);
                    $imported++;
                }
                
                $message = "$imported abonnés importés";
                if (!empty($errors)) {
                    $message .= " - " . count($errors) . " erreurs détectées";
                }
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Filtres et pagination
$filter_status = $_GET['status'] ?? 'all';
$filter_source = $_GET['source'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Construction de la requête
$where_conditions = [];
$params = [];

if ($filter_status === 'active') {
    $where_conditions[] = "is_active = 1 AND is_confirmed = 1";
} elseif ($filter_status === 'inactive') {
    $where_conditions[] = "is_active = 0 OR is_confirmed = 0";
}

if ($filter_source !== 'all') {
    $where_conditions[] = "source = ?";
    $params[] = $filter_source;
}

if (!empty($search)) {
    $where_conditions[] = "(email LIKE ? OR name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Récupération des abonnés
$subscribers_query = "
    SELECT * FROM newsletter_subscribers
    $where_clause
    ORDER BY subscribed_at DESC
    LIMIT $per_page OFFSET $offset
";

$subscribers = $db->prepare($subscribers_query);
$subscribers->execute($params);
$subscribers = $subscribers->fetchAll();

// Comptage total
$count_query = "SELECT COUNT(*) as total FROM newsletter_subscribers $where_clause";
$count_stmt = $db->prepare($count_query);
$count_stmt->execute($params);
$total_subscribers = $count_stmt->fetch()['total'];
$total_pages = ceil($total_subscribers / $per_page);

// Statistiques
$stats = $db->query("
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN is_active = 1 AND is_confirmed = 1 THEN 1 END) as active,
        COUNT(CASE WHEN is_active = 0 OR is_confirmed = 0 THEN 1 END) as inactive,
        COUNT(CASE WHEN DATE(subscribed_at) = CURDATE() THEN 1 END) as today,
        COUNT(CASE WHEN subscribed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as week,
        COUNT(CASE WHEN subscribed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as month
    FROM newsletter_subscribers
")->fetch();

// Sources disponibles
$sources = $db->query("SELECT DISTINCT source FROM newsletter_subscribers WHERE source IS NOT NULL ORDER BY source")->fetchAll();

// Rendu de la page
$template->renderHeader();
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <h2 class="page-title">Abonnés Newsletter</h2>
    <div class="btn-group">
        <button onclick="showAddSubscriber()" class="btn btn-primary">+ Ajouter Abonné</button>
        <button onclick="showImportForm()" class="btn btn-success">📥 Importer CSV</button>
        <a href="../newsletter/" class="btn btn-outline">📧 Newsletter</a>
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

<!-- STATISTIQUES -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-bottom: 30px;">
    <div class="content-card" style="padding: 20px; text-align: center;">
        <div style="font-size: 1.8rem; font-weight: bold; color: var(--primary-color);"><?= $stats['total'] ?></div>
        <div style="color: var(--text-light); font-size: 0.9rem;">Total abonnés</div>
    </div>
    <div class="content-card" style="padding: 20px; text-align: center;">
        <div style="font-size: 1.8rem; font-weight: bold; color: var(--success-color);"><?= $stats['active'] ?></div>
        <div style="color: var(--text-light); font-size: 0.9rem;">Actifs</div>
    </div>
    <div class="content-card" style="padding: 20px; text-align: center;">
        <div style="font-size: 1.8rem; font-weight: bold; color: var(--warning-color);"><?= $stats['inactive'] ?></div>
        <div style="color: var(--text-light); font-size: 0.9rem;">Inactifs</div>
    </div>
    <div class="content-card" style="padding: 20px; text-align: center;">
        <div style="font-size: 1.8rem; font-weight: bold; color: var(--info-color);">+<?= $stats['week'] ?></div>
        <div style="color: var(--text-light); font-size: 0.9rem;">Cette semaine</div>
    </div>
</div>

<!-- FORMULAIRE AJOUT ABONNÉ (caché) -->
<div id="add-subscriber-form" class="content-card" style="display: none; margin-bottom: 20px;">
    <div class="card-header">
        <h3 class="card-title">Ajouter un nouvel abonné</h3>
        <button onclick="hideAddSubscriber()" class="btn btn-outline" style="padding: 4px 8px;">✕</button>
    </div>
    <div class="card-content">
        <form method="POST">
            <input type="hidden" name="action" value="add_subscriber">
            <div style="display: grid; grid-template-columns: 1fr 1fr 200px auto; gap: 15px; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" required class="form-control" placeholder="exemple@email.com">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Nom (optionnel)</label>
                    <input type="text" name="name" class="form-control" placeholder="Nom complet">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Source</label>
                    <select name="source" class="form-control">
                        <option value="admin">Admin</option>
                        <option value="website">Site web</option>
                        <option value="import">Import</option>
                        <option value="manual">Manuel</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<!-- FORMULAIRE IMPORT CSV (caché) -->
<div id="import-form" class="content-card" style="display: none; margin-bottom: 20px;">
    <div class="card-header">
        <h3 class="card-title">Importer des abonnés depuis un fichier CSV</h3>
        <button onclick="hideImportForm()" class="btn btn-outline" style="padding: 4px 8px;">✕</button>
    </div>
    <div class="card-content">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="import_csv">
                    
                    <div class="form-group">
                        <label class="form-label">Fichier CSV</label>
                        <input type="file" name="csv_file" accept=".csv" required class="form-control">
                        <small style="color: var(--text-light);">Format requis : email, nom (optionnel)</small>
                    </div>
                    
                    <button type="submit" class="btn btn-success">📥 Importer</button>
                </form>
            </div>
            <div>
                <h4>Format attendu :</h4>
                <div style="background: var(--background-light); padding: 15px; border-radius: 8px; font-family: monospace; margin: 10px 0;">
email,name<br>
john@exemple.com,John Doe<br>
jane@exemple.com,Jane Smith<br>
user@test.com,
                </div>
                <p style="color: var(--text-light); font-size: 0.9rem;">
                    La première ligne (en-tête) sera ignorée. Le nom est optionnel.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- FILTRES -->
<div class="content-card">
    <div class="card-content">
        <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Recherche</label>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Email ou nom..." class="form-control">
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Statut</label>
                <select name="status" class="form-control">
                    <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>Tous</option>
                    <option value="active" <?= $filter_status === 'active' ? 'selected' : '' ?>>Actifs uniquement</option>
                    <option value="inactive" <?= $filter_status === 'inactive' ? 'selected' : '' ?>>Inactifs uniquement</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Source</label>
                <select name="source" class="form-control">
                    <option value="all" <?= $filter_source === 'all' ? 'selected' : '' ?>>Toutes les sources</option>
                    <?php foreach ($sources as $source): ?>
                        <option value="<?= $source['source'] ?>" <?= $filter_source === $source['source'] ? 'selected' : '' ?>>
                            <?= ucfirst($source['source']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <a href="?" class="btn btn-outline">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- LISTE DES ABONNÉS -->
<div class="content-card">
    <div class="card-header">
        <h3 class="card-title">Abonnés (<?= $total_subscribers ?> résultats)</h3>
        
        <!-- ACTIONS EN LOT -->
        <div style="display: flex; align-items: center; gap: 10px;">
            <select id="bulk-action" style="padding: 8px; border: 1px solid var(--border-color); border-radius: 6px;">
                <option value="">Actions en lot</option>
                <option value="activate">Activer</option>
                <option value="deactivate">Désactiver</option>
                <option value="delete">Supprimer</option>
            </select>
            <button onclick="executeBulkAction()" class="btn btn-sm btn-outline">Appliquer</button>
        </div>
    </div>
    
    <div class="card-content" style="padding: 0;">
        <?php if (!empty($subscribers)): ?>
            <form id="bulk-form" method="POST">
                <input type="hidden" name="action" value="bulk_action">
                <input type="hidden" name="bulk_action" id="bulk-action-input">
                
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="select-all" onchange="toggleAllCheckboxes()">
                            </th>
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
                                <td>
                                    <input type="checkbox" name="selected_subscribers[]" value="<?= $subscriber['id'] ?>" class="subscriber-checkbox">
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($subscriber['email']) ?></strong>
                                </td>
                                <td>
                                    <?= htmlspecialchars($subscriber['name'] ?: '-') ?>
                                </td>
                                <td>
                                    <span style="background: var(--info-color); color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem;">
                                        <?= ucfirst($subscriber['source'] ?: 'blog') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($subscriber['is_active'] && $subscriber['is_confirmed']): ?>
                                        <span class="status-badge active">Actif</span>
                                    <?php elseif ($subscriber['is_active'] && !$subscriber['is_confirmed']): ?>
                                        <span class="status-badge pending">En attente</span>
                                    <?php else: ?>
                                        <span class="status-badge inactive">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y H:i', strtotime($subscriber['subscribed_at'])) ?>
                                </td>
                                <td>
                                    <?= $subscriber['last_activity_at'] ? date('d/m/Y', strtotime($subscriber['last_activity_at'])) : 'Jamais' ?>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_subscriber">
                                            <input type="hidden" name="id" value="<?= $subscriber['id'] ?>">
                                            <button type="submit" class="btn btn-sm <?= $subscriber['is_active'] ? 'btn-outline' : 'btn-success' ?>" title="<?= $subscriber['is_active'] ? 'Désactiver' : 'Activer' ?>">
                                                <?= $subscriber['is_active'] ? '⏸️' : '▶️' ?>
                                            </button>
                                        </form>
                                        
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cet abonné ?')">
                                            <input type="hidden" name="action" value="delete_subscriber">
                                            <input type="hidden" name="id" value="<?= $subscriber['id'] ?>">
                                            <button type="submit" class="btn btn-sm" style="background: var(--error-color); color: white;" title="Supprimer">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">👥</div>
                <h3>Aucun abonné trouvé</h3>
                <p>Aucun abonné ne correspond à vos critères</p>
                <button onclick="showAddSubscriber()" class="btn btn-primary" style="margin-top: 15px;">Ajouter le premier abonné</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- PAGINATION -->
<?php if ($total_pages > 1): ?>
    <div class="content-card">
        <div class="card-content" style="text-align: center;">
            <div style="display: inline-flex; gap: 10px; align-items: center;">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="btn btn-outline">← Précédent</a>
                <?php endif; ?>
                
                <span style="color: var(--text-light);">
                    Page <?= $page ?> sur <?= $total_pages ?>
                </span>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="btn btn-outline">Suivant →</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function showAddSubscriber() {
    document.getElementById('add-subscriber-form').style.display = 'block';
    document.getElementById('import-form').style.display = 'none';
}

function hideAddSubscriber() {
    document.getElementById('add-subscriber-form').style.display = 'none';
}

function showImportForm() {
    document.getElementById('import-form').style.display = 'block';
    document.getElementById('add-subscriber-form').style.display = 'none';
}

function hideImportForm() {
    document.getElementById('import-form').style.display = 'none';
}

function toggleAllCheckboxes() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.subscriber-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function executeBulkAction() {
    const bulkAction = document.getElementById('bulk-action').value;
    const checkedBoxes = document.querySelectorAll('.subscriber-checkbox:checked');
    
    if (!bulkAction) {
        alert('Veuillez sélectionner une action');
        return;
    }
    
    if (checkedBoxes.length === 0) {
        alert('Veuillez sélectionner au moins un abonné');
        return;
    }
    
    let confirmMessage = '';
    switch (bulkAction) {
        case 'activate':
            confirmMessage = `Activer ${checkedBoxes.length} abonné(s) ?`;
            break;
        case 'deactivate':
            confirmMessage = `Désactiver ${checkedBoxes.length} abonné(s) ?`;
            break;
        case 'delete':
            confirmMessage = `Supprimer définitivement ${checkedBoxes.length} abonné(s) ?`;
            break;
    }
    
    if (confirm(confirmMessage)) {
        document.getElementById('bulk-action-input').value = bulkAction;
        document.getElementById('bulk-form').submit();
    }
}
</script>

<?php $template->renderFooter(); ?>