<?php
// blog_widget.php - Widget des derniers articles pour la page d'accueil
// À intégrer dans votre index.html (renommé en index.php)

// Configuration BDD
$DB_CONFIG = [
    'host' => 'localhost',
    'dbname' => 'techessentials_blog',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

try {
    $dsn = "mysql:host={$DB_CONFIG['host']};dbname={$DB_CONFIG['dbname']};charset={$DB_CONFIG['charset']}";
    $pdo = new PDO($dsn, $DB_CONFIG['username'], $DB_CONFIG['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Récupérer les 4 derniers articles
    $latest_articles = $pdo->query("
        SELECT 
            a.id, a.title, a.slug, a.excerpt, a.content, a.featured_image, a.published_at,
            c.name as category_name, c.color as category_color, c.icon as category_icon,
            (SELECT COUNT(*) FROM comments WHERE article_id = a.id AND status = 'approved') as comments_count
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.status = 'published'
        ORDER BY a.published_at DESC
        LIMIT 4
    ")->fetchAll();

    // Statistiques du blog
    $blog_stats = $pdo->query("
        SELECT 
            COUNT(*) as total_articles,
            (SELECT COUNT(*) FROM comments WHERE status = 'approved') as total_comments
        FROM articles 
        WHERE status = 'published'
    ")->fetch();

} catch (PDOException $e) {
    $latest_articles = [];
    $blog_stats = ['total_articles' => 0, 'total_comments' => 0];
}

// Fonction pour générer un excerpt court
function generateShortExcerpt($content, $length = 100) {
    $content = strip_tags($content);
    if (strlen($content) <= $length) {
        return $content;
    }
    return substr($content, 0, $length) . '...';
}

// Fonction pour formater les dates
function timeAgoShort($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 3600) return floor($time/60) . 'min';
    if ($time < 86400) return floor($time/3600) . 'h';
    if ($time < 2592000) return floor($time/86400) . 'j';
    
    return date('d/m/Y', strtotime($datetime));
}
?>

<!-- Section Blog pour Page d'Accueil -->
<section class="blog-section" id="blog">
    <div class="container">
        <!-- En-tête de section -->
        <div class="section-header">
            <div class="section-title-container">
                <h2 class="section-title">📝 Derniers Articles Tech</h2>
                <p class="section-subtitle">
                    Découvrez nos analyses, tests et guides pour rester à la pointe de la technologie
                </p>
            </div>
            <div class="section-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo $blog_stats['total_articles']; ?></span>
                    <span class="stat-label">Articles</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $blog_stats['total_comments']; ?></span>
                    <span class="stat-label">Commentaires</span>
                </div>
            </div>
        </div>

        <?php if (!empty($latest_articles)): ?>
            <!-- Grille des articles -->
            <div class="blog-articles-grid">
                <?php foreach ($latest_articles as $index => $article): ?>
                    <article class="blog-article-card <?php echo $index === 0 ? 'featured' : ''; ?>">
                        <a href="blog/article.php?id=<?php echo $article['id']; ?>" class="article-link">
                            <!-- Image -->
                            <div class="article-image-container">
                                <?php if (!empty($article['featured_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($article['title']); ?>" 
                                         class="article-image"
                                         loading="lazy">
                                <?php else: ?>
                                    <div class="article-image article-image-placeholder">
                                        <span class="placeholder-icon">📱</span>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Badge catégorie -->
                                <?php if (!empty($article['category_name'])): ?>
                                    <div class="article-category-badge" 
                                         style="background-color: <?php echo htmlspecialchars($article['category_color']); ?>;">
                                        <span><?php echo htmlspecialchars($article['category_icon']); ?></span>
                                        <span><?php echo htmlspecialchars($article['category_name']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Contenu -->
                            <div class="article-content">
                                <h3 class="article-title">
                                    <?php echo htmlspecialchars($article['title']); ?>
                                </h3>
                                
                                <p class="article-excerpt">
                                    <?php 
                                    $excerpt = !empty($article['excerpt']) 
                                        ? $article['excerpt'] 
                                        : generateShortExcerpt($article['content'], $index === 0 ? 150 : 80);
                                    echo htmlspecialchars($excerpt);
                                    ?>
                                </p>
                                
                                <div class="article-meta">
                                    <div class="meta-left">
                                        <span class="meta-item">
                                            <span class="meta-icon">📅</span>
                                            <span><?php echo timeAgoShort($article['published_at']); ?></span>
                                        </span>
                                        <span class="meta-item">
                                            <span class="meta-icon">💬</span>
                                            <span><?php echo $article['comments_count']; ?></span>
                                        </span>
                                    </div>
                                    <span class="read-more">Lire la suite →</span>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Bouton voir plus -->
            <div class="blog-cta">
                <a href="blog/" class="blog-cta-button">
                    <span>📚 Voir tous les articles</span>
                    <span class="cta-count">(<?php echo $blog_stats['total_articles']; ?> articles disponibles)</span>
                </a>
            </div>

        <?php else: ?>
            <!-- État vide -->
            <div class="blog-empty-state">
                <div class="empty-icon">📝</div>
                <h3>Blog en préparation</h3>
                <p>Nos premiers articles tech arrivent bientôt !</p>
                <a href="blog/" class="empty-cta">Découvrir le blog →</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* === STYLES POUR LA SECTION BLOG === */
.blog-section {
    padding: 80px 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    position: relative;
    overflow: hidden;
}

.blog-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="%23e1e5e9" stroke-width="0.5" opacity="0.3"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.3;
    pointer-events: none;
}

.blog-section .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    position: relative;
    z-index: 1;
}

/* En-tête de section */
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 50px;
    flex-wrap: wrap;
    gap: 30px;
}

.section-title-container {
    flex: 1;
    min-width: 300px;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 10px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.section-subtitle {
    font-size: 1.1rem;
    color: #666;
    line-height: 1.6;
    max-width: 500px;
}

.section-stats {
    display: flex;
    gap: 30px;
}

.stat-item {
    text-align: center;
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    min-width: 80px;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: #667eea;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9rem;
    color: #666;
    font-weight: 500;
}

/* Grille des articles */
.blog-articles-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    grid-template-rows: auto auto;
    gap: 25px;
    margin-bottom: 50px;
}

.blog-article-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.blog-article-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.12);
}

.blog-article-card.featured {
    grid-row: span 2;
}

.article-link {
    text-decoration: none;
    color: inherit;
    display: block;
    height: 100%;
}

/* Images des articles */
.article-image-container {
    position: relative;
    overflow: hidden;
}

.blog-article-card.featured .article-image-container {
    height: 300px;
}

.blog-article-card:not(.featured) .article-image-container {
    height: 180px;
}

.article-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.article-image-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.placeholder-icon {
    font-size: 3rem;
    opacity: 0.8;
}

.blog-article-card:hover .article-image {
    transform: scale(1.05);
}

/* Badge catégorie */
.article-category-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    padding: 6px 12px;
    border-radius: 20px;
    color: white;
    font-size: 0.8rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

/* Contenu des articles */
.article-content {
    padding: 25px;
    height: calc(100% - 180px);
    display: flex;
    flex-direction: column;
}

.blog-article-card.featured .article-content {
    height: calc(100% - 300px);
    padding: 30px;
}

.article-title {
    font-size: 1.3rem;
    font-weight: 600;
    line-height: 1.3;
    margin-bottom: 12px;
    color: #333;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.blog-article-card.featured .article-title {
    font-size: 1.6rem;
    -webkit-line-clamp: 3;
    margin-bottom: 15px;
}

.article-excerpt {
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
    flex: 1;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
}

.blog-article-card.featured .article-excerpt {
    -webkit-line-clamp: 4;
    font-size: 1.05rem;
}

/* Métadonnées */
.article-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
}

.meta-left {
    display: flex;
    gap: 15px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.85rem;
    color: #888;
}

.meta-icon {
    font-size: 0.9rem;
}

.read-more {
    color: #667eea;
    font-weight: 600;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.blog-article-card:hover .read-more {
    color: #764ba2;
}

/* Call-to-action */
.blog-cta {
    text-align: center;
    margin-top: 20px;
}

.blog-cta-button {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-decoration: none;
    padding: 20px 40px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.blog-cta-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
}

.cta-count {
    font-size: 0.85rem;
    opacity: 0.9;
    font-weight: 400;
}

/* État vide */
.blog-empty-state {
    text-align: center;
    padding: 80px 20px;
    color: #666;
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

.blog-empty-state h3 {
    font-size: 1.8rem;
    margin-bottom: 15px;
    color: #333;
}

.blog-empty-state p {
    font-size: 1.1rem;
    margin-bottom: 30px;
}

.empty-cta {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    padding: 15px 30px;
    border: 2px solid #667eea;
    border-radius: 25px;
    transition: all 0.3s ease;
}

.empty-cta:hover {
    background: #667eea;
    color: white;
}

/* Responsive */
@media (max-width: 1024px) {
    .blog-articles-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    .blog-article-card.featured {
        grid-column: span 2;
        grid-row: span 1;
    }
}

@media (max-width: 768px) {
    .blog-section {
        padding: 60px 0;
    }
    
    .section-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
        margin-bottom: 40px;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .section-stats {
        gap: 20px;
    }
    
    .blog-articles-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .blog-article-card.featured {
        grid-column: span 1;
    }
    
    .article-content {
        padding: 20px;
    }
    
    .blog-article-card.featured .article-content {
        padding: 25px;
    }
}

@media (max-width: 480px) {
    .stat-item {
        padding: 15px;
        min-width: 70px;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .blog-cta-button {
        padding: 18px 30px;
        font-size: 1rem;
    }
}

/* Animation au scroll */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.blog-article-card {
    animation: slideInUp 0.6s ease-out;
}

.blog-article-card:nth-child(1) { animation-delay: 0.1s; }
.blog-article-card:nth-child(2) { animation-delay: 0.2s; }
.blog-article-card:nth-child(3) { animation-delay: 0.3s; }
.blog-article-card:nth-child(4) { animation-delay: 0.4s; }
</style>

<script>
// Animation au scroll pour la section blog
document.addEventListener('DOMContentLoaded', function() {
    const blogSection = document.querySelector('.blog-section');
    
    if (blogSection) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        observer.observe(blogSection);
    }
});
</script>