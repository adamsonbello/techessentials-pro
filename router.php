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
// require_once INCLUDES_PATH . 'language.php'; // COMMENTÉ - fichier manquant

// Initialiser la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===============================
// GESTION AMÉLIORÉE DES URLs
// ===============================

// Récupérer et nettoyer l'URL
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';

// Déterminer le chemin de base (pour les installations dans des sous-dossiers)
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = str_replace('/router.php', '', $script_name);
$base_path = str_replace('/index.php', '', $base_path);

// Extraire la route en enlevant le chemin de base
$route_path = str_replace($base_path, '', $request_uri);
$route_path = parse_url($route_path, PHP_URL_PATH);
$route_path = trim($route_path, '/');

// Gérer le paramètre de langue
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'fr';
$available_langs = ['fr', 'en'];
if (in_array($lang, $available_langs)) {
    $_SESSION['lang'] = $lang;
    $GLOBALS['current_lang'] = $lang;
} else {
    $lang = 'fr';
    $GLOBALS['current_lang'] = $lang;
}

// Parser la route
$route_segments = !empty($route_path) ? explode('/', $route_path) : [];
$main_route = $route_segments[0] ?? 'home';
$sub_route = $route_segments[1] ?? null;
$param = $route_segments[2] ?? null;

// Nettoyer la route principale
if (empty($main_route) || $main_route == 'router.php' || $main_route == 'index.php') {
    $main_route = 'home';
}

// Variables globales pour les templates
$page_data = [
    'current_route' => $main_route,
    'current_lang' => $lang,
    'site_stats' => [],
    'base_url' => $base_path
];

// ===============================
// FONCTION RENDERPAGE SI NON DÉFINIE
// ===============================

if (!function_exists('renderPage')) {
    function renderPage($template, $data = []) {
        // Extraire les variables pour les rendre disponibles dans le template
        extract($data);
        
        // Déterminer le chemin du template
        $template_file = str_replace('.', '/', $template);
        $template_file = str_replace('//', '/', $template_file);
        
        // Chemins possibles pour les templates
        $possible_paths = [
            __DIR__ . "/templates/{$template_file}.php",
            __DIR__ . "/views/{$template_file}.php",
            __DIR__ . "/pages/{$template_file}.php",
            __DIR__ . "/{$template_file}.php"
        ];
        
        // Chercher le template
        $template_found = false;
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                include $path;
                $template_found = true;
                break;
            }
        }
        
        // Si aucun template n'est trouvé, afficher une page d'erreur basique
        if (!$template_found) {
            // Pour la page 404
            if ($template === 'errors/404' || $template === '404') {
                ?>
                <!DOCTYPE html>
                <html lang="<?php echo $data['current_lang'] ?? 'fr'; ?>">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>404 - Page non trouvée | TechEssentials Pro</title>
                    <style>
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }
                        body {
                            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            min-height: 100vh;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            padding: 20px;
                        }
                        .error-container {
                            background: white;
                            border-radius: 20px;
                            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                            padding: 60px 40px;
                            text-align: center;
                            max-width: 500px;
                            width: 100%;
                        }
                        .error-code {
                            font-size: 120px;
                            font-weight: bold;
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            -webkit-background-clip: text;
                            -webkit-text-fill-color: transparent;
                            margin-bottom: 10px;
                            line-height: 1;
                        }
                        h2 {
                            color: #333;
                            font-size: 28px;
                            margin-bottom: 20px;
                        }
                        p {
                            color: #666;
                            font-size: 16px;
                            line-height: 1.6;
                            margin-bottom: 30px;
                        }
                        .home-button {
                            display: inline-block;
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            color: white;
                            padding: 14px 35px;
                            text-decoration: none;
                            border-radius: 50px;
                            font-weight: 600;
                            font-size: 16px;
                            transition: transform 0.3s ease, box-shadow 0.3s ease;
                        }
                        .home-button:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
                        }
                    </style>
                </head>
                <body>
                    <div class="error-container">
                        <div class="error-code">404</div>
                        <h2>Page non trouvée</h2>
                        <p>Désolé, la page que vous recherchez n'existe pas ou a été déplacée.</p>
                        <a href="<?php echo $data['base_url'] ?? '/'; ?>" class="home-button">Retour à l'accueil</a>
                    </div>
                </body>
                </html>
                <?php
            } 
            // Pour la page 500
            elseif ($template === 'errors/500' || $template === '500') {
                ?>
                <!DOCTYPE html>
                <html lang="<?php echo $data['current_lang'] ?? 'fr'; ?>">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>500 - Erreur serveur | TechEssentials Pro</title>
                    <style>
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }
                        body {
                            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                            background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
                            min-height: 100vh;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            padding: 20px;
                        }
                        .error-container {
                            background: white;
                            border-radius: 20px;
                            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                            padding: 60px 40px;
                            text-align: center;
                            max-width: 500px;
                            width: 100%;
                        }
                        .error-code {
                            font-size: 120px;
                            font-weight: bold;
                            background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
                            -webkit-background-clip: text;
                            -webkit-text-fill-color: transparent;
                            margin-bottom: 10px;
                            line-height: 1;
                        }
                        h2 {
                            color: #333;
                            font-size: 28px;
                            margin-bottom: 20px;
                        }
                        p {
                            color: #666;
                            font-size: 16px;
                            line-height: 1.6;
                            margin-bottom: 30px;
                        }
                        .home-button {
                            display: inline-block;
                            background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
                            color: white;
                            padding: 14px 35px;
                            text-decoration: none;
                            border-radius: 50px;
                            font-weight: 600;
                            font-size: 16px;
                            transition: transform 0.3s ease, box-shadow 0.3s ease;
                        }
                        .home-button:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 10px 20px rgba(245, 87, 108, 0.4);
                        }
                    </style>
                </head>
                <body>
                    <div class="error-container">
                        <div class="error-code">500</div>
                        <h2>Erreur serveur</h2>
                        <p>Une erreur inattendue s'est produite. Nos équipes ont été notifiées et travaillent à résoudre le problème.</p>
                        <a href="<?php echo $data['base_url'] ?? '/'; ?>" class="home-button">Retour à l'accueil</a>
                    </div>
                </body>
                </html>
                <?php
            }
            // Pour les autres templates manquants
            else {
                echo "<!DOCTYPE html><html><head><title>Template manquant</title></head><body>";
                echo "<h1>Erreur: Template non trouvé</h1>";
                echo "<p>Le template '{$template}' n'a pas été trouvé.</p>";
                echo "<p>Chemins vérifiés:</p><ul>";
                foreach ($possible_paths as $path) {
                    echo "<li>$path</li>";
                }
                echo "</ul>";
                echo "</body></html>";
            }
        }
    }
}

// ===============================
// FONCTION DE TRADUCTION (doit être définie AVANT utilisation)
// ===============================

if (!function_exists('__')) {
    function __($key) { 
        // Traductions basiques FR
        $translations = [
            'meta.home.title' => 'Accueil - TechEssentials Pro',
            'meta.home.description' => 'Découvrez les meilleurs accessoires tech pour le télétravail',
            'meta.reviews.title' => 'Reviews - TechEssentials Pro',
            'meta.reviews.description' => 'Analyses détaillées des meilleurs produits tech',
            'meta.blog.title' => 'Blog - TechEssentials Pro',
            'meta.blog.description' => 'Articles et conseils pour optimiser votre espace de travail',
            'meta.contact.title' => 'Contact - TechEssentials Pro',
            'meta.contact.description' => 'Contactez notre équipe',
            'contact.success' => 'Message envoyé avec succès !',
            'newsletter.success' => 'Inscription confirmée !',
            'errors.404.title' => '404 - Page non trouvée',
            'errors.404.message' => 'La page demandée n\'existe pas',
            'errors.500.title' => '500 - Erreur serveur',
            'errors.500.message' => 'Une erreur inattendue s\'est produite'
        ];
        
        return $translations[$key] ?? $key;
    }
}

// ===============================
// ROUTER PRINCIPAL
// ===============================

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
        case 'sitemap.xml':
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
    if (function_exists('logError')) {
        logError("Router error: " . $e->getMessage());
    }
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
    
    // Utiliser des valeurs par défaut si les fonctions n'existent pas
    if (function_exists('getReviews')) {
        $page_data['featured_reviews'] = getReviews(6, null, true);
    } else {
        $page_data['featured_reviews'] = [];
    }
    
    if (function_exists('getBlogPosts')) {
        $page_data['recent_posts'] = getBlogPosts(4);
    } else {
        $page_data['recent_posts'] = [];
    }
    
    renderPage('home', $page_data);
}

/**
 * Products page
 */
function handleProductsPage() {
    global $page_data;
    
    $page = (int)($_GET['page'] ?? 1);
    $category = isset($_GET['category']) ? (function_exists('clean') ? clean($_GET['category']) : htmlspecialchars($_GET['category'])) : '';
    $sort = isset($_GET['sort']) ? (function_exists('clean') ? clean($_GET['sort']) : htmlspecialchars($_GET['sort'])) : 'featured';
    $limit = 20;
    $offset = ($page - 1) * $limit;
    
    if (function_exists('getProducts')) {
        $products_data = getProducts($limit, $offset, $category, $sort);
    } else {
        $products_data = ['products' => [], 'total' => 0];
    }
    
    $page_data['page_title'] = 'Products Catalog - TechEssentials Pro';
    $page_data['products'] = $products_data['products'];
    $page_data['total_products'] = $products_data['total'];
    $page_data['current_page'] = $page;
    $page_data['total_pages'] = $products_data['total'] > 0 ? ceil($products_data['total'] / $limit) : 1;
    
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
            $category = isset($_GET['category']) ? (function_exists('clean') ? clean($_GET['category']) : htmlspecialchars($_GET['category'])) : '';
            $limit = 12;
            
            $page_data['page_title'] = __('meta.reviews.title');
            $page_data['page_description'] = __('meta.reviews.description');
            
            if (function_exists('getReviews')) {
                $page_data['reviews'] = getReviews($limit, $category);
            } else {
                $page_data['reviews'] = [];
            }
            
            if (function_exists('getReviewCategories')) {
                $page_data['categories'] = getReviewCategories();
            } else {
                $page_data['categories'] = [];
            }
            
            $page_data['current_category'] = $category;
            $page_data['current_page'] = $page;
            
            renderPage('reviews/index', $page_data);
            break;
            
        case 'category':
            // Reviews par catégorie
            if (!$param) {
                if (function_exists('redirect') && function_exists('url')) {
                    redirect(url('reviews'));
                } else {
                    header('Location: /reviews');
                    exit;
                }
            }
            
            $page = (int)($_GET['page'] ?? 1);
            $limit = 12;
            
            $page_data['page_title'] = ucfirst($param) . ' Reviews - TechEssentials Pro';
            
            if (function_exists('getReviews')) {
                $page_data['reviews'] = getReviews($limit, $param);
            } else {
                $page_data['reviews'] = [];
            }
            
            $page_data['category'] = $param;
            $page_data['current_page'] = $page;
            
            renderPage('reviews/category', $page_data);
            break;
            
        default:
            // Review individuelle (slug)
            if (function_exists('getReviewBySlug')) {
                $review = getReviewBySlug($sub_route);
            } else {
                $review = null;
            }
            
            if (!$review) {
                handle404();
                return;
            }
            
            $page_data['page_title'] = $review['meta_title'] ?? $review['title'];
            $page_data['page_description'] = $review['meta_description'] ?? $review['excerpt'];
            $page_data['review'] = $review;
            
            if (function_exists('getReviews')) {
                $page_data['related_reviews'] = getReviews(4, $review['category']);
            } else {
                $page_data['related_reviews'] = [];
            }
            
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
            $category = isset($_GET['category']) ? (function_exists('clean') ? clean($_GET['category']) : htmlspecialchars($_GET['category'])) : '';
            $limit = 10;
            
            $page_data['page_title'] = __('meta.blog.title');
            $page_data['page_description'] = __('meta.blog.description');
            
            if (function_exists('getBlogPosts')) {
                $page_data['articles'] = getBlogPosts($limit, $category);
            } else {
                $page_data['articles'] = [];
            }
            
            if (function_exists('getBlogCategories')) {
                $page_data['categories'] = getBlogCategories();
            } else {
                $page_data['categories'] = [];
            }
            
            $page_data['current_category'] = $category;
            $page_data['current_page'] = $page;
            
            renderPage('blog/index', $page_data);
            break;
            
        case 'category':
            // Articles par catégorie
            if (!$param) {
                if (function_exists('redirect') && function_exists('url')) {
                    redirect(url('blog'));
                } else {
                    header('Location: /blog');
                    exit;
                }
            }
            
            $page_data['page_title'] = ucfirst($param) . ' Articles - TechEssentials Pro';
            
            if (function_exists('getBlogPosts')) {
                $page_data['articles'] = getBlogPosts(10, $param);
            } else {
                $page_data['articles'] = [];
            }
            
            $page_data['category'] = $param;
            
            renderPage('blog/category', $page_data);
            break;
            
        default:
            // Article individuel (slug)
            if (function_exists('getBlogPostBySlug')) {
                $article = getBlogPostBySlug($sub_route);
            } else {
                $article = null;
            }
            
            if (!$article) {
                handle404();
                return;
            }
            
            $page_data['page_title'] = $article['meta_title'] ?? $article['title'];
            $page_data['page_description'] = $article['meta_description'] ?? $article['excerpt'];
            $page_data['article'] = $article;
            
            if (function_exists('getBlogPosts')) {
                $page_data['related_articles'] = getBlogPosts(4, $article['category']);
            } else {
                $page_data['related_articles'] = [];
            }
            
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
    
    if (function_exists('getActiveDeals')) {
        $page_data['deals'] = getActiveDeals();
    } else {
        $page_data['deals'] = [];
    }
    
    renderPage('deals', $page_data);
}

/**
 * Page de contact
 */
function handleContactPage() {
    global $page_data;
    
    $page_data['page_title'] = __('meta.contact.title');
    $page_data['page_description'] = __('meta.contact.description');
    
    if (function_exists('generateCSRF')) {
        $page_data['csrf_token'] = generateCSRF();
    } else {
        $page_data['csrf_token'] = '';
    }
    
    // Traiter le formulaire si POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && function_exists('processContactForm')) {
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
            
            if (function_exists('confirmNewsletterSubscription')) {
                $result = confirmNewsletterSubscription($param);
                $page_data['confirmation_result'] = $result;
            }
            
            renderPage('newsletter/confirm', $page_data);
            break;
            
        case 'unsubscribe':
            // Désabonnement
            if (!$param) {
                handle404();
                return;
            }
            
            if (function_exists('unsubscribeNewsletter')) {
                $result = unsubscribeNewsletter($param);
                $page_data['unsubscribe_result'] = $result;
            }
            
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
    
    $query = isset($_GET['q']) ? (function_exists('clean') ? clean($_GET['q']) : htmlspecialchars($_GET['q'])) : '';
    $page = (int)($_GET['page'] ?? 1);
    
    if (empty($query)) {
        $page_data['search_results'] = [];
        $page_data['search_query'] = '';
    } else {
        if (function_exists('performSearch')) {
            $page_data['search_results'] = performSearch($query, $page);
        } else {
            $page_data['search_results'] = [];
        }
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
    
    if (function_exists('generateSitemap')) {
        $sitemap = generateSitemap();
    } else {
        // Sitemap basique si la fonction n'existe pas
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $sitemap .= '<url><loc>' . (isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : '') . '/</loc></url>' . "\n";
        $sitemap .= '</urlset>';
    }
    
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
// FONCTIONS UTILITAIRES (si non définies ailleurs)
// ===============================

/**
 * Récupérer une review par slug
 */
if (!function_exists('getReviewBySlug')) {
    function getReviewBySlug($slug) {
        if (!function_exists('getDB')) {
            return null;
        }
        
        try {
            $db = getDB('main');
            $stmt = $db->prepare("
                SELECT * FROM reviews 
                WHERE slug = ? AND status = 'published'
            ");
            $stmt->execute([$slug]);
            return $stmt->fetch();
        } catch (Exception $e) {
            if (function_exists('logError')) {
                logError("Error fetching review by slug: " . $e->getMessage());
            }
            return null;
        }
    }
}

/**
 * Récupérer un article de blog par slug
 */
if (!function_exists('getBlogPostBySlug')) {
    function getBlogPostBySlug($slug) {
        if (!function_exists('getDB')) {
            return null;
        }
        
        try {
            $db = getDB('blog');
            $stmt = $db->prepare("
                SELECT * FROM articles 
                WHERE slug = ? AND status = 'published'
            ");
            $stmt->execute([$slug]);
            return $stmt->fetch();
        } catch (Exception $e) {
            if (function_exists('logError')) {
                logError("Error fetching blog post by slug: " . $e->getMessage());
            }
            return null;
        }
    }
}

/**
 * Récupérer les catégories de reviews
 */
if (!function_exists('getReviewCategories')) {
    function getReviewCategories() {
        if (!function_exists('getDB')) {
            return [];
        }
        
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
            if (function_exists('logError')) {
                logError("Error fetching review categories: " . $e->getMessage());
            }
            return [];
        }
    }
}

/**
 * Récupérer les catégories de blog
 */
if (!function_exists('getBlogCategories')) {
    function getBlogCategories() {
        if (!function_exists('getDB')) {
            return [];
        }
        
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
            if (function_exists('logError')) {
                logError("Error fetching blog categories: " . $e->getMessage());
            }
            return [];
        }
    }
}

/**
 * Récupérer les deals actifs
 */
if (!function_exists('getActiveDeals')) {
    function getActiveDeals() {
        if (!function_exists('getDB')) {
            return [];
        }
        
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
            if (function_exists('logError')) {
                logError("Error fetching deals: " . $e->getMessage());
            }
            return [];
        }
    }
}

/**
 * Traiter le formulaire de contact
 */
if (!function_exists('processContactForm')) {
    function processContactForm($data) {
        if (!function_exists('validateForm')) {
            return ['success' => false, 'error' => 'Validation function not available'];
        }
        
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
        if (function_exists('verifyCSRF') && !verifyCSRF($data['csrf_token'] ?? '')) {
            return ['success' => false, 'error' => 'Invalid security token'];
        }
        
        // Sauvegarder le message
        try {
            if (!function_exists('getDB')) {
                return ['success' => false, 'error' => 'Database connection not available'];
            }
            
            $db = getDB('main');
            $stmt = $db->prepare("
                INSERT INTO contact_messages (name, email, subject, message, ip_address, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $clean_func = function_exists('clean') ? 'clean' : 'htmlspecialchars';
            
            $stmt->execute([
                $clean_func($data['name']),
                $clean_func($data['email']),
                $clean_func($data['subject'] ?? 'Website Contact'),
                $clean_func($data['message']),
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            return ['success' => true, 'message' => __('contact.success')];
            
        } catch (Exception $e) {
            if (function_exists('logError')) {
                logError("Contact form error: " . $e->getMessage());
            }
            return ['success' => false, 'error' => 'Failed to send message'];
        }
    }
}

/**
 * Confirmer l'abonnement newsletter
 */
if (!function_exists('confirmNewsletterSubscription')) {
    function confirmNewsletterSubscription($token) {
        if (!function_exists('getDB')) {
            return ['success' => false, 'error' => 'Database connection not available'];
        }
        
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
            if (function_exists('logError')) {
                logError("Newsletter confirmation error: " . $e->getMessage());
            }
            return ['success' => false, 'error' => 'Confirmation failed'];
        }
    }
}

/**
 * Désabonner de la newsletter
 */
if (!function_exists('unsubscribeNewsletter')) {
    function unsubscribeNewsletter($token) {
        if (!function_exists('getDB')) {
            return ['success' => false, 'error' => 'Database connection not available'];
        }
        
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
            if (function_exists('logError')) {
                logError("Newsletter unsubscribe error: " . $e->getMessage());
            }
            return ['success' => false, 'error' => 'Unsubscribe failed'];
        }
    }
}

/**
 * Effectuer une recherche
 */
if (!function_exists('performSearch')) {
    function performSearch($query, $page = 1) {
        if (!function_exists('getDB')) {
            return ['reviews' => [], 'articles' => [], 'total' => 0];
        }
        
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
            if (function_exists('logError')) {
                logError("Search error: " . $e->getMessage());
            }
            return ['reviews' => [], 'articles' => [], 'total' => 0];
        }
    }
}

/**
 * Générer le sitemap XML
 */
if (!function_exists('generateSitemap')) {
    function generateSitemap() {
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sitemap .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Pages principales
        $pages = ['', 'products', 'reviews', 'blog', 'deals', 'contact', 'about'];
        foreach ($pages as $page) {
            $sitemap .= '<url>' . "\n";
            $sitemap .= '<loc>' . $base_url . '/' . $page . '</loc>' . "\n";
            $sitemap .= '<changefreq>weekly</changefreq>' . "\n";
            $sitemap .= '<priority>0.8</priority>' . "\n";
            $sitemap .= '</url>' . "\n";
        }
        
        // Reviews
        if (function_exists('getDB')) {
            try {
                $db = getDB('main');
                $stmt = $db->prepare("SELECT slug, updated_at FROM reviews WHERE status = 'published'");
                $stmt->execute();
                $reviews = $stmt->fetchAll();
                
                foreach ($reviews as $review) {
                    $sitemap .= '<url>' . "\n";
                    $sitemap .= '<loc>' . $base_url . '/reviews/' . $review['slug'] . '</loc>' . "\n";
                    $sitemap .= '<lastmod>' . date('c', strtotime($review['updated_at'])) . '</lastmod>' . "\n";
                    $sitemap .= '<changefreq>monthly</changefreq>' . "\n";
                    $sitemap .= '<priority>0.6</priority>' . "\n";
                    $sitemap .= '</url>' . "\n";
                }
            } catch (Exception $e) {
                if (function_exists('logError')) {
                    logError("Sitemap reviews error: " . $e->getMessage());
                }
            }
            
            // Articles de blog
            try {
                $db = getDB('blog');
                $stmt = $db->prepare("SELECT slug, updated_at FROM articles WHERE status = 'published'");
                $stmt->execute();
                $articles = $stmt->fetchAll();
                
                foreach ($articles as $article) {
                    $sitemap .= '<url>' . "\n";
                    $sitemap .= '<loc>' . $base_url . '/blog/' . $article['slug'] . '</loc>' . "\n";
                    $sitemap .= '<lastmod>' . date('c', strtotime($article['updated_at'])) . '</lastmod>' . "\n";
                    $sitemap .= '<changefreq>monthly</changefreq>' . "\n";
                    $sitemap .= '<priority>0.6</priority>' . "\n";
                    $sitemap .= '</url>' . "\n";
                }
            } catch (Exception $e) {
                if (function_exists('logError')) {
                    logError("Sitemap blog error: " . $e->getMessage());
                }
            }
        }
        
        $sitemap .= '</urlset>';
        
        return $sitemap;
    }
}

// Fin du fichier router.php