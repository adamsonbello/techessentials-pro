<?php
// config/email.php - Configuration email centralisée

// ===============================
// CONFIGURATION SMTP
// ===============================
return [
    // Paramètres SMTP principaux
    'smtp' => [
        'host'     => 'smtp.gmail.com',           // Serveur SMTP
        'port'     => 587,                        // Port (587 pour TLS, 465 pour SSL)
        'secure'   => 'tls',                      // Sécurité: 'tls' ou 'ssl'
        'auth'     => true,                       // Authentification requise
        'username' => 'votre-email@gmail.com',   // ⚠️ À CHANGER
        'password' => 'votre-mot-de-passe-app',  // ⚠️ Mot de passe d'application
    ],
    
    // Informations expéditeur
  
    'from' => [
    'email' => 'newsletter@techessentialspro.com',  // ← Changement ici
    'name'  => 'TechEssentials Pro'
],
    
    // Templates par défaut
    'templates' => [
        'welcome' => [
            'subject' => [
                'en' => 'Welcome to TechEssentials Pro Newsletter!',
                'fr' => 'Bienvenue dans la Newsletter TechEssentials Pro !'
            ]
        ],
        'newsletter' => [
            'subject' => [
                'en' => 'Weekly Tech Deals & Reviews',
                'fr' => 'Offres Tech & Avis Hebdomadaires'
            ]
        ]
    ],
    
    // Paramètres généraux
    'settings' => [
        'charset'     => 'UTF-8',
        'timeout'     => 30,
        'debug'       => false,  // true pour développement
        'auto_tls'    => true,
        'verify_peer' => false   // false pour développement local
    ]
];

// ===============================
// CONFIGURATION ALTERNATIVE POUR DIFFÉRENTS PROVIDERS
// ===============================

/*
// Gmail (avec mot de passe d'application)
'smtp' => [
    'host'     => 'smtp.gmail.com',
    'port'     => 587,
    'secure'   => 'tls',
    'username' => 'votre-email@gmail.com',
    'password' => 'mot-de-passe-application-16-caracteres',
],

// Outlook/Hotmail
'smtp' => [
    'host'     => 'smtp-mail.outlook.com',
    'port'     => 587,
    'secure'   => 'tls',
    'username' => 'votre-email@outlook.com',
    'password' => 'votre-mot-de-passe',
],

// SendGrid (service professionnel)
'smtp' => [
    'host'     => 'smtp.sendgrid.net',
    'port'     => 587,
    'secure'   => 'tls',
    'username' => 'apikey',
    'password' => 'votre-api-key-sendgrid',
],

// Serveur SMTP personnalisé
'smtp' => [
    'host'     => 'mail.votre-domaine.com',
    'port'     => 587,
    'secure'   => 'tls',
    'username' => 'newsletter@votre-domaine.com',
    'password' => 'mot-de-passe-email',
],
*/
?>