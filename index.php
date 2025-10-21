<?php

/**
 * TechEssentials Pro V2 - Page d'accueil automatisée
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-19
 */

// Configuration
define('TECHESSENTIALS_PRO', true);
require_once __DIR__ . '/includes/config.php';
require_once INCLUDES_PATH . 'functions.php';



// Initialiser la session pour langue
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Gestion de la langue
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'fr';
if (in_array($lang, ['fr', 'en'])) {
    $_SESSION['lang'] = $lang;
}

// EXACTEMENT tes noms de fichiers que tu m'as donnés
$all_products = [
    [
        'id' => 'anker-737',
        'image' => 'anker-737.jpg',
        'name' => 'Anker 737 PowerCore 24K',
        'description' => 'High-capacity 24,000mAh power bank with 140W output for laptops and devices.',
        'price' => 139.99,
        'original_price' => 159.99,
        'discount_percentage' => 13,
        'is_featured' => 1,
        'rating' => 4.8,
        'amazon_url' => 'https://amazon.fr/anker-737',
        'slug' => 'anker-737'
    ],
    [
        'id' => 'asus-proart-pa248qv',
        'image' => 'asus-proart-pa248QV.jpg',
        'name' => 'ASUS ProArt Display PA248QV',
        'description' => 'Professional monitor with exceptional color accuracy and Pantone certification.',
        'price' => 449.99,
        'original_price' => 599.99,
        'discount_percentage' => 25,
        'is_featured' => 1,
        'rating' => 4.9,
        'amazon_url' => 'https://amazon.fr/asus-proart',
        'slug' => 'asus-proart-monitor'
    ],
    [
        'id' => 'logitech-mx-master-3s',
        'image' => 'logitech-mx-master-3s.jpg',
        'name' => 'Logitech MX Master 3S',
        'description' => 'Advanced wireless mouse with MagSpeed scroll wheel and precision tracking.',
        'price' => 99.99,
        'original_price' => 129.99,
        'discount_percentage' => 23,
        'is_featured' => 1,
        'rating' => 4.7,
        'amazon_url' => 'https://amazon.fr/logitech-mx-master',
        'slug' => 'logitech-mx-master'
    ],
    [
        'id' => 'dell-ultrasharp-u2720q',
        'image' => 'dell-ultrasharp-u2720q.jpg',
        'name' => 'Dell UltraSharp U2720Q 27"',
        'description' => 'Professional 4K monitor with USB-C connectivity and premium color accuracy.',
        'price' => 699.99,
        'original_price' => 899.99,
        'discount_percentage' => 22,
        'is_featured' => 1,
        'rating' => 4.6,
        'amazon_url' => 'https://amazon.fr/dell-ultrasharp',
        'slug' => 'dell-ultrasharp'
    ],
    [
        'id' => 'logitech-mx-keys',
        'image' => 'logitech-mx-keys.jpg',
        'name' => 'Logitech MX Keys Advanced',
        'description' => 'Premium wireless keyboard with smart illumination and precision typing.',
        'price' => 109.99,
        'original_price' => 139.99,
        'discount_percentage' => 21,
        'is_featured' => 1,
        'rating' => 4.5,
        'amazon_url' => 'https://amazon.fr/logitech-mx-keys',
        'slug' => 'logitech-mx-keys'
    ],
    [
        'id' => 'anker-powercore',
        'image' => 'anker-powercore.jpg',
        'name' => 'Anker PowerCore Portable Charger',
        'description' => 'High-capacity portable charger with fast charging technology and multiple ports.',
        'price' => 79.99,
        'original_price' => 99.99,
        'discount_percentage' => 20,
        'is_featured' => 1,
        'rating' => 4.8,
        'amazon_url' => 'https://amazon.fr/anker-powercore',
        'slug' => 'anker-powercore'
    ]
];

// Récupérer seulement les 6 produits featured
$featured_products = array_filter($all_products, function($product) {
    return $product['is_featured'] == 1;
});
$featured_products = array_slice($featured_products, 0, 6);

// Traductions
// Traductions
$t = [
    'fr' => [
        'site_title' => 'TechEssentials Pro',
        'tagline' => 'Équipez votre espace de télétravail avec les meilleurs outils tech',
        'hero_title' => 'L\'Équipement Tech des Télétravailleurs',
        'hero_subtitle' => 'Productivité maximale pour nomades digitaux et professionnels en remote',
        'featured_products' => 'Produits Recommandés',
        'view_all_products' => 'Voir tous les produits',
        'buy_now' => 'Acheter maintenant',
        'learn_more' => 'En savoir plus',
        'newsletter_title' => 'Restez informé',
        'newsletter_subtitle' => 'Recevez nos meilleures recommandations tech pour le télétravail',
        'newsletter_placeholder' => 'Votre email',
        'newsletter_button' => "S'abonner",
        'stats_products' => 'Produits testés',
        'stats_reviews' => 'Avis vérifiés',
        'stats_partners' => 'Partenaires',
        'stats_users' => 'Télétravailleurs équipés'
    ],
    'en' => [
        'site_title' => 'TechEssentials Pro',
        'tagline' => 'Equip your remote workspace with the best tech tools',
        'hero_title' => 'Tech Gear for Remote Workers',
        'hero_subtitle' => 'Maximum productivity for digital nomads and remote professionals',
        'featured_products' => 'Featured Products',
        'view_all_products' => 'View all products',
        'buy_now' => 'Buy now',
        'learn_more' => 'Learn more',
        'newsletter_title' => 'Stay Updated',
        'newsletter_subtitle' => 'Get our best tech recommendations for remote work',
        'newsletter_placeholder' => 'Your email',
        'newsletter_button' => 'Subscribe',
        'stats_products' => 'Products tested',
        'stats_reviews' => 'Verified reviews',
        'stats_partners' => 'Partners',
        'stats_users' => 'Remote workers equipped'
    ]
];

$translations = $t[$lang];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">

<!-- 1. INDEX.PHP - PAGE D'ACCUEIL -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechEssentials Pro - Équipement Tech pour Télétravail & Nomades Digitaux</title>
    <meta name="description" content="Découvrez les meilleurs outils tech pour télétravailleurs à domicile et nomades digitaux. Tests d'experts, recommandations et deals exclusifs pour optimiser votre productivité en remote.">
    <meta name="keywords" content="télétravail, home office, nomade digital, remote work, équipement bureau domicile, outils productivité télétravail, tech nomade, bureau à distance">
    <meta name="author" content="TechEssentials Pro">
    
    <!-- Open Graph pour réseaux sociaux -->
    <meta property="og:title" content="TechEssentials Pro - Tech pour Télétravail & Nomades">
    <meta property="og:description" content="Les meilleurs équipements tech pour télétravailleurs et nomades digitaux. Tests, comparatifs et recommandations d'experts.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://techessentialspro.com">
    
    <!-- Hreflang pour versions linguistiques -->
    <link rel="alternate" hreflang="fr" href="https://techessentialspro.com/index.php?lang=fr">
    <link rel="alternate" hreflang="en" href="https://techessentialspro.com/index.php?lang=en">
    <link rel="canonical" href="https://techessentialspro.com">

    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header & Navigation */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            background: linear-gradient(45deg, #4facfe, #00f2fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #4facfe;
        }

        .lang-switch {
            display: flex;
            gap: 0.5rem;
        }

        .lang-switch a {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            text-decoration: none;
            color: #666;
            transition: all 0.3s ease;
        }

        .lang-switch a.active {
            background: #4facfe;
            color: white;
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 120px 0 80px;
            text-align: center;
            margin-top: 70px;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .cta-button {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .cta-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        /* Stats Section */
        .stats-section {
            background: linear-gradient(45deg, #4facfe 0%, #00f2fe 100%);
            padding: 60px 0;
            color: white;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .stat-item h3 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .stat-item p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Featured Products */
        .featured-section {
            padding: 80px 0;
            background: #f8fafc;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: #2d3748;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            position: relative;
            overflow: hidden;
            height: 250px;
            background: #f8f9fa;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            transition: transform 0.3s ease;
            padding: 15px;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #ff6b6b;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .product-badge.featured {
            background: #4facfe;
        }

        .product-info {
            padding: 1.5rem;
        }

        .product-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #2d3748;
        }

        .product-price {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .current-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #4facfe;
        }

        .original-price {
            font-size: 1rem;
            color: #a0aec0;
            text-decoration: line-through;
        }

        .discount {
            background: #e53e3e;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .product-description {
            color: #718096;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .product-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            flex: 1;
            text-align: center;
        }

        .btn-primary {
            background: #4facfe;
            color: white;
        }

        .btn-primary:hover {
            background: #2196f3;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: transparent;
            color: #4facfe;
            border: 2px solid #4facfe;
        }

        .btn-secondary:hover {
            background: #4facfe;
            color: white;
        }

        /* Newsletter Section */
        .newsletter {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 60px 0;
            text-align: center;
        }
        
        .newsletter h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #333;
        }
        
        .newsletter p {
            color: #666;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }
        
        .newsletter-form {
            max-width: 500px;
            margin: 2rem auto;
            display: flex;
            gap: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border-radius: 50px;
            overflow: hidden;
            background: white;
        }
        
        .newsletter-input {
            flex: 1;
            padding: 15px 20px;
            border: none;
            font-size: 1rem;
            outline: none;
        }
        
        .newsletter-button {
            padding: 15px 30px;
            background: #667eea;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .newsletter-button:hover {
            background: #5a6fd8;
        }

        /* Footer */
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 3rem 0;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
            color: #667eea;
        }

        .footer-section p,
        .footer-section a {
            color: #ccc;
            text-decoration: none;
            line-height: 1.6;
        }

        .footer-section a:hover {
            color: #667eea;
        }

        .admin-access {
            text-align: center;
            padding: 15px 0;
            border-top: 1px solid #555;
            margin-top: 20px;
        }

        .admin-access a {
            color: #667eea;
            text-decoration: none;
            margin: 0 15px;
            font-size: 0.9rem;
        }

        .admin-access a:hover {
            color: #5a6fd8;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }

            .newsletter-form {
                flex-direction: column;
            }

            .nav-links {
                display: none;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <style>
/* Blog Carousel Section */
.blog-carousel-section {
    padding: 4rem 0;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.section-title {
    text-align: center;
    font-size: 2.5rem;
    margin-bottom: 3rem;
    color: #2c3e50;
    font-weight: 700;
}

.carousel-wrapper {
    position: relative;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 60px;
}

.blog-carousel {
    display: flex;
    gap: 1.5rem;
    overflow: hidden;
    scroll-behavior: smooth;
}

.blog-card {
    min-width: calc(20% - 1.2rem);
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
}

.blog-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.blog-card-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.blog-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.blog-card:hover .blog-card-image img {
    transform: scale(1.1);
}

.blog-card-content {
    padding: 1.5rem;
}

.blog-card-date {
    font-size: 0.85rem;
    color: #667eea;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.blog-card-title {
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 0.8rem;
    color: #2c3e50;
    line-height: 1.4;
    height: 3rem;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.blog-card-excerpt {
    font-size: 0.9rem;
    color: #7f8c8d;
    line-height: 1.6;
    margin-bottom: 1rem;
    height: 4rem;
    overflow: hidden;
}

.blog-card-link {
    color: #667eea;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: color 0.3s ease;
}

.blog-card-link:hover {
    color: #764ba2;
}

/* Carousel Navigation Buttons */
.carousel-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: white;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    font-size: 2rem;
    color: #667eea;
    cursor: pointer;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    z-index: 10;
}

.carousel-btn:hover {
    background: #667eea;
    color: white;
    transform: translateY(-50%) scale(1.1);
}

.carousel-btn.prev {
    left: 0;
}

.carousel-btn.next {
    right: 0;
}

.blog-card-link-wrapper {
    text-decoration: none;
    color: inherit;
    display: block;
    min-width: calc(20% - 1.2rem);
}

.blog-card-cta {
    color: #667eea;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    transition: color 0.3s ease;
}

.blog-card-link-wrapper:hover .blog-card-cta {
    color: #764ba2;
}

/* Responsive */
@media (max-width: 1200px) {
    .blog-card-link-wrapper {
        min-width: calc(33.333% - 1rem);
    }
}

@media (max-width: 768px) {
    .blog-card-link-wrapper {
        min-width: calc(50% - 0.75rem);
    }
}
    
    .carousel-wrapper {
        padding: 0 50px;
    }
    
    .carousel-btn {
        width: 40px;
        height: 40px;
        font-size: 1.5rem;
    }


@media (max-width: 480px) {
    .blog-card-link-wrapper {
        min-width: 100%;
    }
}

</style>
</head>

<body>
   <!-- 2. NOUVEAU HEADER PRINCIPAL UNIFIÉ (pour includes/layouts/header.php) -->

<header class="site-header">
    <div class="header-content">
        <link rel="stylesheet" href="assets/css/header-unified.css">
        <a href="<?= BASE_URL ?>index.php?lang=<?= $lang ?>" class="logo">TechEssentials Pro</a>
        <nav>
            <ul class="nav-menu">
                <li><a href="<?= BASE_URL ?>index.php?lang=<?= $lang ?>" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">🏠 <?= $lang === 'fr' ? 'Accueil' : 'Home' ?></a></li>
                <li><a href="<?= BASE_URL ?>products.php?lang=<?= $lang ?>" class="<?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : '' ?>">📱 <?= $lang === 'fr' ? 'Produits' : 'Products' ?></a></li>
                <li><a href="<?= BASE_URL ?>reviews.php?lang=<?= $lang ?>" class="<?= basename($_SERVER['PHP_SELF']) === 'reviews.php' ? 'active' : '' ?>">⭐ <?= $lang === 'fr' ? 'Tests' : 'Reviews' ?></a></li>
                <li><a href="<?= BASE_URL ?>blog/?lang=<?= $lang ?>" class="<?= strpos($_SERVER['REQUEST_URI'], '/blog/') !== false ? 'active' : '' ?>">📝 Blog</a></li>
                <li><a href="<?= BASE_URL ?>deals.php?lang=<?= $lang ?>" class="<?= basename($_SERVER['PHP_SELF']) === 'deals.php' ? 'active' : '' ?>">💰 <?= $lang === 'fr' ? 'Bons Plans' : 'Deals' ?></a></li>
                <li><a href="<?= BASE_URL ?>contact.php?lang=<?= $lang ?>" class="<?= basename($_SERVER['PHP_SELF']) === 'contact.php' ? 'active' : '' ?>">📞 Contact</a></li>
            </ul>
        </nav>
        
        <div class="lang-switch">
            <a href="<?= $_SERVER['PHP_SELF'] ?>?lang=fr<?= isset($_GET) && count($_GET) > 1 ? '&' . http_build_query(array_diff_key($_GET, ['lang' => ''])) : '' ?>" 
               class="<?= $lang === 'fr' ? 'active' : '' ?>">FR</a>
            <a href="<?= $_SERVER['PHP_SELF'] ?>?lang=en<?= isset($_GET) && count($_GET) > 1 ? '&' . http_build_query(array_diff_key($_GET, ['lang' => ''])) : '' ?>" 
               class="<?= $lang === 'en' ? 'active' : '' ?>">EN</a>
        </div>
    </div>
</header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1><?= $translations['hero_title'] ?></h1>
            <p><?= $translations['hero_subtitle'] ?></p>
            <a href="/products.php" class="cta-button"><?= $translations['view_all_products'] ?></a>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <h3>150+</h3>
                    <p><?= $translations['stats_products'] ?></p>
                </div>
                <div class="stat-item">
                    <h3>1,200+</h3>
                    <p><?= $translations['stats_reviews'] ?></p>
                </div>
                <div class="stat-item">
                    <h3>50+</h3>
                    <p><?= $translations['stats_partners'] ?></p>
                </div>
                <div class="stat-item">
                    <h3>10K+</h3>
                    <p><?= $translations['stats_users'] ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-section">
        <div class="container">
            <h2 class="section-title"><?= $translations['featured_products'] ?></h2>
            
            <div class="products-grid">
                <?php if (!empty($featured_products)): ?>
                    <?php foreach ($featured_products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="assets/images/products/<?= htmlspecialchars($product['image']) ?>" 
                                     alt="<?= htmlspecialchars($product['name'] ?? 'Produit') ?>">
                                
                                <?php if (isset($product['is_featured']) && $product['is_featured']): ?>
                                    <div class="product-badge featured">Featured</div>
                                <?php endif; ?>
                                
                                <?php if (isset($product['discount_percentage']) && $product['discount_percentage'] > 0): ?>
                                    <div class="product-badge">-<?= $product['discount_percentage'] ?>%</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info">
                                <h3 class="product-title"><?= htmlspecialchars($product['name'] ?? 'Produit sans nom') ?></h3>
                                
                                <div class="product-price">
                                    <span class="current-price">€<?= number_format($product['price'] ?? $product['current_price'] ?? 0, 2) ?></span>
                                    
                                    <?php if (isset($product['original_price']) && $product['original_price'] > ($product['price'] ?? $product['current_price'] ?? 0)): ?>
                                        <span class="original-price">€<?= number_format($product['original_price'], 2) ?></span>
                                        <span class="discount">-<?= $product['discount_percentage'] ?? 0 ?>%</span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="product-description">
                                    <?php 
                                    $description = $product['description'] ?? $product['short_description'] ?? 'Produit de qualité professionnelle';
                                    echo htmlspecialchars(substr($description, 0, 100)) . '...';
                                    ?>
                                </p>
                                
                                <div class="product-actions">
                                    <a href="product-detail.php?id=<?= htmlspecialchars($product['id'] ?? '#') ?>&lang=<?= $lang ?>"
                                     
                                          


                                       class="btn btn-secondary"><?= $translations['learn_more'] ?></a>
                                    
                                    <a href="<?= htmlspecialchars($product['amazon_url'] ?? $product['affiliate_url'] ?? '#'); ?>" 
                                       class="btn btn-primary" target="_blank"><?= $translations['buy_now'] ?></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; grid-column: 1 / -1;">
                        <?= $lang == 'fr' ? 'Aucun produit featured trouvé.' : 'No featured products found.' ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <div style="text-align: center;">
                <a href="/products.php" class="cta-button"><?= $translations['view_all_products'] ?></a>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
<?php 
// Include newsletter et footer

// Debug temporaire
error_log("Newsletter.php loaded with GET: " . print_r($_GET, true));

//Section Blog Articles Carousel 

// Récupérer les 10 derniers articles publiés
try {
    $db = getDB('main');
    $stmt = $db->prepare("
        SELECT id, title, slug, excerpt, featured_image, created_at 
        FROM blog_articles 
        WHERE status = 'published' 
        ORDER BY published_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $articles = [];
}
?>

<?php if (!empty($articles)): ?>
<section class="blog-carousel-section">
    <div class="container">
        <h2 class="section-title">
            <?= $lang === 'fr' ? 'Derniers Articles' : 'Latest Articles' ?>
        </h2>
        
        <div class="carousel-wrapper">
            <button class="carousel-btn prev" onclick="moveCarousel(-1)">‹</button>
            
            <div class="blog-carousel" id="blogCarousel">
               <?php foreach ($articles as $article): ?>
    <a href="blog/article.php?slug=<?= $article['slug'] ?>&lang=<?= $lang ?>" 
       class="blog-card-link-wrapper">
        <div class="blog-card">
            <div class="blog-card-image">
                <?php if ($article['featured_image']): ?>
                    <img src="<?= htmlspecialchars($article['featured_image']) ?>" 
                         alt="<?= htmlspecialchars($article['title']) ?>">
                <?php else: ?>
                    <img src="assets/images/blog-placeholder.jpg" alt="Article image">
                <?php endif; ?>
            </div>
            <div class="blog-card-content">
                <div class="blog-card-date">
                    <?= date('d M Y', strtotime($article['created_at'])) ?>
                </div>
                <h3 class="blog-card-title">
                    <?= htmlspecialchars($article['title']) ?>
                </h3>
                <p class="blog-card-excerpt">
                    <?= htmlspecialchars(substr($article['excerpt'], 0, 100)) ?>...
                </p>
                <span class="blog-card-cta">
                    <?= $lang === 'fr' ? 'Lire la suite' : 'Read more' ?> →
                </span>
            </div>
        </div>
    </a>
<?php endforeach; ?>
            </div>
            
            <button class="carousel-btn next" onclick="moveCarousel(1)">›</button>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
// Carrousel Blog - Défilement automatique
let carouselPosition = 0;
let autoScrollInterval;

function moveCarousel(direction) {
    const carousel = document.getElementById('blogCarousel');
    if (!carousel) return;
    
    const cards = carousel.querySelectorAll('.blog-card');
    const cardWidth = cards[0].offsetWidth + 24; // 24px = gap
    
    carouselPosition += direction * cardWidth;
    
    const maxScroll = -(cardWidth * (cards.length - 5));
    
    if (carouselPosition > 0) {
        carouselPosition = maxScroll;
    } else if (carouselPosition < maxScroll) {
        carouselPosition = 0;
    }
    
    carousel.style.transform = `translateX(${carouselPosition}px)`;
}

// Défilement automatique toutes les 5 secondes
function startAutoScroll() {
    autoScrollInterval = setInterval(() => {
        moveCarousel(-1);
    }, 5000);
}

// Pause au survol
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.querySelector('.blog-carousel-section');
    if (carousel) {
        startAutoScroll();
        
        carousel.addEventListener('mouseenter', () => {
            clearInterval(autoScrollInterval);
        });
        
        carousel.addEventListener('mouseleave', () => {
            startAutoScroll();
        });
    }
});
</script>
<?php
include 'includes/layouts/newsletter.php';
include 'includes/layouts/footer.php';
?>

    <!-- Footer -->
</body>
</html>