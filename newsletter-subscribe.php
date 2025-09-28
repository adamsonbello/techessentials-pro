<?php
define('TECHESSENTIALS_PRO', true);
require_once __DIR__ . '/includes/config.php';
require_once INCLUDES_PATH . 'functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$lang = $_POST['lang'] ?? $_SESSION['lang'] ?? 'fr';
$email = trim($_POST['email'] ?? '');

// Nettoyer l'URL de référence (supprimer les paramètres newsletter existants)
$referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
$referer = preg_replace('/[?&]newsletter_(success|error)=[^&]*/', '', $referer);

// Validation
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = $lang === 'fr' ? 'Adresse email invalide' : 'Invalid email address';
    $separator = strpos($referer, '?') !== false ? '&' : '?';
    header('Location: ' . $referer . $separator . 'newsletter_error=' . urlencode($error));
    exit;
}

try {
    $db = getDB('main');
    
    // Vérifier si l'email existe déjà
    $stmt = $db->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();
    
    if ($existing && $existing['status'] === 'active') {
        $message = $lang === 'fr' ? 'Vous êtes déjà abonné!' : 'Already subscribed!';
    } else {
        if ($existing) {
            $stmt = $db->prepare("UPDATE newsletter_subscribers SET status = 'active', updated_at = NOW() WHERE id = ?");
            $stmt->execute([$existing['id']]);
        } else {
            $stmt = $db->prepare("
                INSERT INTO newsletter_subscribers (email, language, status, ip_address, user_agent) 
                VALUES (?, ?, 'active', ?, ?)
            ");
            $stmt->execute([
                $email, 
                $lang, 
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        }
        $message = $lang === 'fr' ? 'Merci pour votre abonnement!' : 'Thank you for subscribing!';
    }
    
    $separator = strpos($referer, '?') !== false ? '&' : '?';
    header('Location: ' . $referer . $separator . 'newsletter_success=' . urlencode($message));
    exit;
    
} catch (Exception $e) {
    error_log("Newsletter subscription error: " . $e->getMessage());
    $error = $lang === 'fr' ? 'Erreur technique' : 'Technical error';
    $separator = strpos($referer, '?') !== false ? '&' : '?';
    header('Location: ' . $referer . $separator . 'newsletter_error=' . urlencode($error));
    exit;
}
?>