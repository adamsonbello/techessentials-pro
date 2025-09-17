<?php
/**
 * TechEssentials Pro - Authentication Middleware
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 */

class AuthMiddleware {
    private $db;
    
    public function __construct() {
        $this->db = getDB('main');
    }
    
    /**
     * Vérifier l'authentification admin
     */
    public static function requireAdmin() {
        $auth = new self();
        return $auth->checkAdminAuth();
    }
    
    /**
     * Vérifier l'authentification utilisateur
     */
    public static function requireUser() {
        $auth = new self();
        return $auth->checkUserAuth();
    }
    
    /**
     * Vérifier l'authentification API via token
     */
    public static function requireAPIToken() {
        $auth = new self();
        return $auth->checkAPIToken();
    }
    
    /**
     * Middleware pour les routes admin
     */
    private function checkAdminAuth() {
        // Vérifier la session admin
        if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_role'])) {
            $this->unauthorizedResponse('Admin authentication required');
            return false;
        }
        
        try {
            // Vérifier que l'admin existe toujours
            $stmt = $this->db->prepare("
                SELECT id, username, role, status, last_login
                FROM admin_users 
                WHERE id = ? AND status = 'active'
            ");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch();
            
            if (!$admin) {
                // Session invalide
                session_destroy();
                $this->unauthorizedResponse('Invalid admin session');
                return false;
            }
            
            // Vérifier la durée de session
            $last_activity = $_SESSION['last_activity'] ?? 0;
            $session_timeout = SESSION_LIFETIME; // Défini dans config.php
            
            if (time() - $last_activity > $session_timeout) {
                session_destroy();
                $this->unauthorizedResponse('Session expired');
                return false;
            }
            
            // Mettre à jour l'activité
            $_SESSION['last_activity'] = time();
            
            // Mettre les infos admin dans les globals pour l'utiliser dans les contrôleurs
            $GLOBALS['current_admin'] = $admin;
            
            return true;
            
        } catch (Exception $e) {
            logError("Admin auth check error: " . $e->getMessage());
            $this->unauthorizedResponse('Authentication error');
            return false;
        }
    }
    
    /**
     * Middleware pour les routes utilisateur
     */
    private function checkUserAuth() {
        // Vérifier la session utilisateur
        if (!isset($_SESSION['user_id'])) {
            $this->unauthorizedResponse('User authentication required');
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT id, email, username, status, last_login
                FROM users 
                WHERE id = ? AND status = 'active'
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                session_destroy();
                $this->unauthorizedResponse('Invalid user session');
                return false;
            }
            
            $GLOBALS['current_user'] = $user;
            return true;
            
        } catch (Exception $e) {
            logError("User auth check error: " . $e->getMessage());
            $this->unauthorizedResponse('Authentication error');
            return false;
        }
    }
    
    /**
     * Middleware pour les API tokens
     */
    private function checkAPIToken() {
        $token = $this->extractToken();
        
        if (!$token) {
            $this->unauthorizedResponse('API token required');
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT id, name, permissions, rate_limit, last_used
                FROM api_tokens 
                WHERE token = ? AND status = 'active' AND (expires_at IS NULL OR expires_at > NOW())
            ");
            $stmt->execute([$token]);
            $api_token = $stmt->fetch();
            
            if (!$api_token) {
                $this->unauthorizedResponse('Invalid API token');
                return false;
            }
            
            // Mettre à jour la date de dernière utilisation
            $stmt = $this->db->prepare("UPDATE api_tokens SET last_used = NOW(), usage_count = usage_count + 1 WHERE id = ?");
            $stmt->execute([$api_token['id']]);
            
            $GLOBALS['current_api_token'] = $api_token;
            return true;
            
        } catch (Exception $e) {
            logError("API token check error: " . $e->getMessage());
            $this->unauthorizedResponse('Authentication error');
            return false;
        }
    }
    
    /**
     * Extraire le token de la requête
     */
    private function extractToken() {
        // Vérifier dans l'header Authorization
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
            if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
                return trim($matches[1]);
            }
        }
        
        // Vérifier dans l'header X-API-Token
        if (isset($_SERVER['HTTP_X_API_TOKEN'])) {
            return trim($_SERVER['HTTP_X_API_TOKEN']);
        }
        
        // Vérifier dans les paramètres GET/POST
        return $_GET['api_token'] ?? $_POST['api_token'] ?? null;
    }
    
    /**
     * Vérifier les permissions spécifiques
     */
    public static function checkPermission($permission) {
        if (isset($GLOBALS['current_admin'])) {
            $role = $GLOBALS['current_admin']['role'];
            return self::adminHasPermission($role, $permission);
        }
        
        if (isset($GLOBALS['current_api_token'])) {
            $permissions = json_decode($GLOBALS['current_api_token']['permissions'], true) ?? [];
            return in_array($permission, $permissions) || in_array('*', $permissions);
        }
        
        return false;
    }
    
    /**
     * Vérifier les permissions admin selon le rôle
     */
    private static function adminHasPermission($role, $permission) {
        $role_permissions = [
            'super_admin' => ['*'], // Toutes les permissions
            'admin' => [
                'read_users', 'write_users',
                'read_articles', 'write_articles',
                'read_reviews', 'write_reviews',
                'read_newsletter', 'write_newsletter',
                'read_contact', 'write_contact',
                'read_analytics'
            ],
            'editor' => [
                'read_articles', 'write_articles',
                'read_reviews', 'write_reviews',
                'read_newsletter'
            ],
            'moderator' => [
                'read_contact', 'write_contact',
                'read_comments', 'write_comments'
            ]
        ];
        
        $permissions = $role_permissions[$role] ?? [];
        return in_array('*', $permissions) || in_array($permission, $permissions);
    }
    
    /**
     * Réponse d'erreur d'authentification
     */
    private function unauthorizedResponse($message = 'Authentication required') {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message,
            'code' => 'UNAUTHORIZED'
        ]);
        exit();
    }
    
    /**
     * Réponse d'erreur de permission
     */
    public static function forbiddenResponse($message = 'Insufficient permissions') {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $message,
            'code' => 'FORBIDDEN'
        ]);
        exit();
    }
    
    /**
     * Login admin
     */
    public function loginAdmin($username, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, username, password_hash, role, status, failed_attempts, locked_until
                FROM admin_users 
                WHERE username = ?
            ");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if (!$admin) {
                return ['success' => false, 'error' => 'Invalid credentials'];
            }
            
            // Vérifier si le compte est verrouillé
            if ($admin['locked_until'] && strtotime($admin['locked_until']) > time()) {
                return ['success' => false, 'error' => 'Account temporarily locked'];
            }
            
            if ($admin['status'] !== 'active') {
                return ['success' => false, 'error' => 'Account inactive'];
            }
            
            // Vérifier le mot de passe
            if (!password_verify($password, $admin['password_hash'])) {
                // Incrémenter les tentatives échouées
                $failed_attempts = $admin['failed_attempts'] + 1;
                $locked_until = null;
                
                // Verrouiller après 5 tentatives échouées
                if ($failed_attempts >= 5) {
                    $locked_until = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                }
                
                $stmt = $this->db->prepare("
                    UPDATE admin_users 
                    SET failed_attempts = ?, locked_until = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$failed_attempts, $locked_until, $admin['id']]);
                
                return ['success' => false, 'error' => 'Invalid credentials'];
            }
            
            // Connexion réussie
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['last_activity'] = time();
            
            // Réinitialiser les tentatives échouées et mettre à jour le last_login
            $stmt = $this->db->prepare("
                UPDATE admin_users 
                SET failed_attempts = 0, locked_until = NULL, last_login = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$admin['id']]);
            
            return [
                'success' => true,
                'admin' => [
                    'id' => $admin['id'],
                    'username' => $admin['username'],
                    'role' => $admin['role']
                ]
            ];
            
        } catch (Exception $e) {
            logError("Admin login error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Login failed'];
        }
    }
    
    /**
     * Logout admin
     */
    public function logoutAdmin() {
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_role']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['last_activity']);
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    /**
     * Générer un token API
     */
    public function generateAPIToken($name, $permissions = [], $expires_in_days = null) {
        try {
            $token = bin2hex(random_bytes(32));
            $expires_at = $expires_in_days ? date('Y-m-d H:i:s', strtotime("+{$expires_in_days} days")) : null;
            
            $stmt = $this->db->prepare("
                INSERT INTO api_tokens (token, name, permissions, expires_at, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $token,
                $name,
                json_encode($permissions),
                $expires_at
            ]);
            
            return [
                'success' => true,
                'token' => $token,
                'expires_at' => $expires_at
            ];
            
        } catch (Exception $e) {
            logError("API token generation error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to generate token'];
        }
    }
}