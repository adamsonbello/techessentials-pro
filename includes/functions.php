<?php
/**
 * TechEssentials Pro - Fonctions Utilitaires
 * @author Adams (Fred) - CTO  
 * @version 2.0
 * @date 2025-09-16
 */

// Empêcher l'accès direct
if (!defined('TECHESSENTIALS_PRO')) {
    die('Direct access not allowed');
}

// ===============================
// TEMPLATE ENGINE SIMPLE
// ===============================

/**
 * Rendre une page avec template
 */
function renderPage($template, $data = [], $layout = 'main') {
    global $current_lang;
    
    // Extraire les données pour les variables de template
    extract($data);
    
    // Charger la langue
    Language::load($current_lang ?? DEFAULT_LANG);
    
    // Inclure le layout principal
    include INCLUDES_PATH . "layouts/{$layout}.php";
}

/**
 * Rendre une partie de template
 */
function renderPartial($partial, $data = []) {
    extract($data);
    
    $partial_file = INCLUDES_PATH . "partials/{$partial}.php";
    if (file_exists($partial_file)) {
        include $partial_file;
    } else {
        logError("Partial not found: {$partial}");
    }
}

/**
 * Inclure une section
 */
function includeSection($section, $data = []) {
    extract($data);
    
    $section_file = INCLUDES_PATH . "sections/{$section}.php";
    if (file_exists($section_file)) {
        include $section_file;
    } else {
        logError("Section not found: {$section}");
    }
}

// ===============================
// SEO HELPERS
// ===============================

/**
 * Générer les meta tags SEO
 */
function generateSEOMeta($title = '', $description = '', $keywords = '', $image = '', $url = '') {
    global $SEO_CONFIG;
    
    $title = $title ?: $SEO_CONFIG['default_title'];
    $description = $description ?: $SEO_CONFIG['default_description'];
    $keywords = $keywords ?: $SEO_CONFIG['default_keywords'];
    $image = $image ?: $SEO_CONFIG['og_image'];
    $url = $url ?: getCurrentURL();
    
    $meta = [];
    
    // Meta tags basiques
    $meta[] = '<title>' . clean($title) . '</title>';
    $meta[] = '<meta name="description" content="' . clean($description) . '">';
    $meta[] = '<meta name="keywords" content="' . clean($keywords) . '">';
    $meta[] = '<meta name="author" content="' . $SEO_CONFIG['author'] . '">';
    
    // Open Graph
    $meta[] = '<meta property="og:title" content="' . clean($title) . '">';
    $meta[] = '<meta property="og:description" content="' . clean($description) . '">';
    $meta[] = '<meta property="og:image" content="' . $image . '">';
    $meta[] = '<meta property="og:url" content="' . $url . '">';
    $meta[] = '<meta property="og:type" content="website">';
    $meta[] = '<meta property="og:site_name" content="TechEssentials Pro">';
    
    // Twitter Card
    $meta[] = '<meta name="twitter:card" content="summary_large_image">';
    $meta[] = '<meta name="twitter:site" content="' . $SEO_CONFIG['twitter_handle'] . '">';
    $meta[] = '<meta name="twitter:title" content="' . clean($title) . '">';
    $meta[] = '<meta name="twitter:description" content="' . clean($description) . '">';
    $meta[] = '<meta name="twitter:image" content="' . $image . '">';
    
    // Canonical URL
    $meta[] = '<link rel="canonical" href="' . $url . '">';
    
    return implode("\n", $meta);
}

/**
 * Générer le breadcrumb JSON-LD
 */
function generateBreadcrumb($items) {
    $breadcrumb = [
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "itemListElement" => []
    ];
    
    foreach ($items as $position => $item) {
        $breadcrumb["itemListElement"][] = [
            "@type" => "ListItem",
            "position" => $position + 1,
            "name" => $item['name'],
            "item" => $item['url'] ?? null
        ];
    }
    
    return '<script type="application/ld+json">' . json_encode($breadcrumb) . '</script>';
}

// ===============================
// URL & ROUTING HELPERS
// ===============================

/**
 * Obtenir l'URL actuelle
 */
function getCurrentURL() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Obtenir le slug de l'URL
 */
function getCurrentSlug() {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    return trim(str_replace('/techessentialspro', '', $path), '/');
}

/**
 * Vérifier si une URL est active
 */
function isActiveURL($url_pattern) {
    $current = getCurrentSlug();
    
    if ($url_pattern === '' && $current === '') {
        return true; // Page d'accueil
    }
    
    return strpos($current, $url_pattern) === 0;
}

/**
 * Générer une URL propre
 */
function generateSlug($text) {
    // Remplacer les caractères spéciaux
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    $text = trim($text, '-');
    
    return $text;
}

// ===============================
// DATA HELPERS
// ===============================

/**
 * Récupérer les reviews avec cache
 */
function getReviews($limit = 10, $category = null, $featured_only = false) {
    static $cache = [];
    $cache_key = "reviews_{$limit}_{$category}_{$featured_only}";
    
    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }
    
    try {
        $db = getDB('main');
        
        $where_clauses = ["status = 'published'"];
        $params = [];
        
        if ($category) {
            $where_clauses[] = "category = ?";
            $params[] = $category;
        }
        
        if ($featured_only) {
            $where_clauses[] = "is_featured = 1";
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        $params[] = (int)$limit;
        
        $stmt = $db->prepare("
            SELECT id, title, slug, excerpt, rating, featured_image, 
                   price, discount_price, created_at
            FROM reviews 
            WHERE {$where_sql}
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        
        $stmt->execute($params);
        $reviews = $stmt->fetchAll();
        
        $cache[$cache_key] = $reviews;
        return $reviews;
        
    } catch (Exception $e) {
        logError("Error fetching reviews: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupérer les articles de blog avec cache
 */
function getBlogPosts($limit = 5, $category = null) {
    static $cache = [];
    $cache_key = "blog_{$limit}_{$category}";
    
    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }
    
    try {
        $db = getDB('blog');
        
        $where_clause = "status = 'published'";
        $params = [];
        
        if ($category) {
            $where_clause .= " AND category = ?";
            $params[] = $category;
        }
        
        $params[] = (int)$limit;
        
        $stmt = $db->prepare("
            SELECT id, title, slug, excerpt, featured_image, 
                   views, reading_time, created_at
            FROM articles 
            WHERE {$where_clause}
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        
        $stmt->execute($params);
        $posts = $stmt->fetchAll();
        
        $cache[$cache_key] = $posts;
        return $posts;
        
    } catch (Exception $e) {
        logError("Error fetching blog posts: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupérer les statistiques du site
 */
function getSiteStats() {
    static $stats = null;
    
    if ($stats !== null) {
        return $stats;
    }
    
    try {
        $db_main = getDB('main');
        $db_blog = getDB('blog');
        
        // Statistiques reviews
        $stmt = $db_main->prepare("SELECT COUNT(*) as count FROM reviews WHERE status = 'published'");
        $stmt->execute();
        $reviews_count = $stmt->fetch()['count'];
        
        // Statistiques newsletter
        $stmt = $db_main->prepare("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE status = 'active'");
        $stmt->execute();
        $subscribers_count = $stmt->fetch()['count'];
        
        // Statistiques blog
        $stmt = $db_blog->prepare("SELECT COUNT(*) as count FROM articles WHERE status = 'published'");
        $stmt->execute();
        $articles_count = $stmt->fetch()['count'];
        
        // Note moyenne des reviews
        $stmt = $db_main->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE status = 'published'");
        $stmt->execute();
        $avg_rating = round($stmt->fetch()['avg_rating'] ?? 0, 1);
        
        $stats = [
            'reviews' => (int)$reviews_count,
            'subscribers' => (int)$subscribers_count,
            'articles' => (int)$articles_count,
            'avg_rating' => $avg_rating
        ];
        
        return $stats;
        
    } catch (Exception $e) {
        logError("Error fetching site stats: " . $e->getMessage());
        return [
            'reviews' => 0,
            'subscribers' => 0,
            'articles' => 0,
            'avg_rating' => 0
        ];
    }
}

// ===============================
// FORM HELPERS
// ===============================

/**
 * Générer un champ de formulaire avec validation
 */
function renderFormField($type, $name, $value = '', $attributes = [], $errors = []) {
    $id = $attributes['id'] ?? $name;
    $class = $attributes['class'] ?? '';
    $placeholder = $attributes['placeholder'] ?? '';
    $required = isset($attributes['required']) ? 'required' : '';
    
    $error_class = isset($errors[$name]) ? 'error' : '';
    $field_class = trim("form-field {$class} {$error_class}");
    
    $html = "<div class=\"{$field_class}\">";
    
    // Label
    if (isset($attributes['label'])) {
        $html .= "<label for=\"{$id}\">{$attributes['label']}</label>";
    }
    
    // Champ
    switch ($type) {
        case 'textarea':
            $rows = $attributes['rows'] ?? 4;
            $html .= "<textarea id=\"{$id}\" name=\"{$name}\" rows=\"{$rows}\" placeholder=\"{$placeholder}\" {$required}>" . clean($value) . "</textarea>";
            break;
            
        case 'select':
            $options = $attributes['options'] ?? [];
            $html .= "<select id=\"{$id}\" name=\"{$name}\" {$required}>";
            foreach ($options as $option_value => $option_label) {
                $selected = ($value === $option_value) ? 'selected' : '';
                $html .= "<option value=\"{$option_value}\" {$selected}>{$option_label}</option>";
            }
            $html .= "</select>";
            break;
            
        default:
            $html .= "<input type=\"{$type}\" id=\"{$id}\" name=\"{$name}\" value=\"" . clean($value) . "\" placeholder=\"{$placeholder}\" {$required}>";
    }
    
    // Message d'erreur
    if (isset($errors[$name])) {
        $html .= "<span class=\"error-message\">{$errors[$name]}</span>";
    }
    
    $html .= "</div>";
    
    return $html;
}

/**
 * Valider les données de formulaire
 */
function validateForm($rules, $data) {
    $errors = [];
    
    foreach ($rules as $field => $rule_set) {
        $value = $data[$field] ?? '';
        
        foreach ($rule_set as $rule) {
            $rule_parts = explode(':', $rule);
            $rule_name = $rule_parts[0];
            $rule_param = $rule_parts[1] ?? null;
            
            switch ($rule_name) {
                case 'required':
                    if (empty($value)) {
                        $errors[$field] = ucfirst($field) . ' is required';
                        continue 2;
                    }
                    break;
                    
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = 'Invalid email format';
                    }
                    break;
                    
                case 'min':
                    if (strlen($value) < (int)$rule_param) {
                        $errors[$field] = ucfirst($field) . " must be at least {$rule_param} characters";
                    }
                    break;
                    
                case 'max':
                    if (strlen($value) > (int)$rule_param) {
                        $errors[$field] = ucfirst($field) . " must not exceed {$rule_param} characters";
                    }
                    break;
            }
        }
    }
    
    return $errors;
}

// ===============================
// IMAGE HELPERS
// ===============================

/**
 * Optimiser et redimensionner une image
 */
function optimizeImage($source_path, $destination_path, $max_width = 800, $max_height = 600, $quality = 85) {
    if (!file_exists($source_path)) {
        return false;
    }
    
    $image_info = getimagesize($source_path);
    if (!$image_info) {
        return false;
    }
    
    $mime_type = $image_info['mime'];
    
    // Créer l'image source
    switch ($mime_type) {
        case 'image/jpeg':
            $source_image = imagecreatefromjpeg($source_path);
            break;
        case 'image/png':
            $source_image = imagecreatefrompng($source_path);
            break;
        case 'image/webp':
            $source_image = imagecreatefromwebp($source_path);
            break;
        default:
            return false;
    }
    
    $source_width = imagesx($source_image);
    $source_height = imagesy($source_image);
    
    // Calculer les nouvelles dimensions
    $ratio = min($max_width / $source_width, $max_height / $source_height);
    $new_width = round($source_width * $ratio);
    $new_height = round($source_height * $ratio);
    
    // Créer la nouvelle image
    $new_image = imagecreatetruecolor($new_width, $new_height);
    
    // Préserver la transparence pour PNG
    if ($mime_type === 'image/png') {
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
        $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
        imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
    }
    
    // Redimensionner
    imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $source_width, $source_height);
    
    // Sauvegarder
    $result = false;
    switch ($mime_type) {
        case 'image/jpeg':
            $result = imagejpeg($new_image, $destination_path, $quality);
            break;
        case 'image/png':
            $result = imagepng($new_image, $destination_path, 9);
            break;
        case 'image/webp':
            $result = imagewebp($new_image, $destination_path, $quality);
            break;
    }
    
    // Nettoyer
    imagedestroy($source_image);
    imagedestroy($new_image);
    
    return $result;
}

/**
 * Générer une image responsive
 */
function generateResponsiveImage($image_path, $alt_text = '', $sizes = ['400', '800', '1200']) {
    $base_path = pathinfo($image_path, PATHINFO_DIRNAME);
    $filename = pathinfo($image_path, PATHINFO_FILENAME);
    $extension = pathinfo($image_path, PATHINFO_EXTENSION);
    
    $srcset = [];
    
    foreach ($sizes as $size) {
        $responsive_path = "{$base_path}/{$filename}-{$size}w.{$extension}";
        if (file_exists(ROOT_PATH . $responsive_path)) {
            $srcset[] = asset($responsive_path) . " {$size}w";
        }
    }
    
    $srcset_attr = implode(', ', $srcset);
    
    return "<img src=\"" . asset($image_path) . "\" 
                 srcset=\"{$srcset_attr}\" 
                 sizes=\"(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw\" 
                 alt=\"" . clean($alt_text) . "\" 
                 loading=\"lazy\">";
}

// ===============================
// PAGINATION HELPERS
// ===============================

/**
 * Générer la pagination
 */
function generatePagination($current_page, $total_pages, $base_url = '', $range = 2) {
    if ($total_pages <= 1) {
        return '';
    }
    
    $html = '<nav class="pagination">';
    $html .= '<ul class="pagination-list">';
    
    // Bouton Précédent
    if ($current_page > 1) {
        $prev_url = $base_url . '?page=' . ($current_page - 1);
        $html .= "<li><a href=\"{$prev_url}\" class=\"pagination-link prev\">&larr; Previous</a></li>";
    }
    
    // Première page
    if ($current_page > $range + 1) {
        $html .= '<li><a href="' . $base_url . '?page=1" class="pagination-link">1</a></li>';
        if ($current_page > $range + 2) {
            $html .= '<li><span class="pagination-ellipsis">...</span></li>';
        }
    }
    
    // Pages autour de la page actuelle
    for ($i = max(1, $current_page - $range); $i <= min($total_pages, $current_page + $range); $i++) {
        $active_class = ($i === $current_page) ? ' active' : '';
        $page_url = $base_url . '?page=' . $i;
        $html .= "<li><a href=\"{$page_url}\" class=\"pagination-link{$active_class}\">{$i}</a></li>";
    }
    
    // Dernière page
    if ($current_page < $total_pages - $range) {
        if ($current_page < $total_pages - $range - 1) {
            $html .= '<li><span class="pagination-ellipsis">...</span></li>';
        }
        $last_url = $base_url . '?page=' . $total_pages;
        $html .= "<li><a href=\"{$last_url}\" class=\"pagination-link\">{$total_pages}</a></li>";
    }
    
    // Bouton Suivant
    if ($current_page < $total_pages) {
        $next_url = $base_url . '?page=' . ($current_page + 1);
        $html .= "<li><a href=\"{$next_url}\" class=\"pagination-link next\">Next &rarr;</a></li>";
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

// ===============================
// DATE & TIME HELPERS
// ===============================

/**
 * Formater une date de façon relative
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);
    
    if ($diff->days > 30) {
        return $past->format('M j, Y');
    } elseif ($diff->days > 0) {
        return $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } elseif ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    } else {
        return 'Just now';
    }
}

/**
 * Calculer le temps de lecture estimé
 */
function calculateReadingTime($content) {
    $word_count = str_word_count(strip_tags($content));
    $minutes = ceil($word_count / 200); // 200 mots par minute
    return max(1, $minutes);
}

// ===============================
// CACHE HELPERS
// ===============================

/**
 * Cache simple basé sur les fichiers
 */
function cacheGet($key) {
    $cache_file = ROOT_PATH . 'cache/' . md5($key) . '.cache';
    
    if (!file_exists($cache_file)) {
        return null;
    }
    
    $data = file_get_contents($cache_file);
    $cache_data = json_decode($data, true);
    
    if (!$cache_data || $cache_data['expires'] < time()) {
        unlink($cache_file);
        return null;
    }
    
    return $cache_data['data'];
}

/**
 * Sauvegarder en cache
 */
function cacheSet($key, $data, $ttl = 3600) {
    $cache_dir = ROOT_PATH . 'cache/';
    if (!file_exists($cache_dir)) {
        mkdir($cache_dir, 0777, true);
    }
    
    $cache_file = $cache_dir . md5($key) . '.cache';
    $cache_data = [
        'data' => $data,
        'expires' => time() + $ttl
    ];
    
    return file_put_contents($cache_file, json_encode($cache_data));
}

/**
 * Vider le cache
 */
function cacheClear($pattern = '*') {
    $cache_dir = ROOT_PATH . 'cache/';
    $files = glob($cache_dir . '*.cache');
    
    foreach ($files as $file) {
        unlink($file);
    }
    
    return true;
}