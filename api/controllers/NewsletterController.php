<?php
/**
 * TechEssentials Pro - Newsletter Controller
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 */

class NewsletterController {
    private $db;
    private $emailService;
    
    public function __construct() {
        $this->db = getDB('main');
        $this->emailService = new EmailService();
    }
    
    /**
     * POST /api/newsletter/subscribe
     * Inscription à la newsletter
     */
    public function postSubscribe($data, $id = null) {
        try {
            // Validation des données
            $validation = $this->validateSubscriptionData($data);
            if (!$validation['valid']) {
                http_response_code(400);
                return json_encode([
                    'success' => false,
                    'error' => $validation['error'],
                    'code' => 'VALIDATION_ERROR'
                ]);
            }
            
            $email = clean($data['email']);
            $name = clean($data['name'] ?? '');
            $language = clean($data['language'] ?? DEFAULT_LANG);
            $source = clean($data['source'] ?? 'website');
            
            // Vérifier si l'email existe déjà
            $stmt = $this->db->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
            $stmt->execute([$email]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                if ($existing['status'] === 'active') {
                    return json_encode([
                        'success' => true,
                        'message' => 'Email already subscribed',
                        'code' => 'ALREADY_SUBSCRIBED'
                    ]);
                } else {
                    // Réactiver l'abonnement
                    $stmt = $this->db->prepare("
                        UPDATE newsletter_subscribers 
                        SET status = 'active', updated_at = NOW() 
                        WHERE id = ?
                    ");
                    $stmt->execute([$existing['id']]);
                    
                    return json_encode([
                        'success' => true,
                        'message' => 'Subscription reactivated',
                        'code' => 'REACTIVATED'
                    ]);
                }
            }
            
            // Créer un token de confirmation
            $confirm_token = bin2hex(random_bytes(32));
            
            // Insérer le nouvel abonné
            $stmt = $this->db->prepare("
                INSERT INTO newsletter_subscribers 
                (email, name, language, source, status, confirm_token, created_at) 
                VALUES (?, ?, ?, ?, 'pending', ?, NOW())
            ");
            
            $stmt->execute([$email, $name, $language, $source, $confirm_token]);
            $subscriber_id = $this->db->lastInsertId();
            
            // Envoyer l'email de confirmation
            $confirm_url = url("newsletter/confirm/{$confirm_token}");
            $email_sent = $this->sendConfirmationEmail($email, $name, $confirm_url, $language);
            
            if (!$email_sent) {
                logError("Failed to send confirmation email to: {$email}");
            }
            
            return json_encode([
                'success' => true,
                'message' => 'Please check your email to confirm your subscription',
                'code' => 'CONFIRMATION_SENT',
                'subscriber_id' => $subscriber_id
            ]);
            
        } catch (Exception $e) {
            logError("Newsletter subscription error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Subscription failed',
                'code' => 'SUBSCRIPTION_FAILED'
            ]);
        }
    }
    
    /**
     * GET /api/newsletter/confirm/{token}
     * Confirmation d'abonnement
     */
    public function getConfirm($data, $token = null) {
        if (!$token) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'error' => 'Confirmation token required',
                'code' => 'TOKEN_REQUIRED'
            ]);
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT id, email, name, status 
                FROM newsletter_subscribers 
                WHERE confirm_token = ? AND status = 'pending'
            ");
            $stmt->execute([$token]);
            $subscriber = $stmt->fetch();
            
            if (!$subscriber) {
                http_response_code(404);
                return json_encode([
                    'success' => false,
                    'error' => 'Invalid or expired confirmation token',
                    'code' => 'INVALID_TOKEN'
                ]);
            }
            
            // Activer l'abonnement
            $stmt = $this->db->prepare("
                UPDATE newsletter_subscribers 
                SET status = 'active', confirm_token = NULL, confirmed_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$subscriber['id']]);
            
            // Envoyer l'email de bienvenue
            $this->sendWelcomeEmail($subscriber['email'], $subscriber['name']);
            
            return json_encode([
                'success' => true,
                'message' => 'Subscription confirmed successfully!',
                'code' => 'CONFIRMED'
            ]);
            
        } catch (Exception $e) {
            logError("Newsletter confirmation error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Confirmation failed',
                'code' => 'CONFIRMATION_FAILED'
            ]);
        }
    }
    
    /**
     * GET /api/newsletter/unsubscribe/{token}
     * Désabonnement
     */
    public function getUnsubscribe($data, $token = null) {
        if (!$token) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'error' => 'Unsubscribe token required',
                'code' => 'TOKEN_REQUIRED'
            ]);
        }
        
        try {
            $stmt = $this->db->prepare("
                SELECT id, email FROM newsletter_subscribers 
                WHERE MD5(CONCAT(id, email, 'unsubscribe_salt')) = ?
            ");
            $stmt->execute([$token]);
            $subscriber = $stmt->fetch();
            
            if (!$subscriber) {
                http_response_code(404);
                return json_encode([
                    'success' => false,
                    'error' => 'Invalid unsubscribe token',
                    'code' => 'INVALID_TOKEN'
                ]);
            }
            
            // Désactiver l'abonnement
            $stmt = $this->db->prepare("
                UPDATE newsletter_subscribers 
                SET status = 'unsubscribed', updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$subscriber['id']]);
            
            return json_encode([
                'success' => true,
                'message' => 'You have been unsubscribed successfully',
                'code' => 'UNSUBSCRIBED'
            ]);
            
        } catch (Exception $e) {
            logError("Newsletter unsubscribe error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Unsubscribe failed',
                'code' => 'UNSUBSCRIBE_FAILED'
            ]);
        }
    }
    
    /**
     * GET /api/newsletter/stats
     * Statistiques de la newsletter (admin seulement)
     */
    public function getStats($data, $id = null) {
        // TODO: Ajouter l'authentification admin
        
        try {
            $stats = [];
            
            // Statistiques générales
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'unsubscribed' THEN 1 ELSE 0 END) as unsubscribed
                FROM newsletter_subscribers
            ");
            $stmt->execute();
            $stats['overview'] = $stmt->fetch();
            
            // Statistiques par langue
            $stmt = $this->db->prepare("
                SELECT language, COUNT(*) as count 
                FROM newsletter_subscribers 
                WHERE status = 'active'
                GROUP BY language
            ");
            $stmt->execute();
            $stats['by_language'] = $stmt->fetchAll();
            
            // Croissance mensuelle
            $stmt = $this->db->prepare("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as subscriptions 
                FROM newsletter_subscribers 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month DESC
            ");
            $stmt->execute();
            $stats['monthly_growth'] = $stmt->fetchAll();
            
            return json_encode([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (Exception $e) {
            logError("Newsletter stats error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Failed to fetch stats',
                'code' => 'STATS_FAILED'
            ]);
        }
    }
    
    /**
     * Validation des données d'inscription
     */
    private function validateSubscriptionData($data) {
        if (empty($data['email'])) {
            return ['valid' => false, 'error' => 'Email is required'];
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error' => 'Invalid email format'];
        }
        
        if (isset($data['language']) && !in_array($data['language'], AVAILABLE_LANGS)) {
            return ['valid' => false, 'error' => 'Invalid language'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Envoyer l'email de confirmation
     */
    private function sendConfirmationEmail($email, $name, $confirm_url, $language = 'en') {
        $subjects = [
            'en' => 'Please confirm your subscription to TechEssentials Pro',
            'fr' => 'Veuillez confirmer votre abonnement à TechEssentials Pro'
        ];
        
        $templates = [
            'en' => [
                'greeting' => $name ? "Hi {$name}!" : 'Hi!',
                'message' => 'Thank you for subscribing to TechEssentials Pro newsletter. Please click the button below to confirm your subscription:',
                'button_text' => 'Confirm Subscription',
                'footer' => 'If you didn\'t subscribe to this newsletter, you can safely ignore this email.'
            ],
            'fr' => [
                'greeting' => $name ? "Bonjour {$name} !" : 'Bonjour !',
                'message' => 'Merci de vous être abonné(e) à la newsletter TechEssentials Pro. Veuillez cliquer sur le bouton ci-dessous pour confirmer votre abonnement :',
                'button_text' => 'Confirmer l\'abonnement',
                'footer' => 'Si vous ne vous êtes pas abonné(e) à cette newsletter, vous pouvez ignorer cet email en toute sécurité.'
            ]
        ];
        
        $lang = in_array($language, AVAILABLE_LANGS) ? $language : DEFAULT_LANG;
        $template = $templates[$lang];
        $subject = $subjects[$lang];
        
        $html_body = "
        <div style='max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;'>
            <h2 style='color: #667eea;'>{$template['greeting']}</h2>
            <p>{$template['message']}</p>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$confirm_url}' style='background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                    {$template['button_text']}
                </a>
            </div>
            <p style='color: #666; font-size: 12px;'>{$template['footer']}</p>
        </div>";
        
        return $this->emailService->send($email, $subject, $html_body);
    }
    
    /**
     * Envoyer l'email de bienvenue
     */
    private function sendWelcomeEmail($email, $name) {
        // TODO: Implémenter l'email de bienvenue
        return true;
    }
}