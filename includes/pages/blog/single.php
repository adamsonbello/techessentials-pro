<?php
/**
 * TechEssentials Pro - Template Article Individual
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 */

// Empêcher l'accès direct
if (!defined('TECHESSENTIALS_PRO')) {
    die('Direct access not allowed');
}
?>

<!-- Breadcrumb -->
<nav class="breadcrumb-nav">
    <div class="container">
        <ol class="breadcrumb">
            <li><a href="<?= url() ?>"><?= Language::get('navigation.home') ?></a></li>
            <li><a href="<?= url('blog') ?>"><?= Language::get('navigation.blog') ?></a></li>
            <li><a href="<?= url('blog?category=' . $article['category']) ?>"><?= ucfirst($article['category']) ?></a></li>
            <li aria-current="page"><?= clean($article['title']) ?></li>
        </ol>
    </div>
</nav>

<!-- Article Header -->
<header class="article-header">
    <div class="container">
        <div class="article-header-content">
            <div class="article-meta">
                <span class="article-category"><?= ucfirst($article['category']) ?></span>
                <time datetime="<?= date('c', strtotime($article['created_at'])) ?>">
                    <?= utils::formatDate($article['created_at']) ?>
                </time>
                <?php if ($article['reading_time']): ?>
                <span class="reading-time">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <?= $article['reading_time'] ?> <?= Language::get('blog_section.reading_time') ?>
                </span>
                <?php endif; ?>
                <span class="views-count">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <?= number_format($article['views']) ?> views
                </span>
            </div>
            
            <h1 class="article-title"><?= clean($article['title']) ?></h1>
            
            <?php if ($article['excerpt']): ?>
            <p class="article-excerpt"><?= clean($article['excerpt']) ?></p>
            <?php endif; ?>
            
            <!-- Social Share -->
            <div class="article-share">
                <span>Share:</span>
                <div class="share-buttons">
                    <button class="share-btn twitter" data-share="twitter">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                    </button>
                    <button class="share-btn facebook" data-share="facebook">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </button>
                    <button class="share-btn linkedin" data-share="linkedin">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </button>
                    <button class="share-btn copy-link" data-share="copy">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Featured Image -->
<?php if ($article['featured_image']): ?>
<section class="article-image">
    <div class="container">
        <div class="image-container">
            <img src="<?= $article['featured_image'] ?>" 
                 alt="<?= clean($article['title']) ?>"
                 width="800" height="400"
                 loading="eager">
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Article Content -->
<main class="article-main">
    <div class="container">
        <div class="article-layout">
            <!-- Article Content -->
            <article class="article-content">
                <!-- Table of Contents (if needed) -->
                <div class="table-of-contents" id="table-of-contents" style="display: none;">
                    <h3>Table of Contents</h3>
                    <!-- Generated by JavaScript -->
                </div>
                
                <!-- Article Body -->
                <div class="article-body">
                    <?= $article['content'] ?>
                </div>
                
                <!-- Article Footer -->
                <footer class="article-footer">
                    <div class="article-tags">
                        <?php if (!empty($article['tags'])): ?>
                        <div class="tags-section">
                            <span class="tags-label">Tags:</span>
                            <?php foreach ($article['tags'] as $tag): ?>
                            <a href="<?= url('blog?tag=' . urlencode($tag)) ?>" class="tag-link"><?= clean($tag) ?></a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="article-actions">
                        <button class="action-btn bookmark-btn" data-article-id="<?= $article['id'] ?>">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                            </svg>
                            Bookmark
                        </button>
                        <button class="action-btn print-btn" onclick="window.print()">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Print
                        </button>
                    </div>
                </footer>
            </article>
            
            <!-- Sidebar -->
            <aside class="article-sidebar">
                <!-- Progress Bar -->
                <div class="reading-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" id="reading-progress"></div>
                    </div>
                    <span class="progress-text">Reading Progress</span>
                </div>
                
                <!-- Newsletter Signup -->
                <div class="sidebar-newsletter">
                    <h3>Never Miss an Update</h3>
                    <p>Get the latest tech insights delivered to your inbox.</p>
                    <form class="mini-newsletter-form" data-action="newsletter/subscribe">
                        <input type="email" name="email" placeholder="Your email" required>
                        <input type="hidden" name="source" value="blog_article">
                        <input type="hidden" name="language" value="<?= $current_lang ?>">
                        <button type="submit" class="btn btn-primary btn-sm">Subscribe</button>
                    </form>
                </div>
                
                <!-- Related Articles -->
                <?php if (!empty($related_articles)): ?>
                <div class="related-articles">
                    <h3>Related Articles</h3>
                    <div class="related-list">
                        <?php foreach ($related_articles as $related): ?>
                        <article class="related-item">
                            <a href="<?= url('blog/' . $related['slug']) ?>" class="related-link">
                                <?php if ($related['featured_image']): ?>
                                <img src="<?= $related['featured_image'] ?>" 
                                     alt="<?= clean($related['title']) ?>"
                                     width="80" height="60"
                                     loading="lazy">
                                <?php endif; ?>
                                <div class="related-content">
                                    <h4><?= clean($related['title']) ?></h4>
                                    <time><?= timeAgo($related['created_at']) ?></time>
                                </div>
                            </a>
                        </article>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Popular Articles -->
                <div class="popular-articles">
                    <h3>Popular This Week</h3>
                    <!-- This would be populated with popular articles -->
                </div>
            </aside>
        </div>
    </div>
</main>

<!-- Comments Section -->
<?php if (isset($article['comments'])): ?>
<section class="comments-section">
    <div class="container">
        <div class="comments-container">
            <h2>Comments (<?= count($article['comments']) ?>)</h2>
            
            <!-- Comment Form -->
            <form class="comment-form" method="post" action="/api/blog/comment/<?= $article['id'] ?>">
                <h3>Leave a Comment</h3>
                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Your Email" required>
                    </div>
                </div>
                <div class="form-group">
                    <textarea name="comment" rows="4" placeholder="Write your comment..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Post Comment</button>
                <p class="comment-policy">Comments are moderated and may take some time to appear.</p>
            </form>
            
            <!-- Comments List -->
            <?php if (!empty($article['comments'])): ?>
            <div class="comments-list">
                <?php foreach ($article['comments'] as $comment): ?>
                <div class="comment" data-comment-id="<?= $comment['id'] ?>">
                    <div class="comment-header">
                        <strong class="comment-author"><?= clean($comment['name']) ?></strong>
                        <time class="comment-date"><?= timeAgo($comment['created_at']) ?></time>
                    </div>
                    <div class="comment-content">
                        <p><?= clean($comment['comment']) ?></p>
                    </div>
                    
                    <?php if (!empty($comment['replies'])): ?>
                    <div class="comment-replies">
                        <?php foreach ($comment['replies'] as $reply): ?>
                        <div class="comment reply">
                            <div class="comment-header">
                                <strong class="comment-author"><?= clean($reply['name']) ?></strong>
                                <time class="comment-date"><?= timeAgo($reply['created_at']) ?></time>
                            </div>
                            <div class="comment-content">
                                <p><?= clean($reply['comment']) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- More Articles -->
<section class="more-articles">
    <div class="container">
        <h2>More from <?= ucfirst($article['category']) ?></h2>
        
        <?php if (!empty($related_articles)): ?>
        <div class="more-articles-grid">
            <?php foreach (array_slice($related_articles, 0, 3) as $related): ?>
            <article class="more-article-card">
                <a href="<?= url('blog/' . $related['slug']) ?>" class="more-article-link">
                    <?php if ($related['featured_image']): ?>
                    <img src="<?= $related['featured_image'] ?>" 
                         alt="<?= clean($related['title']) ?>"
                         width="300" height="200"
                         loading="lazy">
                    <?php endif; ?>
                    <div class="more-article-content">
                        <h3><?= clean($related['title']) ?></h3>
                        <time><?= timeAgo($related['created_at']) ?></time>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="more-articles-cta">
            <a href="<?= url('blog') ?>" class="btn btn-outline btn-lg">
                View All Articles
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
</section>

<!-- Article Schema.org -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "<?= clean($article['title']) ?>",
    "description": "<?= clean($article['excerpt']) ?>",
    "image": "<?= $article['featured_image'] ?>",
    "datePublished": "<?= date('c', strtotime($article['created_at'])) ?>",
    "dateModified": "<?= date('c', strtotime($article['updated_at'] ?? $article['created_at'])) ?>",
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
    },
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "<?= url('blog/' . $article['slug']) ?>"
    },
    "wordCount": <?= str_word_count(strip_tags($article['content'])) ?>,
    "timeRequired": "PT<?= $article['reading_time'] ?? 5 ?>M",
    "articleSection": "<?= ucfirst($article['category']) ?>"
}
</script>

<style>
.breadcrumb-nav {
    background: var(--gray-50);
    padding: var(--space-4) 0;
    border-bottom: 1px solid var(--gray-200);
}

.breadcrumb {
    display: flex;
    list-style: none;
    gap: var(--space-2);
    margin: 0;
    padding: 0;
    font-size: var(--font-size-sm);
}

.breadcrumb li::after {
    content: '>';
    margin-left: var(--space-2);
    color: var(--gray-400);
}

.breadcrumb li:last-child::after {
    display: none;
}

.breadcrumb a {
    color: var(--gray-600);
}

.breadcrumb a:hover {
    color: var(--primary);
}

.article-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    padding: var(--space-16) 0;
    text-align: center;
}

.article-meta {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: var(--space-6);
    margin-bottom: var(--space-6);
    font-size: var(--font-size-sm);
    flex-wrap: wrap;
}

.article-category {
    background: rgba(255, 255, 255, 0.2);
    padding: var(--space-1) var(--space-3);
    border-radius: var(--border-radius);
    font-weight: 600;
}

.reading-time,
.views-count {
    display: flex;
    align-items: center;
    gap: var(--space-1);
}

.article-title {
    font-size: var(--font-size-5xl);
    margin-bottom: var(--space-6);
    line-height: 1.1;
}

.article-excerpt {
    font-size: var(--font-size-xl);
    opacity: 0.9;
    max-width: 800px;
    margin: 0 auto var(--space-8);
}

.article-share {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: var(--space-4);
}

.share-buttons {
    display: flex;
    gap: var(--space-3);
}

.share-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    color: white;
}

.share-btn:hover {
    background: white;
    color: var(--primary);
}

.article-image {
    padding: var(--space-8) 0;
    background: var(--gray-50);
}

.image-container {
    text-align: center;
}

.image-container img {
    max-width: 100%;
    height: auto;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--box-shadow-lg);
}

.article-main {
    padding: var(--space-16) 0;
}

.article-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: var(--space-12);
    align-items: start;
}

.article-content {
    max-width: none;
}

.table-of-contents {
    background: var(--gray-50);
    border-radius: var(--border-radius);
    padding: var(--space-6);
    margin-bottom: var(--space-8);
    position: sticky;
    top: var(--space-8);
}

.article-body {
    line-height: 1.7;
    font-size: var(--font-size-lg);
}

.article-body h2,
.article-body h3 {
    margin-top: var(--space-8);
    margin-bottom: var(--space-4);
}

.article-body p {
    margin-bottom: var(--space-6);
}

.article-body img {
    max-width: 100%;
    height: auto;
    border-radius: var(--border-radius);
    margin: var(--space-6) 0;
}

.article-footer {
    margin-top: var(--space-12);
    padding-top: var(--space-8);
    border-top: 1px solid var(--gray-200);
}

.tags-section {
    margin-bottom: var(--space-6);
}

.tags-label {
    font-weight: 600;
    margin-right: var(--space-4);
}

.tag-link {
    display: inline-block;
    background: var(--gray-100);
    color: var(--gray-700);
    padding: var(--space-1) var(--space-3);
    border-radius: var(--border-radius);
    font-size: var(--font-size-sm);
    margin: 0 var(--space-2) var(--space-2) 0;
    transition: var(--transition);
}

.tag-link:hover {
    background: var(--primary);
    color: white;
}

.article-actions {
    display: flex;
    gap: var(--space-4);
}

.action-btn {
    display: flex;
    align-items: center;
    gap: var(--space-2);
    padding: var(--space-3) var(--space-4);
    background: var(--gray-100);
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
    color: var(--gray-700);
}

.action-btn:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.article-sidebar {
    position: sticky;
    top: var(--space-8);
}

.reading-progress {
    background: white;
    border-radius: var(--border-radius);
    padding: var(--space-6);
    margin-bottom: var(--space-8);
    box-shadow: var(--box-shadow);
}

.progress-bar {
    height: 4px;
    background: var(--gray-200);
    border-radius: 2px;
    margin-bottom: var(--space-3);
}

.progress-fill {
    height: 100%;
    background: var(--primary);
    border-radius: 2px;
    width: 0%;
    transition: width 0.3s ease;
}

.sidebar-newsletter {
    background: var(--gray-900);
    color: white;
    border-radius: var(--border-radius);
    padding: var(--space-6);
    margin-bottom: var(--space-8);
    text-align: center;
}

.mini-newsletter-form {
    margin-top: var(--space-4);
}

.mini-newsletter-form input[type="email"] {
    width: 100%;
    padding: var(--space-3);
    border: 1px solid var(--gray-600);
    border-radius: var(--border-radius);
    background: var(--gray-800);
    color: white;
    margin-bottom: var(--space-3);
}

.related-articles,
.popular-articles {
    background: white;
    border-radius: var(--border-radius);
    padding: var(--space-6);
    margin-bottom: var(--space-8);
    box-shadow: var(--box-shadow);
}

.related-list {
    margin-top: var(--space-4);
}

.related-item {
    margin-bottom: var(--space-4);
    padding-bottom: var(--space-4);
    border-bottom: 1px solid var(--gray-100);
}

.related-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.related-link {
    display: flex;
    gap: var(--space-3);
    text-decoration: none;
    color: inherit;
}

.related-link img {
    border-radius: var(--border-radius);
    object-fit: cover;
}

.related-content h4 {
    font-size: var(--font-size-sm);
    margin-bottom: var(--space-1);
    line-height: 1.3;
}

.related-content time {
    font-size: var(--font-size-xs);
    color: var(--gray-500);
}

.comments-section {
    background: var(--gray-50);
    padding: var(--space-16) 0;
}

.comments-container {
    max-width: 800px;
    margin: 0 auto;
}

.comment-form {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: var(--space-8);
    margin-bottom: var(--space-12);
    box-shadow: var(--box-shadow);
}

.comment-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-4);
}

.comment-form .form-group {
    margin-bottom: var(--space-4);
}

.comment-form input,
.comment-form textarea {
    width: 100%;
    padding: var(--space-3);
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
}

.comment-policy {
    font-size: var(--font-size-sm);
    color: var(--gray-600);
    margin-top: var(--space-4);
}

.comments-list {
    background: white;
    border-radius: var(--border-radius-lg);
    padding: var(--space-8);
    box-shadow: var(--box-shadow);
}

.comment {
    padding: var(--space-6);
    border-bottom: 1px solid var(--gray-100);
}

.comment:last-child {
    border-bottom: none;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-3);
}

.comment-author {
    color: var(--gray-900);
}

.comment-date {
    font-size: var(--font-size-sm);
    color: var(--gray-500);
}

.comment-replies {
    margin-left: var(--space-8);
    margin-top: var(--space-4);
    border-left: 2px solid var(--gray-200);
    padding-left: var(--space-6);
}

.more-articles {
    padding: var(--space-16) 0;
    background: var(--gray-50);
}

.more-articles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--space-8);
    margin-bottom: var(--space-12);
}

.more-article-card {
    background: white;
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
}

.more-article-card:hover {
    transform: translateY(-2px);
}

.more-article-link {
    display: block;
    text-decoration: none;
    color: inherit;
}

.more-article-link img {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.more-article-content {
    padding: var(--space-6);
}

.more-article-content h3 {
    margin-bottom: var(--space-3);
}

.more-articles-cta {
    text-align: center;
}

@media (max-width: 768px) {
    .article-layout {
        grid-template-columns: 1fr;
        gap: var(--space-8);
    }
    
    .article-sidebar {
        position: static;
        order: -1;
    }
    
    .article-title {
        font-size: var(--font-size-3xl);
    }
    
    .article-meta {
        flex-direction: column;
        gap: var(--space-3);
    }
    
    .comment-form .form-row {
        grid-template-columns: 1fr;
    }
    
    .comment-replies {
        margin-left: var(--space-4);
        padding-left: var(--space-4);
    }
}
</style>

<script>
// Reading Progress
function updateReadingProgress() {
    const article = document.querySelector('.article-body');
    const progressBar = document.getElementById('reading-progress');
    
    if (!article || !progressBar) return;
    
    const articleHeight = article.offsetHeight;
    const articleTop = article.offsetTop;
    const scrollTop = window.pageYOffset;
    const windowHeight = window.innerHeight;
    
    const progress = Math.min(
        Math.max((scrollTop - articleTop + windowHeight) / articleHeight * 100, 0),
        100
    );
    
    progressBar.style.width = progress + '%';
}

window.addEventListener('scroll', updateReadingProgress);
window.addEventListener('resize', updateReadingProgress);

// Table of Contents Generation
function generateTableOfContents() {
    const toc = document.getElementById('table-of-contents');
    const headings = document.querySelectorAll('.article-body h2, .article-body h3');
    
    if (!toc || headings.length === 0) return;
    
    const tocList = document.createElement('ul');
    tocList.className = 'toc-list';
    
    headings.forEach((heading, index) => {
        if (!heading.id) {
            heading.id = `heading-${index}`;
        }
        
        const tocItem = document.createElement('li');
        tocItem.className = `toc-item toc-${heading.tagName.toLowerCase()}`;
        
        const tocLink = document.createElement('a');
        tocLink.href = `#${heading.id}`;
        tocLink.textContent = heading.textContent;
        tocLink.className = 'toc-link';
        
        tocItem.appendChild(tocLink);
        tocList.appendChild(tocItem);
    });
    
    if (tocList.children.length > 0) {
        toc.appendChild(tocList);
        toc.style.display = 'block';
    }
}

// Social Sharing
document.querySelectorAll('[data-share]').forEach(button => {
    button.addEventListener('click', (e) => {
        e.preventDefault();
        const platform = button.dataset.share;
        const url = encodeURIComponent(window.location.href);
        const title = encodeURIComponent(document.title);
        
        let shareUrl;
        switch (platform) {
            case 'twitter':
                shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
                break;
            case 'facebook':
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                break;
            case 'linkedin':
                shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
                break;
            case 'copy':
                navigator.clipboard.writeText(window.location.href).then(() => {
                    button.innerHTML = '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                    setTimeout(() => {
                        button.innerHTML = '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>';
                    }, 2000);
                });
                return;
            default:
                return;
        }
        
        window.open(shareUrl, 'share', 'width=600,height=400');
    });
});

// Initialize features
document.addEventListener('DOMContentLoaded', () => {
    generateTableOfContents();
    updateReadingProgress();
});
</script>