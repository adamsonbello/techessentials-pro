<?php
/**
 * TechEssentials Pro - Reviews Controller
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 */

class ReviewsController {
    private $db;
    
    public function __construct() {
        $this->db = getDB('main');
    }
    
    /**
     * GET /api/reviews
     * GET /api/reviews/{category}
     * Récupérer toutes les reviews ou par catégorie
     */
    public function getIndex($data, $category = null) {
        try {
            $limit = min((int)($data['limit'] ?? 10), 50); // Max 50 reviews
            $offset = (int)($data['offset'] ?? 0);
            $lang = clean($data['lang'] ?? DEFAULT_LANG);
            $featured_only = isset($data['featured']) && $data['featured'] === 'true';
            $order_by = clean($data['order_by'] ?? 'created_at');
            $order_dir = strtoupper(clean($data['order_dir'] ?? 'DESC'));
            
            // Validation des paramètres
            $allowed_order_by = ['created_at', 'rating', 'title', 'views'];
            if (!in_array($order_by, $allowed_order_by)) {
                $order_by = 'created_at';
            }
            
            if (!in_array($order_dir, ['ASC', 'DESC'])) {
                $order_dir = 'DESC';
            }
            
            // Construction de la requête
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
            
            // Requête principale
            $sql = "
                SELECT 
                    id, title, slug, excerpt, category, rating, 
                    featured_image, affiliate_link, price, discount_price,
                    is_featured, views, created_at
                FROM reviews 
                WHERE {$where_sql}
                ORDER BY {$order_by} {$order_dir}
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $reviews = $stmt->fetchAll();
            
            // Compter le total
            $count_sql = "SELECT COUNT(*) as total FROM reviews WHERE {$where_sql}";
            $count_params = array_slice($params, 0, -2); // Enlever limit et offset
            $stmt = $this->db->prepare($count_sql);
            $stmt->execute($count_params);
            $total = $stmt->fetch()['total'];
            
            // Formater les données
            $formatted_reviews = array_map([$this, 'formatReviewData'], $reviews);
            
            return json_encode([
                'success' => true,
                'data' => $formatted_reviews,
                'meta' => [
                    'total' => (int)$total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => ($offset + $limit) < $total
                ]
            ]);
            
        } catch (Exception $e) {
            logError("Reviews fetch error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Failed to fetch reviews',
                'code' => 'FETCH_FAILED'
            ]);
        }
    }
    
    /**
     * GET /api/reviews/single/{id_or_slug}
     * Récupérer une review spécifique
     */
    public function getSingle($data, $id_or_slug = null) {
        if (!$id_or_slug) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'error' => 'Review ID or slug required',
                'code' => 'ID_REQUIRED'
            ]);
        }
        
        try {
            // Déterminer si c'est un ID ou un slug
            $is_id = is_numeric($id_or_slug);
            $field = $is_id ? 'id' : 'slug';
            
            $stmt = $this->db->prepare("
                SELECT 
                    id, title, slug, content, excerpt, category, rating,
                    featured_image, gallery_images, affiliate_link, 
                    price, discount_price, specifications, pros, cons,
                    is_featured, views, meta_title, meta_description,
                    created_at, updated_at
                FROM reviews 
                WHERE {$field} = ? AND status = 'published'
            ");
            $stmt->execute([$id_or_slug]);
            $review = $stmt->fetch();
            
            if (!$review) {
                http_response_code(404);
                return json_encode([
                    'success' => false,
                    'error' => 'Review not found',
                    'code' => 'NOT_FOUND'
                ]);
            }
            
            // Incrémenter les vues
            $this->incrementViews($review['id']);
            
            // Formater les données
            $formatted_review = $this->formatReviewData($review, true); // detailed = true
            
            // Récupérer les reviews similaires
            $similar_reviews = $this->getSimilarReviews($review['category'], $review['id']);
            
            return json_encode([
                'success' => true,
                'data' => $formatted_review,
                'similar' => $similar_reviews
            ]);
            
        } catch (Exception $e) {
            logError("Single review fetch error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Failed to fetch review',
                'code' => 'FETCH_FAILED'
            ]);
        }
    }
    
    /**
     * GET /api/reviews/categories
     * Récupérer les catégories de reviews avec statistiques
     */
    public function getCategories($data, $id = null) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    category,
                    COUNT(*) as count,
                    AVG(rating) as avg_rating,
                    MAX(created_at) as latest_review
                FROM reviews 
                WHERE status = 'published'
                GROUP BY category
                ORDER BY count DESC
            ");
            $stmt->execute();
            $categories = $stmt->fetchAll();
            
            // Formater les données
            $formatted_categories = array_map(function($cat) {
                return [
                    'name' => $cat['category'],
                    'count' => (int)$cat['count'],
                    'avg_rating' => round($cat['avg_rating'], 1),
                    'latest_review' => $cat['latest_review']
                ];
            }, $categories);
            
            return json_encode([
                'success' => true,
                'data' => $formatted_categories
            ]);
            
        } catch (Exception $e) {
            logError("Categories fetch error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Failed to fetch categories',
                'code' => 'FETCH_FAILED'
            ]);
        }
    }
    
    /**
     * GET /api/reviews/featured
     * Récupérer les reviews mises en avant
     */
    public function getFeatured($data, $id = null) {
        try {
            $limit = min((int)($data['limit'] ?? 6), 20);
            
            $stmt = $this->db->prepare("
                SELECT 
                    id, title, slug, excerpt, category, rating,
                    featured_image, price, discount_price, views
                FROM reviews 
                WHERE status = 'published' AND is_featured = 1
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $reviews = $stmt->fetchAll();
            
            $formatted_reviews = array_map([$this, 'formatReviewData'], $reviews);
            
            return json_encode([
                'success' => true,
                'data' => $formatted_reviews
            ]);
            
        } catch (Exception $e) {
            logError("Featured reviews fetch error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Failed to fetch featured reviews',
                'code' => 'FETCH_FAILED'
            ]);
        }
    }
    
    /**
     * GET /api/reviews/search
     * Recherche dans les reviews
     */
    public function getSearch($data, $id = null) {
        $query = clean($data['q'] ?? '');
        
        if (empty($query)) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'error' => 'Search query required',
                'code' => 'QUERY_REQUIRED'
            ]);
        }
        
        try {
            $limit = min((int)($data['limit'] ?? 10), 50);
            $offset = (int)($data['offset'] ?? 0);
            
            $stmt = $this->db->prepare("
                SELECT 
                    id, title, slug, excerpt, category, rating,
                    featured_image, price, discount_price, views
                FROM reviews 
                WHERE status = 'published' 
                AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ? OR category LIKE ?)
                ORDER BY 
                    CASE 
                        WHEN title LIKE ? THEN 1
                        WHEN excerpt LIKE ? THEN 2
                        ELSE 3
                    END,
                    created_at DESC
                LIMIT ? OFFSET ?
            ");
            
            $search_term = "%{$query}%";
            $params = array_fill(0, 6, $search_term);
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt->execute($params);
            $reviews = $stmt->fetchAll();
            
            // Compter le total
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM reviews 
                WHERE status = 'published' 
                AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ? OR category LIKE ?)
            ");
            $stmt->execute([$search_term, $search_term, $search_term, $search_term]);
            $total = $stmt->fetch()['total'];
            
            $formatted_reviews = array_map([$this, 'formatReviewData'], $reviews);
            
            return json_encode([
                'success' => true,
                'data' => $formatted_reviews,
                'meta' => [
                    'total' => (int)$total,
                    'query' => $query,
                    'limit' => $limit,
                    'offset' => $offset
                ]
            ]);
            
        } catch (Exception $e) {
            logError("Review search error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Search failed',
                'code' => 'SEARCH_FAILED'
            ]);
        }
    }
    
    /**
     * Formater les données d'une review
     */
    private function formatReviewData($review, $detailed = false) {
        $formatted = [
            'id' => (int)$review['id'],
            'title' => $review['title'],
            'slug' => $review['slug'],
            'excerpt' => $review['excerpt'],
            'category' => $review['category'],
            'rating' => (float)$review['rating'],
            'featured_image' => $review['featured_image'],
            'is_featured' => (bool)$review['is_featured'],
            'views' => (int)$review['views'],
            'created_at' => $review['created_at']
        ];
        
        // Prix avec gestion de la promotion
        if (isset($review['price']) && $review['price']) {
            $formatted['price'] = [
                'regular' => (float)$review['price'],
                'discount' => $review['discount_price'] ? (float)$review['discount_price'] : null,
                'currency' => 'USD' // TODO: Rendre configurable
            ];
            
            if ($review['discount_price']) {
                $formatted['price']['savings'] = $review['price'] - $review['discount_price'];
                $formatted['price']['discount_percentage'] = round((($review['price'] - $review['discount_price']) / $review['price']) * 100);
            }
        }
        
        // Données détaillées pour la vue single
        if ($detailed) {
            $formatted['content'] = $review['content'] ?? '';
            $formatted['affiliate_link'] = $review['affiliate_link'] ?? '';
            $formatted['gallery_images'] = $review['gallery_images'] ? json_decode($review['gallery_images'], true) : [];
            $formatted['specifications'] = $review['specifications'] ? json_decode($review['specifications'], true) : [];
            $formatted['pros'] = $review['pros'] ? json_decode($review['pros'], true) : [];
            $formatted['cons'] = $review['cons'] ? json_decode($review['cons'], true) : [];
            $formatted['meta'] = [
                'title' => $review['meta_title'],
                'description' => $review['meta_description']
            ];
            $formatted['updated_at'] = $review['updated_at'];
        }
        
        return $formatted;
    }
    
    /**
     * Incrémenter le nombre de vues
     */
    private function incrementViews($review_id) {
        try {
            $stmt = $this->db->prepare("UPDATE reviews SET views = views + 1 WHERE id = ?");
            $stmt->execute([$review_id]);
        } catch (Exception $e) {
            logError("Failed to increment views for review {$review_id}: " . $e->getMessage());
        }
    }
    
    /**
     * Récupérer les reviews similaires
     */
    private function getSimilarReviews($category, $current_id, $limit = 4) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, title, slug, rating, featured_image, price, discount_price
                FROM reviews 
                WHERE category = ? AND id != ? AND status = 'published'
                ORDER BY rating DESC, views DESC
                LIMIT ?
            ");
            $stmt->execute([$category, $current_id, $limit]);
            $reviews = $stmt->fetchAll();
            
            return array_map([$this, 'formatReviewData'], $reviews);
            
        } catch (Exception $e) {
            logError("Failed to fetch similar reviews: " . $e->getMessage());
            return [];
        }
    }
}