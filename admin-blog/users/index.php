<?php
// admin-blog/users/index.php - Gestion des utilisateurs du blog
require_once '../includes/template.php';

$template = new BlogAdminTemplate('Utilisateurs', 'users');
$db = $template->getDB();

$message = '';
$error = '';

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add_user':
                $username = trim($_POST['username'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $full_name = trim($_POST['full_name'] ?? '');
                $role = $_POST['role'] ?? 'editor';
                $password = $_POST['password'] ?? '';
                
                if (empty($username)) throw new Exception("Nom d'utilisateur requis");
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Email valide requis");
                }
                if (strlen($password) < 6) {
                    throw new Exception("Mot de passe minimum 6 caractères");
                }
                
                // Vérifier l'unicité
                $stmt = $db->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                if ($stmt->fetch()) {
                    throw new Exception("Nom d'utilisateur ou email déjà utilisé");
                }
                
                // Création
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    INSERT INTO admin_users 
                    (username, email, full_name, role, password_hash, is_active, created_by) 
                    VALUES (?, ?, ?, ?, ?, 1, ?)
                ");
                $stmt->execute([
                    $username, $email, $full_name, $role, 
                    $password_hash, $_SESSION['blog_admin_user'] ?? 'admin'
                ]);
                
                $message = "Utilisateur '$username' créé avec succès";
                break;
                
            case 'edit_user':
                $user_id = (int)$_POST['user_id'];
                $username = trim($_POST['username'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $full_name = trim($_POST['full_name'] ?? '');
                $role = $_POST['role'] ?? 'editor';
                $new_password = $_POST['new_password'] ?? '';
                
                if (empty($username)) throw new Exception("Nom d'utilisateur requis");
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Email valide requis");
                }
                
                // Vérifier l'unicité (sauf pour l'utilisateur actuel)
                $stmt = $db->prepare("SELECT id FROM admin_users WHERE (username = ? OR email = ?) AND id != ?");
                $stmt->execute([$username, $email, $user_id]);
                if ($stmt->fetch()) {
                    throw new Exception("Nom d'utilisateur ou email déjà utilisé");
                }
                
                // Mise à jour
                if (!empty($new_password)) {
                    if (strlen($new_password) < 6) {
                        throw new Exception("Nouveau mot de passe minimum 6 caractères");
                    }
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("
                        UPDATE admin_users 
                        SET username = ?, email = ?, full_name = ?, role = ?, password_hash = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $stmt->execute([$username, $email, $full_name, $role, $password_hash, $user_id]);
                } else {
                    $stmt = $db->prepare("
                        UPDATE admin_users 
                        SET username = ?, email = ?, full_name = ?, role = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $stmt->execute([$username, $email, $full_name, $role, $user_id]);
                }
                
                $message = "Utilisateur mis à jour avec succès";
                break;
                
            case 'toggle_user':
                $user_id = (int)$_POST['user_id'];
                $stmt = $db->prepare("UPDATE admin_users SET is_active = 1 - is_active WHERE id = ?");
                $stmt->execute([$user_id]);
                $message = "Statut de l'utilisateur mis à jour";
                break;
                
            case 'delete_user':
                $user_id = (int)$_POST['user_id'];
                
                // Vérifier qu'on ne supprime pas le dernier admin
                $stmt = $db->query("SELECT COUNT(*) as admin_count FROM admin_users WHERE role = 'admin' AND is_active = 1");
                $admin_count = $stmt->fetchColumn();
                
                $stmt = $db->prepare("SELECT role FROM admin_users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user_role = $stmt->fetchColumn();
                
                if ($user_role === 'admin' && $admin_count <= 1) {
                    throw new Exception("Impossible de supprimer le dernier administrateur");
                }
                
                $stmt = $db->prepare("DELETE FROM admin_users WHERE id = ?");
                $stmt->execute([$user_id]);
                $message = "Utilisateur supprimé";
                break;
                
            case 'change_password':
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                if (empty($current_password)) throw new Exception("Mot de passe actuel requis");
                if (empty($new_password)) throw new Exception("Nouveau mot de passe requis");
                if ($new_password !== $confirm_password) throw new Exception("Les mots de passe ne correspondent pas");
                if (strlen($new_password) < 6) throw new Exception("Nouveau mot de passe minimum 6 caractères");
                
                // Vérifier le mot de passe actuel
                $stmt = $db->prepare("SELECT password_hash FROM admin_users WHERE username = ?");
                $stmt->execute([$_SESSION['blog_admin_user'] ?? '']);
                $current_hash = $stmt->fetchColumn();
                
                if (!password_verify($current_password, $current_hash)) {
                    throw new Exception("Mot de passe actuel incorrect");
                }
                
                // Mettre à jour
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE username = ?");
                $stmt->execute([$new_hash, $_SESSION['blog_admin_user'] ?? '']);
                
                $message = "Mot de passe modifié avec succès";
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Récupération des utilisateurs
$users_query = "
    SELECT 
        id, username, email, full_name, role, is_active, 
        last_login_at, created_at, 
        CASE 
            WHEN role = 'admin' THEN 'Administrateur'
            WHEN role = 'editor' THEN 'Éditeur'
            WHEN role = 'author' THEN 'Auteur'
            ELSE 'Utilisateur'
        END as role_name
    FROM admin_users 
    ORDER BY created_at DESC
";

$users = $db->query($users_query)->fetchAll();

// Statistiques
$stats = $db->query("
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN is_active = 1 THEN 1 END) as active,
        COUNT(CASE WHEN role = 'admin' THEN 1 END) as admins,
        COUNT(CASE WHEN role = 'editor' THEN 1 END) as editors,
        COUNT(CASE WHEN last_login_at IS NOT NULL THEN 1 END) as have_logged_in
    FROM admin_users
")->fetch();

// Utilisateur en cours d'édition
$edit_user = null;
$edit_id = (int)($_GET['edit'] ?? 0);
if ($edit_id > 0) {
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_user = $stmt->fetch();
}

// La table admin_users existe déjà avec sa propre structure
// Pas besoin de la créer ou modifier


// Rendu de la page
$template->renderHeader();
?>

<!-- PAGE HEADER -->
<div class="page-header">
    <h2 class="page-title">Gestion des Utilisateurs</h2>
    <div class="btn-group">
        <button onclick="showAddUser()" class="btn btn-primary">+ Nouvel Utilisateur</button>
        <button onclick="showChangePassword()" class="btn btn-outline">🔑 Mon Mot de Passe</button>
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
        <div style="color: var(--text-light); font-size: 0.9rem;">Total utilisateurs</div>
    </div>
    <div class="content-card" style="padding: 20px; text-align: center;">
        <div style="font-size: 1.8rem; font-weight: bold; color: var(--success-color);"><?= $stats['active'] ?></div>
        <div style="color: var(--text-light); font-size: 0.9rem;">Actifs</div>
    </div>
    <div class="content-card" style="padding: 20px; text-align: center;">
        <div style="font-size: 1.8rem; font-weight: bold; color: var(--error-color);"><?= $stats['admins'] ?></div>
        <div style="color: var(--text-light); font-size: 0.9rem;">Administrateurs</div>
    </div>
    <div class="content-card" style="padding: 20px; text-align: center;">
        <div style="font-size: 1.8rem; font-weight: bold; color: var(--info-color);"><?= $stats['editors'] ?></div>
        <div style="color: var(--text-light); font-size: 0.9rem;">Éditeurs</div>
    </div>
</div>

<!-- FORMULAIRE AJOUT UTILISATEUR (caché) -->
<div id="add-user-form" class="content-card" style="<?= $edit_user ? 'display: block;' : 'display: none;' ?> margin-bottom: 20px;">
    <div class="card-header">
        <h3 class="card-title"><?= $edit_user ? 'Modifier l\'utilisateur' : 'Ajouter un nouvel utilisateur' ?></h3>
        <button onclick="hideAddUser()" class="btn btn-outline" style="padding: 4px 8px;">✕</button>
    </div>
    <div class="card-content">
        <form method="POST">
            <input type="hidden" name="action" value="<?= $edit_user ? 'edit_user' : 'add_user' ?>">
            <?php if ($edit_user): ?>
                <input type="hidden" name="user_id" value="<?= $edit_user['id'] ?>">
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <div>
                    <div class="form-group">
                        <label class="form-label">Nom d'utilisateur *</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($edit_user['username'] ?? '') ?>" 
                               required class="form-control" placeholder="nom_utilisateur">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($edit_user['email'] ?? '') ?>" 
                               required class="form-control" placeholder="user@exemple.com">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Nom complet</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($edit_user['full_name'] ?? '') ?>" 
                               class="form-control" placeholder="Prénom Nom">
                    </div>
                </div>
                
                <div>
                    <div class="form-group">
                        <label class="form-label">Rôle</label>
                        <select name="role" class="form-control">
                            <option value="author" <?= ($edit_user['role'] ?? '') === 'author' ? 'selected' : '' ?>>Auteur (lecture/écriture articles)</option>
                            <option value="editor" <?= ($edit_user['role'] ?? 'editor') === 'editor' ? 'selected' : '' ?>>Éditeur (gestion contenu)</option>
                            <option value="admin" <?= ($edit_user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrateur (tous droits)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?= $edit_user ? 'Nouveau mot de passe (optionnel)' : 'Mot de passe *' ?></label>
                        <input type="password" name="<?= $edit_user ? 'new_password' : 'password' ?>" 
                               class="form-control" placeholder="Minimum 6 caractères"
                               <?= $edit_user ? '' : 'required' ?>>
                        <?php if ($edit_user): ?>
                            <small style="color: var(--text-light);">Laissez vide pour conserver l'actuel</small>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($edit_user): ?>
                        <div style="background: var(--background-light); padding: 15px; border-radius: 8px; margin-top: 15px;">
                            <strong>Informations :</strong><br>
                            <small style="color: var(--text-light);">
                                Créé le <?= date('d/m/Y H:i', strtotime($edit_user['created_at'])) ?><br>
                                <?php if ($edit_user['created_by']): ?>
                                    Par : <?= htmlspecialchars($edit_user['created_by']) ?><br>
                                <?php endif; ?>
                                <?php if ($edit_user['last_login_at']): ?>
                                    Dernière connexion : <?= date('d/m/Y H:i', strtotime($edit_user['last_login_at'])) ?>
                                <?php else: ?>
                                    Jamais connecté
                                <?php endif; ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn btn-success">
                    <?= $edit_user ? 'Mettre à jour' : 'Créer l\'utilisateur' ?>
                </button>
                
                <?php if ($edit_user): ?>
                    <a href="?" class="btn btn-outline">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- FORMULAIRE CHANGEMENT MOT DE PASSE (caché) -->
<div id="change-password-form" class="content-card" style="display: none; margin-bottom: 20px;">
    <div class="card-header">
        <h3 class="card-title">Changer mon mot de passe</h3>
        <button onclick="hideChangePassword()" class="btn btn-outline" style="padding: 4px 8px;">✕</button>
    </div>
    <div class="card-content">
        <form method="POST">
            <input type="hidden" name="action" value="change_password">
            
            <div style="max-width: 400px;">
                <div class="form-group">
                    <label class="form-label">Mot de passe actuel *</label>
                    <input type="password" name="current_password" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nouveau mot de passe *</label>
                    <input type="password" name="new_password" required class="form-control" minlength="6">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Confirmer le nouveau mot de passe *</label>
                    <input type="password" name="confirm_password" required class="form-control" minlength="6">
                </div>
                
                <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
            </div>
        </form>
    </div>
</div>

<!-- LISTE DES UTILISATEURS -->
<div class="content-card">
    <div class="card-header">
        <h3 class="card-title">Liste des utilisateurs (<?= count($users) ?>)</h3>
    </div>
    
    <div class="card-content" style="padding: 0;">
        <?php if (!empty($users)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Dernière connexion</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr <?= ($edit_user && $edit_user['id'] == $user['id']) ? 'style="background: rgba(102, 126, 234, 0.05);"' : '' ?>>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($user['username']) ?></strong>
                                    <?php if ($user['full_name']): ?>
                                        <div style="color: var(--text-light); font-size: 0.85rem;">
                                            <?= htmlspecialchars($user['full_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="status-badge <?= $user['role'] ?>">
                                    <?php
                                    $role_icons = ['admin' => '👑', 'editor' => '✏️', 'author' => '📝'];
                                    echo $role_icons[$user['role']] . ' ' . $user['role_name'];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="status-badge active">Actif</span>
                                <?php else: ?>
                                    <span class="status-badge inactive">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['last_login_at']): ?>
                                    <?= date('d/m/Y H:i', strtotime($user['last_login_at'])) ?>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">Jamais</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                <?php if ($user['created_by']): ?>
                                    <div style="color: var(--text-light); font-size: 0.8rem;">
                                    
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="?edit=<?= $user['id'] ?>" class="btn btn-sm btn-outline" title="Modifier">
                                        ✏️
                                    </a>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_user">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-sm <?= $user['is_active'] ? 'btn-outline' : 'btn-success' ?>" 
                                                title="<?= $user['is_active'] ? 'Désactiver' : 'Activer' ?>">
                                            <?= $user['is_active'] ? '⏸️' : '▶️' ?>
                                        </button>
                                    </form>
                                    
                                    <?php 
                                    // Empêcher la suppression du dernier admin ou de soi-même
                                    $can_delete = true;
                                    if ($user['role'] === 'admin' && $stats['admins'] <= 1) $can_delete = false;
                                    if ($user['username'] === ($_SESSION['blog_admin_user'] ?? '')) $can_delete = false;
                                    ?>
                                    
                                    <?php if ($can_delete): ?>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-sm" style="background: var(--error-color); color: white;" title="Supprimer">
                                                🗑️
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm" disabled title="Protection : dernier admin ou utilisateur actuel" style="background: #ccc; color: #666;">
                                            🛡️
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">👤</div>
                <h3>Aucun utilisateur</h3>
                <p>Commencez par créer le premier utilisateur</p>
                <button onclick="showAddUser()" class="btn btn-primary" style="margin-top: 15px;">Créer un utilisateur</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- INFORMATIONS SÉCURITÉ -->
<div class="content-card">
    <div class="card-header">
        <h3 class="card-title">🔐 Sécurité et Permissions</h3>
    </div>
    <div class="card-content">
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 30px;">
            <div>
                <h4 style="color: var(--error-color); margin-bottom: 10px;">👑 Administrateur</h4>
                <ul style="color: var(--text-light); font-size: 0.9rem; line-height: 1.5; margin-left: 20px;">
                    <li>Accès total au système</li>
                    <li>Gestion des utilisateurs</li>
                    <li>Configuration système</li>
                    <li>Gestion de la sécurité</li>
                </ul>
            </div>
            
            <div>
                <h4 style="color: var(--info-color); margin-bottom: 10px;">✏️ Éditeur</h4>
                <ul style="color: var(--text-light); font-size: 0.9rem; line-height: 1.5; margin-left: 20px;">
                    <li>Gestion complète du contenu</li>
                    <li>Articles, catégories, tags</li>
                    <li>Modération commentaires</li>
                    <li>Newsletter et abonnés</li>
                </ul>
            </div>
            
            <div>
                <h4 style="color: var(--success-color); margin-bottom: 10px;">📝 Auteur</h4>
                <ul style="color: var(--text-light); font-size: 0.9rem; line-height: 1.5; margin-left: 20px;">
                    <li>Écriture d'articles</li>
                    <li>Gestion de ses articles</li>
                    <li>Médiathèque personnelle</li>
                    <li>Consultation des stats</li>
                </ul>
            </div>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: rgba(255, 193, 7, 0.1); border-radius: 8px; border-left: 4px solid var(--warning-color);">
            <strong>⚠️ Bonnes pratiques :</strong>
            <ul style="margin: 10px 0 0 20px; color: var(--text-light);">
                <li>Utilisez des mots de passe forts (8+ caractères, majuscules, chiffres, symboles)</li>
                <li>Changez régulièrement les mots de passe</li>
                <li>Gardez toujours au moins un administrateur actif</li>
                <li>Désactivez les comptes inutilisés au lieu de les supprimer</li>
            </ul>
        </div>
    </div>
</div>

<style>
.status-badge.admin { background: rgba(220, 53, 69, 0.1); color: var(--error-color); }
.status-badge.editor { background: rgba(33, 150, 243, 0.1); color: var(--info-color); }
.status-badge.author { background: rgba(40, 167, 69, 0.1); color: var(--success-color); }
</style>

<script>
function showAddUser() {
    document.getElementById('add-user-form').style.display = 'block';
    document.getElementById('change-password-form').style.display = 'none';
}

function hideAddUser() {
    document.getElementById('add-user-form').style.display = 'none';
}

function showChangePassword() {
    document.getElementById('change-password-form').style.display = 'block';
    document.getElementById('add-user-form').style.display = 'none';
}

function hideChangePassword() {
    document.getElementById('change-password-form').style.display = 'none';
}

// Validation en temps réel des mots de passe
document.addEventListener('DOMContentLoaded', function() {
    const newPasswordInput = document.querySelector('input[name="new_password"]');
    const confirmPasswordInput = document.querySelector('input[name="confirm_password"]');
    
    if (newPasswordInput && confirmPasswordInput) {
        function validatePasswords() {
            if (newPasswordInput.value && confirmPasswordInput.value) {
                if (newPasswordInput.value === confirmPasswordInput.value) {
                    confirmPasswordInput.style.borderColor = 'var(--success-color)';
                } else {
                    confirmPasswordInput.style.borderColor = 'var(--error-color)';
                }
            } else {
                confirmPasswordInput.style.borderColor = '';
            }
        }
        
        newPasswordInput.addEventListener('input', validatePasswords);
        confirmPasswordInput.addEventListener('input', validatePasswords);
    }
    
    // Auto-scroll vers l'utilisateur en cours d'édition
    const editRow = document.querySelector('tr[style*="background"]');
    if (editRow) {
        editRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>

<?php $template->renderFooter(); ?>