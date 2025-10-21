<?php
/**
 * TechEssentials Pro - Données Produits Centralisées
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-21
 * 
 * Fichier central contenant tous les produits
 * Utilisé par products.php et product-detail.php
 * En production : sera remplacé par des requêtes DB
 */

// Vérifier que le fichier est inclus correctement
if (!defined('TECHESSENTIALS_PRO')) {
    die('Accès direct non autorisé');
}

/**
 * Tous les produits avec données complètes
 * Structure identique pour products.php et product-detail.php
 */
$all_products = [
    // PRODUIT 1
    [
        'id' => 'anker-737',
        'image' => 'anker-737.jpg',  // Pour products.php (liste)
        'category' => 'chargers',
        'name' => $lang === 'fr' ? 'Anker 737 PowerCore 24K' : 'Anker 737 PowerCore 24K',
        'description' => $lang === 'fr' ? 'Batterie externe haute capacité...' : 'High-capacity power bank...',
        'price' => 139.99,
        'original_price' => 159.99,
        'discount_percentage' => 13,
        'rating' => 4.8,
        'amazon_url' => 'https://amazon.fr/anker-737',
        
        // ✅ GALERIE 4 IMAGES
        'images' => [
            'main' => 'anker-737/main.jpg',
            'gallery' => [
                'anker-737/gallery-1.jpg',
                'anker-737/gallery-2.jpg',
                'anker-737/gallery-3.jpg',
                'anker-737/gallery-4.jpg'
            ]
        ],
        
        'asin' => 'B0ANKER737',
        'ean' => '0123456789012',
        'sku' => 'ANKER-737-24K',
        'keywords' => 'Anker PowerCore 737'
    ],
[
        'id' => 'anker-dock',
        'image' => 'anker-dock.jpg',  // Pour products.php (liste)
        'category' => 'hubs',
        'name' => $lang === 'fr' ? 'Anker PowerExpand+ Hub USB-C' : 'Anker PowerExpand+ USB-C Hub',
        'description' => $lang === 'fr' ? 'Hub USB-C 7-en-1...' : '7-in-1 USB-C hub...',
        'price' => 79.99,
        'original_price' => 99.99,
        'discount_percentage' => 20,
        'rating' => 4.6,
        'amazon_url' => 'https://amazon.fr/anker-dock',
        
        // ✅ GALERIE 4 IMAGES
        'images' => [
            'main' => 'anker-dock/main.jpg',
            'gallery' => [
                'anker-dock/gallery-1.jpg',
                'anker-dock/gallery-2.jpg',
                'anker-dock/gallery-3.jpg',
                'anker-dock/gallery-4.jpg'
            ]
        ],
        
        'asin' => 'B0ANKERDOCK',
        'ean' => '9876543210987',
        'sku' => 'ANKER-DOCK-7IN1',
        'keywords' => 'Anker PowerExpand USB-C Hub'
    ],

    //FAIS PAREILLE POUR LE RESTE DE PRODUITS
    [
        'id' => 'anker-nebula',
        'image' => 'anker-nebula.jpg',
        'category' => 'projectors',
        'name' => $lang === 'fr' ? 'Anker Nebula Projector' : 'Anker Nebula Projector',
        'description' => $lang === 'fr' ? 'Projecteur portable compact avec qualité Full HD et haut-parleurs intégrés.' : 'Compact portable projector with Full HD quality and built-in speakers.',
        'price' => 399.99,
        'original_price' => 499.99,
        'discount_percentage' => 20,
        'rating' => 4.4,
        'amazon_url' => 'https://amazon.fr/anker-nebula',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ],
    [
        'id' => 'anker-powercore',
        'image' => 'anker-powercore.jpg',
        'category' => 'chargers',
        'name' => $lang === 'fr' ? 'Anker PowerCore Portable' : 'Anker PowerCore Portable',
        'description' => $lang === 'fr' ? 'Chargeur portable haute capacité avec technologie de charge rapide et ports multiples.' : 'High-capacity portable charger with fast charging technology and multiple ports.',
        'price' => 49.99,
        'original_price' => 69.99,
        'discount_percentage' => 29,
        'rating' => 4.7,
        'amazon_url' => 'https://amazon.fr/anker-powercore',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ],
    [
        'id' => 'anker-soundcore-q30',
        'image' => 'anker-soundcore-q30.jpg',
        'category' => 'headphones',
        'name' => $lang === 'fr' ? 'Anker Soundcore Q30' : 'Anker Soundcore Q30',
        'description' => $lang === 'fr' ? 'Casque sans fil avec réduction de bruit hybride et 40h d\'autonomie.' : 'Wireless headphones with hybrid noise cancellation and 40h battery life.',
        'price' => 79.99,
        'original_price' => 99.99,
        'discount_percentage' => 20,
        'rating' => 4.5,
        'amazon_url' => 'https://amazon.fr/anker-soundcore-q30',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ],
    [
        'id' => 'asus-proart-pa248qv',
        'image' => 'asus-proart-pa248QV.jpg',
        'category' => 'monitors',
        'name' => $lang === 'fr' ? 'ASUS ProArt PA248QV' : 'ASUS ProArt PA248QV',
        'description' => $lang === 'fr' ? 'Moniteur professionnel 24.1" avec précision colorimétrique exceptionnelle et certification Pantone.' : 'Professional 24.1" monitor with exceptional color accuracy and Pantone certification.',
        'price' => 449.99,
        'original_price' => 599.99,
        'discount_percentage' => 25,
        'rating' => 4.9,
        'amazon_url' => 'https://amazon.fr/asus-proart',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ],
    [
        'id' => 'aukey-hub',
        'image' => 'aukey-hub.jpg',
        'category' => 'hubs',
        'name' => $lang === 'fr' ? 'Aukey Hub USB-C' : 'Aukey USB-C Hub',
        'description' => $lang === 'fr' ? 'Hub USB-C compact avec ports USB 3.0, HDMI et lecteur de cartes SD.' : 'Compact USB-C hub with USB 3.0 ports, HDMI and SD card reader.',
        'price' => 39.99,
        'original_price' => 49.99,
        'discount_percentage' => 20,
        'rating' => 4.3,
        'amazon_url' => 'https://amazon.fr/aukey-hub',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ],
    [
        'id' => 'benq-screenbar',
        'image' => 'benq-screenbar.jpg',
        'category' => 'lighting',
        'name' => $lang === 'fr' ? 'BenQ ScreenBar' : 'BenQ ScreenBar',
        'description' => $lang === 'fr' ? 'Lampe d\'écran LED avec contrôle automatique de la luminosité et économie d\'espace.' : 'LED screen lamp with automatic brightness control and space-saving design.',
        'price' => 109.99,
        'original_price' => 129.99,
        'discount_percentage' => 15,
        'rating' => 4.6,
        'amazon_url' => 'https://amazon.fr/benq-screenbar',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ],
    [
        'id' => 'blue-yeti-nano',
        'image' => 'blue-yeti-nano.png',
        'category' => 'microphones',
        'name' => $lang === 'fr' ? 'Blue Yeti Nano' : 'Blue Yeti Nano',
        'description' => $lang === 'fr' ? 'Microphone USB professionnel compact avec qualité broadcast et plug-and-play.' : 'Compact professional USB microphone with broadcast quality and plug-and-play.',
        'price' => 99.99,
        'original_price' => 119.99,
        'discount_percentage' => 17,
        'rating' => 4.7,
        'amazon_url' => 'https://amazon.fr/blue-yeti-nano',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ],
    [
        'id' => 'bose-qc45',
        'image' => 'bose-qc45.jpg',
        'category' => 'headphones',
        'name' => $lang === 'fr' ? 'Bose QuietComfort 45' : 'Bose QuietComfort 45',
        'description' => $lang === 'fr' ? 'Casque sans fil premium avec réduction de bruit de pointe et qualité sonore exceptionnelle.' : 'Premium wireless headphones with industry-leading noise cancellation and exceptional sound quality.',
        'price' => 329.99,
        'original_price' => 379.99,
        'discount_percentage' => 13,
        'rating' => 4.8,
        'amazon_url' => 'https://amazon.fr/bose-qc45',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ],
    [
        'id' => 'dell-ultrasharp-u2720q',
        'image' => 'dell-ultrasharp-u2720q.jpg',
        'category' => 'monitors',
        'name' => $lang === 'fr' ? 'Dell UltraSharp U2720Q' : 'Dell UltraSharp U2720Q',
        'description' => $lang === 'fr' ? 'Moniteur 4K 27" avec connectivité USB-C et précision colorimétrique premium.' : 'Professional 27" 4K monitor with USB-C connectivity and premium color accuracy.',
        'price' => 699.99,
        'original_price' => 899.99,
        'discount_percentage' => 22,
        'rating' => 4.6,
        'amazon_url' => 'https://amazon.fr/dell-ultrasharp',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ],
    [
        'id' => 'herman-miller-sayl',
        'image' => 'herman-miller-sayl.jpg',
        'category' => 'chairs',
        'name' => $lang === 'fr' ? 'Herman Miller Sayl' : 'Herman Miller Sayl',
        'description' => $lang === 'fr' ? 'Chaise ergonomique innovante avec dossier suspension et design moderne.' : 'Innovative ergonomic chair with suspension back and modern design.',
        'price' => 395.99,
        'original_price' => 495.99,
        'discount_percentage' => 20,
        'rating' => 4.4,
        'amazon_url' => 'https://amazon.fr/herman-miller-sayl',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ],
    [
        'id' => 'logitech-brio',
        'image' => 'logitech-brio.jpg',
        'category' => 'webcams',
        'name' => $lang === 'fr' ? 'Logitech Brio 4K' : 'Logitech Brio 4K',
        'description' => $lang === 'fr' ? 'Webcam 4K Ultra HD avec autofocus et correction d\'éclairage automatique.' : '4K Ultra HD webcam with autofocus and automatic light correction.',
        'price' => 199.99,
        'original_price' => 249.99,
        'discount_percentage' => 20,
        'rating' => 4.5,
        'amazon_url' => 'https://amazon.fr/logitech-brio',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ],
    [
        'id' => 'logitech-c920',
        'image' => 'logitech-c920.jpg',
        'category' => 'webcams',
        'name' => $lang === 'fr' ? 'Logitech C920 HD Pro' : 'Logitech C920 HD Pro',
        'description' => $lang === 'fr' ? 'Webcam Full HD 1080p avec autofocus et microphones stéréo intégrés.' : 'Full HD 1080p webcam with autofocus and built-in stereo microphones.',
        'price' => 69.99,
        'original_price' => 89.99,
        'discount_percentage' => 22,
        'rating' => 4.6,
        'amazon_url' => 'https://amazon.fr/logitech-c920',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ],
    [
        'id' => 'logitech-ergo-k860',
        'image' => 'logitech-ergo-k860.jpg',
        'category' => 'keyboards',
        'name' => $lang === 'fr' ? 'Logitech Ergo K860' : 'Logitech Ergo K860',
        'description' => $lang === 'fr' ? 'Clavier ergonomique sans fil avec design courbé et repose-poignets intégré.' : 'Wireless ergonomic keyboard with curved design and integrated palm rest.',
        'price' => 129.99,
        'original_price' => 159.99,
        'discount_percentage' => 19,
        'rating' => 4.4,
        'amazon_url' => 'https://amazon.fr/logitech-ergo-k860',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ],
    [
        'id' => 'logitech-mx-keys',
        'image' => 'logitech-mx-keys.jpg',
        'category' => 'keyboards',
        'name' => $lang === 'fr' ? 'Logitech MX Keys' : 'Logitech MX Keys',
        'description' => $lang === 'fr' ? 'Clavier sans fil avancé avec rétroéclairage intelligent et frappe de précision.' : 'Advanced wireless keyboard with smart illumination and precision typing.',
        'price' => 109.99,
        'original_price' => 139.99,
        'discount_percentage' => 21,
        'rating' => 4.5,
        'amazon_url' => 'https://amazon.fr/logitech-mx-keys',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ],
    [
        'id' => 'logitech-mx-master-3s',
        'image' => 'logitech-mx-master-3s.jpg',
        'category' => 'mice',
        'name' => $lang === 'fr' ? 'Logitech MX Master 3S' : 'Logitech MX Master 3S',
        'description' => $lang === 'fr' ? 'Souris sans fil avancée avec molette MagSpeed et suivi de précision.' : 'Advanced wireless mouse with MagSpeed scroll wheel and precision tracking.',
        'price' => 99.99,
        'original_price' => 129.99,
        'discount_percentage' => 23,
        'rating' => 4.7,
        'amazon_url' => 'https://amazon.fr/logitech-mx-master',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ],
    [
        'id' => 'logitech-streamcam',
        'image' => 'logitech-streamcam.jpg',
        'category' => 'webcams',
        'name' => $lang === 'fr' ? 'Logitech StreamCam' : 'Logitech StreamCam',
        'description' => $lang === 'fr' ? 'Webcam Full HD 1080p à 60fps optimisée pour le streaming et création de contenu.' : 'Full HD 1080p 60fps webcam optimized for streaming and content creation.',
        'price' => 159.99,
        'original_price' => 189.99,
        'discount_percentage' => 16,
        'rating' => 4.5,
        'amazon_url' => 'https://amazon.fr/logitech-streamcam',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ],
    [
        'id' => 'logitech-zone-wireless',
        'image' => 'logitech-zone-wireless.jpg',
        'category' => 'headphones',
        'name' => $lang === 'fr' ? 'Logitech Zone Wireless' : 'Logitech Zone Wireless',
        'description' => $lang === 'fr' ? 'Casque professionnel sans fil avec microphone antibruit pour téléconférences.' : 'Professional wireless headset with noise-canceling microphone for video conferencing.',
        'price' => 199.99,
        'original_price' => 229.99,
        'discount_percentage' => 13,
        'rating' => 4.3,
        'amazon_url' => 'https://amazon.fr/logitech-zone-wireless',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ],
    [
        'id' => 'seagate-2tb',
        'image' => 'seagate-2tb.jpg',
        'category' => 'storage',
        'name' => $lang === 'fr' ? 'Seagate 2TB Portable' : 'Seagate 2TB Portable',
        'description' => $lang === 'fr' ? 'Disque dur externe portable 2TB ultra-mince avec USB 3.0 et sauvegarde automatique.' : 'Ultra-slim 2TB portable external drive with USB 3.0 and automatic backup.',
        'price' => 79.99,
        'original_price' => 99.99,
        'discount_percentage' => 20,
        'rating' => 4.4,
        'amazon_url' => 'https://amazon.fr/seagate-2tb',

        // ✅ NOUVEAUX CHAMPS POUR API
        'asin' => 'B0XXXXXX',           // Amazon Standard Identification Number
        'ean' => '0123456789012',       // European Article Number (code-barres)
        'sku' => 'APPLE-IP16P-256',     // Stock Keeping Unit
        'keywords' => 'iPhone 16 Pro'  // Pour recherche AliExpress
        // ... reste des données
    ]
];

/**
 * Fonction utilitaire pour récupérer un produit par ID
 * @param string $product_id
 * @return array|null
 */
function getProductById($product_id) {
    global $all_products;
    
    foreach ($all_products as $product) {
        if ($product['id'] === $product_id) {
            return $product;
        }
    }
    
    return null;
}

/**
 * Fonction utilitaire pour récupérer les produits par catégorie
 * @param string $category
 * @return array
 */
function getProductsByCategory($category) {
    global $all_products;
    
    if ($category === 'all') {
        return $all_products;
    }
    
    return array_filter($all_products, function($product) use ($category) {
        return $product['category'] === $category;
    });
}

/**
 * Fonction utilitaire pour récupérer les produits recommandés
 * @param string $exclude_id - ID du produit à exclure
 * @param int $limit - Nombre de produits à retourner
 * @return array
 */
function getRecommendedProducts($exclude_id = '', $limit = 4) {
    global $all_products;
    
    $products = array_filter($all_products, function($product) use ($exclude_id) {
        return $product['id'] !== $exclude_id;
    });
    
    // Trier par rating décroissant
    usort($products, function($a, $b) {
        return $b['rating'] <=> $a['rating'];
    });
    
    return array_slice($products, 0, $limit);
}