<?php
/**
 * TechEssentials Pro - Page Produits
 * Catalogue complet avec tes 20 produits réels
 */

// Configuration
define('TECHESSENTIALS_PRO', true);
session_start();

// Gestion de la langue
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'fr';
if (in_array($lang, ['fr', 'en'])) {
    $_SESSION['lang'] = $lang;
}

// Variables pour le header
$page_title = $lang === 'fr' ? 'Produits Tech Professionnels - TechEssentials Pro' : 'Professional Tech Products - TechEssentials Pro';
$page_description = $lang === 'fr' ? 'Découvrez notre sélection de 20 produits tech essentiels pour professionnels. Moniteurs, claviers, souris, casques et plus.' : 'Discover our selection of 20 essential tech products for professionals. Monitors, keyboards, mice, headphones and more.';

// Tous tes 20 produits avec vrais noms d'images
$all_products = [
    [
        'id' => 'anker-737',
        'image' => 'anker-737.jpg',
        'category' => 'chargers',
        'name' => $lang === 'fr' ? 'Anker 737 PowerCore 24K' : 'Anker 737 PowerCore 24K',
        'description' => $lang === 'fr' ? 'Batterie externe haute capacité 24000mAh avec sortie 140W pour ordinateurs portables et appareils.' : 'High-capacity 24,000mAh power bank with 140W output for laptops and devices.',
        'price' => 139.99,
        'original_price' => 159.99,
        'discount_percentage' => 13,
        'rating' => 4.8,
        'amazon_url' => 'https://amazon.fr/anker-737'
    ],
    [
        'id' => 'anker-dock',
        'image' => 'anker-dock.jpg',
        'category' => 'hubs',
        'name' => $lang === 'fr' ? 'Anker PowerExpand+ Hub USB-C' : 'Anker PowerExpand+ USB-C Hub',
        'description' => $lang === 'fr' ? 'Hub USB-C 7-en-1 avec HDMI 4K, ports USB 3.0 et Power Delivery 85W.' : '7-in-1 USB-C hub with 4K HDMI, USB 3.0 ports and 85W Power Delivery.',
        'price' => 79.99,
        'original_price' => 99.99,
        'discount_percentage' => 20,
        'rating' => 4.6,
        'amazon_url' => 'https://amazon.fr/anker-dock'
    ],
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
        'amazon_url' => 'https://amazon.fr/anker-nebula'
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
        'amazon_url' => 'https://amazon.fr/anker-powercore'
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
        'amazon_url' => 'https://amazon.fr/anker-soundcore-q30'
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
        'amazon_url' => 'https://amazon.fr/asus-proart'
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
        'amazon_url' => 'https://amazon.fr/aukey-hub'
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
        'amazon_url' => 'https://amazon.fr/benq-screenbar'
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
        'amazon_url' => 'https://amazon.fr/blue-yeti-nano'
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
        'amazon_url' => 'https://amazon.fr/bose-qc45'
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
        'amazon_url' => 'https://amazon.fr/dell-ultrasharp'
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
        'amazon_url' => 'https://amazon.fr/herman-miller-sayl'
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
        'amazon_url' => 'https://amazon.fr/logitech-brio'
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
        'amazon_url' => 'https://amazon.fr/logitech-c920'
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
        'amazon_url' => 'https://amazon.fr/logitech-ergo-k860'
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
        'amazon_url' => 'https://amazon.fr/logitech-mx-keys'
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
        'amazon_url' => 'https://amazon.fr/logitech-mx-master'
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
        'amazon_url' => 'https://amazon.fr/logitech-streamcam'
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
        'amazon_url' => 'https://amazon.fr/logitech-zone-wireless'
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
        'amazon_url' => 'https://amazon.fr/seagate-2tb'
    ]
];

// Filtrage par catégorie
$selected_category = $_GET['category'] ?? 'all';
$products_to_show = $selected_category === 'all' ? $all_products : array_filter($all_products, function($product) use ($selected_category) {
    return $product['category'] === $selected_category;
});

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$products_per_page = 12;
$total_products = count($products_to_show);
$total_pages = ceil($total_products / $products_per_page);
$offset = ($page - 1) * $products_per_page;
$current_products = array_slice($products_to_show, $offset, $products_per_page);

// Categories pour le filtre
$categories = [
    'all' => $lang === 'fr' ? 'Toutes' : 'All',
    'monitors' => $lang === 'fr' ? 'Moniteurs' : 'Monitors',
    'keyboards' => $lang === 'fr' ? 'Claviers' : 'Keyboards',
    'mice' => $lang === 'fr' ? 'Souris' : 'Mice',
    'headphones' => $lang === 'fr' ? 'Casques' : 'Headphones',
    'webcams' => $lang === 'fr' ? 'Webcams' : 'Webcams',
    'chargers' => $lang === 'fr' ? 'Chargeurs' : 'Chargers',
    'hubs' => $lang === 'fr' ? 'Hubs' : 'Hubs',
    'chairs' => $lang === 'fr' ? 'Chaises' : 'Chairs',
    'lighting' => $lang === 'fr' ? 'Éclairage' : 'Lighting',
    'microphones' => $lang === 'fr' ? 'Micros' : 'Microphones',
    'projectors' => $lang === 'fr' ? 'Projecteurs' : 'Projectors',
    'storage' => $lang === 'fr' ? 'Stockage' : 'Storage'
];

// Include header
include 'includes/layouts/header.php';
?>

<style>
/* Styles spécifiques à la page produits */
.products-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 0 60px;
    text-align: center;
}

.products-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.products-hero p {
    font-size: 1.2rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
}

.filters-section {
    background: #f8f9fa;
    padding: 30px 0;
    border-bottom: 1px solid #e9ecef;
}

.filters-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.category-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.filter-btn {
    padding: 8px 16px;
    border: 1px solid #dee2e6;
    background: white;
    color: #6c757d;
    text-decoration: none;
    border-radius: 20px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.filter-btn:hover,
.filter-btn.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.products-count {
    color: #6c757d;
    font-size: 0.9rem;
}

.products-section {
    padding: 60px 0;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.product-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
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
    height: 220px;
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
    background: #e74c3c;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.product-info {
    padding: 1.5rem;
}

.product-category {
    font-size: 0.8rem;
    color: #667eea;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.product-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #2d3748;
    line-height: 1.3;
}

.product-description {
    color: #718096;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    line-height: 1.4;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.stars {
    color: #ffd700;
    font-size: 0.9rem;
}

.rating-text {
    font-size: 0.8rem;
    color: #6c757d;
}

.product-price {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.current-price {
    font-size: 1.2rem;
    font-weight: 700;
    color: #667eea;
}

.original-price {
    font-size: 0.9rem;
    color: #a0aec0;
    text-decoration: line-through;
}

.discount {
    background: #e53e3e;
    color: white;
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: 600;
}

.product-actions {
    display: flex;
    gap: 0.5rem;
}

.btn {
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    cursor: pointer;
    flex: 1;
    text-align: center;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5a6fd8;
    transform: translateY(-1px);
}

.btn-secondary {
    background: transparent;
    color: #667eea;
    border: 1px solid #667eea;
}

.btn-secondary:hover {
    background: #667eea;
    color: white;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    margin-top: 3rem;
}

.pagination a,
.pagination span {
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    color: #6c757d;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.pagination a:hover,
.pagination .current {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.pagination .disabled {
    opacity: 0.5;
    pointer-events: none;
}

/* Responsive */
@media (max-width: 768px) {
    .filters-container {
        flex-direction: column;
        align-items: stretch;
    }
    
    .category-filters {
        justify-content: center;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    
    .products-hero h1 {
        font-size: 2rem;
    }
    
    .product-actions {
        flex-direction: column;
    }
}
</style>

<!-- Hero Section -->
<section class="products-hero">
    <div class="container">
        <h1><?= $lang === 'fr' ? 'Catalogue Produits Tech' : 'Tech Products Catalog' ?></h1>
        <p><?= $lang === 'fr' ? 'Découvrez notre sélection de 20 produits tech essentiels sélectionnés pour les professionnels exigeants' : 'Discover our selection of 20 essential tech products curated for demanding professionals' ?></p>
    </div>
</section>

<!-- Filters Section -->
<section class="filters-section">
    <div class="container">
        <div class="filters-container">
            <div class="category-filters">
                <?php foreach ($categories as $cat_key => $cat_name): ?>
                    <a href="?category=<?= $cat_key ?>&lang=<?= $lang ?>" 
                       class="filter-btn <?= $selected_category === $cat_key ? 'active' : '' ?>">
                        <?= $cat_name ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="products-count">
                <?= count($current_products) ?> <?= $lang === 'fr' ? 'produits' : 'products' ?>
            </div>
        </div>
    </div>
</section>

<!-- Products Section -->
<section class="products-section">
    <div class="container">
        <div class="products-grid">
            <?php foreach ($current_products as $product): ?>
                <div class="product-card">
                    <div class="product-image" onclick="openImageZoom('assets/images/products/<?= $product['image'] ?>', '<?= htmlspecialchars($product['name']) ?>')">
                        <img src="assets/images/products/<?= htmlspecialchars($product['image']) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>">
                        
                        <div class="zoom-indicator">🔍</div>
                        
                        <?php if ($product['discount_percentage'] > 0): ?>
                            <div class="product-badge">-<?= $product['discount_percentage'] ?>%</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-info">
                        <div class="product-category"><?= $categories[$product['category']] ?></div>
                        <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                        
                        <div class="product-rating">
                            <div class="stars">
                                <?php
                                $rating = $product['rating'];
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating ? '★' : '☆';
                                }
                                ?>
                            </div>
                            <span class="rating-text"><?= $product['rating'] ?>/5</span>
                        </div>
                        
                        <div class="product-price">
                            <span class="current-price">€<?= number_format($product['price'], 2) ?></span>
                            
                            <?php if ($product['original_price'] > $product['price']): ?>
                                <span class="original-price">€<?= number_format($product['original_price'], 2) ?></span>
                                <span class="discount">-<?= $product['discount_percentage'] ?>%</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-actions">
                            <a href="product-detail.php?id=<?= $product['id'] ?>&lang=<?= $lang ?>" 
                               class="btn btn-secondary">
                                <?= $lang === 'fr' ? 'Détails' : 'Details' ?>
                            </a>
                            
                            <button onclick="openProductPopup('<?= $product['id'] ?>')" 
                                    class="btn btn-secondary" style="border: 1px solid #667eea;">
                                <?= $lang === 'fr' ? 'Aperçu' : 'Quick View' ?>
                            </button>
                            
                            <a href="<?= htmlspecialchars($product['amazon_url']) ?>" 
                               class="btn btn-primary" target="_blank" rel="noopener">
                                <?= $lang === 'fr' ? 'Acheter' : 'Buy Now' ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?category=<?= $selected_category ?>&page=<?= $page - 1 ?>&lang=<?= $lang ?>">&laquo; <?= $lang === 'fr' ? 'Précédent' : 'Previous' ?></a>
                <?php else: ?>
                    <span class="disabled">&laquo; <?= $lang === 'fr' ? 'Précédent' : 'Previous' ?></span>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?category=<?= $selected_category ?>&page=<?= $i ?>&lang=<?= $lang ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?category=<?= $selected_category ?>&page=<?= $page + 1 ?>&lang=<?= $lang ?>"><?= $lang === 'fr' ? 'Suivant' : 'Next' ?> &raquo;</a>
                <?php else: ?>
                    <span class="disabled"><?= $lang === 'fr' ? 'Suivant' : 'Next' ?> &raquo;</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Product Popup Modal -->
<div class="product-popup-overlay" id="productPopup">
    <div class="product-popup">
        <div class="popup-header">
            <div class="popup-title" id="popupTitle"></div>
            <button class="popup-close" onclick="closeProductPopup()">&times;</button>
        </div>
        <div class="popup-content">
            <div class="popup-image">
                <img id="popupImage" src="" alt="" onclick="openImageZoomFromPopup()">
            </div>
            <div class="popup-info">
                <div class="popup-category" id="popupCategory"></div>
                <h3 id="popupName"></h3>
                <div class="popup-rating">
                    <div class="stars" id="popupStars"></div>
                    <span class="rating-text" id="popupRating"></span>
                </div>
                <div class="popup-description" id="popupDescription"></div>
                <div class="popup-price" id="popupPrice"></div>
                <div class="popup-actions">
                    <a id="popupDetailBtn" href="#" class="btn btn-secondary">
                        <?= $lang === 'fr' ? 'Voir les détails' : 'View Details' ?>
                    </a>
                    <a id="popupBuyBtn" href="#" class="btn btn-primary" target="_blank">
                        <?= $lang === 'fr' ? 'Acheter maintenant' : 'Buy Now' ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Zoom Overlay -->
<div class="image-zoom-overlay" id="imageZoomOverlay" onclick="closeImageZoom()">
    <img id="zoomImage" src="" alt="">
</div>

<script>
// Données des produits pour JavaScript
const productsData = <?= json_encode($all_products) ?>;
const currentLang = '<?= $lang ?>';

// Fonction pour ouvrir le popup produit
function openProductPopup(productId) {
    const product = productsData.find(p => p.id === productId);
    if (!product) return;
    
    // Remplir les données du popup
    document.getElementById('popupTitle').textContent = product.name;
    document.getElementById('popupImage').src = 'assets/images/products/' + product.image;
    document.getElementById('popupImage').alt = product.name;
    document.getElementById('popupCategory').textContent = product.category.charAt(0).toUpperCase() + product.category.slice(1);
    document.getElementById('popupName').textContent = product.name;
    document.getElementById('popupDescription').textContent = product.description;
    
    // Rating stars
    const stars = '★'.repeat(Math.floor(product.rating)) + '☆'.repeat(5 - Math.floor(product.rating));
    document.getElementById('popupStars').textContent = stars;
    document.getElementById('popupRating').textContent = product.rating + '/5';
    
    // Prix
    let priceHTML = `<span class="current-price">€${product.price.toFixed(2)}</span>`;
    if (product.original_price > product.price) {
        priceHTML += ` <span class="original-price">€${product.original_price.toFixed(2)}</span>`;
        priceHTML += ` <span class="discount">-${product.discount_percentage}%</span>`;
    }
    document.getElementById('popupPrice').innerHTML = priceHTML;
    
    // Liens
    document.getElementById('popupDetailBtn').href = `product-detail.php?id=${product.id}&lang=${currentLang}`;
    document.getElementById('popupBuyBtn').href = product.amazon_url;
    
    // Afficher le popup
    document.getElementById('productPopup').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Fonction pour fermer le popup produit
function closeProductPopup() {
    document.getElementById('productPopup').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Fonction pour ouvrir le zoom d'image depuis la grille
function openImageZoom(imageSrc, productName) {
    document.getElementById('zoomImage').src = imageSrc;
    document.getElementById('zoomImage').alt = productName;
    document.getElementById('imageZoomOverlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Fonction pour ouvrir le zoom d'image depuis le popup
function openImageZoomFromPopup() {
    const popupImage = document.getElementById('popupImage');
    openImageZoom(popupImage.src, popupImage.alt);
}

// Fonction pour fermer le zoom d'image
function closeImageZoom() {
    document.getElementById('imageZoomOverlay').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Gestion des événements clavier
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeProductPopup();
        closeImageZoom();
    }
});

// Fermer le popup en cliquant à l'extérieur
document.getElementById('productPopup').addEventListener('click', function(e) {
    if (e.target === this) {
        closeProductPopup();
    }
});

// Empêcher la fermeture en cliquant sur le contenu du popup
document.querySelector('.product-popup').addEventListener('click', function(e) {
    e.stopPropagation();
});
</script>

<?php 
// Include newsletter et footer
include 'includes/layouts/newsletter.php';
include 'includes/layouts/footer.php';
?>