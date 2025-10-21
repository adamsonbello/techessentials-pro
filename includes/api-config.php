<?php
/**
 * TechEssentials Pro - Configuration API externes
 * Sockets préparés pour intégration future des API vendors
 */

if (!defined('TECHESSENTIALS_PRO')) {
    die('Accès direct non autorisé');
}

return [
    // ============================================
    // AMAZON PRODUCT ADVERTISING API
    // ============================================
    'amazon' => [
        'enabled' => false,  // Passer à true quand API configurée
        'api_key' => '',     // Access Key ID
        'api_secret' => '',  // Secret Access Key
        'endpoint' => 'https://webservices.amazon.fr/paapi5/searchitems',
        'associate_tag' => 'techessentials-21',  // Ton ID affilié Amazon
        'region' => 'eu-west-1',
        'marketplace' => 'www.amazon.fr'
    ],
    
    // ============================================
    // FNAC API PARTENAIRE
    // ============================================
    'fnac' => [
        'enabled' => false,
        'api_key' => '',
        'partner_id' => '',
        'endpoint' => 'https://api.fnac.com/v1/products',
        'affiliate_url' => 'https://www.fnac.com/SearchResult/ResultList.aspx?Search='
    ],
    
    // ============================================
    // CDISCOUNT API
    // ============================================
    'cdiscount' => [
        'enabled' => false,
        'api_key' => '',
        'endpoint' => 'https://api.cdiscount.com/OpenApi/json/Search',
        'site_id' => '100'
    ],
    
    // ============================================
    // ALIEXPRESS AFFILIATE API
    // ============================================
    'aliexpress' => [
        'enabled' => false,
        'app_key' => '',
        'app_secret' => '',
        'tracking_id' => '',
        'endpoint' => 'https://api-sg.aliexpress.com/sync',
        'region' => 'FR'
    ],
    
    // ============================================
    // RAKUTEN (ex-PriceMinister)
    // ============================================
    'rakuten' => [
        'enabled' => false,
        'affiliate_id' => '',
        'endpoint' => 'https://fr.shopping.rakuten.com/search/'
    ],
    
    // ============================================
    // CONFIGURATION CACHE & FALLBACK
    // ============================================
    'cache' => [
        'enabled' => true,
        'duration' => 3600,        // 1 heure en secondes
        'directory' => __DIR__ . '/../cache/api/'
    ],
    
    'fallback' => [
        'use_mock_data' => true,   // Utiliser données mockées si API fail
        'log_errors' => true,      // Logger les erreurs API
        'retry_attempts' => 3,     // Nombre de tentatives si échec
        'timeout' => 10            // Timeout requête en secondes
    ],
    
    // ============================================
    // PARAMÈTRES GLOBAUX
    // ============================================
    'global' => [
        'default_currency' => 'EUR',
        'vat_rate' => 0.20,        // TVA 20%
        'affiliate_disclosure' => true  // Afficher mention liens affiliés
    ]
];