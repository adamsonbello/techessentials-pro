<?php
/**
 * TechEssentials Pro - Router Principal
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 */

// Définir la constante et inclure la configuration
define('TECHESSENTIALS_PRO', true);
require_once __DIR__ . '/includes/config.php';
require_once INCLUDES_PATH . 'functions.php';
require_once INCLUDES_PATH . 'language.php';


// Initialiser la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupérer la route et les paramètres
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$script_name = dirname($_SERVER['SCRIPT_NAME']);
$route_path = str_replace($script_name, '', $request_uri);
$route_path = ltrim(parse_url($route_path, PHP_URL_PATH), '/');

// Gérer le paramètre de langue
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? DEFAULT_LANG;
if (in_array($lang, AVAILABLE_LANGS)) {
    $_SESSION['lang'] = $lang;
    $GLOBALS['current_lang'] = $lang;

} else {
    $lang = DEFAULT_LANG;
    $GLOBALS['current_lang'] = $lang;
}

// Parser la route - VERSION CORRIGÉE
$route_segments = array_filter(explode('/', $route_path));
$main_route = $route_segments[1] ?? $route_segments[0] ?? 'home'; // Prendre le 2ème segment
$sub_route = $route_segments[2] ?? null;
$param = $route_segments[3] ?? null;

// Language déjà initialisé automatiquement via le singleton

// Parser la route
$route_segments = array_filter(explode('/', $route_path));
$main_route = $route_segments[0] ?? 'home';
$sub_route = $route_segments[1] ?? null;
$param = $route_segments[2] ?? null;

// Variables globales pour les templates
$page_data = [
    'current_route' => $main_route,
    'current_lang' => $lang,
    'site_stats' => []
];

// Router principal
try {
    switch ($main_route) {
        case '':
        case 'home':
            handleHomePage();
            break;

        case 'products':
            handleProductsPage();
            break;
            
        case 'reviews':
            handleReviewsPage($sub_route, $param);
            break;
            
        case 'blog':
            handleBlogPage($sub_route, $param);
            break;
            
        case 'deals':
            handleDealsPage();
            break;
            
        case 'contact':
            handleContactPage();
            break;
            
        case 'about':
            handleAboutPage();
            break;
            
        case 'newsletter':
            handleNewsletterPage($sub_route, $param);
            break;
            
        case 'search':
            handleSearchPage();
            break;
            
        case 'sitemap':
            handleSitemap();
            break;
            
        case 'privacy':
            handlePrivacyPage();
            break;
            
        case 'terms':
            handleTermsPage();
            break;
            
        default:
            handle404();
    }
    
} catch (Exception $e) {
    logError("Router error: " . $e->getMessage());
    handle500();
}

// ===============================
// HANDLERS DES PAGES
// ===============================

/**
 * Page d'accueil
 */
function handleHomePage() {
    global $page_data;
    
    $page_data['page_title'] = __('meta.home.title');
    $page_data['page_description'] = __('meta.home.description');
    $page_data['featured_reviews'] = getReviews(6, null, true);
    $page_data['recent_posts'] = getBlogPosts(4);
    
    renderPage('home', $page_data);
}



/**
 * Products page
 */
function handleProductsPage() {
    global $page_data;
    
    $page = (int)($_GET['page'] ?? 1);
    $category = clean($_GET['category'] ?? '');
    $sort = clean($_GET['sort'] ?? 'featured');
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    $products_data = getProducts($limit, $offset, $category, $sort);
    
    $page_data['page_title'] = 'Products Catalog - TechEssentials Pro';
    $page_data['products'] = $products_data['products'];
    $page_data['total_products'] = $products_data['total'];
    $page_data['current_page'] = $page;
    $page_data['total_pages'] = ceil($products_data['total'] / $limit);
    
    renderPage('products/index', $page_data);
}

/**
 * Section Reviews
 */
function handleReviewsPage($sub_route, $param) {
    global $page_data;
    
    switch ($sub_route) {
        case null:
            // Liste des reviews
            $page = (int)($_GET['page'] ?? 1);
            $category = clean($_GET['category'] ?? '');
            $limit = 12;
            $offset = ($page - 1) * $limit;
            
            $page_data['page_title'] = __('meta.reviews.title');
            $page_data['page_description'] = __('meta.reviews.description');
            $page_data['reviews'] = getReviews($limit, $category);
            $page_data['categories'] = getReviewCategories();
            $page_data['current_category'] = $category;
            $page_data['current_page'] = $page;
            
            renderPage('reviews/index', $page_data);
            break;
            
        case 'category':
            // Reviews par catégorie
            if (!$param) {
                redirect(url('reviews'));
            }
            
            $page = (int)($_GET['page'] ?? 1);
            $limit = 12;
            
            $page_data['page_title'] = ucfirst($param) . ' Reviews - TechEssentials Pro';
            $page_data['reviews'] = getReviews($limit, $param);
            $page_data['category'] = $param;
            $page_data['current_page'] = $page;
            
            renderPage('reviews/category', $page_data);
            break;
            
        default:
            // Review individuelle (slug)
            $review = getReviewBySlug($sub_route);
            
            if (!$review) {
                handle404();
                return;
            }
            
            $page_data['page_title'] = $review['meta_title'] ?? $review['title'];
            $page_data['page_description'] = $review['meta_description'] ?? $review['excerpt'];
            $page_data['review'] = $review;
            $page_data['related_reviews'] = getReviews(4, $review['category']);
            
            renderPage('reviews/single', $page_data);
    }
}

/**
 * Section Blog
 */
function handleBlogPage($sub_route, $param) {
    global $page_data;
    
    switch ($sub_route) {
        case null:
            // Liste des articles
            $page = (int)($_GET['page'] ?? 1);
            $category = clean($_GET['category'] ?? '');
            $limit = 10;
            
            $page_data['page_title'] = __('meta.blog.title');
            $page_data['page_description'] = __('meta.blog.description');
            $page_data['articles'] = getBlogPosts($limit, $category);
            $page_data['categories'] = getBlogCategories();
            $page_data['current_category'] = $category;
            $page_data['current_page'] = $page;
            
            renderPage('blog/index', $page_data);
            break;
            
        case 'category':
            // Articles par catégorie
            if (!$param) {
                redirect(url('blog'));
            }
            
            $page_data['page_title'] = ucfirst($param) . ' Articles - TechEssentials Pro';
            $page_data['articles'] = getBlogPosts(10, $param);
            $page_data['category'] = $param;
            
            renderPage('blog/category', $page_data);
            break;
            
        default:
            // Article individuel (slug)
            $article = getBlogPostBySlug($sub_route);
            
            if (!$article) {
                handle404();
                return;
            }
            
            $page_data['page_title'] = $article['meta_title'] ?? $article['title'];
            $page_data['page_description'] = $article['meta_description'] ?? $article['excerpt'];
            $page_data['article'] = $article;
            $page_data['related_articles'] = getBlogPosts(4, $article['category']);
            
            renderPage('blog/single', $page_data);
    }
}

/**
 * Page des deals
 */
function handleDealsPage() {
    global $page_data;
    
    $page_data['page_title'] = 'Best Tech Deals - TechEssentials Pro';
    $page_data['page_description'] = 'Exclusive deals and discounts on the best tech accessories for remote workers.';
    $page_data['deals'] = getActiveDeals();
    
    renderPage('deals', $page_data);
}

/**
 * Page de contact
 */
function handleContactPage() {
    global $page_data;
    
    $page_data['page_title'] = __('meta.contact.title');
    $page_data['page_description'] = __('meta.contact.description');
    $page_data['csrf_token'] = generateCSRF();
    
    // Traiter le formulaire si POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $page_data['form_result'] = processContactForm($_POST);
    }
    
    renderPage('contact', $page_data);
}

/**
 * Page À propos
 */
function handleAboutPage() {
    global $page_data;
    
    $page_data['page_title'] = 'About Us - TechEssentials Pro';
    $page_data['page_description'] = 'Learn about our mission to help remote workers find the best tech accessories.';
    
    renderPage('about', $page_data);
}

/**
 * Section Newsletter
 */
function handleNewsletterPage($sub_route, $param) {
    global $page_data;
    
    switch ($sub_route) {
        case 'confirm':
            // Confirmation d'abonnement
            if (!$param) {
                handle404();
                return;
            }
            
            $result = confirmNewsletterSubscription($param);
            $page_data['confirmation_result'] = $result;
            
            renderPage('newsletter/confirm', $page_data);
            break;
            
        case 'unsubscribe':
            // Désabonnement
            if (!$param) {
                handle404();
                return;
            }
            
            $result = unsubscribeNewsletter($param);
            $page_data['unsubscribe_result'] = $result;
            
            renderPage('newsletter/unsubscribe', $page_data);
            break;
            
        default:
            // Page newsletter principale
            $page_data['page_title'] = 'Newsletter - TechEssentials Pro';
            $page_data['page_description'] = 'Subscribe to our newsletter for weekly tech reviews and productivity tips.';
            
            renderPage('newsletter/index', $page_data);
    }
}

/**
 * Page de recherche
 */
function handleSearchPage() {
    global $page_data;
    
    $query = clean($_GET['q'] ?? '');
    $page = (int)($_GET['page'] ?? 1);
    
    if (empty($query)) {
        $page_data['search_results'] = [];
        $page_data['search_query'] = '';
    } else {
        $page_data['search_results'] = performSearch($query, $page);
        $page_data['search_query'] = $query;
    }
    
    $page_data['page_title'] = $query ? "Search Results for '{$query}'" : 'Search - TechEssentials Pro';
    $page_data['current_page'] = $page;
    
    renderPage('search', $page_data);
}

/**
 * Sitemap XML
 */
function handleSitemap() {
    header('Content-Type: application/xml; charset=UTF-8');
    
    $sitemap = generateSitemap();
    echo $sitemap;
    exit;
}

/**
 * Page de confidentialité
 */
function handlePrivacyPage() {
    global $page_data;
    
    $page_data['page_title'] = 'Privacy Policy - TechEssentials Pro';
    $page_data['last_updated'] = '2025-09-16';
    
    renderPage('privacy', $page_data);
}

/**
 * Page des conditions d'utilisation
 */
function handleTermsPage() {
    global $page_data;
    
    $page_data['page_title'] = 'Terms of Service - TechEssentials Pro';
    $page_data['last_updated'] = '2025-09-16';
    
    renderPage('terms', $page_data);
}

/**
 * Page 404
 */
function handle404() {
    global $page_data;
    
    http_response_code(404);
    
    $page_data['page_title'] = __('errors.404.title');
    $page_data['error_code'] = '404';
    $page_data['error_message'] = __('errors.404.message');
    
    renderPage('errors/404', $page_data);
}

/**
 * Page 500
 */
function handle500() {
    global $page_data;
    
    http_response_code(500);
    
    $page_data['page_title'] = __('errors.500.title');
    $page_data['error_code'] = '500';
    $page_data['error_message'] = __('errors.500.message');
    
    renderPage('errors/500', $page_data);
}

// ===============================
// FONCTIONS UTILITAIRES
// ===============================

/**
 * Récupérer une review par slug
 */
function getReviewBySlug($slug) {
    try {
        $db = getDB('main');
        $stmt = $db->prepare("
            SELECT * FROM reviews 
            WHERE slug = ? AND status = 'published'
        ");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    } catch (Exception $e) {
        logError("Error fetching review by slug: " . $e->getMessage());
        return null;
    }
}

/**
 * Récupérer un article de blog par slug
 */
function getBlogPostBySlug($slug) {
    try {
        $db = getDB('blog');
        $stmt = $db->prepare("
            SELECT * FROM articles 
            WHERE slug = ? AND status = 'published'
        ");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    } catch (Exception $e) {
        logError("Error fetching blog post by slug: " . $e->getMessage());
        return null;
    }
}

/**
 * Récupérer les catégories de reviews
 */
function getReviewCategories() {
    try {
        $db = getDB('main');
        $stmt = $db->prepare("
            SELECT category, COUNT(*) as count 
            FROM reviews 
            WHERE status = 'published' 
            GROUP BY category 
            ORDER BY count DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        logError("Error fetching review categories: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupérer les catégories de blog
 */
function getBlogCategories() {
    try {
        $db = getDB('blog');
        $stmt = $db->prepare("
            SELECT category, COUNT(*) as count 
            FROM articles 
            WHERE status = 'published' 
            GROUP BY category 
            ORDER BY count DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        logError("Error fetching blog categories: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupérer les deals actifs
 */
function getActiveDeals() {
    try {
        $db = getDB('main');
        $stmt = $db->prepare("
            SELECT * FROM reviews 
            WHERE status = 'published' 
            AND discount_price IS NOT NULL 
            AND discount_price > 0
            ORDER BY (price - discount_price) DESC 
            LIMIT 20
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        logError("Error fetching deals: " . $e->getMessage());
        return [];
    }
}

/**
 * Traiter le formulaire de contact
 */
function processContactForm($data) {
    $validation_rules = [
        'name' => ['required', 'min:2', 'max:100'],
        'email' => ['required', 'email'],
        'message' => ['required', 'min:10', 'max:5000']
    ];
    
    $errors = validateForm($validation_rules, $data);
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Vérifier CSRF
    if (!verifyCSRF($data['csrf_token'] ?? '')) {
        return ['success' => false, 'error' => 'Invalid security token'];
    }
    
    // Sauvegarder le message
    try {
        $db = getDB('main');
        $stmt = $db->prepare("
            INSERT INTO contact_messages (name, email, subject, message, ip_address, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            clean($data['name']),
            clean($data['email']),
            clean($data['subject'] ?? 'Website Contact'),
            clean($data['message']),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        // Envoyer l'email de notification (optionnel)
        // sendContactNotification($data);
        
        return ['success' => true, 'message' => __('contact.success')];
        
    } catch (Exception $e) {
        logError("Contact form error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to send message'];
    }
}

/**
 * Confirmer l'abonnement newsletter
 */
function confirmNewsletterSubscription($token) {
    try {
        $db = getDB('main');
        $stmt = $db->prepare("
            SELECT id FROM newsletter_subscribers 
            WHERE confirm_token = ? AND status = 'pending'
        ");
        $stmt->execute([$token]);
        $subscriber = $stmt->fetch();
        
        if (!$subscriber) {
            return ['success' => false, 'error' => 'Invalid or expired token'];
        }
        
        $stmt = $db->prepare("
            UPDATE newsletter_subscribers 
            SET status = 'active', confirm_token = NULL, confirmed_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$subscriber['id']]);
        
        return ['success' => true, 'message' => __('newsletter.success')];
        
    } catch (Exception $e) {
        logError("Newsletter confirmation error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Confirmation failed'];
    }
}

/**
 * Désabonner de la newsletter
 */
function unsubscribeNewsletter($token) {
    try {
        $db = getDB('main');
        $stmt = $db->prepare("
            SELECT id FROM newsletter_subscribers 
            WHERE MD5(CONCAT(id, email, 'unsubscribe_salt')) = ?
        ");
        $stmt->execute([$token]);
        $subscriber = $stmt->fetch();
        
        if (!$subscriber) {
            return ['success' => false, 'error' => 'Invalid unsubscribe token'];
        }
        
        $stmt = $db->prepare("
            UPDATE newsletter_subscribers 
            SET status = 'unsubscribed', updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$subscriber['id']]);
        
        return ['success' => true, 'message' => 'You have been unsubscribed successfully'];
        
    } catch (Exception $e) {
        logError("Newsletter unsubscribe error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Unsubscribe failed'];
    }
}

/**
 * Effectuer une recherche
 */
function performSearch($query, $page = 1) {
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    try {
        $results = ['reviews' => [], 'articles' => [], 'total' => 0];
        
        // Recherche dans les reviews
        $db_main = getDB('main');
        $stmt = $db_main->prepare("
            SELECT 'review' as type, id, title, slug, excerpt, featured_image, created_at
            FROM reviews 
            WHERE status = 'published' 
            AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $search_term = "%{$query}%";
        $stmt->execute([$search_term, $search_term, $search_term, $limit]);
        $results['reviews'] = $stmt->fetchAll();
        
        // Recherche dans les articles
        $db_blog = getDB('blog');
        $stmt = $db_blog->prepare("
            SELECT 'article' as type, id, title, slug, excerpt, featured_image, created_at
            FROM articles 
            WHERE status = 'published' 
            AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$search_term, $search_term, $search_term, $limit]);
        $results['articles'] = $stmt->fetchAll();
        
        $results['total'] = count($results['reviews']) + count($results['articles']);
        
        return $results;
        
    } catch (Exception $e) {
        logError("Search error: " . $e->getMessage());
        return ['reviews' => [], 'articles' => [], 'total' => 0];
    }
}

/**
 * Générer le sitemap XML
 */
function generateSitemap() {
    $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    
    // Pages principales
    $pages = ['', 'products','reviews', 'blog', 'deals', 'contact', 'about'];
    foreach ($pages as $page) {
        $sitemap .= '<url>' . "\n";
        $sitemap .= '<loc>' . url($page) . '</loc>' . "\n";
        $sitemap .= '<changefreq>weekly</changefreq>' . "\n";
        $sitemap .= '<priority>0.8</priority>' . "\n";
        $sitemap .= '</url>' . "\n";
    }
    
    // Reviews
    try {
        $db = getDB('main');
        $stmt = $db->prepare("SELECT slug, updated_at FROM reviews WHERE status = 'published'");
        $stmt->execute();
        $reviews = $stmt->fetchAll();
        
        foreach ($reviews as $review) {
            $sitemap .= '<url>' . "\n";
            $sitemap .= '<loc>' . url('reviews/' . $review['slug']) . '</loc>' . "\n";
            $sitemap .= '<lastmod>' . date('c', strtotime($review['updated_at'])) . '</lastmod>' . "\n";
            $sitemap .= '<changefreq>monthly</changefreq>' . "\n";
            $sitemap .= '<priority>0.6</priority>' . "\n";
            $sitemap .= '</url>' . "\n";
        }
    } catch (Exception $e) {
        logError("Sitemap reviews error: " . $e->getMessage());
    }
    
    // Articles de blog
    try {
        $db = getDB('blog');
        $stmt = $db->prepare("SELECT slug, updated_at FROM articles WHERE status = 'published'");
        $stmt->execute();
        $articles = $stmt->fetchAll();
        
        foreach ($articles as $article) {
            $sitemap .= '<url>' . "\n";
            $sitemap .= '<loc>' . url('blog/' . $article['slug']) . '</loc>' . "\n";
            $sitemap .= '<lastmod>' . date('c', strtotime($article['updated_at'])) . '</lastmod>' . "\n";
            $sitemap .= '<changefreq>monthly</changefreq>' . "\n";
            $sitemap .= '<priority>0.6</priority>' . "\n";
            $sitemap .= '</url>' . "\n";
        }
    } catch (Exception $e) {
        logError("Sitemap blog error: " . $e->getMessage());
    }
    
    $sitemap .= '</urlset>';
    
    return $sitemap;
}