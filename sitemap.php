<?php
header('Content-Type: application/xml; charset=utf-8');

// Configuration BDD
$DB_CONFIG = [
    'host' => 'localhost',
    'dbname' => 'techessentials_blog',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

try {
    $pdo = new PDO(
        "mysql:host={$DB_CONFIG['host']};dbname={$DB_CONFIG['dbname']};charset={$DB_CONFIG['charset']}",
        $DB_CONFIG['username'],
        $DB_CONFIG['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Erreur: " . $e->getMessage());
}

// URL de base (À CHANGER pour ton domaine en production)
$base_url = 'http://localhost/TechessentialsPro'; // ← Change ça en production

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    
    <!-- Page d'accueil -->
    <url>
        <loc><?= $base_url ?>/</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    
    <!-- Blog index -->
    <url>
        <loc><?= $base_url ?>/blog/</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    
    <!-- Articles -->
    <?php
    $articles = $pdo->query("
        SELECT id, slug, title, featured_image, published_at, updated_at
        FROM articles
        WHERE status = 'published'
        ORDER BY published_at DESC
    ")->fetchAll();
    
    foreach ($articles as $article):
        $lastmod = $article['updated_at'] ?: $article['published_at'];
        $article_url = $base_url . '/blog/article.php?id=' . $article['id'];
    ?>
    <url>
        <loc><?= htmlspecialchars($article_url) ?></loc>
        <lastmod><?= date('Y-m-d', strtotime($lastmod)) ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
        <?php if (!empty($article['featured_image'])): ?>
        <image:image>
            <image:loc><?= htmlspecialchars($article['featured_image']) ?></image:loc>
            <image:title><?= htmlspecialchars($article['title']) ?></image:title>
        </image:image>
        <?php endif; ?>
    </url>
    <?php endforeach; ?>
    
    <!-- Catégories -->
    <?php
    $categories = $pdo->query("
        SELECT id, slug, name
        FROM categories
        ORDER BY name
    ")->fetchAll();
    
    foreach ($categories as $category):
        $cat_url = $base_url . '/blog/?category=' . $category['id'];
    ?>
    <url>
        <loc><?= htmlspecialchars($cat_url) ?></loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.7</priority>
    </url>
    <?php endforeach; ?>
    
    <!-- Pages statiques -->
    <url>
        <loc><?= $base_url ?>/products.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    
    <url>
        <loc><?= $base_url ?>/reviews.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    
    <url>
        <loc><?= $base_url ?>/deals.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    
    <url>
        <loc><?= $base_url ?>/contact.php</loc>
        <lastmod><?= date('Y-m-d') ?></lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>
    
</urlset>