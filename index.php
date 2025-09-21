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

echo "BASE_URL = " . BASE_URL . "<br>";

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
        'id' => 'asus-proart-monitor',
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
        'id' => 'logitech-mx-master',
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
        'id' => 'dell-ultrasharp',
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
$t = [
    'fr' => [
        'site_title' => 'TechEssentials Pro',
        'tagline' => 'Les meilleurs produits tech pour les professionnels',
        'hero_title' => 'Découvrez les Essentiels Tech',
        'hero_subtitle' => 'Sélection premium pour professionnels exigeants',
        'featured_products' => 'Produits Recommandés',
        'view_all_products' => 'Voir tous les produits',
        'buy_now' => 'Acheter maintenant',
        'learn_more' => 'En savoir plus',
        'newsletter_title' => 'Restez informé',
        'newsletter_subtitle' => 'Recevez nos dernières recommandations et bons plans tech',
        'newsletter_placeholder' => 'Votre email',
        'newsletter_button' => "S'abonner",
        'stats_products' => 'Produits testés',
        'stats_reviews' => 'Avis vérifiés',
        'stats_partners' => 'Partenaires',
        'stats_users' => 'Utilisateurs actifs'
    ],
    'en' => [
        'site_title' => 'TechEssentials Pro',
        'tagline' => 'Best tech products for professionals',
        'hero_title' => 'Discover Tech Essentials',
        'hero_subtitle' => 'Premium selection for demanding professionals',
        'featured_products' => 'Featured Products',
        'view_all_products' => 'View all products',
        'buy_now' => 'Buy now',
        'learn_more' => 'Learn more',
        'newsletter_title' => 'Stay Updated',
        'newsletter_subtitle' => 'Get our latest recommendations and tech deals',
        'newsletter_placeholder' => 'Your email',
        'newsletter_button' => 'Subscribe',
        'stats_products' => 'Products tested',
        'stats_reviews' => 'Verified reviews',
        'stats_partners' => 'Partners',
        'stats_users' => 'Active users'
    ]
];

$translations = $t[$lang];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $translations['site_title'] ?> - <?= $translations['tagline'] ?></title>
    <meta name="description" content="<?= $translations['tagline'] ?>">
    
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
</head>

<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav container">
            <div class="logo"><?= $translations['site_title'] ?></div>
            
          <ul class="nav-links">
               <li><a href="<?= BASE_URL ?>index.php?lang=<?= $lang ?>"><?= ucfirst($lang == 'fr' ? 'Accueil' : 'Home') ?></a></li>
               <li><a href="<?= BASE_URL ?>products.php?lang=<?= $lang ?>"><?= $lang == 'fr' ? 'Produits' : 'Products' ?></a></li>
               <li><a href="<?= BASE_URL ?>reviews.php?lang=<?= $lang ?>"><?= $lang == 'fr' ? 'Tests' : 'Reviews' ?></a></li>
               <li><a href="<?= BASE_URL ?>blog.php?lang=<?= $lang ?>"><?= $lang == 'fr' ? 'Blog' : 'Blog' ?></a></li>
               <li><a href="<?= BASE_URL ?>contact.php?lang=<?= $lang ?>"><?= $lang == 'fr' ? 'Contact' : 'Contact' ?></a></li>
          </ul>

            <div class="lang-switch">
                <a href="?lang=fr" class="<?= $lang === 'fr' ? 'active' : '' ?>">FR</a>
                <a href="?lang=en" class="<?= $lang === 'en' ? 'active' : '' ?>">EN</a>
            </div>
        </nav>
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
                                    <a href="/product-detail.php?slug=<?= htmlspecialchars($product['slug'] ?? $product['id'] ?? '#') ?>" 
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
include 'includes/layouts/newsletter.php';
include 'includes/layouts/footer.php';
?>

    <!-- Footer -->
</body>
</html>