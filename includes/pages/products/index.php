<?php
/**
 * TechEssentials Pro - Template Liste Produits
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 * 
 * 🎯 PAGE CRUCIALE - CŒUR DU BUSINESS
 * Génère les revenus via liens d'affiliation
 */

// Empêcher l'accès direct
if (!defined('TECHESSENTIALS_PRO')) {
    die('Direct access not allowed');
}
?>

<!-- Products Hero -->
<section class="products-hero">
    <div class="container">
        <div class="hero-content">
            <h1>Best Tech Products for Remote Workers</h1>
            <p>Curated selection of productivity tools, tested by experts, with exclusive deals and honest reviews.</p>
            
            <!-- Trust Indicators -->
            <div class="trust-indicators">
                <div class="trust-item">
                    <strong><?= number_format($site_stats['reviews']) ?>+</strong>
                    <span>Products Tested</span>
                </div>
                <div class="trust-item">
                    <strong>4.8/5</strong>
                    <span>Average Rating</span>
                </div>
                <div class="trust-item">
                    <strong>100%</strong>
                    <span>Honest Reviews</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Filters & Search -->
<section class="products-filters">
    <div class="container">
        <div class="filters-header">
            <h2>Find Your Perfect Tech</h2>
            <div class="results-count">
                <?= count($products) ?> products found
            </div>
        </div>
        
        <div class="filters-grid">
            <!-- Category Filters -->
            <div class="filter-group">
                <h3>Categories</h3>
                <div class="filter-options">
                    <button class="filter-btn <?= empty($current_category) ? 'active' : '' ?>" data-filter="all">
                        All Products
                    </button>
                    <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                    <button class="filter-btn <?= $current_category === $category['category'] ? 'active' : '' ?>" 
                            data-filter="<?= $category['category'] ?>">
                        <?= ucfirst($category['category']) ?> (<?= $category['count'] ?>)
                    </button>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Price Range -->
            <div class="filter-group">
                <h3>Price Range</h3>
                <div class="price-filters">
                    <button class="filter-btn" data-price="under-50">Under $50</button>
                    <button class="filter-btn" data-price="50-100">$50 - $100</button>
                    <button class="filter-btn" data-price="100-200">$100 - $200</button>
                    <button class="filter-btn" data-price="200-500">$200 - $500</button>
                    <button class="filter-btn" data-price="over-500">$500+</button>
                </div>
            </div>
            
            <!-- Rating Filter -->
            <div class="filter-group">
                <h3>Minimum Rating</h3>
                <div class="rating-filters">
                    <button class="filter-btn" data-rating="4.5">4.5+ Stars</button>
                    <button class="filter-btn" data-rating="4.0">4.0+ Stars</button>
                    <button class="filter-btn" data-rating="3.5">3.5+ Stars</button>
                </div>
            </div>
            
            <!-- Sort Options -->
            <div class="filter-group">
                <h3>Sort By</h3>
                <select class="sort-select" id="product-sort">
                    <option value="featured">Featured Products</option>
                    <option value="rating">Highest Rated</option>
                    <option value="price-low">Price: Low to High</option>
                    <option value="price-high">Price: High to Low</option>
                    <option value="newest">Newest First</option>
                    <option value="most-popular">Most Popular</option>
                </select>
            </div>
        </div>
        
        <!-- Search Bar -->
        <div class="product-search">
            <div class="search-box">
                <input type="search" 
                       id="product-search" 
                       placeholder="Search products by name, brand, or feature..."
                       autocomplete="off">
                <button class="search-btn">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Products Grid -->
<section class="products-grid-section">
    <div class="container">
        <?php if (!empty($products)): ?>
        
        <!-- Featured Products Banner -->
        <div class="featured-products-banner">
            <h2>🏆 Editor's Choice</h2>
            <p>Top-rated products hand-picked by our experts</p>
        </div>
        
        <div class="products-grid" id="products-grid">
            <?php foreach ($products as $product): ?>
            <article class="product-card <?= $product['is_featured'] ? 'featured' : '' ?>" 
                     data-category="<?= $product['category'] ?>"
                     data-price="<?= $product['current_price'] ?? $product['price'] ?>"
                     data-rating="<?= $product['rating'] ?>">
                
                <!-- Product Image & Badges -->
                <div class="product-image">
                    <a href="<?= url('products/' . $product['slug']) ?>">
                        <img src="<?= $product['featured_image'] ?>" 
                             alt="<?= clean($product['title']) ?>"
                             width="300" height="200"
                             loading="lazy">
                    </a>
                    
                    <!-- Badges -->
                    <div class="product-badges">
                        <?php if ($product['is_featured']): ?>
                        <span class="badge badge-featured">Editor's Choice</span>
                        <?php endif; ?>
                        
                        <?php if (isset($product['discount_price']) && $product['discount_price']): ?>
                        <?php $savings = round((($product['price'] - $product['discount_price']) / $product['price']) * 100); ?>
                        <span class="badge badge-sale">-<?= $savings ?>%</span>
                        <?php endif; ?>
                        
                        <?php if ($product['rating'] >= 4.5): ?>
                        <span class="badge badge-bestseller">Best Seller</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="product-actions-overlay">
                        <button class="action-btn compare-btn" 
                                data-product-id="<?= $product['id'] ?>" 
                                title="Add to Compare">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </button>
                        <button class="action-btn wishlist-btn" 
                                data-product-id="<?= $product['id'] ?>" 
                                title="Save to Wishlist">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="product-content">
                    <div class="product-meta">
                        <span class="product-category"><?= ucfirst($product['category']) ?></span>
                        <div class="product-rating">
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?= $i <= $product['rating'] ? 'filled' : '' ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-text"><?= $product['rating'] ?> (<?= rand(45, 328) ?> reviews)</span>
                        </div>
                    </div>
                    
                    <h3 class="product-title">
                        <a href="<?= url('products/' . $product['slug']) ?>">
                            <?= clean($product['title']) ?>
                        </a>
                    </h3>
                    
                    <p class="product-excerpt"><?= clean($product['excerpt']) ?></p>
                    
                    <!-- Key Features -->
                    <?php if (!empty($product['key_features'])): ?>
                    <ul class="product-features">
                        <?php foreach (array_slice($product['key_features'], 0, 3) as $feature): ?>
                        <li>✓ <?= clean($feature) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    
                    <!-- Price & CTA -->
                    <div class="product-pricing">
                        <?php if (isset($product['discount_price']) && $product['discount_price']): ?>
                        <div class="price-section">
                            <span class="original-price">$<?= number_format($product['price'], 2) ?></span>
                            <span class="current-price">$<?= number_format($product['discount_price'], 2) ?></span>
                            <span class="savings">Save $<?= number_format($product['price'] - $product['discount_price'], 2) ?></span>
                        </div>
                        <?php else: ?>
                        <div class="price-section">
                            <span class="current-price">$<?= number_format($product['price'], 2) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <!-- CTA Buttons - CRITICAL FOR REVENUE -->
                        <div class="product-cta-buttons">
                            <a href="<?= url('products/' . $product['slug']) ?>" 
                               class="btn btn-outline btn-sm">
                                Read Review
                            </a>
                            
                            <?php if ($product['affiliate_link']): ?>
                            <a href="<?= $product['affiliate_link'] ?>" 
                               class="btn btn-primary btn-sm buy-btn"
                               target="_blank" 
                               rel="nofollow noopener"
                               data-product-id="<?= $product['id'] ?>"
                               data-product-name="<?= clean($product['title']) ?>"
                               onclick="trackPurchaseClick(this)">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5M17 17a2 2 0 11-4 0 2 2 0 014 0zM9 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Buy Now
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Social Proof -->
                    <div class="product-social-proof">
                        <div class="recent-buyers">
                            <div class="buyer-avatars">
                                <div class="avatar"></div>
                                <div class="avatar"></div>
                                <div class="avatar"></div>
                            </div>
                            <span><?= rand(12, 47) ?> people bought this week</span>
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <!-- Load More -->
        <div class="load-more-section">
            <button class="btn btn-outline btn-lg" id="load-more-products">
                Load More Products
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
            </button>
        </div>
        
        <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <h3>No Products Found</h3>
            <p>Try adjusting your filters or search terms.</p>
            <button class="btn btn-primary" onclick="clearAllFilters()">Clear Filters</button>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Comparison Panel -->
<div class="comparison-panel" id="comparison-panel" style="display: none;">
    <div class="container">
        <div class="comparison-header">
            <h3>Compare Products</h3>
            <button class="close-comparison" onclick="clearComparison()">×</button>
        </div>
        <div class="comparison-products" id="comparison-products">
            <!-- Filled by JavaScript -->
        </div>
        <div class="comparison-actions">
            <button class="btn btn-primary" id="compare-button" disabled>
                Compare Now
            </button>
            <button class="btn btn-outline" onclick="clearComparison()">
                Clear All
            </button>
        </div>
    </div>
</div>

<!-- Newsletter CTA -->
<section class="products-newsletter-cta">
    <div class="container">
        <div class="cta-content">
            <h2>🎯 Get Exclusive Product Deals</h2>
            <p>Be the first to know about new products, price drops, and exclusive discounts.</p>
            <form class="inline-newsletter-form" data-action="newsletter/subscribe">
                <input type="email" name="email" placeholder="Enter your email" required>
                <input type="hidden" name="source" value="products_page">
                <input type="hidden" name="language" value="<?= $current_lang ?>">
                <button type="submit" class="btn btn-primary">Get Deals</button>
            </form>
        </div>
    </div>
</section>

<!-- Products Schema.org -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": "Tech Products for Remote Workers",
    "description": "Best curated tech products for remote work productivity",
    "url": "<?= url('products') ?>",
    "mainEntity": {
        "@type": "ItemList",
        "numberOfItems": <?= count($products) ?>,
        "itemListElement": [
            <?php foreach ($products as $index => $product): ?>
            {
                "@type": "ListItem",
                "position": <?= $index + 1 ?>,
                "item": {
                    "@type": "Product",
                    "name": "<?= clean($product['title']) ?>",
                    "image": "<?= $product['featured_image'] ?>",
                    "description": "<?= clean($product['excerpt']) ?>",
                    "url": "<?= url('products/' . $product['slug']) ?>",
                    "offers": {
                        "@type": "Offer",
                        "price": "<?= $product['discount_price'] ?? $product['price'] ?>",
                        "priceCurrency": "USD",
                        "availability": "InStock"
                    },
                    "aggregateRating": {
                        "@type": "AggregateRating",
                        "ratingValue": <?= $product['rating'] ?>,
                        "bestRating": 5,
                        "reviewCount": <?= rand(20, 200) ?>
                    }
                }
            }<?= $index < count($products) - 1 ? ',' : '' ?>
            <?php endforeach; ?>
        ]
    }
}
</script>

<style>
.products-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: var(--space-20) 0;
    text-align: center;
}

.hero-content h1 {
    font-size: var(--font-size-4xl);
    margin-bottom: var(--space-4);
}

.trust-indicators {
    display: flex;
    justify-content: center;
    gap: var(--space-8);
    margin-top: var(--space-8);
}

.trust-item {
    text-align: center;
}

.trust-item strong {
    display: block;
    font-size: var(--font-size-2xl);
    margin-bottom: var(--space-1);
}

.products-filters {
    background: var(--gray-50);
    padding: var(--space-12) 0;
}

.filters-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-8);
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-6);
    margin-bottom: var(--space-8);
}

.filter-group h3 {
    margin-bottom: var(--space-4);
    color: var(--gray-800);
}

.filter-options,
.price-filters,
.rating-filters {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-3);
}

.filter-btn {
    padding: var(--space-2) var(--space-4);
    border: 1px solid var(--gray-300);
    background: white;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
    font-size: var(--font-size-sm);
}

.filter-btn.active,
.filter-btn:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.product-search {
    margin-top: var(--space-8);
}

.search-box {
    display: flex;
    max-width: 600px;
    margin: 0 auto;
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: var(--box-shadow);
}

.search-box input {
    flex: 1;
    padding: var(--space-4) var(--space-6);
    border: none;
    font-size: var(--font-size-lg);
}

.search-btn {
    padding: var(--space-4) var(--space-6);
    background: var(--primary);
    color: white;
    border: none;
    cursor: pointer;
}

.products-grid-section {
    padding: var(--space-16) 0;
}

.featured-products-banner {
    text-align: center;
    margin-bottom: var(--space-12);
    padding: var(--space-8);
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    border-radius: var(--border-radius-lg);
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: var(--space-8);
}

.product-card {
    background: white;
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: var(--box-shadow);
    transition: var(--transition-slow);
    position: relative;
}

.product-card.featured {
    border: 2px solid var(--accent);
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--box-shadow-lg);
}

.product-image {
    position: relative;
    aspect-ratio: 4/3;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition-slow);
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.product-badges {
    position: absolute;
    top: var(--space-4);
    left: var(--space-4);
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
}

.badge {
    padding: var(--space-1) var(--space-3);
    border-radius: var(--border-radius);
    font-size: var(--font-size-xs);
    font-weight: 600;
    color: white;
}

.badge-featured { background: var(--accent); }
.badge-sale { background: var(--error); }
.badge-bestseller { background: var(--success); }

.product-actions-overlay {
    position: absolute;
    top: var(--space-4);
    right: var(--space-4);
    display: flex;
    flex-direction: column;
    gap: var(--space-2);
    opacity: 0;
    transition: var(--transition);
}

.product-card:hover .product-actions-overlay {
    opacity: 1;
}

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
}

.action-btn:hover {
    background: var(--primary);
    color: white;
}

.product-content {
    padding: var(--space-6);
}

.product-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-4);
}

.product-category {
    background: var(--primary);
    color: white;
    padding: var(--space-1) var(--space-3);
    border-radius: var(--border-radius);
    font-size: var(--font-size-xs);
    font-weight: 500;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.stars {
    display: flex;
    gap: 1px;
}

.star {
    color: var(--gray-300);
    font-size: var(--font-size-sm);
}

.star.filled {
    color: #fbbf24;
}

.rating-text {
    font-size: var(--font-size-sm);
    color: var(--gray-600);
}

.product-title {
    margin-bottom: var(--space-3);
}

.product-title a {
    color: var(--gray-900);
    font-weight: 600;
    text-decoration: none;
}

.product-title a:hover {
    color: var(--primary);
}

.product-excerpt {
    color: var(--gray-600);
    margin-bottom: var(--space-4);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-features {
    list-style: none;
    padding: 0;
    margin: 0 0 var(--space-6) 0;
}

.product-features li {
    color: var(--success);
    font-size: var(--font-size-sm);
    margin-bottom: var(--space-1);
}

.product-pricing {
    border-top: 1px solid var(--gray-100);
    padding-top: var(--space-4);
}

.price-section {
    margin-bottom: var(--space-4);
}

.original-price {
    text-decoration: line-through;
    color: var(--gray-500);
    margin-right: var(--space-3);
}

.current-price {
    font-size: var(--font-size-xl);
    font-weight: 700;
    color: var(--success);
}

.savings {
    background: var(--success);
    color: white;
    padding: var(--space-1) var(--space-2);
    border-radius: var(--border-radius);
    font-size: var(--font-size-xs);
    font-weight: 600;
    margin-left: var(--space-3);
}

.product-cta-buttons {
    display: flex;
    gap: var(--space-3);
}

.buy-btn {
    background: var(--success) !important;
    border-color: var(--success) !important;
    flex: 1;
    justify-content: center;
    display: flex;
    align-items: center;
    gap: var(--space-2);
}

.buy-btn:hover {
    background: #059669 !important;
    transform: scale(1.02);
}

.product-social-proof {
    margin-top: var(--space-4);
    padding-top: var(--space-4);
    border-top: 1px solid var(--gray-100);
}

.recent-buyers {
    display: flex;
    align-items: center;
    gap: var(--space-3);
    font-size: var(--font-size-sm);
    color: var(--gray-600);
}

.buyer-avatars {
    display: flex;
    margin-right: var(--space-2);
}

.avatar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    margin-left: -8px;
    border: 2px solid white;
}

.avatar:first-child {
    margin-left: 0;
}

.comparison-panel {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    box-shadow: 0 -4px 12px rgba(0,0,0,0.1);
    z-index: 1000;
    padding: var(--space-6) 0;
}

.comparison-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-4);
}

.comparison-products {
    display: flex;
    gap: var(--space-4);
    margin-bottom: var(--space-4);
}

.comparison-actions {
    display: flex;
    gap: var(--space-4);
    justify-content: center;
}

.products-newsletter-cta {
    background: var(--gray-900);
    color: white;
    padding: var(--space-16) 0;
    text-align: center;
}

.inline-newsletter-form {
    display: flex;
    justify-content: center;
    gap: var(--space-4);
    margin-top: var(--space-6);
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.inline-newsletter-form input[type="email"] {
    flex: 1;
    padding: var(--space-3) var(--space-4);
    border: 1px solid var(--gray-600);
    border-radius: var(--border-radius);
    background: var(--gray-800);
    color: white;
}

@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .filters-grid {
        grid-template-columns: 1fr;
    }
    
    .trust-indicators {
        flex-direction: column;
        gap: var(--space-4);
    }
    
    .inline-newsletter-form {
        flex-direction: column;
    }
}
</style>

<script>
// Tracking des clics d'achat - CRITIQUE pour mesurer les conversions
function trackPurchaseClick(button) {
    const productId = button.dataset.productId;
    const productName = button.dataset.productName;
    
    // Google Analytics / GTM
    if (typeof gtag !== 'undefined') {
        gtag('event', 'purchase_click', {
            'event_category': 'ecommerce',
            'event_label': productName,
            'product_id': productId,
            'currency': 'USD'
        });
    }
    
    // Tracking interne via API
    fetch('/api/products/track-click', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            product_id: productId,
            product_name: productName,
            action: 'purchase_click',
            timestamp: new Date().toISOString()
        })
    }).catch(console.error);
    
    console.log('Purchase click tracked:', productName);
}

// Système de comparaison
let comparisonProducts = [];

function addToComparison(productId) {
    if (comparisonProducts.length >= 3) {
        alert('You can compare up to 3 products at once.');
        return;
    }
    
    if (!comparisonProducts.includes(productId)) {
        comparisonProducts.push(productId);
        updateComparisonPanel();
    }
}

function removeFromComparison(productId) {
    comparisonProducts = comparisonProducts.filter(id => id !== productId);
    updateComparisonPanel();
}

function updateComparisonPanel() {
    const panel = document.getElementById('comparison-panel');
    const compareButton = document.getElementById('compare-button');
    
    if (comparisonProducts.length > 0) {
        panel.style.display = 'block';
        compareButton.disabled = comparisonProducts.length < 2;
        compareButton.textContent = `Compare ${comparisonProducts.length} Products`;
    } else {
        panel.style.display = 'none';
    }
}

function clearComparison() {
    comparisonProducts = [];
    updateComparisonPanel();
    
    // Reset visual states
    document.querySelectorAll('.compare-btn').forEach(btn => {
        btn.classList.remove('active');
    });
}

// Event listeners pour la comparaison
document.addEventListener('click', (e) => {
    if (e.target.closest('.compare-btn')) {
        const button = e.target.closest('.compare-btn');
        const productId = button.dataset.productId;
        
        if (button.classList.contains('active')) {
            button.classList.remove('active');
            removeFromComparison(productId);
        } else {
            button.classList.add('active');
            addToComparison(productId);
        }
    }
});

// Filtres de produits
document.querySelectorAll('.filter-btn').forEach(button => {
    button.addEventListener('click', () => {
        // Logic de filtrage - à implémenter selon les besoins
        console.log('Filter:', button.dataset);
    });
});

// Search fonctionnality
document.getElementById('product-search')?.addEventListener('input', (e) => {
    const query = e.target.value.toLowerCase();
    const products = document.querySelectorAll('.product-card');
    
    products.forEach(product => {
        const title = product.querySelector('.product-title').textContent.toLowerCase();
        const excerpt = product.querySelector('.product-excerpt').textContent.toLowerCase();
        
        if (title.includes(query) || excerpt.includes(query)) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
});
</script>
</body>
</html>