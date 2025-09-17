<?php
/**
 * TechEssentials Pro - Template Liste Blog
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
<section class="blog-header">
    <div class="container">
        <div class="blog-header-content">
            <h1><?= Language::get('meta.blog.title') ?></h1>
            <p><?= Language::get('meta.blog.description') ?></p>
        </div>
        
        <!-- Categories Filter -->
        <?php if (!empty($categories)): ?>
        <div class="blog-categories">
            <div class="categories-scroll">
                <button class="category-btn <?= empty($current_category) ? 'active' : '' ?>" data-category="all">
                    All Articles
                </button>
                <?php foreach ($categories as $category): ?>
                <button class="category-btn <?= $current_category === $category['category'] ? 'active' : '' ?>" 
                        data-category="<?= $category['category'] ?>">
                    <?= ucfirst($category['category']) ?> (<?= $category['count'] ?>)
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Featured Article -->
<?php if (!empty($articles) && $current_page === 1): ?>
<?php $featured = $articles[0]; ?>
<section class="featured-article">
    <div class="container">
        <div class="featured-content">
            <div class="featured-image">
                <a href="<?= url('blog/' . $featured['slug']) ?>">
                    <img src="<?= $featured['featured_image'] ?>" 
                         alt="<?= clean($featured['title']) ?>"
                         width="600" height="400"
                         loading="eager">
                </a>
            </div>
            <div class="featured-text">
                <div class="featured-meta">
                    <span class="featured-badge">Featured</span>
                    <time datetime="<?= date('c', strtotime($featured['created_at'])) ?>">
                        <?= timeAgo($featured['created_at']) ?>
                    </time>
                    <?php if ($featured['reading_time']): ?>
                    <span class="reading-time">
                        <?= $featured['reading_time'] ?> <?= Language::get('blog_section.reading_time') ?>
                    </span>
                    <?php endif; ?>
                </div>
                
                <h2 class="featured-title">
                    <a href="<?= url('blog/' . $featured['slug']) ?>">
                        <?= clean($featured['title']) ?>
                    </a>
                </h2>
                
                <p class="featured-excerpt"><?= clean($featured['excerpt']) ?></p>
                
                <div class="featured-actions">
                    <a href="<?= url('blog/' . $featured['slug']) ?>" class="btn btn-primary">
                        Read Full Article
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    
                    <div class="social-share">
                        <button class="share-btn" data-share="twitter" data-url="<?= url('blog/' . $featured['slug']) ?>" data-title="<?= clean($featured['title']) ?>">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </button>
                        <button class="share-btn" data-share="facebook" data-url="<?= url('blog/' . $featured['slug']) ?>">
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Articles Grid -->
<section class="blog-articles">
    <div class="container">
        <?php if (!empty($articles)): ?>
        <div class="articles-grid">
            <?php 
            // Skip first article if it's featured and we're on page 1
            $articles_to_show = ($current_page === 1 && count($articles) > 1) ? array_slice($articles, 1) : $articles;
            ?>
            
            <?php foreach ($articles_to_show as $article): ?>
            <article class="article-card filterable-item" data-category="<?= $article['category'] ?>">
                <div class="article-image">
                    <a href="<?= url('blog/' . $article['slug']) ?>">
                        <img src="<?= $article['featured_image'] ?>" 
                             alt="<?= clean($article['title']) ?>"
                             width="400" height="250"
                             loading="lazy">
                    </a>
                    <div class="article-category-badge">
                        <?= ucfirst($article['category']) ?>
                    </div>
                </div>
                
                <div class="article-content">
                    <div class="article-meta">
                        <time datetime="<?= date('c', strtotime($article['created_at'])) ?>">
                            <?= timeAgo($article['created_at']) ?>
                        </time>
                        <?php if ($article['reading_time']): ?>
                        <span class="reading-time">
                            <?= $article['reading_time'] ?> <?= Language::get('blog_section.reading_time') ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($article['views']): ?>
                        <span class="views-count">
                            <?= number_format($article['views']) ?> views
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <h3 class="article-title">
                        <a href="<?= url('blog/' . $article['slug']) ?>">
                            <?= clean($article['title']) ?>
                        </a>
                    </h3>
                    
                    <p class="article-excerpt"><?= clean($article['excerpt']) ?></p>
                    
                    <div class="article-footer">
                        <a href="<?= url('blog/' . $article['slug']) ?>" class="read-more-link">
                            <?= Language::get('blog_section.read_more') ?>
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        
                        <div class="article-actions">
                            <button class="action-btn" data-bookmark="<?= $article['id'] ?>" title="Bookmark">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                </svg>
                            </button>
                            <button class="action-btn" data-share="article" data-url="<?= url('blog/' . $article['slug']) ?>" title="Share">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        
        <!-- Load More / Pagination -->
        <?php if (isset($total_pages) && $total_pages > 1): ?>
        <div class="blog-pagination">
            <?php if ($current_page < $total_pages): ?>
            <button class="btn btn-outline btn-lg" id="load-more-articles" 
                    data-page="<?= $current_page + 1 ?>"
                    data-category="<?= $current_category ?>">
                Load More Articles
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                </svg>
            </button>
            <?php endif; ?>
            
            <!-- Traditional Pagination -->
            <nav class="pagination-nav">
                <?= generatePagination($current_page, $total_pages, url('blog')) ?>
            </nav>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-state-content">
                <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <h3>No Articles Found</h3>
                <p>We're working on some amazing content. Check back soon!</p>
                <a href="<?= url() ?>" class="btn btn-primary">Back to Home</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Newsletter Signup -->
<section class="blog-newsletter">
    <div class="container">
        <div class="newsletter-content">
            <div class="newsletter-text">
                <h2>Stay Updated with Latest Tech Insights</h2>
                <p>Get our weekly roundup of the best tech articles, reviews, and productivity tips delivered to your inbox.</p>
            </div>
            <form class="newsletter-form" data-action="newsletter/subscribe" method="post">
                <div class="form-group">
                    <input type="email" 
                           name="email" 
                           placeholder="Enter your email address"
                           required>
                    <input type="hidden" name="language" value="<?= $current_lang ?>">
                    <input type="hidden" name="source" value="blog_page">
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </div>
                <p class="privacy-notice">No spam, unsubscribe anytime.</p>
            </form>
        </div>
    </div>
</section>

<!-- Blog Schema.org -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Blog",
    "name": "<?= Language::get('meta.blog.title') ?>",
    "description": "<?= Language::get('meta.blog.description') ?>",
    "url": "<?= url('blog') ?>",
    "author": {
        "@type": "Organization",
        "name": "TechEssentials Pro"
    },
    "publisher": {
        "@type": "Organization",
        "name": "TechEssentials Pro",
        "logo": {
            "@type": "ImageObject",
            "url": "<?= asset('images/logo.png') ?>"
        }
    }
}
</script>

<style>
.blog-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    padding: var(--space-12) 0 var(--space-8);
    text-align: center;
}

.blog-header-content h1 {
    font-size: var(--font-size-4xl);
    margin-bottom: var(--space-4);
}

.blog-categories {
    margin-top: var(--space-8);
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.categories-scroll {
    display: flex;
    gap: var(--space-3);
    padding: var(--space-4) 0;
    min-width: max-content;
}

.category-btn {
    padding: var(--space-2) var(--space-4);
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
    white-space: nowrap;
}

.category-btn.active,
.category-btn:hover {
    background: white;
    color: var(--primary);
}

.featured-article {
    padding: var(--space-16) 0;
    background: var(--gray-50);
}

.featured-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-12);
    align-items: center;
}

.featured-image img {
    width: 100%;
    height: auto;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--box-shadow-lg);
}

.featured-meta {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    margin-bottom: var(--space-4);
    font-size: var(--font-size-sm);
    color: var(--gray-600);
}

.featured-badge {
    background: var(--accent);
    color: white;
    padding: var(--space-1) var(--space-3);
    border-radius: var(--border-radius);
    font-weight: 600;
    font-size: var(--font-size-xs);
}

.featured-title {
    font-size: var(--font-size-3xl);
    margin-bottom: var(--space-4);
}

.featured-title a {
    color: var(--gray-900);
}

.featured-excerpt {
    font-size: var(--font-size-lg);
    color: var(--gray-600);
    margin-bottom: var(--space-8);
    line-height: 1.6;
}

.featured-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.social-share {
    display: flex;
    gap: var(--space-2);
}

.share-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--gray-100);
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    color: var(--gray-600);
}

.share-btn:hover {
    background: var(--primary);
    color: white;
}

.blog-articles {
    padding: var(--space-16) 0;
}

.articles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: var(--space-8);
}

.article-card {
    background: white;
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: var(--box-shadow);
    transition: var(--transition-slow);
}

.article-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--box-shadow-lg);
}

.article-image {
    position: relative;
    aspect-ratio: 16/10;
    overflow: hidden;
}

.article-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition-slow);
}

.article-card:hover .article-image img {
    transform: scale(1.05);
}

.article-category-badge {
    position: absolute;
    top: var(--space-4);
    left: var(--space-4);
    background: var(--primary);
    color: white;
    padding: var(--space-1) var(--space-3);
    border-radius: var(--border-radius);
    font-size: var(--font-size-xs);
    font-weight: 600;
}

.article-content {
    padding: var(--space-6);
}

.article-meta {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    margin-bottom: var(--space-4);
    font-size: var(--font-size-sm);
    color: var(--gray-600);
}

.article-title {
    margin-bottom: var(--space-3);
}

.article-title a {
    color: var(--gray-900);
    font-weight: 600;
}

.article-title a:hover {
    color: var(--primary);
}

.article-excerpt {
    color: var(--gray-600);
    margin-bottom: var(--space-6);
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.article-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.article-actions {
    display: flex;
    gap: var(--space-2);
}

.action-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--gray-100);
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    color: var(--gray-500);
}

.action-btn:hover {
    background: var(--primary);
    color: white;
}

.blog-pagination {
    text-align: center;
    margin-top: var(--space-16);
}

#load-more-articles {
    margin-bottom: var(--space-8);
}

.blog-newsletter {
    background: var(--gray-900);
    color: white;
    padding: var(--space-16) 0;
}

.newsletter-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-12);
    align-items: center;
}

.newsletter-form .form-group {
    display: flex;
    gap: var(--space-4);
    margin-bottom: var(--space-4);
}

.newsletter-form input[type="email"] {
    flex: 1;
    padding: var(--space-3) var(--space-4);
    border: 1px solid var(--gray-600);
    border-radius: var(--border-radius);
    background: var(--gray-800);
    color: white;
}

.privacy-notice {
    font-size: var(--font-size-sm);
    color: var(--gray-400);
    margin: 0;
}

@media (max-width: 768px) {
    .featured-content,
    .newsletter-content {
        grid-template-columns: 1fr;
        gap: var(--space-8);
    }
    
    .articles-grid {
        grid-template-columns: 1fr;
    }
    
    .article-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-4);
    }
    
    .newsletter-form .form-group {
        flex-direction: column;
    }
}
</style>

<script>
// Load More functionality
document.getElementById('load-more-articles')?.addEventListener('click', async function() {
    const button = this;
    const page = parseInt(button.dataset.page);
    const category = button.dataset.category || '';
    
    button.disabled = true;
    button.innerHTML = 'Loading...';
    
    try {
        const response = await fetch(`/api/blog?page=${page}&category=${category}&limit=6`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            // Append new articles to grid
            const grid = document.querySelector('.articles-grid');
            // Implementation would append new article cards
            
            // Update button for next page
            if (data.meta.has_more) {
                button.dataset.page = page + 1;
                button.disabled = false;
                button.innerHTML = 'Load More Articles';
            } else {
                button.style.display = 'none';
            }
        } else {
            button.style.display = 'none';
        }
        
    } catch (error) {
        button.disabled = false;
        button.innerHTML = 'Try Again';
        console.error('Load more failed:', error);
    }
});

// Category filtering
document.querySelectorAll('.category-btn').forEach(button => {
    button.addEventListener('click', function() {
        const category = this.dataset.category;
        
        // Update active state
        document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
        
        // Filter articles or redirect
        if (category === 'all') {
            window.location.href = '/blog';
        } else {
            window.location.href = `/blog?category=${category}`;
        }
    });
});
</script>