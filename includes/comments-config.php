<?php
/**
 * TechEssentials Pro - Configuration Commentaires & Anti-spam
 * Sockets préparés pour Akismet, reCAPTCHA, Disqus
 */

if (!defined('TECHESSENTIALS_PRO')) {
    die('Accès direct non autorisé');
}

return [
    // ============================================
    // AKISMET ANTI-SPAM
    // ============================================
    'akismet' => [
        'enabled' => false,
        'api_key' => '',  // Obtenir sur https://akismet.com/
        'site_url' => 'https://techessentialspro.com',
        'endpoint' => 'https://rest.akismet.com/1.1/',
        'auto_delete_spam' => false,  // Supprimer auto ou modérer ?
        'spam_threshold' => 0.5       // Seuil de détection (0-1)
    ],
    
    // ============================================
    // GOOGLE reCAPTCHA v3
    // ============================================
    'recaptcha' => [
        'enabled' => true,
        'version' => 'v3',  // v2 ou v3
        'site_key' => '6Ldm--4rAAAAAOS7KSKh332MRhn3tpBwl4UrdHcu',   // Clé publique (frontend)
        'secret_key' => '6Ldm--4rAAAAAFoS8qHMHEGzO8PuPJAVowG2jfee', // Clé secrète (backend)
        'endpoint' => 'https://www.google.com/recaptcha/api/siteverify',
        'min_score' => 0.5, // Score minimum pour v3 (0-1)
        'action' => 'submit_comment'
    ],
    
    // ============================================
    // DISQUS (Alternative complète)
    // ============================================
    'disqus' => [
        'enabled' => false,
        'shortname' => '',  // Ton identifiant Disqus
        'api_key' => '',
        'api_secret' => '',
        'embed_url' => 'https://SHORTNAME.disqus.com/embed.js'
    ],
    
    // ============================================
    // MODÉRATION AUTOMATIQUE
    // ============================================
    'moderation' => [
        'auto_approve' => false,        // Approuver auto ou modération ?
        'require_email_verify' => false, // Vérifier email avant ?
        'max_links' => 2,                // Nb max liens autorisés
        'banned_words' => [              // Mots interdits
            'spam', 'viagra', 'casino', 'porn', 'xxx'
        ],
        'min_comment_length' => 10,      // Minimum caractères
        'max_comment_length' => 2000,    // Maximum caractères
        'rate_limit' => [
            'enabled' => true,
            'max_comments' => 5,         // Max commentaires
            'time_window' => 3600        // Par heure (en secondes)
        ]
    ],
    
    // ============================================
    // NOTIFICATIONS
    // ============================================
    'notifications' => [
        'notify_admin' => true,          // Notifier admin nouveau commentaire
        'admin_email' => 'admin@techessentialspro.com',
        'notify_user_reply' => true      // Notifier utilisateur si réponse
    ]
];