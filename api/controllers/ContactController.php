<?php
/**
 * TechEssentials Pro - Contact Controller
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 */

class ContactController {
    private $db;
    private $emailService;
    
    public function __construct() {
        $this->db = getDB('main');
        $this->emailService = new EmailService();
    }
    
    /**
     * POST /api/contact/send
     * Envoyer un message de contact
     */
    public function postSend($data, $id = null) {
        try {
            // Validation CSRF
            if (!isset($data['csrf_token']) || !verifyCSRF($data['csrf_token'])) {
                http_response_code(403);
                return json_encode([
                    'success' => false,
                    'error' => 'Invalid CSRF token',
                    'code' => 'CSRF_INVALID'
                ]);
            }
            
            // Validation des données
            $validation = $this->validateContactData($data);
            if (!$validation['valid']) {
                http_response_code(400);
                return json_encode([
                    'success' => false,
                    'error' => $validation['error'],
                    'code' => 'VALIDATION_ERROR'
                ]);
            }
            
            // Protection anti-spam basique
            if ($this->isSpam($data)) {
                http_response_code(429);
                return json_encode([
                    'success' => false,
                    'error' => 'Message flagged as spam',
                    'code' => 'SPAM_DETECTED'
                ]);
            }
            
            // Nettoyer les données
            $name = clean($data['name']);
            $email = clean($data['email']);
            $subject = clean($data['subject'] ?? 'Contact Form Submission');
            $message = clean($data['message']);
            $phone = clean($data['phone'] ?? '');
            $company = clean($data['company'] ?? '');
            $source = clean($data['source'] ?? 'contact_form');
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            // Sauvegarder dans la base de données
            $stmt = $this->db->prepare("
                INSERT INTO contact_messages 
                (name, email, subject, message, phone, company, source, ip_address, user_agent, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'new', NOW())
            ");
            
            $stmt->execute([
                $name, $email, $subject, $message, 
                $phone, $company, $source, $ip_address, $user_agent
            ]);
            
            $message_id = $this->db->lastInsertId();
            
            // Envoyer l'email de notification à l'admin
            $admin_email_sent = $this->sendAdminNotification([
                'id' => $message_id,
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message,
                'phone' => $phone,
                'company' => $company
            ]);
            
            // Envoyer l'email de confirmation à l'utilisateur
            $user_email_sent = $this->sendUserConfirmation($email, $name, $subject);
            
            if (!$admin_email_sent) {
                logError("Failed to send admin notification for contact message {$message_id}");
            }
            
            if (!$user_email_sent) {
                logError("Failed to send user confirmation for contact message {$message_id}");
            }
            
            return json_encode([
                'success' => true,
                'message' => 'Your message has been sent successfully. We\'ll get back to you soon!',
                'code' => 'MESSAGE_SENT',
                'message_id' => $message_id
            ]);
            
        } catch (Exception $e) {
            logError("Contact form error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Failed to send message. Please try again later.',
                'code' => 'SEND_FAILED'
            ]);
        }
    }
    
    /**
     * POST /api/contact/quick-inquiry
     * Demande rapide (pour les popups, CTA, etc.)
     */
    public function postQuickInquiry($data, $id = null) {
        try {
            // Validation minimale
            if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                http_response_code(400);
                return json_encode([
                    'success' => false,
                    'error' => 'Valid email address required',
                    'code' => 'EMAIL_REQUIRED'
                ]);
            }
            
            $email = clean($data['email']);
            $name = clean($data['name'] ?? '');
            $type = clean($data['type'] ?? 'quick_inquiry');
            $message = clean($data['message'] ?? 'Quick inquiry from website');
            $source = clean($data['source'] ?? 'quick_form');
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            
            // Vérifier si ce n'est pas un doublon récent (même email dans les 5 dernières minutes)
            $stmt = $this->db->prepare("
                SELECT id FROM contact_messages 
                WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                return json_encode([
                    'success' => true,
                    'message' => 'We already received your inquiry. We\'ll get back to you soon!',
                    'code' => 'ALREADY_RECEIVED'
                ]);
            }
            
            // Sauvegarder
            $stmt = $this->db->prepare("
                INSERT INTO contact_messages 
                (name, email, subject, message, source, ip_address, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'new', NOW())
            ");
            
            $stmt->execute([
                $name, 
                $email, 
                "Quick Inquiry - {$type}", 
                $message, 
                $source, 
                $ip_address
            ]);
            
            $message_id = $this->db->lastInsertId();
            
            // Email de notification simplifié
            $this->sendQuickInquiryNotification($email, $name, $type, $message_id);
            
            return json_encode([
                'success' => true,
                'message' => 'Thank you for your inquiry! We\'ll be in touch soon.',
                'code' => 'INQUIRY_SENT'
            ]);
            
        } catch (Exception $e) {
            logError("Quick inquiry error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Failed to submit inquiry',
                'code' => 'INQUIRY_FAILED'
            ]);
        }
    }
    
    /**
     * GET /api/contact/messages (Admin uniquement)
     * Récupérer les messages de contact
     */
    public function getMessages($data, $id = null) {
        // TODO: Ajouter l'authentification admin
        
        try {
            $limit = min((int)($data['limit'] ?? 20), 100);
            $offset = (int)($data['offset'] ?? 0);
            $status = clean($data['status'] ?? 'all');
            
            $where_clause = "";
            $params = [];
            
            if ($status !== 'all') {
                $where_clause = "WHERE status = ?";
                $params[] = $status;
            }
            
            // Récupérer les messages
            $sql = "
                SELECT 
                    id, name, email, subject, message, phone, company,
                    source, status, created_at
                FROM contact_messages 
                {$where_clause}
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $messages = $stmt->fetchAll();
            
            // Compter le total
            $count_sql = "SELECT COUNT(*) as total FROM contact_messages {$where_clause}";
            $count_params = array_slice($params, 0, -2);
            $stmt = $this->db->prepare($count_sql);
            $stmt->execute($count_params);
            $total = $stmt->fetch()['total'];
            
            return json_encode([
                'success' => true,
                'data' => $messages,
                'meta' => [
                    'total' => (int)$total,
                    'limit' => $limit,
                    'offset' => $offset
                ]
            ]);
            
        } catch (Exception $e) {
            logError("Contact messages fetch error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Failed to fetch messages',
                'code' => 'FETCH_FAILED'
            ]);
        }
    }
    
    /**
     * PUT /api/contact/messages/{id}/status
     * Mettre à jour le statut d'un message
     */
    public function putStatus($data, $id = null) {
        if (!$id) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'error' => 'Message ID required',
                'code' => 'ID_REQUIRED'
            ]);
        }
        
        $new_status = clean($data['status'] ?? '');
        $allowed_statuses = ['new', 'read', 'replied', 'resolved', 'archived'];
        
        if (!in_array($new_status, $allowed_statuses)) {
            http_response_code(400);
            return json_encode([
                'success' => false,
                'error' => 'Invalid status',
                'code' => 'INVALID_STATUS'
            ]);
        }
        
        try {
            $stmt = $this->db->prepare("
                UPDATE contact_messages 
                SET status = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$new_status, $id]);
            
            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                return json_encode([
                    'success' => false,
                    'error' => 'Message not found',
                    'code' => 'NOT_FOUND'
                ]);
            }
            
            return json_encode([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);
            
        } catch (Exception $e) {
            logError("Contact status update error: " . $e->getMessage());
            http_response_code(500);
            
            return json_encode([
                'success' => false,
                'error' => 'Failed to update status',
                'code' => 'UPDATE_FAILED'
            ]);
        }
    }
    
    /**
     * Validation des données de contact
     */
    private function validateContactData($data) {
        $required_fields = ['name', 'email', 'message'];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['valid' => false, 'error' => ucfirst($field) . ' is required'];
            }
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error' => 'Invalid email format'];
        }
        
        if (strlen($data['name']) < 2 || strlen($data['name']) > 100) {
            return ['valid' => false, 'error' => 'Name must be between 2 and 100 characters'];
        }
        
        if (strlen($data['message']) < 10 || strlen($data['message']) > 5000) {
            return ['valid' => false, 'error' => 'Message must be between 10 and 5000 characters'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Détection basique de spam
     */
    private function isSpam($data) {
        $spam_indicators = [
            'viagra', 'cialis', 'casino', 'poker', 'loan', 'credit',
            'make money', 'click here', 'limited time', 'act now'
        ];
        
        $text = strtolower($data['name'] . ' ' . $data['message']);
        
        foreach ($spam_indicators as $indicator) {
            if (strpos($text, $indicator) !== false) {
                return true;
            }
        }
        
        // Vérifier si trop de liens
        if (substr_count($data['message'], 'http') > 2) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Envoyer la notification à l'admin
     */
    private function sendAdminNotification($message_data) {
        $admin_email = 'admin@techessentialspro.com'; // TODO: Configurable
        
        $subject = "New Contact Message: {$message_data['subject']}";
        
        $html_body = "
        <h2>New Contact Message Received</h2>
        <p><strong>Message ID:</strong> {$message_data['id']}</p>
        <p><strong>Name:</strong> {$message_data['name']}</p>
        <p><strong>Email:</strong> {$message_data['email']}</p>
        <p><strong>Phone:</strong> {$message_data['phone']}</p>
        <p><strong>Company:</strong> {$message_data['company']}</p>
        <p><strong>Subject:</strong> {$message_data['subject']}</p>
        <div style='background: #f5f5f5; padding: 15px; border-left: 4px solid #667eea;'>
            <p><strong>Message:</strong></p>
            <p>{$message_data['message']}</p>
        </div>
        <p style='margin-top: 20px;'>
            <a href='" . url("admin/contact/messages/{$message_data['id']}") . "' 
               style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
                View in Admin
            </a>
        </p>";
        
        return $this->emailService->send($admin_email, $subject, $html_body);
    }
    
    /**
     * Envoyer la confirmation à l'utilisateur
     */
    private function sendUserConfirmation($email, $name, $subject) {
        $confirmation_subject = "We received your message: {$subject}";
        
        $html_body = "
        <div style='max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;'>
            <h2 style='color: #667eea;'>Thank you for contacting us!</h2>
            <p>Hi " . ($name ? $name : 'there') . ",</p>
            <p>We've received your message and will get back to you within 24 hours.</p>
            <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <p><strong>Your message subject:</strong> {$subject}</p>
            </div>
            <p>In the meantime, feel free to browse our latest <a href='" . url('reviews') . "'>product reviews</a> and <a href='" . url('blog') . "'>tech articles</a>.</p>
            <p>Best regards,<br>The TechEssentials Pro Team</p>
        </div>";
        
        return $this->emailService->send($email, $confirmation_subject, $html_body);
    }
    
    /**
     * Notification pour les demandes rapides
     */
    private function sendQuickInquiryNotification($email, $name, $type, $message_id) {
        $admin_email = 'admin@techessentialspro.com';
        $subject = "Quick Inquiry - {$type}";
        
        $html_body = "
        <h3>Quick Inquiry Received</h3>
        <p><strong>Type:</strong> {$type}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>Message ID:</strong> {$message_id}</p>";
        
        return $this->emailService->send($admin_email, $subject, $html_body);
    }
}