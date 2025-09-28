<?php
// Configuration
define('TECHESSENTIALS_PRO', true);
require_once __DIR__ . '/includes/config.php';
require_once INCLUDES_PATH . 'functions.php';

function getProductByIdOrSlug($identifier) {
    global $all_products;
    
    if (empty($identifier)) return null;
    
    foreach ($all_products as $product) {
        // Comparaison stricte avec les IDs string
        if (isset($product['id']) && $product['id'] === $identifier) {
            return $product;
        }
    }
    return null;
}


// Variables de base AVANT d'inclure products-data
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'fr';
$product_id = $_GET['id'] ?? $_GET['slug'] ?? '';

if (in_array($lang, ['fr', 'en'])) {
    $_SESSION['lang'] = $lang;
}

// Maintenant on peut charger products-data (qui utilise $lang)
require_once INCLUDES_PATH . 'products-data.php';

// Vérification produit avec la fonction centralisée
$product = getProductByIdOrSlug($product_id);

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
    'gallery_images' => [
        $product['image'],
        $product['image'], 
        $product['image'], 
        $product['image']
    ], // MODIFIÉ : 4 images au lieu d'1
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
    /* SUPPRIMÉ : cursor: zoom-in; */
    /* SUPPRIMÉ : onclick */
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

/* SYSTÈME DE LOUPE CARRÉE - CSS AJOUTÉ */
.magnifier-overlay {
    position: absolute;
    pointer-events: none;
    z-index: 1000;
    display: none;
}

.magnifier-lens {
    position: absolute;
    border: 2px solid rgba(102, 126, 234, 0.8);
    background: rgba(102, 126, 234, 0.15);
    pointer-events: none;
    width: 120px;
    height: 120px;
    backdrop-filter: blur(1px);
    box-shadow: 0 0 15px rgba(102, 126, 234, 0.4);
}

.magnifier-result {
    position: absolute;
    border: 2px solid #667eea;
    background: white;
    overflow: hidden;
    width: 300px;
    height: 300px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    z-index: 1001;
}

.magnifier-result img {
    position: absolute;
    width: 600px;
    height: 600px;
    object-fit: contain;
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
    
    /* Masquer la loupe sur mobile */
    .magnifier-overlay,
    .magnifier-result {
        display: none !important;
    }

    .magnifier-result {
    position: absolute;
    border: 2px solid #667eea;
    background: white;
    overflow: hidden;
    width: 300px;
    height: 300px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    z-index: 1001;
    /* AJOUTER POUR TEST : */
    left: 450px !important;
    top: 0px !important;
    display: block !important;
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
                    <div class="main-image">
                        <!-- SUPPRIMÉ onclick="openZoom(...)" -->
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

<!-- SUPPRIMÉ COMPLÈTEMENT : Zoom Overlay -->

<!-- SYSTÈME DE LOUPE CARRÉE INTÉGRÉ -->
<!-- REMPLACER LE SCRIPT JAVASCRIPT À LA FIN DE VOTRE PRODUCT-DETAIL.PHP PAR CECI : -->

<script>
// Image Gallery Functions - CONSERVÉES
function changeMainImage(imageSrc, thumbnail) {
    document.getElementById('mainProductImage').src = imageSrc;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
    thumbnail.classList.add('active');
    
    // Mettre à jour l'image de zoom aussi
    const zoomImg = document.querySelector('.magnifier-result img');
    if (zoomImg) {
        zoomImg.src = imageSrc;
    }
}



// Tab Functions - CONSERVÉES
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}

// SYSTÈME DE LOUPE CARRÉE FINAL
document.addEventListener('DOMContentLoaded', function() {
    const mainImage = document.querySelector('.main-image');
    const mainImg = document.getElementById('mainProductImage');
    
    if (!mainImage || !mainImg) {
        return;
    }
    
    // Vérifier si on est sur mobile
    if (window.innerWidth <= 768 || 'ontouchstart' in window) {
        return; // Pas de loupe sur mobile
    }
    
    // Créer la loupe carrée
    const lens = document.createElement('div');
    lens.style.cssText = `
        position: absolute;
        border: 2px solid rgba(102, 126, 234, 0.8);
        background: rgba(102, 126, 234, 0.15);
        pointer-events: none;
        width: 120px;
        height: 120px;
        backdrop-filter: blur(1px);
        box-shadow: 0 0 15px rgba(102, 126, 234, 0.4);
        display: none;
        z-index: 1000;
    `;
    
    // Créer la fenêtre zoom
    const result = document.createElement('div');
    result.className = 'magnifier-result';
    result.style.cssText = `
        position: fixed;
        border: 2px solid #667eea;
        background: white;
        overflow: hidden;
        width: 600px;
        height: 600px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        z-index: 1001;
        display: none;
    `;
    
    // Image dans la fenêtre zoom
    const zoomImg = document.createElement('img');
    zoomImg.src = mainImg.src;
    zoomImg.alt = 'Vue agrandie';
    zoomImg.style.cssText = `
        position: absolute;
        width: 1200px;
        height: 1200px;
        object-fit: contain;
    `;
    result.appendChild(zoomImg);
    
    // Ajouter au container
    mainImage.style.position = 'relative';
    mainImage.appendChild(lens);
    mainImage.appendChild(result);
    
    // Positionner la fenêtre zoom
    function positionZoomWindow() {
        const productInfo = document.querySelector('.product-info');
        if (productInfo) {
            const infoRect = productInfo.getBoundingClientRect();
            result.style.left = (infoRect.left + 50) + 'px';
            result.style.top = (infoRect.top -100) + 'px';
        }
    }
    
    positionZoomWindow();
    
    // Events
    mainImage.addEventListener('mouseenter', function() {
        lens.style.display = 'block';
        result.style.display = 'block';
        mainImage.style.cursor = 'none';
        positionZoomWindow(); // Recalculer position
    });
    
    mainImage.addEventListener('mouseleave', function() {
        lens.style.display = 'none';
        result.style.display = 'none';
        mainImage.style.cursor = 'default';
    });
    
    mainImage.addEventListener('mousemove', function(e) {
        const rect = mainImage.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        // Position loupe (centrée sur curseur)
        const lensSize = 120;
        const halfLens = lensSize / 2;
        
        let lensX = x - halfLens;
        let lensY = y - halfLens;
        
        // Contraindre la loupe dans l'image
        lensX = Math.max(0, Math.min(lensX, rect.width - lensSize));
        lensY = Math.max(0, Math.min(lensY, rect.height - lensSize));
        
        lens.style.left = lensX + 'px';
        lens.style.top = lensY + 'px';
        
        // Position zoom avec magnifying power 3x
        const zoomFactor = 3.0;
        const zoomX = -(x * zoomFactor - 300); // 300 = moitié de 600px
        const zoomY = -(y * zoomFactor - 300);
        
        zoomImg.style.left = zoomX + 'px';
        zoomImg.style.top = zoomY + 'px';
    });
    
    // Recalculer position sur resize
    window.addEventListener('resize', positionZoomWindow);
    
    // Observer les changements d'image pour compatibilité avec changeMainImage
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'src') {
                zoomImg.src = mainImg.src;
            }
        });
    });
    
    observer.observe(mainImg, { attributes: true });
});
</script>

<?php 
// Include newsletter et footer
include 'includes/layouts/newsletter.php';
include 'includes/layouts/footer.php';
?>