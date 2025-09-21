<?php
/**
 * TechEssentials Pro - Page Deals/Promotions
 * Bons plans et promotions tech
 */

// Configuration
define('TECHESSENTIALS_PRO', true);
require_once __DIR__ . '/includes/config.php';
require_once INCLUDES_PATH . 'functions.php';

// Variables de base
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'fr';

if (in_array($lang, ['fr', 'en'])) {
    $_SESSION['lang'] = $lang;
}

// Charger les données produits centralisées
require_once INCLUDES_PATH . 'products-data.php';

// Variables pour header
$page_title = $lang === 'fr' ? 'Bons Plans Tech - TechEssentials Pro' : 'Tech Deals - TechEssentials Pro';
$page_description = $lang === 'fr' ? 'Les meilleures promotions et bons plans sur les produits tech professionnels pour télétravail.' : 'Best promotions and deals on professional tech products for remote workers.';

// Créer des deals basés sur les produits avec les meilleures réductions
$deals_data = [];
foreach ($all_products as $product) {
    if (($product['original_price'] ?? 0) > $product['price']) {
        $savings = $product['original_price'] - $product['price'];
        $deals_data[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'image' => $product['image'],
            'category' => $product['category'],
            'price' => $product['price'],
            'original_price' => $product['original_price'],
            'discount_percentage' => $product['discount_percentage'],
            'savings' => $savings,
            'rating' => $product['rating'],
            'amazon_url' => $product['amazon_url'],
            'deal_type' => $savings > 100 ? 'flash' : ($savings > 50 ? 'hot' : 'good'),
            'expires_in' => rand(1, 72), // heures
            'stock_left' => rand(3, 25),
            'is_limited' => rand(0, 100) < 30, // 30% de deals limités
            'vendor_prices' => [
                'amazon' => $product['price'],
                'fnac' => $product['price'] + rand(5, 15),
                'bestbuy' => $product['price'] + rand(10, 25)
            ]
        ];
    }
}

// Trier par économies décroissantes
usort($deals_data, function($a, $b) {
    return $b['savings'] <=> $a['savings'];
});

// Filtrage par type de deal
$deal_filter = $_GET['filter'] ?? 'all';
$filtered_deals = $deal_filter === 'all' ? $deals_data : array_filter($deals_data, function($deal) use ($deal_filter) {
    return $deal['deal_type'] === $deal_filter;
});

// Categories pour navigation
$categories = [
    'all' => $lang === 'fr' ? 'Toutes' : 'All',
    'monitors' => $lang === 'fr' ? 'Moniteurs' : 'Monitors',
    'keyboards' => $lang === 'fr' ? 'Claviers' : 'Keyboards',
    'mice' => $lang === 'fr' ? 'Souris' : 'Mice',
    'headphones' => $lang === 'fr' ? 'Casques' : 'Headphones',
    'webcams' => $lang === 'fr' ? 'Webcams' : 'Webcams',
    'chargers' => $lang === 'fr' ? 'Chargeurs' : 'Chargers'
];

// Include header
include 'includes/layouts/header.php';
?>

<style>
/* Styles spécifiques à la page deals */
.deals-hero {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
    padding: 80px 0 60px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.deals-hero::before {
    content: '💥';
    position: absolute;
    top: 20px;
    right: 20px;
    font-size: 3rem;
    opacity: 0.3;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.deals-hero h1 {
    font-size: 2.8rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.deals-hero p {
    font-size: 1.3rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto 2rem;
}

.hero-stats {
    display: flex;
    justify-content: center;
    gap: 3rem;
    margin-top: 2rem;
}

.hero-stat {
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    display: block;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
}

.deals-filters {
    background: #f8f9fa;
    padding: 30px 0;
    border-bottom: 1px solid #e9ecef;
}

.filters-grid {
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 2rem;
    align-items: center;
}

.deal-types {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.deal-type-btn {
    padding: 10px 20px;
    border: 2px solid #dee2e6;
    background: white;
    color: #6c757d;
    text-decoration: none;
    border-radius: 25px;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    position: relative;
}

.deal-type-btn.flash { border-color: #e74c3c; color: #e74c3c; }
.deal-type-btn.hot { border-color: #f39c12; color: #f39c12; }
.deal-type-btn.good { border-color: #27ae60; color: #27ae60; }

.deal-type-btn:hover,
.deal-type-btn.active {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.deal-type-btn.flash:hover,
.deal-type-btn.flash.active {
    background: #e74c3c;
    color: white;
}

.deals-count {
    color: #6c757d;
    font-weight: 600;
}

.refresh-deals {
    background: #667eea;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.refresh-deals:hover {
    background: #5a6fd8;
    transform: translateY(-2px);
}

.deals-section {
    padding: 60px 0;
}

.deals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.deal-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    position: relative;
    border: 2px solid transparent;
}

.deal-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.deal-card.flash { border-color: #e74c3c; }
.deal-card.hot { border-color: #f39c12; }
.deal-card.good { border-color: #27ae60; }

.deal-header {
    position: relative;
    padding: 1rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.deal-type-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
}

.deal-type-badge.flash { background: #e74c3c; }
.deal-type-badge.hot { background: #f39c12; }
.deal-type-badge.good { background: #27ae60; }

.deal-urgency {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
}

.time-left {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stock-left {
    font-weight: 600;
}

.deal-image {
    height: 200px;
    background: #f8f9fa;
    position: relative;
    overflow: hidden;
}

.deal-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 15px;
    transition: transform 0.3s ease;
}

.deal-card:hover .deal-image img {
    transform: scale(1.05);
}

.discount-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: #e74c3c;
    color: white;
    padding: 8px 12px;
    border-radius: 20px;
    font-weight: 700;
    font-size: 1.1rem;
}

.deal-content {
    padding: 1.5rem;
}

.deal-category {
    color: #667eea;
    font-size: 0.8rem;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.deal-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #2d3748;
    line-height: 1.3;
}

.deal-rating {
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

.deal-pricing {
    margin-bottom: 1.5rem;
}

.price-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.current-price {
    font-size: 1.4rem;
    font-weight: 700;
    color: #e74c3c;
}

.original-price {
    font-size: 1rem;
    color: #a0aec0;
    text-decoration: line-through;
}

.savings-amount {
    background: #e8f5e8;
    color: #27ae60;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 600;
}

.vendor-comparison {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.vendor-comparison h4 {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    color: #2d3748;
}

.vendor-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.3rem;
    font-size: 0.8rem;
}

.vendor-name {
    font-weight: 600;
}

.vendor-price {
    color: #667eea;
}

.vendor-price.best {
    color: #e74c3c;
    font-weight: 700;
}

.deal-actions {
    display: flex;
    gap: 0.5rem;
}

.btn {
    padding: 12px 20px;
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

.btn-deal {
    background: #e74c3c;
    color: white;
}

.btn-deal:hover {
    background: #c0392b;
    transform: translateY(-1px);
}

.btn-details {
    background: transparent;
    color: #667eea;
    border: 2px solid #667eea;
}

.btn-details:hover {
    background: #667eea;
    color: white;
}

.countdown-timer {
    display: flex;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #e74c3c;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    .deals-hero h1 {
        font-size: 2.2rem;
    }
    
    .hero-stats {
        flex-direction: column;
        gap: 1rem;
    }
    
    .filters-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
        text-align: center;
    }
    
    .deals-grid {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    }
    
    .deal-actions {
        flex-direction: column;
    }
}
</style>

<!-- Hero Section -->
<section class="deals-hero">
    <div class="container">
        <h1><?= $lang === 'fr' ? 'Bons Plans Tech' : 'Tech Deals' ?></h1>
        <p><?= $lang === 'fr' ? 'Les meilleures promotions sur les produits tech professionnels - Économisez jusqu\'à 50%' : 'Best deals on professional tech products - Save up to 50%' ?></p>
        
        <div class="hero-stats">
            <div class="hero-stat">
                <span class="stat-number"><?= count($deals_data) ?></span>
                <span class="stat-label"><?= $lang === 'fr' ? 'Deals actifs' : 'Active deals' ?></span>
            </div>
            <div class="hero-stat">
                <span class="stat-number"><?= array_sum(array_column($deals_data, 'savings')) ?>€</span>
                <span class="stat-label"><?= $lang === 'fr' ? 'Économies totales' : 'Total savings' ?></span>
            </div>
            <div class="hero-stat">
                <span class="stat-number"><?= max(array_column($deals_data, 'discount_percentage')) ?>%</span>
                <span class="stat-label"><?= $lang === 'fr' ? 'Réduction max' : 'Max discount' ?></span>
            </div>
        </div>
    </div>
</section>

<!-- Filters Section -->
<section class="deals-filters">
    <div class="container">
        <div class="filters-grid">
            <div class="deal-types">
                <a href="?filter=all&lang=<?= $lang ?>" class="deal-type-btn <?= $deal_filter === 'all' ? 'active' : '' ?>">
                    <?= $lang === 'fr' ? 'Tous' : 'All' ?>
                </a>
                <a href="?filter=flash&lang=<?= $lang ?>" class="deal-type-btn flash <?= $deal_filter === 'flash' ? 'active' : '' ?>">
                    <?= $lang === 'fr' ? 'Flash' : 'Flash' ?>
                </a>
                <a href="?filter=hot&lang=<?= $lang ?>" class="deal-type-btn hot <?= $deal_filter === 'hot' ? 'active' : '' ?>">
                    <?= $lang === 'fr' ? 'Hot' : 'Hot' ?>
                </a>
                <a href="?filter=good&lang=<?= $lang ?>" class="deal-type-btn good <?= $deal_filter === 'good' ? 'active' : '' ?>">
                    <?= $lang === 'fr' ? 'Bon plan' : 'Good deal' ?>
                </a>
            </div>
            
            <div class="deals-count">
                <?= count($filtered_deals) ?> <?= $lang === 'fr' ? 'deals trouvés' : 'deals found' ?>
            </div>
            
            <button class="refresh-deals" onclick="location.reload()">
                🔄 <?= $lang === 'fr' ? 'Actualiser' : 'Refresh' ?>
            </button>
        </div>
    </div>
</section>

<!-- Deals Section -->
<section class="deals-section">
    <div class="container">
        <div class="deals-grid">
            <?php foreach ($filtered_deals as $deal): ?>
                <div class="deal-card <?= $deal['deal_type'] ?>">
                    <div class="deal-header">
                        <div class="deal-type-badge <?= $deal['deal_type'] ?>">
                            <?= $deal['deal_type'] === 'flash' ? ($lang === 'fr' ? 'FLASH' : 'FLASH') : 
                                ($deal['deal_type'] === 'hot' ? 'HOT' : ($lang === 'fr' ? 'BON PLAN' : 'DEAL')) ?>
                        </div>
                        
                        <div class="deal-urgency">
                            <div class="time-left">
                                ⏰ <?= $deal['expires_in'] ?>h <?= $lang === 'fr' ? 'restantes' : 'left' ?>
                            </div>
                            <div class="stock-left">
                                📦 <?= $deal['stock_left'] ?> <?= $lang === 'fr' ? 'en stock' : 'in stock' ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="deal-image">
                        <img src="assets/images/products/<?= htmlspecialchars($deal['image']) ?>" 
                             alt="<?= htmlspecialchars($deal['name']) ?>">
                        
                        <div class="discount-badge">-<?= $deal['discount_percentage'] ?>%</div>
                    </div>
                    
                    <div class="deal-content">
                        <div class="deal-category"><?= $categories[$deal['category']] ?? ucfirst($deal['category']) ?></div>
                        <h3 class="deal-title"><?= htmlspecialchars($deal['name']) ?></h3>
                        
                        <div class="deal-rating">
                            <div class="stars">
                                <?php
                                $rating = $deal['rating'];
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating ? '★' : '☆';
                                }
                                ?>
                            </div>
                            <span class="rating-text"><?= $deal['rating'] ?>/5</span>
                        </div>
                        
                        <div class="deal-pricing">
                            <div class="price-row">
                                <span class="current-price">€<?= number_format($deal['price'], 2) ?></span>
                                <span class="original-price">€<?= number_format($deal['original_price'], 2) ?></span>
                            </div>
                            <div class="savings-amount">
                                💰 <?= $lang === 'fr' ? 'Économisez' : 'Save' ?> €<?= number_format($deal['savings'], 2) ?>
                            </div>
                        </div>
                        
                        <div class="vendor-comparison">
                            <h4><?= $lang === 'fr' ? 'Comparaison prix:' : 'Price comparison:' ?></h4>
                            <div class="vendor-row">
                                <span class="vendor-name">Amazon</span>
                                <span class="vendor-price best">€<?= number_format($deal['vendor_prices']['amazon'], 2) ?></span>
                            </div>
                            <div class="vendor-row">
                                <span class="vendor-name">Fnac</span>
                                <span class="vendor-price">€<?= number_format($deal['vendor_prices']['fnac'], 2) ?></span>
                            </div>
                            <div class="vendor-row">
                                <span class="vendor-name">BestBuy</span>
                                <span class="vendor-price">€<?= number_format($deal['vendor_prices']['bestbuy'], 2) ?></span>
                            </div>
                        </div>
                        
                        <div class="deal-actions">
                            <a href="product-detail.php?id=<?= $deal['id'] ?>&lang=<?= $lang ?>" 
                               class="btn btn-details">
                                <?= $lang === 'fr' ? 'Voir détails' : 'View details' ?>
                            </a>
                            
                            <a href="<?= htmlspecialchars($deal['amazon_url']) ?>" 
                               class="btn btn-deal" target="_blank" rel="noopener">
                                <?= $lang === 'fr' ? 'Profiter du deal' : 'Get deal' ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($filtered_deals)): ?>
            <div style="text-align: center; padding: 3rem;">
                <h3><?= $lang === 'fr' ? 'Aucun deal trouvé' : 'No deals found' ?></h3>
                <p><?= $lang === 'fr' ? 'Essayez un autre filtre ou revenez plus tard.' : 'Try another filter or come back later.' ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Auto-refresh deals every 5 minutes
setTimeout(function() {
    location.reload();
}, 300000);

// Countdown timers (simulation)
function updateCountdowns() {
    const timeElements = document.querySelectorAll('.time-left');
    timeElements.forEach(element => {
        const currentTime = element.textContent;
        const hours = parseInt(currentTime.match(/(\d+)h/)[1]);
        if (hours > 0) {
            element.innerHTML = `⏰ ${hours - 1}h <?= $lang === 'fr' ? 'restantes' : 'left' ?>`;
        }
    });
}

// Update every hour
setInterval(updateCountdowns, 3600000);
</script>

<?php 
// Include newsletter et footer
include 'includes/layouts/newsletter.php';
include 'includes/layouts/footer.php';
?>