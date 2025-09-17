<?php
/**
 * TechEssentials Pro - Template Page d'Accueil
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 */

// Empêcher l'accès direct
if (!defined('TECHESSENTIALS_PRO')) {
    die('Direct access not allowed');
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    <?= Language::get('hero.title') ?>
                </h1>
                <p class="hero-subtitle">
                    <?= Language::get('hero.subtitle') ?>
                </p>
                <div class="hero-actions">
                    <a href="<?= url('reviews') ?>" class="btn btn-primary btn-lg">
                        <?= Language::get('hero.cta_primary') ?>
                    </a>
                    <a href="#newsletter" class="btn btn-secondary btn-lg">
                        <?= Language::get('hero.cta_secondary') ?>
                    </a>
                </div>
            </div>
            <div class="hero-image">
                <img src="<?= asset('images/hero-workspace.webp') ?>" 
                     alt="Remote workspace setup"
                     width="600" height="400"
                     loading="eager">
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number"><?= number_format($site_stats['reviews']) ?></div>
                <div class="stat-label"><?= Language::get('stats.reviews') ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= number_format($site_stats['subscribers']) ?>+</div>
                <div class="stat-label"><?= Language::get('stats.subscribers') ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= number_format($site_stats['articles']) ?></div>
                <div class="stat-label"><?= Language::get('stats.articles') ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= $site_stats['avg_rating'] ?>/5</div>
                <div class="stat-label"><?= Language::get('stats.rating') ?></div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="section-header">
            <h2><?= Language::get('features.title') ?></h2>
        </div>
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3><?= Language::get('features.expert_reviews.title') ?></h3>
                <p><?= Language::get('features.expert_reviews.description') ?></p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <h3><?= Language::get('features.real_deals.title') ?></h3>
                <p><?= Language::get('features.real_deals.description') ?></p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3><?= Language::get('features.community_driven.title') ?></h3>
                <p><?= Language::get('features.community_driven.description') ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Reviews Section -->
<?php if (!empty($featured_reviews)): ?>
<section class="featured-reviews-section">
    <div class="container">
        <div class="section-header">
            <h2><?= Language::get('reviews_section.title') ?></h2>
            <a href="<?= url('reviews') ?>" class="view-all-link">
                <?= Language::get('reviews_section.view_all') ?>
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        <div class="reviews-grid">
            <?php foreach ($featured_reviews as $review): ?>
            <article class="review-card">
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
    </div>
</section>
<?php endif; ?>

<!-- Blog Posts Section -->
<?php if (!empty($recent_posts)): ?>
<section class="blog-posts-section">
    <div class="container">
        <div class="section-header">
            <h2><?= Language::get('blog_section.title') ?></h2>
            <a href="<?= url('blog') ?>" class="view-all-link">
                <?= Language::get('blog_section.view_all') ?>
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        <div class="blog-posts-grid">
            <?php foreach ($recent_posts as $post): ?>
            <article class="blog-post-card">
                <div class="post-image">
                    <a href="<?= url('blog/' . $post['slug']) ?>">
                        <img src="<?= $post['featured_image'] ?>" 
                             alt="<?= clean($post['title']) ?>"
                             width="300" height="200"
                             loading="lazy">
                    </a>
                </div>
                
                <div class="post-content">
                    <div class="post-meta">
                        <time datetime="<?= date('c', strtotime($post['created_at'])) ?>">
                            <?= timeAgo($post['created_at']) ?>
                        </time>
                        <?php if ($post['reading_time']): ?>
                        <span class="reading-time">
                            <?= $post['reading_time'] ?> <?= Language::get('blog_section.reading_time') ?>
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <h3 class="post-title">
                        <a href="<?= url('blog/' . $post['slug']) ?>">
                            <?= clean($post['title']) ?>
                        </a>
                    </h3>
                    
                    <p class="post-excerpt"><?= clean($post['excerpt']) ?></p>
                    
                    <a href="<?= url('blog/' . $post['slug']) ?>" class="read-more-link">
                        <?= Language::get('blog_section.read_more') ?>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Trust Badges Section -->
<section class="trust-badges-section">
    <div class="container">
        <h2 class="section-title"><?= Language::get('trust_badges.title') ?></h2>
        <div class="trust-badges-grid">
            <div class="trust-badge">
                <div class="badge-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3><?= Language::get('trust_badges.transparent') ?></h3>
            </div>
            <div class="trust-badge">
                <div class="badge-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <h3><?= Language::get('trust_badges.tested') ?></h3>
            </div>
            <div class="trust-badge">
                <div class="badge-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3><?= Language::get('trust_badges.community') ?></h3>
            </div>
            <div class="trust-badge">
                <div class="badge-icon">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <h3><?= Language::get('trust_badges.deals') ?></h3>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Upgrade Your Workspace?</h2>
            <p>Join thousands of remote workers who trust our recommendations.</p>
            <div class="cta-actions">
                <a href="<?= url('reviews') ?>" class="btn btn-white btn-lg">Browse Reviews</a>
                <a href="<?= url('deals') ?>" class="btn btn-outline-white btn-lg">View Deals</a>
            </div>
        </div>
    </div>
</section>

<!-- Données structurées JSON-LD pour la page d'accueil -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "<?= Language::get('site.title') ?>",
    "description": "<?= Language::get('site.description') ?>",
    "url": "<?= url() ?>",
    "logo": "<?= asset('images/logo.png') ?>",
    "sameAs": [
        "https://twitter.com/techessentials",
        "https://www.youtube.com/c/techessentials"
    ],
    "contactPoint": {
        "@type": "ContactPoint",
        "contactType": "customer service",
        "url": "<?= url('contact') ?>"
    }
}
</script>