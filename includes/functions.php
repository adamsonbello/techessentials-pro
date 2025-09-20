<?php
/**
 * TechEssentials Pro - Fonctions Produits
 * À ajouter dans includes/functions.php
 */

// ===============================
// FONCTIONS PRODUITS - CORE
// ===============================

/**
 * Récupérer les produits avec pagination et filtres
 */
function getProducts($limit = 20, $offset = 0, $category = '', $sort = 'featured', $search = '') {
    try {
        $db = getDB('main');
        
        // Construction de la requête
        $where = "WHERE status = 'published'";
        $params = [];
        
        // Filtre par catégorie
        if (!empty($category)) {
            $where .= " AND category = ?";
            $params[] = $category;
        }
        
        // Filtre par recherche
        if (!empty($search)) {
            $where .= " AND (name LIKE ? OR short_description LIKE ? OR brand LIKE ?)";
            $search_term = "%{$search}%";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        // Tri
        $order = "ORDER BY ";
        switch ($sort) {
            case 'price-low':
                $order .= "current_price ASC";
                break;
            case 'price-high':
                $order .= "current_price DESC";
                break;
            case 'newest':
                $order .= "created_at DESC";
                break;
            case 'rating':
                $order .= "rating DESC, review_count DESC";
                break;
            case 'popularity':
                $order .= "view_count DESC";
                break;
            default: // 'featured'
                $order .= "is_featured DESC, rating DESC, sort_order ASC";
        }
        
        // Compter le total
        $count_stmt = $db->prepare("SELECT COUNT(*) as total FROM products " . $where);
        $count_stmt->execute($params);
        $total = $count_stmt->fetch()['total'];
        
        // Récupérer les produits
        $sql = "
            SELECT 
                id, name, slug, short_description, brand, category,
                current_price, discount_price, discount_percentage,
                featured_image, rating, review_count, is_featured,
                view_count, stock_status,
                amazon_url, fnac_url, bestbuy_url,
                amazon_price, fnac_price, bestbuy_price,
                created_at
            FROM products 
            {$where} 
            {$order} 
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        
        return [
            'products' => $products,
            'total' => $total
        ];
        
    } catch (Exception $e) {
        logError("Error fetching products: " . $e->getMessage());
        return ['products' => [], 'total' => 0];
    }
}

/**
 * Récupérer les produits featured pour la home
 */
function getFeaturedProducts($limit = 6) {
    try {
        $db = getDB('main');
        $stmt = $db->prepare("
            SELECT 
                id, name, slug, short_description, brand, category,
                current_price, discount_price, discount_percentage,
                featured_image, rating, review_count,
                amazon_url, fnac_url, bestbuy_url
            FROM products 
            WHERE status = 'published' AND is_featured = 1
            ORDER BY sort_order ASC, rating DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        logError("Error fetching featured products: " . $e->getMessage());
        return [];
    }
}



function renderPage($template, $data = []) {
    echo "Page: $template<br>";
    echo "Data: ";
    print_r($data);
}



function getSiteStats() {
    return [
        'reviews' => 0,
        'subscribers' => 0, 
        'articles' => 0,
        'avg_rating' => 0
    ];
}

/**
 * Récupérer un produit par slug
 */
function getProductBySlug($slug) {
    try {
        $db = getDB('main');
        $stmt = $db->prepare("
            SELECT * FROM products 
            WHERE slug = ? AND status = 'published'
        ");
        $stmt->execute([$slug]);
        
        $product = $stmt->fetch();
        
        if ($product) {
            // Incrémenter le compteur de vues
            $update_stmt = $db->prepare("
                UPDATE products 
                SET view_count = view_count + 1 
                WHERE id = ?
            ");
            $update_stmt->execute([$product['id']]);
        }
        
        return $product;
        
    } catch (Exception $e) {
        logError("Error fetching product by slug: " . $e->getMessage());
        return null;
    }
}

/**
 * Récupérer les catégories de produits
 */
function getProductCategories() {
    try {
        $db = getDB('main');
        $stmt = $db->prepare("
            SELECT 
                category,
                COUNT(*) as product_count,
                MIN(current_price) as min_price,
                MAX(current_price) as max_price,
                AVG(rating) as avg_rating
            FROM products 
            WHERE status = 'published' 
            GROUP BY category 
            ORDER BY product_count DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Exception $e) {
        logError("Error fetching product categories: " . $e->getMessage());
        return [];
    }
}

/**
 * Récupérer produits similaires
 */
function getRelatedProducts($category, $exclude_id, $limit = 4) {
    try {
        $db = getDB('main');
        $stmt = $db->prepare("
            SELECT 
                id, name, slug, current_price, discount_price, 
                featured_image, rating, review_count, brand
            FROM products 
            WHERE category = ? AND id != ? AND status = 'published'
            ORDER BY rating DESC, review_count DESC
            LIMIT ?
        ");
        $stmt->execute([$category, $exclude_id, $limit]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        logError("Error fetching related products: " . $e->getMessage());
        return [];
    }
}

// ===============================
// FONCTIONS AFFILIATION & TRACKING
// ===============================

/**
 * Tracker un clic d'affiliation
 */
function trackAffiliateClick($product_id, $vendor, $price = null) {
    try {
        $db = getDB('main');
        
        // Enregistrer le clic
        $stmt = $db->prepare("
            INSERT INTO affiliate_clicks 
            (product_id, vendor, price_at_click, ip_address, user_agent, referrer, clicked_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $product_id,
            $vendor,
            $price,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            $_SERVER['HTTP_REFERER'] ?? ''
        ]);
        
        // Incrémenter le compteur du produit
        $counter_field = "click_count_{$vendor}";
        $update_stmt = $db->prepare("
            UPDATE products 
            SET {$counter_field} = {$counter_field} + 1 
            WHERE id = ?
        ");
        $update_stmt->execute([$product_id]);
        
        return $db->lastInsertId();
        
    } catch (Exception $e) {
        logError("Error tracking affiliate click: " . $e->getMessage());
        return null;
    }
}

/**
 * Récupérer l'URL d'affiliation avec tracking
 */
function getAffiliateUrl($product_id, $vendor) {
    try {
        $db = getDB('main');
        $stmt = $db->prepare("
            SELECT {$vendor}_url as affiliate_url, {$vendor}_price as price
            FROM products 
            WHERE id = ?
        ");
        $stmt->execute([$product_id]);
        $result = $stmt->fetch();
        
        if ($result && $result['affiliate_url']) {
            // Tracker le clic
            trackAffiliateClick($product_id, $vendor, $result['price']);
            
            return $result['affiliate_url'];
        }
        
        return '#';
        
    } catch (Exception $e) {
        logError("Error getting affiliate URL: " . $e->getMessage());
        return '#';
    }
}

// ===============================
// FONCTIONS PRIX & APIS (FUTURES)
// ===============================

/**
 * Mettre à jour les prix depuis les APIs vendors
 */
function updateProductPrices($product_id = null) {
    try {
        $db = getDB('main');
        
        $where = $product_id ? "WHERE id = ?" : "";
        $params = $product_id ? [$product_id] : [];
        
        $stmt = $db->prepare("
            SELECT id, amazon_url, fnac_url, bestbuy_url 
            FROM products 
            {$where}
            AND status = 'published'
        ");
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        
        foreach ($products as $product) {
            // TODO: Implémenter APIs Amazon/Fnac/BestBuy
            // Pour l'instant, simulation de mise à jour
            
            $updated_prices = [
                'amazon_price' => null,
                'fnac_price' => null,
                'bestbuy_price' => null,
                'amazon_updated_at' => null,
                'fnac_updated_at' => null,
                'bestbuy_updated_at' => null
            ];
            
            // Mise à jour en base (quand APIs seront intégrées)
            // updatePriceInDB($product['id'], $updated_prices);
        }
        
        return true;
        
    } catch (Exception $e) {
        logError("Error updating product prices: " . $e->getMessage());
        return false;
    }
}

/**
 * Sauvegarder l'historique des prix
 */
function savePriceHistory($product_id, $vendor, $price, $availability = true) {
    try {
        $db = getDB('main');
        $stmt = $db->prepare("
            INSERT INTO product_price_history 
            (product_id, vendor, price, availability, recorded_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([$product_id, $vendor, $price, $availability]);
        return true;
        
    } catch (Exception $e) {
        logError("Error saving price history: " . $e->getMessage());
        return false;
    }
}

// ===============================
// HELPERS & UTILITIES
// ===============================

/**
 * Rechercher des produits
 */
function searchProducts($query, $limit = 20) {
    try {
        $db = getDB('main');
        $stmt = $db->prepare("
            SELECT 
                id, name, slug, current_price, discount_price,
                featured_image, brand, category, rating
            FROM products 
            WHERE status = 'published'
            AND MATCH(name, short_description, brand, model) AGAINST(? IN NATURAL LANGUAGE MODE)
            ORDER BY rating DESC, view_count DESC
            LIMIT ?
        ");
        
        $stmt->execute([$query, $limit]);
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        // Fallback vers LIKE si fulltext index pas disponible
        $stmt = $db->prepare("
            SELECT 
                id, name, slug, current_price, discount_price,
                featured_image, brand, category, rating
            FROM products 
            WHERE status = 'published'
            AND (name LIKE ? OR short_description LIKE ? OR brand LIKE ?)
            ORDER BY 
                CASE 
                    WHEN name LIKE ? THEN 1
                    WHEN brand LIKE ? THEN 2
                    ELSE 3
                END,
                rating DESC
            LIMIT ?
        ");
        
        $search_term = "%{$query}%";
        $stmt->execute([
            $search_term, $search_term, $search_term,
            $search_term, $search_term, $limit
        ]);
        return $stmt->fetchAll();
    }
}

/**
 * Obtenir le meilleur prix parmi les vendors
 */
function getBestPrice($product) {
    $prices = array_filter([
        $product['amazon_price'] ?? null,
        $product['fnac_price'] ?? null,
        $product['bestbuy_price'] ?? null
    ]);
    
    return empty($prices) ? $product['current_price'] : min($prices);
}

/**
 * Formater le prix pour affichage
 */
function formatPrice($price, $currency = '€') {
    if (!$price) return 'Prix non disponible';
    
    return number_format($price, 2, ',', ' ') . ' ' . $currency;
}

/**
 * Calculer le pourcentage de réduction
 */
function calculateDiscount($original_price, $discount_price) {
    if (!$original_price || !$discount_price || $discount_price >= $original_price) {
        return 0;
    }
    
    return round((($original_price - $discount_price) / $original_price) * 100);
}
?>