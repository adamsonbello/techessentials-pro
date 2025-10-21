<?php
/**
 * TechEssentials Pro - Service Sécurité Commentaires
 * Anti-spam, validation, modération
 */

if (!defined('TECHESSENTIALS_PRO')) {
    die('Accès direct non autorisé');
}

class CommentSecurityService {
    
    private $config;
    private $errors = [];
    
    public function __construct() {
        $this->config = require __DIR__ . '/../comments-config.php';
    }
    
    // ============================================
    // VALIDATION COMMENTAIRE
    // ============================================
    
    public function validateComment($data) {
        $errors = [];
        
        // Nom
        if (empty($data['author_name']) || strlen($data['author_name']) < 2) {
            $errors[] = "Le nom doit contenir au moins 2 caractères";
        }
        
        // Email
        if (empty($data['author_email']) || !filter_var($data['author_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide";
        }
        
        // Contenu
        $content = trim($data['content'] ?? '');
        $min = $this->config['moderation']['min_comment_length'];
        $max = $this->config['moderation']['max_comment_length'];
        
        if (strlen($content) < $min) {
            $errors[] = "Le commentaire doit contenir au moins {$min} caractères";
        }
        
        if (strlen($content) > $max) {
            $errors[] = "Le commentaire ne peut pas dépasser {$max} caractères";
        }
        
        // Vérifier mots interdits
        if ($this->containsBannedWords($content)) {
            $errors[] = "Votre commentaire contient des mots interdits";
        }
        
        // Vérifier nombre de liens
        if ($this->countLinks($content) > $this->config['moderation']['max_links']) {
            $errors[] = "Trop de liens dans votre commentaire";
        }
        
        // Rate limiting
        if ($this->config['moderation']['rate_limit']['enabled']) {
            if (!$this->checkRateLimit($data['author_email'])) {
                $errors[] = "Vous avez atteint la limite de commentaires. Réessayez plus tard.";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    // ============================================
    // SOCKET AKISMET - À REMPLIR QUAND API PRÊTE
    // ============================================
    
    public function checkAkismetSpam($comment_data) {
        if (!$this->config['akismet']['enabled']) {
            return ['is_spam' => false, 'confidence' => 0];
        }
        
        try {
            // TODO: Implémenter Akismet API
            // Documentation: https://akismet.com/developers/
            
            /*
            EXEMPLE CODE À IMPLÉMENTER :
            
            $akismet_data = [
                'blog' => $this->config['akismet']['site_url'],
                'user_ip' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
                'comment_type' => 'comment',
                'comment_author' => $comment_data['author_name'],
                'comment_author_email' => $comment_data['author_email'],
                'comment_author_url' => $comment_data['author_website'] ?? '',
                'comment_content' => $comment_data['content']
            ];
            
            $url = $this->config['akismet']['endpoint'] . 'comment-check';
            $response = $this->makeRequest($url, $akismet_data);
            
            return [
                'is_spam' => ($response === 'true'),
                'confidence' => $response === 'true' ? 0.9 : 0.1
            ];
            */
            
            // Fallback: utiliser règles basiques
            return $this->basicSpamDetection($comment_data);
            
        } catch (Exception $e) {
            $this->logError('akismet', $e->getMessage());
            return $this->basicSpamDetection($comment_data);
        }
    }
    
    // ============================================
    // SOCKET reCAPTCHA - À REMPLIR QUAND API PRÊTE
    // ============================================
    
    public function verifyRecaptcha($token) {
        if (!$this->config['recaptcha']['enabled']) {
            return ['success' => true, 'score' => 1.0];
        }
        
        try {
            // TODO: Implémenter reCAPTCHA verification
            // Documentation: https://developers.google.com/recaptcha/docs/verify
            
            /*
            EXEMPLE CODE À IMPLÉMENTER :
            
            $data = [
                'secret' => $this->config['recaptcha']['secret_key'],
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ];
            
            $url = $this->config['recaptcha']['endpoint'];
            $response = $this->makeRequest($url, $data);
            $result = json_decode($response, true);
            
            if ($this->config['recaptcha']['version'] === 'v3') {
                return [
                    'success' => $result['success'] && $result['score'] >= $this->config['recaptcha']['min_score'],
                    'score' => $result['score'],
                    'action' => $result['action']
                ];
            } else {
                return [
                    'success' => $result['success'],
                    'score' => 1.0
                ];
            }
            */
            
            // Fallback: accepter par défaut
            return ['success' => true, 'score' => 1.0];
            
        } catch (Exception $e) {
            $this->logError('recaptcha', $e->getMessage());
            return ['success' => false, 'score' => 0, 'error' => $e->getMessage()];
        }
    }
    
    // ============================================
    // DÉTECTION SPAM BASIQUE (Fallback)
    // ============================================
    
    private function basicSpamDetection($comment_data) {
        $spam_score = 0;
        $content = strtolower($comment_data['content']);
        
        // Trop de liens
        $link_count = $this->countLinks($content);
        if ($link_count > 3) $spam_score += 0.3;
        
        // Mots suspects
        $spam_words = ['viagra', 'casino', 'porn', 'xxx', 'buy now', 'click here', 'free money'];
        foreach ($spam_words as $word) {
            if (stripos($content, $word) !== false) {
                $spam_score += 0.2;
            }
        }
        
        // Trop de majuscules
        $upper_ratio = $this->getUppercaseRatio($content);
        if ($upper_ratio > 0.5) $spam_score += 0.2;
        
        // Email suspect
        $email = $comment_data['author_email'];
        if (preg_match('/[0-9]{5,}/', $email)) $spam_score += 0.1;
        
        return [
            'is_spam' => $spam_score >= $this->config['akismet']['spam_threshold'],
            'confidence' => $spam_score
        ];
    }
    
    // ============================================
    // HELPERS
    // ============================================
    
    private function containsBannedWords($text) {
        $text = strtolower($text);
        foreach ($this->config['moderation']['banned_words'] as $word) {
            if (stripos($text, $word) !== false) {
                return true;
            }
        }
        return false;
    }
    
    private function countLinks($text) {
        preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $text, $matches);
        return count($matches[0]);
    }
    
    private function getUppercaseRatio($text) {
        $text = preg_replace('/[^a-zA-Z]/', '', $text);
        if (strlen($text) === 0) return 0;
        
        $upper = preg_replace('/[^A-Z]/', '', $text);
        return strlen($upper) / strlen($text);
    }
    
    private function checkRateLimit($email) {
        $cache_file = __DIR__ . '/../../cache/rate_limits/' . md5($email) . '.json';
        $cache_dir = dirname($cache_file);
        
        if (!file_exists($cache_dir)) {
            mkdir($cache_dir, 0755, true);
        }
        
        if (file_exists($cache_file)) {
            $data = json_decode(file_get_contents($cache_file), true);
            $time_window = $this->config['moderation']['rate_limit']['time_window'];
            
            // Nettoyer les anciens timestamps
            $data['timestamps'] = array_filter($data['timestamps'], function($ts) use ($time_window) {
                return $ts > (time() - $time_window);
            });
            
            // Vérifier limite
            if (count($data['timestamps']) >= $this->config['moderation']['rate_limit']['max_comments']) {
                return false;
            }
            
            // Ajouter nouveau timestamp
            $data['timestamps'][] = time();
        } else {
            $data = ['timestamps' => [time()]];
        }
        
        file_put_contents($cache_file, json_encode($data));
        return true;
    }
    
    private function makeRequest($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("CURL Error: {$error}");
        }
        
        return $response;
    }
    
    private function logError($service, $message) {
        $log_file = __DIR__ . '/../../cache/logs/comment_security.log';
        $log_dir = dirname($log_file);
        
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $log_entry = sprintf(
            "[%s] %s: %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($service),
            $message
        );
        
        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }
    
    public function getErrors() {
        return $this->errors;
    }
}