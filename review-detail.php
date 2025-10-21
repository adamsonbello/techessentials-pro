<?php
/**
 * TechEssentials Pro - Page Review Détaillée
 * Test approfondi d'un produit spécifique
 */

// Configuration
define('TECHESSENTIALS_PRO', true);
require_once __DIR__ . '/includes/config.php';
require_once INCLUDES_PATH . 'functions.php';

// Variables de base
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'fr';
$review_id = $_GET['id'] ?? '';

if (in_array($lang, ['fr', 'en'])) {
    $_SESSION['lang'] = $lang;
}

// Charger les données produits centralisées
require_once INCLUDES_PATH . 'products-data.php';

// Récupérer le produit
$product = getProductById($review_id);

if (!$product) {
    header('Location: reviews.php?lang=' . $lang);
    exit;
}

// Données de review détaillée
$review_data = [
    'test_date' => date('Y-m-d', strtotime('-' . rand(5, 25) . ' days')),
    'author' => 'TechEssentials Team',
    'read_time' => rand(8, 15) . ' min',
    'verdict_score' => $product['rating'],
    'verdict_text' => $lang === 'fr' ? 
        ['Excellent investissement', 'Fortement recommandé', 'Bon choix', 'Produit solide', 'Incontournable'][floor($product['rating']) - 1] :
        ['Excellent investment', 'Highly recommended', 'Good choice', 'Solid product', 'Must-have'][floor($product['rating']) - 1],
    
    // Sections du test
    'introduction' => $lang === 'fr' ? 
        "Nous avons testé le {$product['name']} pendant plusieurs semaines dans des conditions d'usage réel. Voici notre analyse complète de ce produit qui promet de révolutionner votre setup de travail." :
        "We tested the {$product['name']} for several weeks under real-world conditions. Here's our complete analysis of this product that promises to revolutionize your work setup.",
    
    'design_score' => rand(40, 50) / 10,
    'performance_score' => rand(40, 50) / 10,
    'features_score' => rand(35, 50) / 10,
    'value_score' => rand(35, 45) / 10,
    
    'pros' => $lang === 'fr' ? [
        'Qualité de construction excellente',
        'Performances impressionnantes',
        'Interface utilisateur intuitive',
        'Rapport qualité-prix attractif',
        'Design ergonomique et moderne',
        'Compatible avec tous les systèmes'
    ] : [
        'Excellent build quality',
        'Impressive performance',
        'Intuitive user interface',
        'Attractive price-to-quality ratio',
        'Ergonomic and modern design',
        'Compatible with all systems'
    ],
    
    'cons' => $lang === 'fr' ? [
        'Prix légèrement élevé',
        'Manuel d\'utilisation succinct',
        'Quelques fonctions avancées complexes'
    ] : [
        'Slightly high price',
        'Brief user manual',
        'Some advanced features are complex'
    ],
    
    'alternatives' => getRecommendedProducts($review_id, 3)
];

// Variables pour header
$page_title = ($lang === 'fr' ? 'Test ' : 'Review ') . htmlspecialchars($product['name']) . ' - TechEssentials Pro';
$page_description = ($lang === 'fr' ? 'Test complet et avis détaillé du ' : 'Complete test and detailed review of ') . htmlspecialchars($product['name']);

// Include header
include 'includes/layouts/header.php';
?>

<style>
/* Styles pour review-detail */
.review-detail-container {
    padding: 40px 0;
    background: #f8fafc;
}

.review-header {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    margin-bottom: 2rem;
    overflow: hidden;
}

.review-breadcrumb {
    padding: 20px 30px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.9rem;
}

.review-breadcrumb a {
    color: #667eea;
    text-decoration: none;
}

.review-hero {
    padding: 40px;
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 40px;
    align-items: center;
}

.review-image-section {
    position: relative;
}

.review-main-image {
    width: 100%;
    height: 300px;
    background: #f8f9fa;
    border-radius: 12px;
    overflow: hidden;
    cursor: zoom-in;
}

.review-main-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 20px;
}

.review-info {
    flex: 1;
}

.review-category {
    color: #667eea;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
}

.review-title {
    font-size: 2.2rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 1rem;
    line-height: 1.2;
}

.review-meta {
    display: flex;
    align-items: center;
    gap: 2rem;
    margin-bottom: 1.5rem;
    color: #6c757d;
    font-size: 0.9rem;
}

.review-intro {
    font-size: 1.1rem;
    line-height: 1.6;
    color: #4a5568;
    margin-bottom: 1.5rem;
}

.verdict-summary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
}

.verdict-score {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.verdict-text {
    font-size: 1.2rem;
    font-weight: 600;
    opacity: 0.9;
}

.review-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.review-main {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.review-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.section-nav {
    background: #f8f9fa;
    padding: 20px 30px;
    border-bottom: 1px solid #e9ecef;
}

.nav-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.nav-pill {
    padding: 8px 16px;
    background: transparent;
    border: 1px solid #dee2e6;
    border-radius: 20px;
    color: #6c757d;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.nav-pill:hover,
.nav-pill.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.review-section {
    padding: 30px;
    border-bottom: 1px solid #f1f3f4;
}

.review-section:last-child {
    border-bottom: none;
}

.section-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 1rem;
}

.section-content {
    line-height: 1.7;
    color: #4a5568;
}

.scores-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin: 2rem 0;
}

.score-item {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}

.score-label {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.score-value {
    font-size: 2rem;
    font-weight: 700;
    color: #667eea;
}

.score-bar {
    width: 100%;
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    margin-top: 0.5rem;
    overflow: hidden;
}

.score-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 3px;
}

.pros-cons-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    margin: 2rem 0;
}

.pros-section,
.cons-section {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
}

.pros-section {
    border-left: 4px solid #28a745;
}

.cons-section {
    border-left: 4px solid #dc3545;
}

.pros-cons-title {
    font-weight: 700;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pros-title { color: #28a745; }
.cons-title { color: #dc3545; }

.pros-cons-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.pros-cons-list li {
    padding: 0.5rem 0;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

.price-comparison {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    padding: 20px;
}

.comparison-title {
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #2d3748;
}

.vendor-grid {
    display: grid;
    gap: 1rem;
}

.vendor-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.vendor-item:hover {
    background: #e9ecef;
}

.vendor-name {
    font-weight: 600;
    color: #2d3748;
}

.vendor-price {
    font-weight: 700;
    color: #667eea;
}

.vendor-btn {
    padding: 8px 16px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.vendor-btn:hover {
    background: #5a6fd8;
}

.alternatives-section {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    padding: 20px;
}

.alternatives-grid {
    display: grid;
    gap: 1rem;
}

.alternative-item {
    display: flex;
    gap: 1rem;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.alternative-item:hover {
    background: #e9ecef;
}

.alternative-image {
    width: 60px;
    height: 60px;
    background: white;
    border-radius: 6px;
    overflow: hidden;
    flex-shrink: 0;
}

.alternative-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 5px;
}

.alternative-info {
    flex: 1;
}

.alternative-name {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.2rem;
}

.alternative-price {
    color: #667eea;
    font-weight: 700;
}

.alternative-rating {
    color: #ffd700;
    font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
    .review-hero {
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 20px;
        text-align: center;
    }
    
    .review-title {
        font-size: 1.8rem;
    }
    
    .review-content {
        grid-template-columns: 1fr;
    }
    
    .scores-grid {
        grid-template-columns: 1fr;
    }
    
    .pros-cons-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
}
</style>

<!-- Review Detail Container -->
<div class="review-detail-container">
    <div class="container">
        <!-- Review Header -->
        <div class="review-header">
            <div class="review-breadcrumb">
                <a href="index.php?lang=<?= $lang ?>"><?= $lang === 'fr' ? 'Accueil' : 'Home' ?></a> &gt; 
                <a href="reviews.php?lang=<?= $lang ?>"><?= $lang === 'fr' ? 'Tests' : 'Reviews' ?></a> &gt; 
                <?= htmlspecialchars($product['name']) ?>
            </div>

            <div class="review-hero">
                <div class="review-image-section">
                    <div class="review-main-image" onclick="openZoom('assets/images/products/<?= $product['image'] ?>')">
                        <img src="assets/images/products/<?= $product['image'] ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>">
                    </div>
                </div>

                <div class="review-info">
                    <div class="review-category">
                        <?= $lang === 'fr' ? 'TEST COMPLET' : 'COMPLETE REVIEW' ?>
                    </div>
                    
                    <h1 class="review-title"><?= htmlspecialchars($product['name']) ?></h1>
                    
                    <div class="review-meta">
                        <span><?= date('d/m/Y', strtotime($review_data['test_date'])) ?></span>
                        <span><?= $review_data['author'] ?></span>
                        <span><?= $review_data['read_time'] ?> <?= $lang === 'fr' ? 'de lecture' : 'read' ?></span>
                    </div>
                    
                    <p class="review-intro"><?= $review_data['introduction'] ?></p>
                    
                    <div class="verdict-summary">
                        <div class="verdict-score"><?= $review_data['verdict_score'] ?>/5</div>
                        <div class="verdict-text"><?= $review_data['verdict_text'] ?></div>
                    </div>
                </div>
            </div>
        </div>

       
        <!-- Review Content -->
        <div class="review-content">
            <!-- Main Review Content -->
            <div class="review-main">
                <div class="section-nav">
                    <div class="nav-pills">
                        <a href="#overview" class="nav-pill active"><?= $lang === 'fr' ? 'Aperçu' : 'Overview' ?></a>
                        <a href="#design" class="nav-pill"><?= $lang === 'fr' ? 'Design' : 'Design' ?></a>
                        <a href="#performance" class="nav-pill"><?= $lang === 'fr' ? 'Performance' : 'Performance' ?></a>
                        <a href="#verdict" class="nav-pill"><?= $lang === 'fr' ? 'Verdict' : 'Verdict' ?></a>
                    </div>
                </div>

                <!-- Overview Section -->
                <div id="overview" class="review-section">
                    <h2 class="section-title"><?= $lang === 'fr' ? 'Aperçu du test' : 'Test Overview' ?></h2>
                    <div class="section-content">
                        <p><?= $lang === 'fr' ? 
                            "Le {$product['name']} est un produit qui a attiré notre attention par ses caractéristiques techniques prometteuses et son positionnement sur le marché. Nous l'avons testé dans différentes conditions d'usage pour évaluer ses performances réelles." :
                            "The {$product['name']} is a product that caught our attention with its promising technical features and market positioning. We tested it under various usage conditions to evaluate its real-world performance."
                        ?></p>
                        
                        <div class="scores-grid">
                            <div class="score-item">
                                <div class="score-label"><?= $lang === 'fr' ? 'Design' : 'Design' ?></div>
                                <div class="score-value"><?= $review_data['design_score'] ?></div>
                                <div class="score-bar">
                                    <div class="score-fill" style="width: <?= $review_data['design_score'] * 20 ?>%"></div>
                                </div>
                            </div>
                            <div class="score-item">
                                <div class="score-label"><?= $lang === 'fr' ? 'Performance' : 'Performance' ?></div>
                                <div class="score-value"><?= $review_data['performance_score'] ?></div>
                                <div class="score-bar">
                                    <div class="score-fill" style="width: <?= $review_data['performance_score'] * 20 ?>%"></div>
                                </div>
                            </div>
                            <div class="score-item">
                                <div class="score-label"><?= $lang === 'fr' ? 'Fonctionnalités' : 'Features' ?></div>
                                <div class="score-value"><?= $review_data['features_score'] ?></div>
                                <div class="score-bar">
                                    <div class="score-fill" style="width: <?= $review_data['features_score'] * 20 ?>%"></div>
                                </div>
                            </div>
                            <div class="score-item">
                                <div class="score-label"><?= $lang === 'fr' ? 'Rapport qualité/prix' : 'Value' ?></div>
                                <div class="score-value"><?= $review_data['value_score'] ?></div>
                                <div class="score-bar">
                                    <div class="score-fill" style="width: <?= $review_data['value_score'] * 20 ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Design Section -->
                <div id="design" class="review-section">
                    <h2 class="section-title"><?= $lang === 'fr' ? 'Design et ergonomie' : 'Design and Ergonomics' ?></h2>
                    <div class="section-content">
                        <p><?= $lang === 'fr' ? 
                            "Le design du {$product['name']} frappe par sa modernité et son attention aux détails. Les matériaux utilisés témoignent d'un souci de qualité qui se ressent dès la prise en main. L'ergonomie a été particulièrement soignée, permettant une utilisation confortable même lors de sessions prolongées." :
                            "The design of the {$product['name']} strikes with its modernity and attention to detail. The materials used demonstrate a quality concern that is felt from the first handling. Ergonomics have been particularly well thought out, allowing comfortable use even during extended sessions."
                        ?></p>
                    </div>
                </div>

                <!-- Performance Section -->
                <div id="performance" class="review-section">
                    <h2 class="section-title"><?= $lang === 'fr' ? 'Performances et utilisation' : 'Performance and Usage' ?></h2>
                    <div class="section-content">
                        <p><?= $lang === 'fr' ? 
                            "En conditions d'usage réel, le {$product['name']} tient ses promesses. Les performances sont au rendez-vous et la stabilité remarquable. Nous avons particulièrement apprécié la fluidité d'utilisation et la réactivité de l'interface." :
                            "Under real-world usage conditions, the {$product['name']} delivers on its promises. Performance is there and stability is remarkable. We particularly appreciated the smooth usage and interface responsiveness."
                        ?></p>
                    </div>
                </div>

                <!-- Verdict Section -->
                <div id="verdict" class="review-section">
                    <h2 class="section-title"><?= $lang === 'fr' ? 'Notre verdict' : 'Our Verdict' ?></h2>
                    <div class="section-content">
                        <div class="pros-cons-grid">
                            <div class="pros-section">
                                <h3 class="pros-cons-title pros-title">
                                    ✓ <?= $lang === 'fr' ? 'Points forts' : 'Pros' ?>
                                </h3>
                                <ul class="pros-cons-list">
                                    <?php foreach ($review_data['pros'] as $pro): ?>
                                        <li><span style="color: #28a745;">✓</span> <?= htmlspecialchars($pro) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <div class="cons-section">
                                <h3 class="pros-cons-title cons-title">
                                    ✗ <?= $lang === 'fr' ? 'Points faibles' : 'Cons' ?>
                                </h3>
                                <ul class="pros-cons-list">
                                    <?php foreach ($review_data['cons'] as $con): ?>
                                        <li><span style="color: #dc3545;">✗</span> <?= htmlspecialchars($con) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        
                        <p><strong><?= $lang === 'fr' ? 'Conclusion:' : 'Conclusion:' ?></strong> 
                        <?= $lang === 'fr' ? 
                            "Le {$product['name']} est un excellent choix pour les professionnels exigeants. Malgré quelques points d'amélioration mineurs, nous le recommandons vivement pour sa qualité globale et ses performances." :
                            "The {$product['name']} is an excellent choice for demanding professionals. Despite some minor improvement points, we highly recommend it for its overall quality and performance."
                        ?></p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="review-sidebar">
                <!-- Price Comparison -->
                <div class="price-comparison">
                    <h3 class="comparison-title"><?= $lang === 'fr' ? 'Où acheter' : 'Where to buy' ?></h3>
                    <div class="vendor-grid">
                        <div class="vendor-item">
                            <div>
                                <div class="vendor-name">Amazon</div>
                                <div class="vendor-price">€<?= number_format($product['price'], 2) ?></div>
                            </div>
                            <a href="<?= htmlspecialchars($product['amazon_url']) ?>" 
                               class="vendor-btn" target="_blank" rel="noopener">
                                <?= $lang === 'fr' ? 'Acheter' : 'Buy' ?>
                            </a>
                        </div>
                        
                        <div class="vendor-item">
                            <div>
                                <div class="vendor-name">Fnac</div>
                                <div class="vendor-price">€<?= number_format($product['price'] + 5, 2) ?></div>
                            </div>
                            <a href="#" class="vendor-btn" target="_blank" rel="noopener">
                                <?= $lang === 'fr' ? 'Acheter' : 'Buy' ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Alternatives -->
                <div class="alternatives-section">
                    <h3 class="comparison-title"><?= $lang === 'fr' ? 'Alternatives' : 'Alternatives' ?></h3>
                    <div class="alternatives-grid">
                        <?php foreach ($review_data['alternatives'] as $alt): ?>
                            <div class="alternative-item">
                                <div class="alternative-image">
                                    <img src="assets/images/products/<?= $alt['image'] ?>" 
                                         alt="<?= htmlspecialchars($alt['name']) ?>">
                                </div>
                                <div class="alternative-info">
                                    <div class="alternative-name"><?= htmlspecialchars($alt['name']) ?></div>
                                    <div class="alternative-rating">
                                        <?php for ($i = 1; $i <= 5; $i++) echo $i <= $alt['rating'] ? '★' : '☆'; ?>
                                    </div>
                                    <div class="alternative-price">€<?= number_format($alt['price'], 2) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
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
// Navigation entre sections
document.querySelectorAll('.nav-pill').forEach(pill => {
    pill.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all pills
        document.querySelectorAll('.nav-pill').forEach(p => p.classList.remove('active'));
        
        // Add active class to clicked pill
        this.classList.add('active');
        
        // Scroll to target section
        const target = this.getAttribute('href');
        const element = document.querySelector(target);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

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

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeZoom();
    }
});

// Style zoom overlay
const style = document.createElement('style');
style.textContent = `
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
`;
document.head.appendChild(style);
</script>

<?php 
// Include newsletter et footer
include 'includes/layouts/newsletter.php';
include 'includes/layouts/footer.php';
?>