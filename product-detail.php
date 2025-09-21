<?php
// Configuration
define('TECHESSENTIALS_PRO', true);
require_once __DIR__ . '/includes/config.php';
require_once INCLUDES_PATH . 'functions.php';

// Variables de base AVANT d'inclure products-data
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'fr';
$product_id = $_GET['id'] ?? '';

if (in_array($lang, ['fr', 'en'])) {
    $_SESSION['lang'] = $lang;
}

// Maintenant on peut charger products-data (qui utilise $lang)
require_once INCLUDES_PATH . 'products-data.php';

// Reste du code...

// Vérification produit avec la fonction centralisée
$product = getProductById($product_id);

if (!$product) {
    // Redirect propre vers products.php si produit non trouvé
    header('Location: products.php?lang=' . $lang);
    exit;
}

// Variables pour header (maintenant $product existe)
$page_title = htmlspecialchars($product['name']) . ' - TechEssentials Pro';
$page_description = htmlspecialchars($product['description']);

// Données supplémentaires pour product-detail (si pas dans l'array de base)
$product_details = [
    'gallery_images' => [$product['image']], // Pour l'instant, une seule image
    'reviews_count' => rand(50, 500), // Temporaire
    'long_description' => $product['description'],
    'in_stock' => true,
    'specifications' => [
        'Brand' => 'Tech Brand',
        'Model' => $product['name'],
        'Category' => ucfirst($product['category']),
        'Rating' => $product['rating'] . '/5',
        'Price' => '€' . number_format($product['price'], 2)
    ],
    'features' => [
        'High quality construction',
        'Professional grade materials',
        'Easy to use interface',
        'Reliable performance'
    ],
    'fnac_url' => 'https://fnac.fr/product/' . $product['id']
];

// Fusionner les données
$product = array_merge($product, $product_details);

// Include header
include 'includes/layouts/header.php';
?>

<style>
/* Styles pour la page product-detail */
.product-detail-container {
    padding: 60px 0;
    background: #f8fafc;
}

.product-detail {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 3rem;
}

.product-breadcrumb {
    padding: 20px 30px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.9rem;
}

.product-breadcrumb a {
    color: #667eea;
    text-decoration: none;
}

.product-breadcrumb a:hover {
    text-decoration: underline;
}

.product-main {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    padding: 3rem;
}

.product-gallery {
    position: relative;
}

.main-image {
    width: 100%;
    height: 400px;
    background: #f8f9fa;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 1rem;
    cursor: zoom-in;
}

.main-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 20px;
}

.thumbnail-gallery {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
}

.thumbnail {
    width: 80px;
    height: 80px;
    background: #f8f9fa;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: border-color 0.3s ease;
}

.thumbnail.active,
.thumbnail:hover {
    border-color: #667eea;
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 8px;
}

.product-info h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.product-category-badge {
    display: inline-block;
    background: #667eea;
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 1rem;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stars {
    color: #ffd700;
    font-size: 1.2rem;
}

.rating-details {
    font-size: 0.9rem;
    color: #6c757d;
}

.product-price {
    margin-bottom: 2rem;
}

.current-price {
    font-size: 2.5rem;
    font-weight: 700;
    color: #667eea;
    margin-right: 1rem;
}

.original-price {
    font-size: 1.2rem;
    color: #a0aec0;
    text-decoration: line-through;
    margin-right: 0.5rem;
}

.savings {
    background: #e53e3e;
    color: white;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
}

.stock-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 2rem;
    font-weight: 600;
    color: #28a745;
}

.stock-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #28a745;
}

.product-description {
    font-size: 1.1rem;
    line-height: 1.6;
    color: #4a5568;
    margin-bottom: 2rem;
}

.vendor-comparison {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.vendor-card {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
}

.vendor-card:hover {
    border-color: #667eea;
    transform: translateY(-2px);
}

.vendor-logo {
    font-size: 1.2rem;
    font-weight: 700;
    color: #667eea;
    margin-bottom: 0.5rem;
}

.vendor-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 1rem;
}

.vendor-btn {
    width: 100%;
    padding: 10px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: block;
    transition: background 0.3s ease;
}

.vendor-btn:hover {
    background: #5a6fd8;
}

.product-tabs {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.tab-navigation {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.tab-btn {
    flex: 1;
    padding: 15px 20px;
    background: none;
    border: none;
    cursor: pointer;
    font-weight: 600;
    color: #6c757d;
    transition: all 0.3s ease;
}

.tab-btn.active,
.tab-btn:hover {
    background: #667eea;
    color: white;
}

.tab-content {
    padding: 2rem;
}

.tab-panel {
    display: none;
}

.tab-panel.active {
    display: block;
}

.specifications-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.spec-item {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f1f3f4;
}

.spec-label {
    font-weight: 600;
    color: #4a5568;
}

.spec-value {
    color: #718096;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.feature-icon {
    color: #667eea;
    font-size: 1.2rem;
    margin-top: 2px;
}

.zoom-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 10000;
    cursor: zoom-out;
}

.zoom-overlay img {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
    border-radius: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .product-main {
        grid-template-columns: 1fr;
        gap: 2rem;
        padding: 2rem;
    }
    
    .product-info h1 {
        font-size: 1.5rem;
    }
    
    .current-price {
        font-size: 2rem;
    }
    
    .tab-navigation {
        flex-wrap: wrap;
    }
    
    .tab-btn {
        flex: none;
        min-width: 120px;
    }
    
    .specifications-grid {
        grid-template-columns: 1fr;
    }
    
    .vendor-comparison {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Product Detail Container -->
<div class="product-detail-container">
    <div class="container">
        <!-- Breadcrumb -->
        <div class="product-detail">
            <div class="product-breadcrumb">
                <a href="index.php?lang=<?= $lang ?>"><?= $lang === 'fr' ? 'Accueil' : 'Home' ?></a> &gt; 
                <a href="products.php?lang=<?= $lang ?>"><?= $lang === 'fr' ? 'Produits' : 'Products' ?></a> &gt; 
                <?= htmlspecialchars($product['name']) ?>
            </div>

            <!-- Product Main Section -->
            <div class="product-main">
                <!-- Product Gallery -->
                <div class="product-gallery">
                    <div class="main-image" onclick="openZoom('assets/images/products/<?= $product['image'] ?>')">
                        <img id="mainProductImage" src="assets/images/products/<?= $product['image'] ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>">
                    </div>
                    
                    <div class="thumbnail-gallery">
                        <?php foreach ($product['gallery_images'] as $index => $image): ?>
                            <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" 
                                 onclick="changeMainImage('assets/images/products/<?= $image ?>', this)">
                                <img src="assets/images/products/<?= $image ?>" alt="<?= htmlspecialchars($product['name']) ?> - Image <?= $index + 1 ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <div class="product-category-badge">
                        <?= ucfirst(str_replace('_', ' ', $product['category'])) ?>
                    </div>
                    
                    <h1><?= htmlspecialchars($product['name']) ?></h1>
                    
                    <div class="product-rating">
                        <div class="stars">
                            <?php
                            $rating = $product['rating'];
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $rating ? '★' : '☆';
                            }
                            ?>
                        </div>
                        <div class="rating-details">
                            <?= $product['rating'] ?>/5 (<?= $product['reviews_count'] ?> <?= $lang === 'fr' ? 'avis' : 'reviews' ?>)
                        </div>
                    </div>
                    
                    <div class="product-price">
                        <span class="current-price">€<?= number_format($product['price'], 2) ?></span>
                        <?php if ($product['original_price'] > $product['price']): ?>
                            <span class="original-price">€<?= number_format($product['original_price'], 2) ?></span>
                            <span class="savings">
                                <?= $lang === 'fr' ? 'Économisez' : 'Save' ?> €<?= number_format($product['original_price'] - $product['price'], 2) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="stock-status">
                        <div class="stock-indicator"></div>
                        <?= $product['in_stock'] ? ($lang === 'fr' ? 'En stock' : 'In stock') : ($lang === 'fr' ? 'Rupture de stock' : 'Out of stock') ?>
                    </div>
                    
                    <div class="product-description">
                        <?= htmlspecialchars($product['long_description']) ?>
                    </div>
                    
                    <!-- Multi-Vendor Purchase Options -->
                    <div class="vendor-comparison">
                        <div class="vendor-card">
                            <div class="vendor-logo">Amazon</div>
                            <div class="vendor-price">€<?= number_format($product['price'], 2) ?></div>
                            <a href="<?= htmlspecialchars($product['amazon_url']) ?>" 
                               class="vendor-btn" target="_blank" rel="noopener">
                                <?= $lang === 'fr' ? 'Acheter sur Amazon' : 'Buy on Amazon' ?>
                            </a>
                        </div>
                        
                        <div class="vendor-card">
                            <div class="vendor-logo">Fnac</div>
                            <div class="vendor-price">€<?= number_format($product['price'] + 5, 2) ?></div>
                            <a href="<?= htmlspecialchars($product['fnac_url'] ?? '#') ?>" 
                               class="vendor-btn" target="_blank" rel="noopener">
                                <?= $lang === 'fr' ? 'Acheter sur Fnac' : 'Buy on Fnac' ?>
                            </a>
                        </div>
                        
                        <div class="vendor-card">
                            <div class="vendor-logo">BestBuy</div>
                            <div class="vendor-price">€<?= number_format($product['price'] + 10, 2) ?></div>
                            <a href="#" class="vendor-btn" target="_blank" rel="noopener">
                                <?= $lang === 'fr' ? 'Acheter sur BestBuy' : 'Buy on BestBuy' ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Tabs -->
        <div class="product-tabs">
            <div class="tab-navigation">
                <button class="tab-btn active" onclick="showTab('specifications')">
                    <?= $lang === 'fr' ? 'Spécifications' : 'Specifications' ?>
                </button>
                <button class="tab-btn" onclick="showTab('features')">
                    <?= $lang === 'fr' ? 'Caractéristiques' : 'Features' ?>
                </button>
                <button class="tab-btn" onclick="showTab('reviews')">
                    <?= $lang === 'fr' ? 'Avis' : 'Reviews' ?> (<?= $product['reviews_count'] ?>)
                </button>
            </div>
            
            <div class="tab-content">
                <!-- Specifications Tab -->
                <div id="specifications" class="tab-panel active">
                    <div class="specifications-grid">
                        <?php foreach ($product['specifications'] as $label => $value): ?>
                            <div class="spec-item">
                                <span class="spec-label"><?= htmlspecialchars($label) ?></span>
                                <span class="spec-value"><?= htmlspecialchars($value) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Features Tab -->
                <div id="features" class="tab-panel">
                    <div class="features-grid">
                        <?php foreach ($product['features'] as $feature): ?>
                            <div class="feature-item">
                                <div class="feature-icon">✓</div>
                                <div><?= htmlspecialchars($feature) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Reviews Tab -->
                <div id="reviews" class="tab-panel">
                    <div style="text-align: center; padding: 2rem;">
                        <h3><?= $lang === 'fr' ? 'Avis clients' : 'Customer Reviews' ?></h3>
                        <p><?= $lang === 'fr' ? 'Section des avis en cours de développement.' : 'Reviews section under development.' ?></p>
                        <div style="margin: 2rem 0;">
                            <div class="stars" style="font-size: 2rem;">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $product['rating'] ? '★' : '☆';
                                }
                                ?>
                            </div>
                            <p><strong><?= $product['rating'] ?>/5</strong> - <?= $product['reviews_count'] ?> <?= $lang === 'fr' ? 'avis vérifiés' : 'verified reviews' ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Zoom Overlay -->
<div class="zoom-overlay" id="zoomOverlay" onclick="closeZoom()">
    <img id="zoomImage" src="" alt="Zoom">
</div>

<script>
// Image Gallery Functions
function changeMainImage(imageSrc, thumbnail) {
    document.getElementById('mainProductImage').src = imageSrc;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
    thumbnail.classList.add('active');
}

// Zoom Functions
function openZoom(imageSrc) {
    document.getElementById('zoomImage').src = imageSrc;
    document.getElementById('zoomOverlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeZoom() {
    document.getElementById('zoomOverlay').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Tab Functions
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}

// Keyboard navigation for zoom
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeZoom();
    }
});
</script>

<?php 
// Include newsletter et footer
include 'includes/layouts/newsletter.php';
include 'includes/layouts/footer.php';
?>