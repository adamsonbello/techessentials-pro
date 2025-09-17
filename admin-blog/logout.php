<?php
// admin-blog/logout.php - Gestion de la déconnexion
session_start();

// Sauvegarder le nom d'utilisateur pour le message
$username = $_SESSION['blog_admin_username'] ?? 'Utilisateur';

// Détruire toutes les variables de session du blog admin
unset($_SESSION['blog_admin_logged']);
unset($_SESSION['blog_admin_username']);
unset($_SESSION['blog_admin_id']);
unset($_SESSION['blog_admin_role']);
unset($_SESSION['blog_admin_email']);

// Si vous voulez détruire complètement la session (déconnexion totale)
// session_destroy();

// Optionnel : Enregistrer la déconnexion dans les logs
try {
    // Configuration DB
    $DB_CONFIG = [
        'host' => 'localhost',
        'dbname' => 'techessentials_blog',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ];
    
    $dsn = "mysql:host={$DB_CONFIG['host']};dbname={$DB_CONFIG['dbname']};charset={$DB_CONFIG['charset']}";
    $pdo = new PDO($dsn, $DB_CONFIG['username'], $DB_CONFIG['password']);
    
    // Enregistrer dans les logs (si la table existe)
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user, action, ip_address, created_at) 
        VALUES (?, 'logout', ?, NOW())
    ");
    $stmt->execute([$username, $_SERVER['REMOTE_ADDR']]);
} catch (Exception $e) {
    // Ignorer les erreurs de log (la table peut ne pas exister)
}

// Message de déconnexion
$_SESSION['logout_message'] = "Vous avez été déconnecté avec succès.";

// Rediriger vers la page de connexion
header('Location: index.php');
exit();
?>