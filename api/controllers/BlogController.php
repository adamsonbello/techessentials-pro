<?php
/**
 * TechEssentials Pro - Blog Controller
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 */

class BlogController {
    private $db;
    
    public function __construct() {
        $this->db = getDB('blog'); // Utilise la base de données blog
    }
    
    /**
     * GET /api/blog
     * GET /api/blog/{category}
     * Récupérer les articles du blog
     */
    public function getIndex($data, $category = null) {
        try {
            $limit = min((int)($data['limit'] ?? 10), 50);
            $offset = (int)($data['offset'] ?? 0);
            $lang = clean($data['lang'] ?? DEFAULT_LANG);
            $featured_only = isset($data['featured']) && $data['featured'] === 'true';
            $order_by = clean($data['order_by'] ?? 'created_at');
            $order_dir = strtoupper(clean($data['order_dir'] ?? 'DESC'));
            
            // Validation
            $allowed_order_by = ['created_at', 'title', 'views', 'updated_at'];
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
                    id, title, slug, excerpt, content, category, 
                    featured_image, is_featured, views, reading_time,
                    meta_title, meta_description, created_at, updated_at,
                    (SELECT COUNT(*) FROM comments WHERE article_id = articles.id AND status = 'approved') as comment_count
                FROM articles 
                WHERE {$where_sql}
                ORDER BY {$order_by} {$order_dir}
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $articles = $stmt->fetchAll();
            
            // Compter le total
            $count_sql = "SELECT COUNT(*) as total FROM articles WHERE {$where_sql}";
            $count_params = array_slice($params, 0, -2);
            $stmt = $this->db->prepare($count_sql);
            $stmt->execute($count_params);
            $total = $stmt->fetch()['total'];
            
            // Formater les données
            $formatted_articles = array_map([$this, 'formatArticleData'], $articles);
            
            return json_encode([
                'success' => true,
                'data' => $formatted_articles,
                'meta' => [
                    'total' => (int)$total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => ($offset + $limit) < $total
                ]
            ]);
            
        } catch (Exception $e) {
            logError("Blog articles fetch error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Failed to fetch articles',
                'code' => 'FETCH_FAILED'
            ]);
        }
    }
    
    /**
     * GET /api/blog/single/{id_or_slug}
     * Récupérer un article spécifique
     */
    public function getSingle($data, $id_or_slug = null) {
        if (!$id_or_slug) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'error' => 'Article ID or slug required',
                'code' => 'ID_REQUIRED'
            ]);
        }
        
        try {
            $is_id = is_numeric($id_or_slug);
            $field = $is_id ? 'id' : 'slug';
            
            $stmt = $this->db->prepare("
                SELECT 
                    a.*, 
                    (SELECT COUNT(*) FROM comments WHERE article_id = a.id AND status = 'approved') as comment_count,
                    (SELECT name FROM users WHERE id = a.author_id) as author_name
                FROM articles a
                WHERE a.{$field} = ? AND a.status = 'published'
            ");
            $stmt->execute([$id_or_slug]);
            $article = $stmt->fetch();
            
            if (!$article) {
                http_response_code(404);
                return json_encode([
                    'success' => false,
                    'error' => 'Article not found',
                    'code' => 'NOT_FOUND'
                ]);
            }
            
            // Incrémenter les vues
            $this->incrementViews($article['id']);
            
            // Récupérer les commentaires approuvés
            $comments = $this->getArticleComments($article['id']);
            
            // Articles similaires
            $related_articles = $this->getRelatedArticles($article['category'], $article['id']);
            
            // Formater les données
            $formatted_article = $this->formatArticleData($article, true);
            $formatted_article['comments'] = $comments;
            
            return json_encode([
                'success' => true,
                'data' => $formatted_article,
                'related' => $related_articles
            ]);
            
        } catch (Exception $e) {
            logError("Single article fetch error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Failed to fetch article',
                'code' => 'FETCH_FAILED'
            ]);
        }
    }
    
    /**
     * GET /api/blog/categories
     * Récupérer les catégories avec statistiques
     */
    public function getCategories($data, $id = null) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    category,
                    COUNT(*) as count,
                    MAX(created_at) as latest_article
                FROM articles 
                WHERE status = 'published'
                GROUP BY category
                ORDER BY count DESC
            ");
            $stmt->execute();
            $categories = $stmt->fetchAll();
            
            $formatted_categories = array_map(function($cat) {
                return [
                    'name' => $cat['category'],
                    'count' => (int)$cat['count'],
                    'latest_article' => $cat['latest_article']
                ];
            }, $categories);
            
            return json_encode([
                'success' => true,
                'data' => $formatted_categories
            ]);
            
        } catch (Exception $e) {
            logError("Blog categories fetch error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Failed to fetch categories',
                'code' => 'FETCH_FAILED'
            ]);
        }
    }
    
    /**
     * GET /api/blog/featured
     * Récupérer les articles en vedette
     */
    public function getFeatured($data, $id = null) {
        try {
            $limit = min((int)($data['limit'] ?? 5), 20);
            
            $stmt = $this->db->prepare("
                SELECT 
                    id, title, slug, excerpt, category, featured_image, 
                    views, reading_time, created_at
                FROM articles 
                WHERE status = 'published' AND is_featured = 1
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $articles = $stmt->fetchAll();
            
            $formatted_articles = array_map([$this, 'formatArticleData'], $articles);
            
            return json_encode([
                'success' => true,
                'data' => $formatted_articles
            ]);
            
        } catch (Exception $e) {
            logError("Featured articles fetch error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Failed to fetch featured articles',
                'code' => 'FETCH_FAILED'
            ]);
        }
    }
    
    /**
     * GET /api/blog/search
     * Recherche d'articles
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
                    id, title, slug, excerpt, category, featured_image,
                    views, reading_time, created_at
                FROM articles 
                WHERE status = 'published' 
                AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)
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
            $params = [$search_term, $search_term, $search_term, $search_term, $search_term, $limit, $offset];
            
            $stmt->execute($params);
            $articles = $stmt->fetchAll();
            
            // Compter le total
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM articles 
                WHERE status = 'published' 
                AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)
            ");
            $stmt->execute([$search_term, $search_term, $search_term]);
            $total = $stmt->fetch()['total'];
            
            $formatted_articles = array_map([$this, 'formatArticleData'], $articles);
            
            return json_encode([
                'success' => true,
                'data' => $formatted_articles,
                'meta' => [
                    'total' => (int)$total,
                    'query' => $query,
                    'limit' => $limit,
                    'offset' => $offset
                ]
            ]);
            
        } catch (Exception $e) {
            logError("Blog search error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Search failed',
                'code' => 'SEARCH_FAILED'
            ]);
        }
    }
    
    /**
     * POST /api/blog/comment/{article_id}
     * Ajouter un commentaire
     */
    public function postComment($data, $article_id = null) {
        if (!$article_id) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'error' => 'Article ID required',
                'code' => 'ARTICLE_ID_REQUIRED'
            ]);
        }
        
        try {
            // Validation
            $validation = $this->validateCommentData($data);
            if (!$validation['valid']) {
                http_response_code(400);
                return json_encode([
                    'success' => false,
                    'error' => $validation['error'],
                    'code' => 'VALIDATION_ERROR'
                ]);
            }
            
            // Vérifier que l'article existe
            $stmt = $this->db->prepare("SELECT id FROM articles WHERE id = ? AND status = 'published'");
            $stmt->execute([$article_id]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                return json_encode([
                    'success' => false,
                    'error' => 'Article not found',
                    'code' => 'ARTICLE_NOT_FOUND'
                ]);
            }
            
            $name = clean($data['name']);
            $email = clean($data['email']);
            $comment = clean($data['comment']);
            $parent_id = isset($data['parent_id']) ? (int)$data['parent_id'] : null;
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            
            // Statut par défaut (modération)
            $status = 'pending'; // Les commentaires nécessitent une approbation
            
            $stmt = $this->db->prepare("
                INSERT INTO comments 
                (article_id, parent_id, name, email, comment, ip_address, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([$article_id, $parent_id, $name, $email, $comment, $ip_address, $status]);
            $comment_id = $this->db->lastInsertId();
            
            return json_encode([
                'success' => true,
                'message' => 'Your comment has been submitted and is pending approval.',
                'code' => 'COMMENT_PENDING',
                'comment_id' => $comment_id
            ]);
            
        } catch (Exception $e) {
            logError("Blog comment error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Failed to submit comment',
                'code' => 'COMMENT_FAILED'
            ]);
        }
    }
    
    /**
     * Formater les données d'un article
     */
    private function formatArticleData($article, $detailed = false) {
        $formatted = [
            'id' => (int)$article['id'],
            'title' => $article['title'],
            'slug' => $article['slug'],
            'excerpt' => $article['excerpt'],
            'category' => $article['category'],
            'featured_image' => $article['featured_image'],
            'is_featured' => (bool)($article['is_featured'] ?? false),
            'views' => (int)($article['views'] ?? 0),
            'reading_time' => (int)($article['reading_time'] ?? 0),
            'comment_count' => (int)($article['comment_count'] ?? 0),
            'created_at' => $article['created_at'],
            'updated_at' => $article['updated_at'] ?? $article['created_at']
        ];
        
        if ($detailed) {
            $formatted['content'] = $article['content'] ?? '';
            $formatted['author'] = $article['author_name'] ?? 'Anonymous';
            $formatted['meta'] = [
                'title' => $article['meta_title'] ?? $article['title'],
                'description' => $article['meta_description'] ?? $article['excerpt']
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Incrémenter les vues
     */
    private function incrementViews($article_id) {
        try {
            $stmt = $this->db->prepare("UPDATE articles SET views = views + 1 WHERE id = ?");
            $stmt->execute([$article_id]);
        } catch (Exception $e) {
            logError("Failed to increment views for article {$article_id}: " . $e->getMessage());
        }
    }
    
    /**
     * Récupérer les commentaires d'un article
     */
    private function getArticleComments($article_id, $parent_id = null) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, name, comment, created_at, parent_id
                FROM comments 
                WHERE article_id = ? AND parent_id " . ($parent_id ? "= ?" : "IS NULL") . " AND status = 'approved'
                ORDER BY created_at ASC
            ");
            
            $params = [$article_id];
            if ($parent_id) {
                $params[] = $parent_id;
            }
            
            $stmt->execute($params);
            $comments = $stmt->fetchAll();
            
            // Récupérer les réponses pour chaque commentaire
            foreach ($comments as &$comment) {
                $comment['replies'] = $this->getArticleComments($article_id, $comment['id']);
                $comment['id'] = (int)$comment['id'];
            }
            
            return $comments;
            
        } catch (Exception $e) {
            logError("Failed to fetch comments for article {$article_id}: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer les articles similaires
     */
    private function getRelatedArticles($category, $current_id, $limit = 4) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, title, slug, featured_image, views, created_at
                FROM articles 
                WHERE category = ? AND id != ? AND status = 'published'
                ORDER BY views DESC, created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$category, $current_id, $limit]);
            $articles = $stmt->fetchAll();
            
            return array_map([$this, 'formatArticleData'], $articles);
            
        } catch (Exception $e) {
            logError("Failed to fetch related articles: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Validation des données de commentaire
     */
    private function validateCommentData($data) {
        $required_fields = ['name', 'email', 'comment'];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['valid' => false, 'error' => ucfirst($field) . ' is required'];
            }
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error' => 'Invalid email format'];
        }
        
        if (strlen($data['comment']) < 5 || strlen($data['comment']) > 2000) {
            return ['valid' => false, 'error' => 'Comment must be between 5 and 2000 characters'];
        }
        
        return ['valid' => true];
    }
}