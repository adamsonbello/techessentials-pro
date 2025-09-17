<?php
/**
 * TechEssentials Pro - Template Liste Reviews
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 */

// Empêcher l'accès direct
if (!defined('TECHESSENTIALS_PRO')) {
    die('Direct access not allowed');
}
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="page-header-content">
            <h1><?= Language::get('meta.reviews.title') ?></h1>
            <p><?= Language::get('meta.reviews.description') ?></p>
        </div>
        
        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="<?= url() ?>"><?= Language::get('navigation.home') ?></a></li>
                <li aria-current="page"><?= Language::get('navigation.reviews') ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Filters Section -->
<section class="filters-section">
    <div class="container">
        <div class="filters-content">
            <div class="filters-left">
                <h3>Filter by Category</h3>
                <div class="filter-buttons">
                    <button class="filter-btn <?= empty($current_category) ? 'active' : '' ?>" 
                            data-filter="all">
                        All Categories
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
            
            <div class="filters-right">
                <div class="sort-dropdown">
                    <select name="sort" id="sort-reviews">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="rating">Highest Rated</option>
                        <option value="price-low">Price: Low to High</option>
                        <option value="price-high">Price: High to Low</option>
                    </select>
                </div>
                
                <div class="view-toggle">
                    <button class="view-btn active" data-view="grid">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M10 18H5V5h5v13zm9 0h-5V5h5v13zM19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2z"/>
                        </svg>
                    </button>
                    <button class="view-btn" data-view="list">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Reviews Grid -->
<section class="reviews-listing">
    <div class="container">
        <?php if (!empty($reviews)): ?>
        <div class="reviews-grid" id="reviews-grid">
            <?php foreach ($reviews as $review): ?>
            <article class="review-card filterable-item" data-category="<?= $review['category'] ?>">
                <?php if ($review['is_featured']): ?>
                <div class="featured-badge"><?= Language::get('reviews_section.featured') ?></div>
                <?php endif; ?>
                
                <div class="review-image">
                    <a href="<?= url('reviews/' . $review['slug']) ?>">
                        <img src="<?= $review['featured_image'] ?>" 
                             alt="<?= clean($review['title']) ?>"
                             width="300" height="200"
                             loading="lazy">
                    </a>
                    
                    <!-- Quick Actions -->
                    <div class="review-actions-overlay">
                        <button class="action-btn" data-compare="<?= $review['id'] ?>" title="Add to Compare">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </button>
                        <button class="action-btn" data-wishlist="<?= $review['id'] ?>" title="Add to Wishlist">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="review-content">
                    <div class="review-meta">
                        <span class="review-category"><?= ucfirst($review['category']) ?></span>
                        <div class="review-rating">
                            <div class="stars" data-rating="<?= $review['rating'] ?>">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?= $i <= $review['rating'] ? 'filled' : '' ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-number"><?= $review['rating'] ?></span>
                        </div>
                    </div>
                    
                    <h3 class="review-title">
                        <a href="<?= url('reviews/' . $review['slug']) ?>">
                            <?= clean($review['title']) ?>
                        </a>
                    </h3>
                    
                    <p class="review-excerpt"><?= clean($review['excerpt']) ?></p>
                    
                    <?php if (isset($review['price'])): ?>
                    <div class="review-price">
                        <?php if ($review['discount_price']): ?>
                        <span class="original-price">$<?= number_format($review['price'], 2) ?></span>
                        <span class="discount-price">$<?= number_format($review['discount_price'], 2) ?></span>
                        <span class="discount-badge">-<?= round((($review['price'] - $review['discount_price']) / $review['price']) * 100) ?>%</span>
                        <?php else: ?>
                        <span class="current-price">$<?= number_format($review['price'], 2) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="review-stats">
                        <span class="views-count">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <?= number_format($review['views']) ?> views
                        </span>
                        <span class="date">
                            <?= timeAgo($review['created_at']) ?>
                        </span>
                    </div>
                    
                    <div class="review-actions">
                        <a href="<?= url('reviews/' . $review['slug']) ?>" class="btn btn-outline">
                            <?= Language::get('reviews_section.read_review') ?>
                        </a>
                        <?php if ($review['affiliate_link']): ?>
                        <a href="<?= $review['affiliate_link'] ?>" 
                           class="btn btn-primary" 
                           target="_blank" 
                           rel="nofollow noopener">
                            <?= Language::get('reviews_section.check_price') ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <!-- Comparison Bar -->
        <div class="comparison-bar" id="comparison-bar" style="display: none;">
            <div class="container">
                <div class="comparison-content">
                    <span class="comparison-count">0 products selected</span>
                    <button class="btn btn-primary" id="compare-button" disabled>
                        Compare Products
                    </button>
                    <button class="btn btn-outline" id="clear-comparison">
                        Clear All
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Pagination -->
        <?php if (isset($total_pages) && $total_pages > 1): ?>
        <nav class="pagination-nav">
            <?= generatePagination($current_page, $total_pages, url('reviews')) ?>
        </nav>
        <?php endif; ?>
        
        <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-state-content">
                <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <h3>No Reviews Found</h3>
                <p>We couldn't find any reviews matching your criteria.</p>
                <a href="<?= url('reviews') ?>" class="btn btn-primary">View All Reviews</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Newsletter CTA -->
<section class="newsletter-cta-section">
    <div class="container">
        <div class="newsletter-cta-content">
            <h2>Never Miss a Review</h2>
            <p>Get notified when we publish new product reviews and exclusive deals.</p>
            <form class="newsletter-inline-form" data-action="newsletter/subscribe" method="post">
                <input type="email" 
                       name="email" 
                       placeholder="Enter your email address"
                       required>
                <input type="hidden" name="language" value="<?= $current_lang ?>">
                <input type="hidden" name="source" value="reviews_page">
                <button type="submit" class="btn btn-primary">Subscribe</button>
            </form>
        </div>
    </div>
</section>

<!-- Données structurées pour les reviews -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": "<?= Language::get('meta.reviews.title') ?>",
    "description": "<?= Language::get('meta.reviews.description') ?>",
    "url": "<?= url('reviews') ?>",
    "mainEntity": {
        "@type": "ItemList",
        "numberOfItems": <?= count($reviews) ?>,
        "itemListElement": [
            <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $index => $review): ?>
            {
                "@type": "ListItem",
                "position": <?= $index + 1 ?>,
                "item": {
                    "@type": "Review",
                    "name": "<?= clean($review['title']) ?>",
                    "url": "<?= url('reviews/' . $review['slug']) ?>",
                    "reviewRating": {
                        "@type": "Rating",
                        "ratingValue": <?= $review['rating'] ?>,
                        "bestRating": 5
                    }
                }
            }<?= $index < count($reviews) - 1 ? ',' : '' ?>
            <?php endforeach; ?>
            <?php endif; ?>
        ]
    }
}
</script>

<style>
.page-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    padding: var(--space-16) 0 var(--space-12);
}

.page-header-content {
    text-align: center;
    margin-bottom: var(--space-8);
}

.page-header h1 {
    font-size: var(--font-size-4xl);
    margin-bottom: var(--space-4);
}

.breadcrumb {
    display: flex;
    justify-content: center;
}

.breadcrumb ol {
    display: flex;
    list-style: none;
    gap: var(--space-4);
    margin: 0;
    padding: 0;
}

.breadcrumb li::after {
    content: '>';
    margin-left: var(--space-4);
    opacity: 0.7;
}

.breadcrumb li:last-child::after {
    display: none;
}

.breadcrumb a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
}

.breadcrumb a:hover {
    color: white;
}

.filters-section {
    background: var(--gray-50);
    padding: var(--space-8) 0;
    border-bottom: 1px solid var(--gray-200);
}

.filters-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: var(--space-6);
}

.filter-buttons {
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
}

.filter-btn.active,
.filter-btn:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.filters-right {
    display: flex;
    align-items: center;
    gap: var(--space-4);
}

.sort-dropdown select {
    padding: var(--space-2) var(--space-4);
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    background: white;
}

.view-toggle {
    display: flex;
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.view-btn {
    padding: var(--space-2) var(--space-3);
    border: none;
    background: white;
    cursor: pointer;
    transition: var(--transition);
}

.view-btn.active,
.view-btn:hover {
    background: var(--primary);
    color: white;
}

.reviews-listing {
    padding: var(--space-16) 0;
}

.review-actions-overlay {
    position: absolute;
    top: var(--space-4);
    right: var(--space-4);
    display: flex;
    gap: var(--space-2);
    opacity: 0;
    transition: var(--transition);
}

.review-card:hover .review-actions-overlay {
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

.comparison-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-top: 1px solid var(--gray-200);
    box-shadow: 0 -4px 12px rgba(0,0,0,0.1);
    z-index: 50;
    padding: var(--space-4) 0;
}

.comparison-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.newsletter-cta-section {
    background: var(--gray-900);
    color: white;
    padding: var(--space-16) 0;
    text-align: center;
}

.newsletter-inline-form {
    display: flex;
    justify-content: center;
    gap: var(--space-4);
    margin-top: var(--space-8);
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.newsletter-inline-form input[type="email"] {
    flex: 1;
    padding: var(--space-3) var(--space-4);
    border: 1px solid var(--gray-600);
    border-radius: var(--border-radius);
    background: var(--gray-800);
    color: white;
}

.empty-state {
    text-align: center;
    padding: var(--space-24) 0;
}

.empty-state-content {
    max-width: 400px;
    margin: 0 auto;
}

.empty-state svg {
    margin-bottom: var(--space-8);
    color: var(--gray-400);
}

@media (max-width: 768px) {
    .filters-content {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filters-right {
        justify-content: space-between;
    }
    
    .newsletter-inline-form {
        flex-direction: column;
    }
}
</style>