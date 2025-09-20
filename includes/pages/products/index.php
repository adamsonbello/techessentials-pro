<?php
/**
 * TechEssentials Pro V2.0 - Products Catalog
 * Architecture MVC - Page Products
 */

// Empêcher l'accès direct
if (!defined('TECHESSENTIALS_PRO')) {
    die('Direct access not allowed');
}

// Gestion pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$products_per_page = 20;
$offset = ($page - 1) * $products_per_page;

// Données des 20 produits avec vrais noms de fichiers
$all_products = [
    [
        'id' => 'anker-737-charger',
        'slug' => 'anker-737-powercore-24k',
        'images' => [
            'assets/images/products/anker-737.jpg'
        ],
        'badge' => 'BESTSELLER',
        'name' => [
            'en' => 'Anker 737 PowerCore 24K Power Bank',
            'fr' => 'Batterie Externe Anker 737 PowerCore 24K'
        ],
        'description' => [
            'en' => 'High-capacity 24,000mAh power bank with 140W output for laptops, tablets and phones. Fast charging with digital display.',
            'fr' => 'Batterie externe haute capacité 24 000mAh avec sortie 140W pour portables, tablettes et téléphones. Charge rapide avec écran numérique.'
        ],
        'features' => [
            'en' => ['24,000mAh capacity', '140W total output', 'Digital display', 'Smart temperature control'],
            'fr' => ['Capacité 24 000mAh', 'Sortie totale 140W', 'Écran numérique', 'Contrôle température intelligent']
        ],
        'specs' => [
            'en' => ['Capacity: 24,000mAh / 86.4Wh', 'USB-C: 140W max', 'USB-A: 18W max', 'Dimensions: 156 × 55 × 50mm'],
            'fr' => ['Capacité: 24 000mAh / 86.4Wh', 'USB-C: 140W max', 'USB-A: 18W max', 'Dimensions: 156 × 55 × 50mm']
        ],
        'price' => '€149.99',
        'price_old' => '€199.99',
        'rating' => 4.8,
        'reviews_count' => 2847,
        'availability' => 'in_stock',
        'category' => 'accessories',
        'vendors' => [
            'amazon' => 'https://amazon.fr/anker-737',
            'fnac' => 'https://fnac.com/anker-737',
            'bestbuy' => 'https://bestbuy.com/anker-737'
        ]
    ],
    [
        'id' => 'anker-dock-hub',
        'slug' => 'anker-powerexpand-elite-13in1',
        'images' => [
            'assets/images/products/anker-dock.jpg'
        ],
        'badge' => 'PREMIUM',
        'name' => [
            'en' => 'Anker PowerExpand Elite 13-in-1 Dock',
            'fr' => 'Station Anker PowerExpand Elite 13-en-1'
        ],
        'description' => [
            'en' => 'Ultimate 13-in-1 docking station with dual 4K displays, high-speed charging, and comprehensive connectivity.',
            'fr' => 'Station d\'accueil ultime 13-en-1 avec double écrans 4K, charge haute vitesse et connectivité complète.'
        ],
        'features' => [
            'en' => ['Dual 4K display support', '85W laptop charging', '13 ports total', 'Gigabit Ethernet'],
            'fr' => ['Support double 4K', 'Charge portable 85W', '13 ports au total', 'Ethernet Gigabit']
        ],
        'specs' => [
            'en' => ['HDMI: Dual 4K@60Hz', 'USB-C PD: 85W', 'USB 3.0: 4 ports', 'Ethernet: 1Gbps'],
            'fr' => ['HDMI: Double 4K@60Hz', 'USB-C PD: 85W', 'USB 3.0: 4 ports', 'Ethernet: 1Gbps']
        ],
        'price' => '€199.99',
        'price_old' => '€249.99',
        'rating' => 4.7,
        'reviews_count' => 1923,
        'availability' => 'in_stock',
        'category' => 'accessories',
        'vendors' => [
            'amazon' => 'https://amazon.fr/anker-dock',
            'fnac' => 'https://fnac.com/anker-dock',
            'bestbuy' => 'https://bestbuy.com/anker-dock'
        ]
    ],
    [
        'id' => 'asus-proart-monitor',
        'slug' => 'asus-proart-pa248qv-24-professional',
        'images' => [
            'assets/images/products/asus-proart-pa248QV.jpg'
        ],
        'badge' => 'PRO',
        'name' => [
            'en' => 'ASUS ProArt PA248QV 24" Professional Monitor',
            'fr' => 'Moniteur Professionnel ASUS ProArt PA248QV 24"'
        ],
        'description' => [
            'en' => 'Professional 24" monitor with 100% sRGB color space, factory calibration, and ergonomic design for creators.',
            'fr' => 'Moniteur professionnel 24" avec espace colorimétrique 100% sRGB, calibrage usine et design ergonomique pour créateurs.'
        ],
        'features' => [
            'en' => ['100% sRGB coverage', 'Factory pre-calibrated', 'USB-C connectivity', 'Height adjustable stand'],
            'fr' => ['Couverture 100% sRGB', 'Pré-calibré en usine', 'Connectivité USB-C', 'Support réglable hauteur']
        ],
        'specs' => [
            'en' => ['Size: 24.1" (61.2cm)', 'Resolution: 1920 x 1200 WUXGA', 'Refresh rate: 75Hz', 'Response time: 5ms'],
            'fr' => ['Taille: 24.1" (61.2cm)', 'Résolution: 1920 x 1200 WUXGA', 'Taux rafraîchissement: 75Hz', 'Temps réponse: 5ms']
        ],
        'price' => '€349.99',
        'price_old' => '€449.99',
        'rating' => 4.7,
        'reviews_count' => 1834,
        'availability' => 'in_stock',
        'category' => 'displays',
        'vendors' => [
            'amazon' => 'https://amazon.fr/asus-proart',
            'fnac' => 'https://fnac.com/asus-proart',
            'bestbuy' => 'https://bestbuy.com/asus-proart'
        ]
    ],
    [
        'id' => 'dell-ultrasharp-u2720q',
        'slug' => 'dell-ultrasharp-u2720q-27-4k',
        'images' => [
            'assets/images/products/dell-ultrasharp-u2720q.jpg'
        ],
        'badge' => 'PROFESSIONAL',
        'name' => [
            'en' => 'Dell UltraSharp U2720Q 27" 4K Monitor',
            'fr' => 'Moniteur 4K Dell UltraSharp U2720Q 27"'
        ],
        'description' => [
            'en' => 'Professional 27" 4K monitor with 99% sRGB, USB-C connectivity with 90W power delivery, and premium build quality.',
            'fr' => 'Moniteur 4K professionnel 27" avec 99% sRGB, connectivité USB-C avec power delivery 90W et qualité construction premium.'
        ],
        'features' => [
            'en' => ['27" 4K IPS display', '99% sRGB coverage', 'USB-C with 90W PD', 'Height adjustable stand'],
            'fr' => ['Écran IPS 4K 27"', 'Couverture 99% sRGB', 'USB-C avec PD 90W', 'Support réglable hauteur']
        ],
        'specs' => [
            'en' => ['Resolution: 3840x2160 4K', 'Refresh rate: 60Hz', 'Response time: 5ms', 'Brightness: 350 cd/m²'],
            'fr' => ['Résolution: 3840x2160 4K', 'Taux rafraîchissement: 60Hz', 'Temps réponse: 5ms', 'Luminosité: 350 cd/m²']
        ],
        'price' => '€649.99',
        'price_old' => '€749.99',
        'rating' => 4.8,
        'reviews_count' => 1276,
        'availability' => 'in_stock',
        'category' => 'displays',
        'vendors' => [
            'amazon' => 'https://amazon.fr/dell-u2720q',
            'fnac' => 'https://fnac.com/dell-monitor',
            'bestbuy' => 'https://bestbuy.com/dell-u2720q'
        ]
    ],
    [
        'id' => 'herman-miller-sayl',
        'slug' => 'herman-miller-sayl-ergonomic-chair',
        'images' => [
            'assets/images/products/herman-miller-sayl.jpg'
        ],
        'badge' => 'ERGONOMIC',
        'name' => [
            'en' => 'Herman Miller Sayl Ergonomic Office Chair',
            'fr' => 'Chaise Bureau Ergonomique Herman Miller Sayl'
        ],
        'description' => [
            'en' => 'Innovative ergonomic chair with suspension back design, modern aesthetics, and exceptional support for all-day comfort.',
            'fr' => 'Chaise ergonomique innovante avec design dossier suspension, esthétique moderne et support exceptionnel pour confort toute la journée.'
        ],
        'features' => [
            'en' => ['Suspension back design', 'PostureFit sacral support', '12-year warranty', 'Adjustable arms'],
            'fr' => ['Design dossier suspension', 'Support sacré PostureFit', 'Garantie 12 ans', 'Bras ajustables']
        ],
        'specs' => [
            'en' => ['Weight capacity: 350 lbs', 'Seat height: 15.75"-20.5"', 'Width: 27"', 'Depth: 27"'],
            'fr' => ['Capacité poids: 159kg', 'Hauteur siège: 40-52cm', 'Largeur: 68cm', 'Profondeur: 68cm']
        ],
        'price' => '€695.00',
        'price_old' => '€895.00',
        'rating' => 4.7,
        'reviews_count' => 892,
        'availability' => 'in_stock',
        'category' => 'accessories',
        'vendors' => [
            'amazon' => 'https://amazon.fr/herman-miller-sayl',
            'fnac' => 'https://fnac.com/herman-miller-sayl',
            'bestbuy' => 'https://bestbuy.com/herman-miller-sayl'
        ]
    ]
    // Ajouter les 15 autres produits...
];

// Pagination
$total_products = count($all_products);
$total_pages = ceil($total_products / $products_per_page);
$products = array_slice($all_products, $offset, $products_per_page);

// Traductions pour cette page
$page_translations = [
    'en' => [
        'page_title' => 'Products Catalog',
        'page_subtitle' => 'Expert-curated tech products for modern remote workers',
        'filter_all' => 'All Products',
        'filter_accessories' => 'Accessories',
        'filter_audio' => 'Audio',
        'filter_displays' => 'Displays',
        'filter_input' => 'Input Devices',
        'view_details' => 'View Details',
        'read_review' => 'Read Review',
        'in_stock' => 'In Stock',
        'limited_stock' => 'Limited Stock',
        'out_of_stock' => 'Out of Stock',
        'reviews' => 'reviews',
        'specifications' => 'Specifications',
        'features' => 'Key Features',
        'close' => 'Close',
        'showing' => 'Showing',
        'products' => 'products',
        'of' => 'of',
        'previous' => 'Previous',
        'next' => 'Next'
    ],
    'fr' => [
        'page_title' => 'Catalogue Produits',
        'page_subtitle' => 'Produits tech sélectionnés par experts pour télétravailleurs modernes',
        'filter_all' => 'Tous Produits',
        'filter_accessories' => 'Accessoires',
        'filter_audio' => 'Audio',
        'filter_displays' => 'Écrans',
        'filter_input' => 'Périphériques',
        'view_details' => 'Voir Détails',
        'read_review' => 'Lire Test',
        'in_stock' => 'En Stock',
        'limited_stock' => 'Stock Limité',
        'out_of_stock' => 'Rupture Stock',
        'reviews' => 'avis',
        'specifications' => 'Spécifications',
        'features' => 'Caractéristiques',
        'close' => 'Fermer',
        'showing' => 'Affichage',
        'products' => 'produits',
        'of' => 'sur',
        'previous' => 'Précédent',
        'next' => 'Suivant'
    ]
];

// Utiliser la langue actuelle de ton système
$current_lang = Language::getInstance()->getCurrentLanguage();
$t = $page_translations[$current_lang];

// Données pour le template
$data = [
    'meta_title' => $t['page_title'] . ' - TechEssentials Pro',
    'meta_description' => 'Browse our curated collection of tech products for remote workers. Expert reviews, detailed specs, and best prices.',
    'meta_keywords' => 'tech products, remote work accessories, productivity tools',
    'page_title' => $t['page_title'],
    'page_subtitle' => $t['page_subtitle'],
    'products' => $products,
    'all_products' => $all_products,
    'current_page' => $page,
    'total_pages' => $total_pages,
    'total_products' => $total_products,
    'products_per_page' => $products_per_page,
    'offset' => $offset,
    'translations' => $t,
    'current_lang' => $current_lang
];

// Générer le contenu de la page
ob_start();
?>

<style>
    /* Page Header */
    .page-header {
        text-align: center;
        padding: 3rem 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        margin-bottom: 3rem;
    }
    
    .page-header h1 {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        font-weight: 700;
    }
    
    .page-header p {
        font-size: 1.2rem;
        opacity: 0.9;
    }
    
    /* Controls */
    .controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .filters {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .filter-btn {
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        background: white;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 0.9rem;
    }
    
    .filter-btn:hover, .filter-btn.active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
    
    /* Products Grid */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }
    
    .product-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .product-image {
        width: 100%;
        height: 200px;
        background: #f8f9fa;
        border-radius: 10px;
        margin-bottom: 1rem;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        position: relative;
    }
    
    .product-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #ff6b6b;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: bold;
    }
    
    .product-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }
    
    .product-description {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .product-rating {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .stars {
        color: #ffc107;
        font-size: 0.9rem;
    }
    
    .rating-text {
        font-size: 0.8rem;
        color: #666;
    }
    
    .product-price {
        font-size: 1.3rem;
        font-weight: bold;
        color: #667eea;
        margin-bottom: 1rem;
    }
    
    .price-old {
        text-decoration: line-through;
        color: #999;
        font-size: 1rem;
        margin-right: 0.5rem;
    }
    
    .availability {
        font-size: 0.8rem;
        margin-bottom: 1rem;
        font-weight: 500;
    }
    
    .availability.in_stock { color: #28a745; }
    .availability.limited_stock { color: #ffc107; }
    .availability.out_of_stock { color: #dc3545; }
    
    .product-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s;
        text-decoration: none;
        text-align: center;
        font-size: 0.85rem;
        display: inline-block;
    }
    
    .btn-primary {
        background: #667eea;
        color: white;
        flex: 1;
    }
    
    .btn-primary:hover {
        background: #5a6fd8;
    }
    
    .btn-outline {
        background: transparent;
        color: #667eea;
        border: 1px solid #667eea;
        flex: 1;
    }
    
    .btn-outline:hover {
        background: #667eea;
        color: white;
    }
    
    /* Modal Popup */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 10000;
        overflow-y: auto;
    }
    
    .modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    
    .modal-content {
        background: white;
        border-radius: 20px;
        max-width: 900px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
        animation: modalSlideIn 0.3s ease;
    }
    
    @keyframes modalSlideIn {
        from { opacity: 0; transform: translateY(50px) scale(0.9); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid #eee;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 2rem;
        cursor: pointer;
        color: #999;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.3s;
    }
    
    .modal-close:hover {
        background: #f8f9fa;
        color: #333;
    }
    
    .modal-body {
        padding: 2rem;
    }
    
    .modal-product-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .modal-main-image {
        width: 100%;
        height: 300px;
        background: #f8f9fa;
        border-radius: 15px;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        cursor: zoom-in;
        transition: transform 0.3s;
    }
    
    .modal-main-image:hover {
        transform: scale(1.05);
    }
    
    .modal-info-section {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .modal-badge {
        background: #ff6b6b;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: bold;
        align-self: flex-start;
    }
    
    .modal-product-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
    }
    
    .modal-rating {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .modal-price {
        font-size: 1.8rem;
        font-weight: bold;
        color: #667eea;
    }
    
    .modal-description {
        color: #666;
        line-height: 1.6;
    }
    
    .modal-section-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #333;
    }
    
    .modal-list {
        list-style: none;
    }
    
    .modal-list li {
        padding: 0.3rem 0;
        padding-left: 20px;
        position: relative;
        color: #666;
    }
    
    .modal-list li::before {
        content: '✓';
        color: #28a745;
        font-weight: bold;
        position: absolute;
        left: 0;
    }
    
    .modal-vendors {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .modal-vendor-btn {
        flex: 1;
        padding: 1rem;
        border: 1px solid #ddd;
        background: white;
        color: #333;
        text-decoration: none;
        border-radius: 10px;
        font-weight: 500;
        text-align: center;
        transition: all 0.3s;
    }
    
    .modal-vendor-btn:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
    
    /* Zoom Modal */
    .zoom-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.95);
        z-index: 20000;
        cursor: zoom-out;
    }
    
    .zoom-modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .zoom-image {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .controls {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filters {
            justify-content: center;
        }
        
        .products-grid {
            grid-template-columns: 1fr;
        }
        
        .modal-content {
            margin: 1rem;
            max-height: 95vh;
        }
        
        .modal-product-grid {
            grid-template-columns: 1fr;
        }
        
        .modal-vendors {
            flex-direction: column;
        }
    }
</style>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1><?= $t['page_title'] ?></h1>
        <p><?= $t['page_subtitle'] ?></p>
    </div>
</section>

<div class="container">
    <!-- Controls -->
    <div class="controls">
        <div class="filters">
            <button class="filter-btn active" data-filter="all"><?= $t['filter_all'] ?></button>
            <button class="filter-btn" data-filter="accessories"><?= $t['filter_accessories'] ?></button>
            <button class="filter-btn" data-filter="audio"><?= $t['filter_audio'] ?></button>
            <button class="filter-btn" data-filter="displays"><?= $t['filter_displays'] ?></button>
            <button class="filter-btn" data-filter="input"><?= $t['filter_input'] ?></button>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="products-grid" id="productsGrid">
        <?php foreach ($products as $index => $product): ?>
        <div class="product-card" data-product-index="<?= $index ?>" data-category="<?= $product['category'] ?>">
            <div class="product-image" style="background-image: url('<?= asset($product['images'][0]) ?>');">
                <div class="product-badge"><?= $product['badge'] ?></div>
            </div>
            
            <h3 class="product-title"><?= $product['name'][$current_lang] ?></h3>
            <p class="product-description"><?= $product['description'][$current_lang] ?></p>
            
            <div class="product-rating">
                <div class="stars">
                    <?php 
                    $rating = $product['rating'];
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $rating ? '★' : '☆';
                    }
                    ?>
                </div>
                <span class="rating-text"><?= $product['rating'] ?> (<?= number_format($product['reviews_count']) ?> <?= $t['reviews'] ?>)</span>
            </div>
            
            <div class="product-price">
                <?php if (isset($product['price_old'])): ?>
                <span class="price-old"><?= $product['price_old'] ?></span>
                <?php endif; ?>
                <?= $product['price'] ?>
            </div>
            
            <div class="availability <?= $product['availability'] ?>">
                ● <?= $t[$product['availability']] ?>
            </div>
            
            <div class="product-actions">
                <button class="btn btn-primary" onclick="openProductModal(<?= $index ?>)">
                    <?= $t['view_details'] ?>
                </button>
                <a href="?page=product-detail&id=<?= $product['slug'] ?>" class="btn btn-outline">
                    <?= $t['read_review'] ?>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <?= generatePagination($page, $total_pages, '?page=products') ?>
    
    <div style="text-align: center; color: #666; margin-bottom: 2rem;">
        <?= $t['showing'] ?> <?= $offset + 1 ?>-<?= min($offset + $products_per_page, $total_products) ?> <?= $t['of'] ?> <?= $total_products ?> <?= $t['products'] ?>
    </div>
    <?php endif; ?>
</div>

<!-- Product Modal -->
<div class="modal" id="productModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle"></h2>
            <button class="modal-close" onclick="closeProductModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="modal-product-grid">
                <div class="modal-image-section">
                    <div class="modal-main-image" id="modalMainImage" onclick="openZoomModal()"></div>
                </div>
                
                <div class="modal-info-section">
                    <div class="modal-badge" id="modalBadge"></div>
                    <h3 class="modal-product-title" id="modalProductTitle"></h3>
                    
                    <div class="modal-rating" id="modalRating"></div>
                    
                    <div class="modal-price" id="modalPrice"></div>
                    
                    <div class="modal-availability" id="modalAvailability"></div>
                    
                    <p class="modal-description" id="modalDescription"></p>
                    
                    <div class="modal-features">
                        <h4 class="modal-section-title"><?= $t['features'] ?></h4>
                        <ul class="modal-list" id="modalFeatures"></ul>
                    </div>
                    
                    <div class="modal-specs">
                        <h4 class="modal-section-title"><?= $t['specifications'] ?></h4>
                        <ul class="modal-list" id="modalSpecs"></ul>
                    </div>
                </div>
            </div>
            
            <div class="modal-vendors" id="modalVendors"></div>
        </div>
    </div>
</div>

<!-- Zoom Modal -->
<div class="zoom-modal" id="zoomModal" onclick="closeZoomModal()">
    <img class="zoom-image" id="zoomImage" alt="Product zoom">
</div>

<script>
    // Données produits pour JavaScript
    const products = <?= json_encode($products) ?>;
    const allProducts = <?= json_encode($all_products) ?>;
    const translations = <?= json_encode($t) ?>;
    const currentLang = '<?= $current_lang ?>';
    let currentModalIndex = 0;

    // Ouvrir modal produit
    function openProductModal(index) {
        currentModalIndex = index;
        const product = products[index];
        
        // Remplir le modal
        document.getElementById('modalTitle').textContent = product.name[currentLang];
        document.getElementById('modalBadge').textContent = product.badge;
        document.getElementById('modalProductTitle').textContent = product.name[currentLang];
        document.getElementById('modalDescription').textContent = product.description[currentLang];
        
        // Rating
        const modalRating = document.getElementById('modalRating');
        let starsHtml = '<div class="stars">';
        for (let i = 1; i <= 5; i++) {
            starsHtml += i <= product.rating ? '★' : '☆';
        }
        starsHtml += `</div><span class="rating-text">${product.rating} (${product.reviews_count.toLocaleString()} ${translations.reviews})</span>`;
        modalRating.innerHTML = starsHtml;
        
        // Prix
        let priceHtml = '';
        if (product.price_old) {
            priceHtml += `<span class="price-old">${product.price_old}</span>`;
        }
        priceHtml += product.price;
        document.getElementById('modalPrice').innerHTML = priceHtml;
        
        // Disponibilité
        const availability = document.getElementById('modalAvailability');
        availability.textContent = '● ' + translations[product.availability];
        availability.className = 'modal-availability ' + product.availability;
        
        // Image principale
        document.getElementById('modalMainImage').style.backgroundImage = `url('<?= BASE_URL ?>/${product.images[0]}')`;
        
        // Caractéristiques
        const featuresEl = document.getElementById('modalFeatures');
        featuresEl.innerHTML = '';
        product.features[currentLang].forEach(feature => {
            const li = document.createElement('li');
            li.textContent = feature;
            featuresEl.appendChild(li);
        });
        
        // Spécifications
        const specsEl = document.getElementById('modalSpecs');
        specsEl.innerHTML = '';
        product.specs[currentLang].forEach(spec => {
            const li = document.createElement('li');
            li.textContent = spec;
            specsEl.appendChild(li);
        });
        
        // Boutons vendors
        const vendorsEl = document.getElementById('modalVendors');
        vendorsEl.innerHTML = `
            <a href="${product.vendors.amazon}" class="modal-vendor-btn" target="_blank" rel="nofollow">
                Amazon
            </a>
            <a href="${product.vendors.fnac}" class="modal-vendor-btn" target="_blank" rel="nofollow">
                Fnac
            </a>
            <a href="${product.vendors.bestbuy}" class="modal-vendor-btn" target="_blank" rel="nofollow">
                Best Buy
            </a>
        `;
        
        // Afficher le modal
        document.getElementById('productModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // Fermer modal produit
    function closeProductModal() {
        document.getElementById('productModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    // Zoom modal
    function openZoomModal() {
        const product = products[currentModalIndex];
        const zoomImage = document.getElementById('zoomImage');
        zoomImage.src = '<?= BASE_URL ?>/' + product.images[0];
        document.getElementById('zoomModal').classList.add('active');
    }

    function closeZoomModal() {
        document.getElementById('zoomModal').classList.remove('active');
    }

    // Filtres par catégorie
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filterValue = this.dataset.filter;
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(card => {
                if (filterValue === 'all') {
                    card.style.display = 'block';
                } else {
                    const category = card.dataset.category;
                    card.style.display = category === filterValue ? 'block' : 'none';
                }
            });
        });
    });

    // Fermer modal avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeProductModal();
            closeZoomModal();
        }
    });

    // Fermer modal en cliquant à l'extérieur
    document.getElementById('productModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeProductModal();
        }
    });

    // Animation au scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.product-card').forEach(card => {
        observer.observe(card);
    });
</script>

<?php
// Stocker le contenu pour le template
$content = ob_get_clean();

// Rendre la page avec le layout principal
renderPage('products', array_merge($data, ['content' => $content]));
?><?php
/**
 * TechEssentials Pro V2.0 - Products Catalog
 * Architecture MVC - Page Products
 */

// Configuration et includes
require_once '../../config.php';
require_once '../../functions.php';

// Gestion pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$products_per_page = 20;
$offset = ($page - 1) * $products_per_page;

// Données des 20 produits avec vrais noms de fichiers
$all_products = [
    [
        'id' => 'anker-737-charger',
        'images' => [
            'assets/images/products/anker-737.jpg'
        ],
        'badge' => 'BESTSELLER',
        'name' => [
            'en' => 'Anker 737 PowerCore 24K Power Bank',
            'fr' => 'Batterie Externe Anker 737 PowerCore 24K'
        ],
        'description' => [
            'en' => 'High-capacity 24,000mAh power bank with 140W output for laptops, tablets and phones. Fast charging with digital display.',
            'fr' => 'Batterie externe haute capacité 24 000mAh avec sortie 140W pour portables, tablettes et téléphones. Charge rapide avec écran numérique.'
        ],
        'features' => [
            'en' => ['24,000mAh capacity', '140W total output', 'Digital display', 'Smart temperature control'],
            'fr' => ['Capacité 24 000mAh', 'Sortie totale 140W', 'Écran numérique', 'Contrôle température intelligent']
        ],
        'specs' => [
            'en' => ['Capacity: 24,000mAh / 86.4Wh', 'USB-C: 140W max', 'USB-A: 18W max', 'Dimensions: 156 × 55 × 50mm'],
            'fr' => ['Capacité: 24 000mAh / 86.4Wh', 'USB-C: 140W max', 'USB-A: 18W max', 'Dimensions: 156 × 55 × 50mm']
        ],
        'price' => '€149.99',
        'price_old' => '€199.99',
        'rating' => 4.8,
        'reviews_count' => 2847,
        'availability' => 'in_stock',
        'category' => 'accessories',
        'vendors' => [
            'amazon' => 'https://amazon.fr/anker-737',
            'fnac' => 'https://fnac.com/anker-737',
            'bestbuy' => 'https://bestbuy.com/anker-737'
        ]
    ],
    [
        'id' => 'anker-dock-hub',
        'images' => [
            'assets/images/products/anker-dock.jpg'
        ],
        'badge' => 'PREMIUM',
        'name' => [
            'en' => 'Anker PowerExpand Elite 13-in-1 Dock',
            'fr' => 'Station Anker PowerExpand Elite 13-en-1'
        ],
        'description' => [
            'en' => 'Ultimate 13-in-1 docking station with dual 4K displays, high-speed charging, and comprehensive connectivity.',
            'fr' => 'Station d\'accueil ultime 13-en-1 avec double écrans 4K, charge haute vitesse et connectivité complète.'
        ],
        'features' => [
            'en' => ['Dual 4K display support', '85W laptop charging', '13 ports total', 'Gigabit Ethernet'],
            'fr' => ['Support double 4K', 'Charge portable 85W', '13 ports au total', 'Ethernet Gigabit']
        ],
        'specs' => [
            'en' => ['HDMI: Dual 4K@60Hz', 'USB-C PD: 85W', 'USB 3.0: 4 ports', 'Ethernet: 1Gbps'],
            'fr' => ['HDMI: Double 4K@60Hz', 'USB-C PD: 85W', 'USB 3.0: 4 ports', 'Ethernet: 1Gbps']
        ],
        'price' => '€199.99',
        'price_old' => '€249.99',
        'rating' => 4.7,
        'reviews_count' => 1923,
        'availability' => 'in_stock',
        'category' => 'accessories',
        'vendors' => [
            'amazon' => 'https://amazon.fr/anker-dock',
            'fnac' => 'https://fnac.com/anker-dock',
            'bestbuy' => 'https://bestbuy.com/anker-dock'
        ]
    ],
    [
        'id' => 'asus-proart-monitor',
        'images' => [
            'assets/images/products/asus-proart-pa248QV.jpg'
        ],
        'badge' => 'PRO',
        'name' => [
            'en' => 'ASUS ProArt PA248QV 24" Professional Monitor',
            'fr' => 'Moniteur Professionnel ASUS ProArt PA248QV 24"'
        ],
        'description' => [
            'en' => 'Professional 24" monitor with 100% sRGB color space, factory calibration, and ergonomic design for creators.',
            'fr' => 'Moniteur professionnel 24" avec espace colorimétrique 100% sRGB, calibrage usine et design ergonomique pour créateurs.'
        ],
        'features' => [
            'en' => ['100% sRGB coverage', 'Factory pre-calibrated', 'USB-C connectivity', 'Height adjustable stand'],
            'fr' => ['Couverture 100% sRGB', 'Pré-calibré en usine', 'Connectivité USB-C', 'Support réglable hauteur']
        ],
        'specs' => [
            'en' => ['Size: 24.1" (61.2cm)', 'Resolution: 1920 x 1200 WUXGA', 'Refresh rate: 75Hz', 'Response time: 5ms'],
            'fr' => ['Taille: 24.1" (61.2cm)', 'Résolution: 1920 x 1200 WUXGA', 'Taux rafraîchissement: 75Hz', 'Temps réponse: 5ms']
        ],
        'price' => '€349.99',
        'price_old' => '€449.99',
        'rating' => 4.7,
        'reviews_count' => 1834,
        'availability' => 'in_stock',
        'category' => 'displays',
        'vendors' => [
            'amazon' => 'https://amazon.fr/asus-proart',
            'fnac' => 'https://fnac.com/asus-proart',
            'bestbuy' => 'https://bestbuy.com/asus-proart'
        ]
    ],
    [
        'id' => 'dell-ultrasharp-u2720q',
        'images' => [
            'assets/images/products/dell-ultrasharp-u2720q.jpg'
        ],
        'badge' => 'PROFESSIONAL',
        'name' => [
            'en' => 'Dell UltraSharp U2720Q 27" 4K Monitor',
            'fr' => 'Moniteur 4K Dell UltraSharp U2720Q 27"'
        ],
        'description' => [
            'en' => 'Professional 27" 4K monitor with 99% sRGB, USB-C connectivity with 90W power delivery, and premium build quality.',
            'fr' => 'Moniteur 4K professionnel 27" avec 99% sRGB, connectivité USB-C avec power delivery 90W et qualité construction premium.'
        ],
        'features' => [
            'en' => ['27" 4K IPS display', '99% sRGB coverage', 'USB-C with 90W PD', 'Height adjustable stand'],
            'fr' => ['Écran IPS 4K 27"', 'Couverture 99% sRGB', 'USB-C avec PD 90W', 'Support réglable hauteur']
        ],
        'specs' => [
            'en' => ['Resolution: 3840x2160 4K', 'Refresh rate: 60Hz', 'Response time: 5ms', 'Brightness: 350 cd/m²'],
            'fr' => ['Résolution: 3840x2160 4K', 'Taux rafraîchissement: 60Hz', 'Temps réponse: 5ms', 'Luminosité: 350 cd/m²']
        ],
        'price' => '€649.99',
        'price_old' => '€749.99',
        'rating' => 4.8,
        'reviews_count' => 1276,
        'availability' => 'in_stock',
        'category' => 'displays',
        'vendors' => [
            'amazon' => 'https://amazon.fr/dell-u2720q',
            'fnac' => 'https://fnac.com/dell-monitor',
            'bestbuy' => 'https://bestbuy.com/dell-u2720q'
        ]
    ],
    [
        'id' => 'herman-miller-sayl',
        'images' => [
            'assets/images/products/herman-miller-sayl.jpg'
        ],
        'badge' => 'ERGONOMIC',
        'name' => [
            'en' => 'Herman Miller Sayl Ergonomic Office Chair',
            'fr' => 'Chaise Bureau Ergonomique Herman Miller Sayl'
        ],
        'description' => [
            'en' => 'Innovative ergonomic chair with suspension back design, modern aesthetics, and exceptional support for all-day comfort.',
            'fr' => 'Chaise ergonomique innovante avec design dossier suspension, esthétique moderne et support exceptionnel pour confort toute la journée.'
        ],
        'features' => [
            'en' => ['Suspension back design', 'PostureFit sacral support', '12-year warranty', 'Adjustable arms'],
            'fr' => ['Design dossier suspension', 'Support sacré PostureFit', 'Garantie 12 ans', 'Bras ajustables']
        ],
        'specs' => [
            'en' => ['Weight capacity: 350 lbs', 'Seat height: 15.75"-20.5"', 'Width: 27"', 'Depth: 27"'],
            'fr' => ['Capacité poids: 159kg', 'Hauteur siège: 40-52cm', 'Largeur: 68cm', 'Profondeur: 68cm']
        ],
        'price' => '€695.00',
        'price_old' => '€895.00',
        'rating' => 4.7,
        'reviews_count' => 892,
        'availability' => 'in_stock',
        'category' => 'accessories',
        'vendors' => [
            'amazon' => 'https://amazon.fr/herman-miller-sayl',
            'fnac' => 'https://fnac.com/herman-miller-sayl',
            'bestbuy' => 'https://bestbuy.com/herman-miller-sayl'
        ]
    ],
    // Ajouter les 15 autres produits...
];

// Pagination
$total_products = count($all_products);
$total_pages = ceil($total_products / $products_per_page);
$products = array_slice($all_products, $offset, $products_per_page);

// Traductions
$page_translations = [
    'en' => [
        'page_title' => 'Products Catalog',
        'page_subtitle' => 'Expert-curated tech products for modern remote workers',
        'filter_all' => 'All Products',
        'filter_accessories' => 'Accessories',
        'filter_audio' => 'Audio',
        'filter_displays' => 'Displays',
        'filter_input' => 'Input Devices',
        'view_details' => 'View Details',
        'read_review' => 'Read Review',
        'in_stock' => 'In Stock',
        'limited_stock' => 'Limited Stock',
        'out_of_stock' => 'Out of Stock',
        'reviews' => 'reviews',
        'specifications' => 'Specifications',
        'features' => 'Key Features',
        'close' => 'Close',
        'showing' => 'Showing',
        'products' => 'products',
        'of' => 'of'
    ],
    'fr' => [
        'page_title' => 'Catalogue Produits',
        'page_subtitle' => 'Produits tech sélectionnés par experts pour télétravailleurs modernes',
        'filter_all' => 'Tous Produits',
        'filter_accessories' => 'Accessoires',
        'filter_audio' => 'Audio',
        'filter_displays' => 'Écrans',
        'filter_input' => 'Périphériques',
        'view_details' => 'Voir Détails',
        'read_review' => 'Lire Test',
        'in_stock' => 'En Stock',
        'limited_stock' => 'Stock Limité',
        'out_of_stock' => 'Rupture Stock',
        'reviews' => 'avis',
        'specifications' => 'Spécifications',
        'features' => 'Caractéristiques',
        'close' => 'Fermer',
        'showing' => 'Affichage',
        'products' => 'produits',
        'of' => 'sur'
    ]
];

$t = $page_translations[getCurrentLanguage()];
$meta_data = [
    'title' => $t['page_title'] . ' - TechEssentials Pro',
    'description' => 'Browse our curated collection of tech products for remote workers. Expert reviews, detailed specs, and best prices.',
    'keywords' => 'tech products, remote work accessories, productivity tools'
];
?>

<?php include '../../layouts/header.php'; ?>

<style>
    /* Page Header */
    .page-header {
        text-align: center;
        padding: 3rem 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        margin-bottom: 3rem;
    }
    
    .page-header h1 {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        font-weight: 700;
    }
    
    .page-header p {
        font-size: 1.2rem;
        opacity: 0.9;
    }
    
    /* Controls */
    .controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .filters {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .filter-btn {
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        background: white;
        border-radius: 25px;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 0.9rem;
    }
    
    .filter-btn:hover, .filter-btn.active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
    
    /* Products Grid */
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }
    
    .product-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    
    .product-image {
        width: 100%;
        height: 200px;
        background: #f8f9fa;
        border-radius: 10px;
        margin-bottom: 1rem;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        position: relative;
    }
    
    .product-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #ff6b6b;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: bold;
    }
    
    .product-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        line-height: 1.3;
    }
    
    .product-description {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .product-rating {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .stars {
        color: #ffc107;
        font-size: 0.9rem;
    }
    
    .rating-text {
        font-size: 0.8rem;
        color: #666;
    }
    
    .product-price {
        font-size: 1.3rem;
        font-weight: bold;
        color: #667eea;
        margin-bottom: 1rem;
    }
    
    .price-old {
        text-decoration: line-through;
        color: #999;
        font-size: 1rem;
        margin-right: 0.5rem;
    }
    
    .availability {
        font-size: 0.8rem;
        margin-bottom: 1rem;
        font-weight: 500;
    }
    
    .availability.in_stock { color: #28a745; }
    .availability.limited_stock { color: #ffc107; }
    .availability.out_of_stock { color: #dc3545; }
    
    .product-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s;
        text-decoration: none;
        text-align: center;
        font-size: 0.85rem;
    }
    
    .btn-primary {
        background: #667eea;
        color: white;
        flex: 1;
    }
    
    .btn-primary:hover {
        background: #5a6fd8;
    }
    
    .btn-outline {
        background: transparent;
        color: #667eea;
        border: 1px solid #667eea;
        flex: 1;
    }
    
    .btn-outline:hover {
        background: #667eea;
        color: white;
    }
    
    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1rem;
        margin: 3rem 0;
    }
    
    .pagination a, .pagination span {
        padding: 0.5rem 1rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        text-decoration: none;
        color: #333;
        transition: all 0.3s;
    }
    
    .pagination a:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
    
    .pagination .current {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
    
    /* Modal Popup */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 10000;
        overflow-y: auto;
    }
    
    .modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    
    .modal-content {
        background: white;
        border-radius: 20px;
        max-width: 900px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
        animation: modalSlideIn 0.3s ease;
    }
    
    @keyframes modalSlideIn {
        from { opacity: 0; transform: translateY(50px) scale(0.9); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid #eee;
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 2rem;
        cursor: pointer;
        color: #999;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.3s;
    }
    
    .modal-close:hover {
        background: #f8f9fa;
        color: #333;
    }
    
    .modal-body {
        padding: 2rem;
    }
    
    .modal-product-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .modal-main-image {
        width: 100%;
        height: 300px;
        background: #f8f9fa;
        border-radius: 15px;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
        cursor: zoom-in;
        transition: transform 0.3s;
    }
    
    .modal-main-image:hover {
        transform: scale(1.05);
    }
    
    .modal-info-section {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .modal-badge {
        background: #ff6b6b;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: bold;
        align-self: flex-start;
    }
    
    .modal-product-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
    }
    
    .modal-rating {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .modal-price {
        font-size: 1.8rem;
        font-weight: bold;
        color: #667eea;
    }
    
    .modal-description {
        color: #666;
        line-height: 1.6;
    }
    
    .modal-section-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #333;
    }
    
    .modal-list {
        list-style: none;
    }
    
    .modal-list li {
        padding: 0.3rem 0;
        padding-left: 20px;
        position: relative;
        color: #666;
    }
    
    .modal-list li::before {
        content: '✓';
        color: #28a745;
        font-weight: bold;
        position: absolute;
        left: 0;
    }
    
    .modal-vendors {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .modal-vendor-btn {
        flex: 1;
        padding: 1rem;
        border: 1px solid #ddd;
        background: white;
        color: #333;
        text-decoration: none;
        border-radius: 10px;
        font-weight: 500;
        text-align: center;
        transition: all 0.3s;
    }
    
    .modal-vendor-btn:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
    
    /* Zoom Modal */
    .zoom-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.95);
        z-index: 20000;
        cursor: zoom-out;
    }
    
    .zoom-modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .zoom-image {
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .controls {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filters {
            justify-content: center;
        }
        
        .products-grid {
            grid-template-columns: 1fr;
        }
        
        .modal-content {
            margin: 1rem;
            max-height: 95vh;
        }
        
        .modal-product-grid {
            grid-template-columns: 1fr;
        }
        
        .modal-vendors {
            flex-direction: column;
        }
    }
</style>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1><?= $t['page_title'] ?></h1>
        <p><?= $t['page_subtitle'] ?></p>
    </div>
</section>

<div class="container">
    <!-- Controls -->
    <div class="controls">
        <div class="filters">
            <button class="filter-btn active" data-filter="all"><?= $t['filter_all'] ?></button>
            <button class="filter-btn" data-filter="accessories"><?= $t['filter_accessories'] ?></button>
            <button class="filter-btn" data-filter="audio"><?= $t['filter_audio'] ?></button>
            <button class="filter-btn" data-filter="displays"><?= $t['filter_displays'] ?></button>
            <button class="filter-btn" data-filter="input"><?= $t['filter_input'] ?></button>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="products-grid" id="productsGrid">
        <?php foreach ($products as $index => $product): ?>
        <div class="product-card" data-product-index="<?= $index ?>" data-category="<?= $product['category'] ?>">
            <div class="product-image" style="background-image: url('<?= $product['images'][0] ?>');">
                <div class="product-badge"><?= $product['badge'] ?></div>
            </div>
            
            <h3 class="product-title"><?= $product['name'][getCurrentLanguage()] ?></h3>
            <p class="product-description"><?= $product['description'][getCurrentLanguage()] ?></p>
            
            <div class="product-rating">
                <div class="stars">
                    <?php 
                    $rating = $product['rating'];
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $rating ? '★' : '☆';
                    }
                    ?>
                </div>
                <span class="rating-text"><?= $product['rating'] ?> (<?= number_format($product['reviews_count']) ?> <?= $t['reviews'] ?>)</span>
            </div>
            
            <div class="product-price">
                <?php if (isset($product['price_old'])): ?>
                <span class="price-old"><?= $product['price_old'] ?></span>
                <?php endif; ?>
                <?= $product['price'] ?>
            </div>
            
            <div class="availability <?= $product['availability'] ?>">
                ● <?= $t[$product['availability']] ?>
            </div>
            
            <div class="product-actions">
                <button class="btn btn-primary" onclick="openProductModal(<?= $index ?>)">
                    <?= $t['view_details'] ?>
                </button>
                <a href="<?= createUrl('review-detail', ['id' => $product['id']]) ?>" class="btn btn-outline">
                    <?= $t['read_review'] ?>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="<?= createUrl('products', ['page' => $page - 1]) ?>">‹ Previous</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php if ($i == $page): ?>
            <span class="current"><?= $i ?></span>
            <?php else: ?>
            <a href="<?= createUrl('products', ['page' => $i]) ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
        <a href="<?= createUrl('products', ['page' => $page + 1]) ?>">Next ›</a>
        <?php endif; ?>
    </div>
    
    <div style="text-align: center; color: #666; margin-bottom: 2rem;">
        <?= $t['showing'] ?> <?= $offset + 1 ?>-<?= min($offset + $products_per_page, $total_products) ?> <?= $t['of'] ?> <?= $total_products ?> <?= $t['products'] ?>
    </div>
    <?php endif; ?>
</div>

<!-- Product Modal -->
<div class="modal" id="productModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle"></h2>
            <button class="modal-close" onclick="closeProductModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="modal-product-grid">
                <div class="modal-image-section">
                    <div class="modal-main-image" id="modalMainImage" onclick="openZoomModal()"></div>
                </div>
                
                <div class="modal-info-section">
                    <div class="modal-badge" id="modalBadge"></div>
                    <h3 class="modal-product-title" id="modalProductTitle"></h3>
                    
                    <div class="modal-rating" id="modalRating"></div>
                    
                    <div class="modal-price" id="modalPrice"></div>
                    
                    <div class="modal-availability" id="modalAvailability"></div>
                    
                    <p class="modal-description" id="modalDescription"></p>
                    
                    <div class="modal-features">
                        <h4 class="modal-section-title"><?= $t['features'] ?></h4>
                        <ul class="modal-list" id="modalFeatures"></ul>
                    </div>
                    
                    <div class="modal-specs">
                        <h4 class="modal-section-title"><?= $t['specifications'] ?></h4>
                        <ul class="modal-list" id="modalSpecs"></ul>
                    </div>
                </div>
            </div>
            
            <div class="modal-vendors" id="modalVendors"></div>
        </div>
    </div>
</div>

<!-- Zoom Modal -->
<div class="zoom-modal" id="zoomModal" onclick="closeZoomModal()">
    <img class="zoom-image" id="zoomImage" alt="Product zoom">
</div>

<script>
    // Données produits pour JavaScript
    const products = <?= json_encode($products) ?>;
    const translations = <?= json_encode($t) ?>;
    const currentLang = '<?= getCurrentLanguage() ?>';
    let currentModalIndex = 0;

    // Ouvrir modal produit
    function openProductModal(index) {
        currentModalIndex = index;
        const product = products[index];
        
        // Remplir le modal
        document.getElementById('modalTitle').textContent = product.name[currentLang];
        document.getElementById('modalBadge').textContent = product.badge;
        document.getElementById('modalProductTitle').textContent = product.name[currentLang];
        document.getElementById('modalDescription').textContent = product.description[currentLang];
        
        // Rating
        const modalRating = document.getElementById('modalRating');
        let starsHtml = '<div class="stars">';
        for (let i = 1; i <= 5; i++) {
            starsHtml += i <= product.rating ? '★' : '☆';
        }
        starsHtml += `</div><span class="rating-text">${product.rating} (${product.reviews_count.toLocaleString()} ${translations.reviews})</span>`;
        modalRating.innerHTML = starsHtml;
        
        // Prix
        let priceHtml = '';
        if (product.price_old) {
            priceHtml += `<span class="price-old">${product.price_old}</span>`;
        }
        priceHtml += product.price;
        document.getElementById('modalPrice').innerHTML = priceHtml;
        
        // Disponibilité
        const availability = document.getElementById('modalAvailability');
        availability.textContent = '● ' + translations[product.availability];
        availability.className = 'modal-availability ' + product.availability;
        
        // Image principale
        document.getElementById('modalMainImage').style.backgroundImage = `url('${product.images[0]}')`;
        
        // Caractéristiques
        const featuresEl = document.getElementById('modalFeatures');
        featuresEl.innerHTML = '';
        product.features[currentLang].forEach(feature => {
            const li = document.createElement('li');
            li.textContent = feature;
            featuresEl.appendChild(li);
        });
        
        // Spécifications
        const specsEl = document.getElementById('modalSpecs');
        specsEl.innerHTML = '';
        product.specs[currentLang].forEach(spec => {
            const li = document.createElement('li');
            li.textContent = spec;
            specsEl.appendChild(li);
        });
        
        // Boutons vendors
        const vendorsEl = document.getElementById('modalVendors');
        vendorsEl.innerHTML = `
            <a href="${product.vendors.amazon}" class="modal-vendor-btn" target="_blank" rel="nofollow">
                Amazon
            </a>
            <a href="${product.vendors.fnac}" class="modal-vendor-btn" target="_blank" rel="nofollow">
                Fnac
            </a>
            <a href="${product.vendors.bestbuy}" class="modal-vendor-btn" target="_blank" rel="nofollow">
                Best Buy
            </a>
        `;
        
        // Afficher le modal
        document.getElementById('productModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // Fermer modal produit
    function closeProductModal() {
        document.getElementById('productModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    // Zoom modal
    function openZoomModal() {
        const product = products[currentModalIndex];
        const zoomImage = document.getElementById('zoomImage');
        zoomImage.src = product.images[0];
        document.getElementById('zoomModal').classList.add('active');
    }

    function closeZoomModal() {
        document.getElementById('zoomModal').classList.remove('active');
    }

    // Filtres par catégorie
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filterValue = this.dataset.filter;
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(card => {
                if (filterValue === 'all') {
                    card.style.display = 'block';
                } else {
                    const category = card.dataset.category;
                    card.style.display = category === filterValue ? 'block' : 'none';
                }
            });
        });
    });

    // Fermer modal avec Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeProductModal();
            closeZoomModal();
        }
    });

    // Fermer modal en cliquant à l'extérieur
    document.getElementById('productModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeProductModal();
        }
    });

    // Animation au scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.product-card').forEach(card => {
        observer.observe(card);
    });
</script>

<?php include '../../layouts/footer.php'; ?>