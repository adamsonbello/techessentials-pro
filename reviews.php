<?php
/**
 * TechEssentials Pro - Page Reviews/Tests
 * Catalogue des tests détaillés
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
$page_title = $lang === 'fr' ? 'Tests & Avis Détaillés - TechEssentials Pro' : 'Detailed Reviews & Tests - TechEssentials Pro';
$page_description = $lang === 'fr' ? 'Tests approfondis et avis détaillés de produits tech par nos experts.' : 'In-depth tests and detailed reviews of tech products by our experts.';

// Créer des données de reviews basées sur les produits
$reviews_data = [];
foreach ($all_products as $product) {
    $reviews_data[] = [
        'id' => $product['id'],
        'product_name' => $product['name'],
        'image' => $product['image'],
        'category' => $product['category'],
        'rating' => $product['rating'],
        'price' => $product['price'],
        'original_price' => $product['original_price'] ?? 0,
        'amazon_url' => $product['amazon_url'],
        'review_date' => date('Y-m-d', strtotime('-' . rand(1, 30) . ' days')),
        'read_time' => rand(5, 12) . ' min',
        'verdict' => $lang === 'fr' ? 
            ['Excellent choix', 'Recommandé', 'Bon rapport qualité-prix', 'Produit solide', 'Incontournable'][rand(0, 4)] :
            ['Excellent choice', 'Recommended', 'Great value', 'Solid product', 'Must-have'][rand(0, 4)],
        'summary' => $lang === 'fr' ? 
            'Test complet avec analyse détaillée des performances, qualité de construction et rapport qualité-prix.' :
            'Complete test with detailed analysis of performance, build quality and value for money.',
        'pros_count' => rand(4, 7),
        'cons_count' => rand(1, 3)
    ];
}

// Filtrage par catégorie
$selected_category = $_GET['category'] ?? 'all';
$reviews_to_show = $selected_category === 'all' ? $reviews_data : array_filter($reviews_data, function($review) use ($selected_category) {
    return $review['category'] === $selected_category;
});

// Tri
$sort = $_GET['sort'] ?? 'recent';
switch ($sort) {
    case 'rating':
        usort($reviews_to_show, function($a, $b) { return $b['rating'] <=> $a['rating']; });
        break;
    case 'name':
        usort($reviews_to_show, function($a, $b) { return strcmp($a['product_name'], $b['product_name']); });
        break;
    case 'recent':
    default:
        usort($reviews_to_show, function($a, $b) { return strcmp($b['review_date'], $a['review_date']); });
        break;
}

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$reviews_per_page = 9;
$total_reviews = count($reviews_to_show);
$total_pages = ceil($total_reviews / $reviews_per_page);
$offset = ($page - 1) * $reviews_per_page;
$current_reviews = array_slice($reviews_to_show, $offset, $reviews_per_page);

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
/* Styles spécifiques à la page reviews */
.reviews-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 0 60px;
    text-align: center;
}

.reviews-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.reviews-hero p {
    font-size: 1.2rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
}

.reviews-filters {
    background: #f8f9fa;
    padding: 30px 0;
    border-bottom: 1px solid #e9ecef;
}

.filters-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
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

.sort-controls {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.sort-select {
    padding: 8px 12px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    background: white;
}

.reviews-stats {
    display: flex;
    gap: 2rem;
    font-size: 0.9rem;
    color: #6c757d;
}

.reviews-section {
    padding: 60px 0;
}

.reviews-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.review-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    position: relative;
}

.review-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.review-image {
    position: relative;
    overflow: hidden;
    height: 200px;
    background: #f8f9fa;
}

.review-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center;
    padding: 15px;
}

.review-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background: #667eea;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.verdict-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    color: white;
}

.verdict-excellent { background: #28a745; }
.verdict-recommended { background: #17a2b8; }
.verdict-good { background: #ffc107; color: #333; }
.verdict-solid { background: #6c757d; }
.verdict-must-have { background: #dc3545; }

.review-content {
    padding: 1.5rem;
}

.review-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 1rem;
}

.review-category {
    color: #667eea;
    text-transform: uppercase;
    font-weight: 600;
}

.review-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #2d3748;
    line-height: 1.3;
}

.review-summary {
    color: #718096;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    line-height: 1.4;
}

.review-rating {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.stars {
    color: #ffd700;
    font-size: 1rem;
}

.rating-text {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 600;
}

.review-highlights {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    font-size: 0.8rem;
}

.pros-cons {
    display: flex;
    align-items: center;
    gap: 0.3rem;
    color: #6c757d;
}

.pros { color: #28a745; }
.cons { color: #dc3545; }

.review-price {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.current-price {
    font-size: 1.1rem;
    font-weight: 700;
    color: #667eea;
}

.original-price {
    font-size: 0.9rem;
    color: #a0aec0;
    text-decoration: line-through;
}

.review-actions {
    display: flex;
    gap: 0.5rem;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
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
    .filters-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .category-filters {
        justify-content: center;
    }
    
    .reviews-grid {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    .reviews-hero h1 {
        font-size: 2rem;
    }
    
    .review-actions {
        flex-direction: column;
    }
    
    .reviews-stats {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<!-- Hero Section -->
<section class="reviews-hero">
    <div class="container">
        <h1><?= $lang === 'fr' ? 'Tests & Avis Détaillés' : 'Detailed Tests & Reviews' ?></h1>
        <p><?= $lang === 'fr' ? 'Nos experts testent rigoureusement chaque produit pour vous aider à faire le meilleur choix' : 'Our experts rigorously test every product to help you make the best choice' ?></p>
    </div>
</section>

<!-- Filters Section -->
<section class="reviews-filters">
    <div class="container">
        <div class="filters-row">
            <div class="category-filters">
                <?php foreach ($categories as $cat_key => $cat_name): ?>
                    <a href="?category=<?= $cat_key ?>&sort=<?= $sort ?>&lang=<?= $lang ?>" 
                       class="filter-btn <?= $selected_category === $cat_key ? 'active' : '' ?>">
                        <?= $cat_name ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <div class="sort-controls">
                <label><?= $lang === 'fr' ? 'Trier par:' : 'Sort by:' ?></label>
                <select class="sort-select" onchange="window.location.href='?category=<?= $selected_category ?>&sort=' + this.value + '&lang=<?= $lang ?>'">
                    <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>><?= $lang === 'fr' ? 'Plus récents' : 'Most recent' ?></option>
                    <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>><?= $lang === 'fr' ? 'Mieux notés' : 'Highest rated' ?></option>
                    <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>><?= $lang === 'fr' ? 'Nom A-Z' : 'Name A-Z' ?></option>
                </select>
            </div>
        </div>
        
        <div class="reviews-stats">
            <span><?= count($current_reviews) ?> <?= $lang === 'fr' ? 'tests affichés' : 'reviews shown' ?></span>
            <span><?= $total_reviews ?> <?= $lang === 'fr' ? 'tests au total' : 'total reviews' ?></span>
            <span><?= $lang === 'fr' ? 'Mis à jour quotidiennement' : 'Updated daily' ?></span>
        </div>
    </div>
</section>

<!-- Reviews Section -->
<section class="reviews-section">
    <div class="container">
        <div class="reviews-grid">
            <?php foreach ($current_reviews as $review): ?>
                <div class="review-card">
                    <div class="review-image">
                        <img src="assets/images/products/<?= htmlspecialchars($review['image']) ?>" 
                             alt="<?= htmlspecialchars($review['product_name']) ?>">
                        
                        <div class="review-badge"><?= $lang === 'fr' ? 'TEST' : 'REVIEW' ?></div>
                        
                        <div class="verdict-badge verdict-<?= strtolower(str_replace(' ', '-', $review['verdict'])) ?>">
                            <?= htmlspecialchars($review['verdict']) ?>
                        </div>
                    </div>
                    
                    <div class="review-content">
                        <div class="review-meta">
                            <span class="review-category"><?= $categories[$review['category']] ?></span>
                            <span><?= $review['read_time'] ?> <?= $lang === 'fr' ? 'lecture' : 'read' ?></span>
                        </div>
                        
                        <h3 class="review-title"><?= htmlspecialchars($review['product_name']) ?></h3>
                        <p class="review-summary"><?= htmlspecialchars($review['summary']) ?></p>
                        
                        <div class="review-rating">
                            <div class="stars">
                                <?php
                                $rating = $review['rating'];
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating ? '★' : '☆';
                                }
                                ?>
                            </div>
                            <span class="rating-text"><?= $review['rating'] ?>/5</span>
                        </div>
                        
                        <div class="review-highlights">
                            <div class="pros-cons pros">
                                ✓ <?= $review['pros_count'] ?> <?= $lang === 'fr' ? 'points forts' : 'pros' ?>
                            </div>
                            <div class="pros-cons cons">
                                ✗ <?= $review['cons_count'] ?> <?= $lang === 'fr' ? 'points faibles' : 'cons' ?>
                            </div>
                            <div><?= date('d/m/Y', strtotime($review['review_date'])) ?></div>
                        </div>
                        
                        <div class="review-price">
                            <span class="current-price">€<?= number_format($review['price'], 2) ?></span>
                            <?php if ($review['original_price'] > $review['price']): ?>
                                <span class="original-price">€<?= number_format($review['original_price'], 2) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="review-actions">
                            <a href="review-detail.php?id=<?= $review['id'] ?>&lang=<?= $lang ?>" 
                               class="btn btn-secondary">
                                <?= $lang === 'fr' ? 'Lire le test' : 'Read review' ?>
                            </a>
                            
                            <a href="<?= htmlspecialchars($review['amazon_url']) ?>" 
                               class="btn btn-primary" target="_blank" rel="noopener">
                                <?= $lang === 'fr' ? 'Acheter' : 'Buy now' ?>
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
                    <a href="?category=<?= $selected_category ?>&sort=<?= $sort ?>&page=<?= $page - 1 ?>&lang=<?= $lang ?>">&laquo; <?= $lang === 'fr' ? 'Précédent' : 'Previous' ?></a>
                <?php else: ?>
                    <span class="disabled">&laquo; <?= $lang === 'fr' ? 'Précédent' : 'Previous' ?></span>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?category=<?= $selected_category ?>&sort=<?= $sort ?>&page=<?= $i ?>&lang=<?= $lang ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?category=<?= $selected_category ?>&sort=<?= $sort ?>&page=<?= $page + 1 ?>&lang=<?= $lang ?>"><?= $lang === 'fr' ? 'Suivant' : 'Next' ?> &raquo;</a>
                <?php else: ?>
                    <span class="disabled"><?= $lang === 'fr' ? 'Suivant' : 'Next' ?> &raquo;</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php 
// Include newsletter et footer
include 'includes/layouts/newsletter.php';
include 'includes/layouts/footer.php';
?>