<?php
/**
 * API Unifiée - TechEssentials Pro
 * Gère les contacts ET les emails/newsletter
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration
define('TECHESSENTIALS_PRO', true);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Simple authentification pour API
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// Fonction principale de routage API
function handleAPIRequest() {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            // === ACTIONS CONTACTS ===
            case 'getContactMessages':
                return getContactMessages();
                
            case 'updateMessageStatus':
                return updateMessageStatus();
                
            case 'sendReply':
                return sendReply();
                
            case 'markAsRead':
                return markAsRead();
                
            case 'getStats':
                return getContactStats();
                
            // === ACTIONS EMAIL/NEWSLETTER ===
            case 'getNewsletterStats':
                return getNewsletterStats();
                
            case 'testEmailConfig':
                return testEmailConfig();
                
            case 'sendTestEmail':
                return sendTestEmail();
                
            case 'sendNewsletterBroadcast':
                return sendNewsletterBroadcast();
                
            case 'getRecentSubscribers':
                return getRecentSubscribers();
                
            case 'exportSubscribers':
                return exportSubscribers();
                
            default:
                throw new Exception('Action non reconnue: ' . $action);
        }
        
    } catch (Exception $e) {
        http_response_code(400);
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// ========== FONCTIONS CONTACTS ==========

/**
 * Récupérer tous les messages de contact avec filtres
 */
function getContactMessages() {
    $db = getDB('main');
    
    // Filtres depuis l'interface
    $status = $_GET['status'] ?? '';
    $subject = $_GET['subject'] ?? '';
    $language = $_GET['language'] ?? '';
    $date = $_GET['date'] ?? '';
    
    // Construction de la requête avec filtres
    $where_conditions = [];
    $params = [];
    
    if ($status) {
        $where_conditions[] = "status = ?";
        $params[] = $status;
    }
    
    if ($subject) {
        $where_conditions[] = "subject = ?";
        $params[] = $subject;
    }
    
    if ($language) {
        $where_conditions[] = "language = ?";
        $params[] = $language;
    }
    
    if ($date) {
        $where_conditions[] = "DATE(created_at) = ?";
        $params[] = $date;
    }
    
    $where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Requête principale
    $stmt = $db->prepare("
        SELECT 
            id, first_name, last_name, email, subject, custom_subject,
            message, language, status, priority, ip_address,
            created_at, updated_at, replied_at, verified_at,
            admin_notes, rgpd_consent
        FROM contact_messages 
        $where_sql 
        ORDER BY 
            CASE status 
                WHEN 'new' THEN 1 
                WHEN 'read' THEN 2 
                WHEN 'replied' THEN 3 
                WHEN 'closed' THEN 4 
            END,
            created_at DESC
    ");
    
    $stmt->execute($params);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Enrichir les données pour l'interface
    foreach ($messages as &$message) {
        // Combiner custom_subject si present
        if ($message['custom_subject'] && $message['subject'] === 'other') {
            $message['subject_display'] = $message['custom_subject'];
        } else {
            $message['subject_display'] = ucfirst($message['subject']);
        }
        
        // Ajouter le statut de vérification email
        $message['is_verified'] = !empty($message['verified_at']);
        
        // Prévisualiser le message (100 caractères)
        $message['message_preview'] = substr($message['message'], 0, 100);
        if (strlen($message['message']) > 100) {
            $message['message_preview'] .= '...';
        }
        
        // Formater les dates
        $message['created_at_formatted'] = date('d/m/Y H:i', strtotime($message['created_at']));
        $message['updated_at_formatted'] = $message['updated_at'] ? date('d/m/Y H:i', strtotime($message['updated_at'])) : null;
    }
    
    // Récupérer les statistiques
    $stats = getContactStatsData($db);
    
    return [
        'success' => true,
        'messages' => $messages,
        'stats' => $stats,
        'total' => count($messages)
    ];
}

/**
 * Récupérer les statistiques des contacts
 */
function getContactStats() {
    $db = getDB('main');
    $stats = getContactStatsData($db);
    
    return [
        'success' => true,
        'stats' => $stats
    ];
}

/**
 * Fonction helper pour les statistiques
 */
function getContactStatsData($db) {
    try {
        // Compter par statut
        $stmt = $db->query("
            SELECT 
                status,
                COUNT(*) as count
            FROM contact_messages 
            GROUP BY status
        ");
        
        $status_counts = [];
        $total = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $status_counts[$row['status']] = (int)$row['count'];
            $total += (int)$row['count'];
        }
        
        // Messages non lus (new + read)
        $pending = ($status_counts['new'] ?? 0) + ($status_counts['read'] ?? 0);
        
        // Temps de réponse moyen (en heures)
        $stmt = $db->query("
            SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, replied_at)) as avg_hours
            FROM contact_messages 
            WHERE replied_at IS NOT NULL
        ");
        
        $avg_response = $stmt->fetchColumn();
        $avg_response_formatted = $avg_response ? round($avg_response, 1) : '-';
        
        // Messages aujourd'hui
        $stmt = $db->query("
            SELECT COUNT(*) 
            FROM contact_messages 
            WHERE DATE(created_at) = CURDATE()
        ");
        $today_count = $stmt->fetchColumn();
        
        return [
            'total' => $total,
            'new' => $status_counts['new'] ?? 0,
            'read' => $status_counts['read'] ?? 0,
            'replied' => $status_counts['replied'] ?? 0,
            'closed' => $status_counts['closed'] ?? 0,
            'pending' => $pending,
            'avg_response_time' => $avg_response_formatted,
            'today' => $today_count
        ];
    } catch (Exception $e) {
        return [
            'total' => 0,
            'new' => 0,
            'read' => 0,
            'replied' => 0,
            'closed' => 0,
            'pending' => 0,
            'avg_response_time' => '-',
            'today' => 0
        ];
    }
}

/**
 * Mettre à jour le statut d'un message
 */
function updateMessageStatus() {
    $message_id = $_POST['message_id'] ?? 0;
    $new_status = $_POST['status'] ?? '';
    
    if (!$message_id || !in_array($new_status, ['new', 'read', 'replied', 'closed'])) {
        throw new Exception('Paramètres invalides pour la mise à jour du statut');
    }
    
    $db = getDB('main');
    
    $update_fields = ['status = ?', 'updated_at = NOW()'];
    $params = [$new_status];
    
    // Si on marque comme répondu, ajouter replied_at
    if ($new_status === 'replied') {
        $update_fields[] = 'replied_at = NOW()';
    }
    
    $stmt = $db->prepare("
        UPDATE contact_messages 
        SET " . implode(', ', $update_fields) . "
        WHERE id = ?
    ");
    
    $params[] = $message_id;
    $result = $stmt->execute($params);
    
    if ($result) {
        return [
            'success' => true,
            'message' => 'Statut mis à jour avec succès'
        ];
    } else {
        throw new Exception('Erreur lors de la mise à jour du statut');
    }
}

/**
 * Marquer un message comme lu
 */
function markAsRead() {
    $message_id = $_POST['message_id'] ?? $_GET['message_id'] ?? 0;
    
    if (!$message_id) {
        throw new Exception('ID de message manquant');
    }
    
    $db = getDB('main');
    
    $stmt = $db->prepare("
        UPDATE contact_messages 
        SET status = 'read', updated_at = NOW()
        WHERE id = ? AND status = 'new'
    ");
    
    $result = $stmt->execute([$message_id]);
    
    return [
        'success' => true,
        'message' => 'Message marqué comme lu'
    ];
}

/**
 * Envoyer une réponse par email
 */
function sendReply() {
    $message_id = $_POST['message_id'] ?? 0;
    $reply_text = trim($_POST['reply_text'] ?? '');
    
    if (!$message_id || !$reply_text) {
        throw new Exception('Données de réponse incomplètes');
    }
    
    $db = getDB('main');
    
    // Récupérer le message original
    $stmt = $db->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$message_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$message) {
        throw new Exception('Message introuvable');
    }
    
    // Préparer l'email de réponse
    $to = $message['email'];
    $subject = "Re: " . ($message['custom_subject'] ?: ucfirst($message['subject'])) . " - TechEssentials Pro";
    
    $html_body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #667eea; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; }
            .original { background: #e9ecef; padding: 15px; margin: 20px 0; border-left: 4px solid #667eea; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>TechEssentials Pro</h2>
                <p>Réponse à votre message</p>
            </div>
            <div class='content'>
                <p>Bonjour " . htmlspecialchars($message['first_name']) . ",</p>
                
                <p>Merci pour votre message. Voici notre réponse :</p>
                
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    " . nl2br(htmlspecialchars($reply_text)) . "
                </div>
                
                <div class='original'>
                    <strong>Votre message original :</strong><br>
                    <em>" . nl2br(htmlspecialchars($message['message'])) . "</em>
                </div>
                
                <p>N'hésitez pas à nous recontacter si vous avez d'autres questions.</p>
                
                <p>Cordialement,<br>
                L'équipe TechEssentials Pro</p>
            </div>
            <div class='footer'>
                <p>TechEssentials Pro - Votre guide tech de confiance</p>
            </div>
        </div>
    </body>
    </html>";
    
    // Headers email
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: TechEssentials Pro <hello@techessentialspro.com>',
        'Reply-To: hello@techessentialspro.com'
    ];
    
    // Envoyer l'email
    $email_sent = mail($to, $subject, $html_body, implode("\r\n", $headers));
    
    if ($email_sent) {
        // Mettre à jour le statut et ajouter la note admin
        $stmt = $db->prepare("
            UPDATE contact_messages 
            SET 
                status = 'replied',
                replied_at = NOW(),
                updated_at = NOW(),
                replied_by = ?,
                admin_notes = CONCAT(COALESCE(admin_notes, ''), '\n--- Réponse envoyée le ', NOW(), ' ---\n', ?)
            WHERE id = ?
        ");
        
        $admin_user = $_SESSION['admin_user'] ?? 'Admin';
        $stmt->execute([$admin_user, $reply_text, $message_id]);
        
        return [
            'success' => true,
            'message' => 'Réponse envoyée avec succès'
        ];
    } else {
        throw new Exception('Erreur lors de l\'envoi de l\'email');
    }
}

// ========== FONCTIONS EMAIL/NEWSLETTER ==========

/**
 * Récupérer les statistiques newsletter
 */
function getNewsletterStats() {
    $db = getDB('main');
    
    try {
        // Vérifier si la table newsletter_subscribers existe
        $stmt = $db->query("SHOW TABLES LIKE 'newsletter_subscribers'");
        if (!$stmt->fetch()) {
            // Table n'existe pas, retourner des données vides
            return [
                'success' => true,
                'total_active' => 0,
                'by_language' => [
                    ['language' => 'fr', 'count' => 0],
                    ['language' => 'en', 'count' => 0]
                ],
                'this_week' => 0,
                'today' => 0
            ];
        }
        
        // Total des abonnés actifs
        $stmt = $db->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'active'");
        $total_active = $stmt->fetchColumn();
        
        // Par langue
        $stmt = $db->query("SELECT language, COUNT(*) as count FROM newsletter_subscribers WHERE status = 'active' GROUP BY language");
        $by_language = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cette semaine
        $stmt = $db->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'active' AND subscribed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $this_week = $stmt->fetchColumn();
        
        // Aujourd'hui
        $stmt = $db->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE status = 'active' AND DATE(subscribed_at) = CURDATE()");
        $today = $stmt->fetchColumn();
        
        return [
            'success' => true,
            'total_active' => (int)$total_active,
            'by_language' => $by_language,
            'this_week' => (int)$this_week,
            'today' => (int)$today
        ];
        
    } catch (Exception $e) {
        return [
            'success' => true,
            'total_active' => 0,
            'by_language' => [
                ['language' => 'fr', 'count' => 0],
                ['language' => 'en', 'count' => 0]
            ],
            'this_week' => 0,
            'today' => 0
        ];
    }
}

/**
 * Tester la configuration email
 */
function testEmailConfig() {
    $config_ok = function_exists('mail');
    $smtp_host = ini_get('SMTP') ?: 'localhost';
    $smtp_port = ini_get('smtp_port') ?: 25;
    
    if (!$config_ok) {
        return [
            'success' => false,
            'error' => 'Fonction mail() non disponible'
        ];
    }
    
    return [
        'success' => true,
        'message' => "Configuration email OK - SMTP: $smtp_host:$smtp_port"
    ];
}

/**
 * Envoyer un email de test
 */
function sendTestEmail() {
    $data = json_decode(file_get_contents('php://input'), true);
    $test_email = $data['email'] ?? '';
    $language = $data['language'] ?? 'fr';
    
    if (!$test_email) {
        throw new Exception('Email de test manquant');
    }
    
    $subject = $language === 'fr' ? 'Test Email - TechEssentials Pro' : 'Test Email - TechEssentials Pro';
    $message = $language === 'fr' ? 
        'Ceci est un email de test depuis votre interface admin TechEssentials Pro.' :
        'This is a test email from your TechEssentials Pro admin interface.';
    
    $headers = [
        'From: TechEssentials Pro <hello@techessentialspro.com>',
        'Reply-To: hello@techessentialspro.com',
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    $result = mail($test_email, $subject, $message, implode("\r\n", $headers));
    
    if ($result) {
        return [
            'success' => true,
            'message' => "Email de test envoyé à $test_email"
        ];
    } else {
        throw new Exception('Échec envoi email de test');
    }
}

/**
 * Envoyer newsletter à tous les abonnés
 */
function sendNewsletterBroadcast() {
    $data = json_decode(file_get_contents('php://input'), true);
    $subject = $data['subject'] ?? '';
    $content = $data['content'] ?? '';
    $language_filter = $data['language'] ?? null;
    
    if (!$subject || !$content) {
        throw new Exception('Sujet et contenu requis');
    }
    
    return [
        'success' => true,
        'results' => [
            'sent' => 0,
            'failed' => 0,
            'errors' => ['Table newsletter_subscribers non configurée']
        ]
    ];
}

/**
 * Récupérer la liste des abonnés récents
 */
function getRecentSubscribers() {
    return [
        'success' => false,
        'error' => 'Table newsletter_subscribers non configurée'
    ];
}

/**
 * Exporter les abonnés en CSV
 */
function exportSubscribers() {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="subscribers_empty_' . date('Y-m-d') . '.csv"');
    
    echo "Email,Langue,Statut,Date\n";
    echo "# Aucun abonné - Table newsletter non configurée\n";
    exit;
}

// Traitement de la requête et envoi de la réponse
try {
    $response = handleAPIRequest();
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// gère verified contacts

/**
 * NOUVELLES FONCTIONS À AJOUTER DANS admin/api.php
 * Pour gérer les contacts vérifiés (verified_contacts)
 */

/**
 * Récupérer les contacts vérifiés
 */
function getVerifiedContacts() {
    try {
        $db = getDB('main');
        
        // Statistiques
        $stats = [];
        
        // Total contacts vérifiés
        $stmt = $db->query("SELECT COUNT(*) FROM verified_contacts");
        $stats['total_verified'] = $stmt->fetchColumn();
        
        // Par statut
        $stmt = $db->query("
            SELECT status, COUNT(*) as count 
            FROM verified_contacts 
            GROUP BY status
        ");
        $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $stats['by_status'] = [
            'verified' => $statusCounts['verified'] ?? 0,
            'processed' => $statusCounts['processed'] ?? 0,
            'replied' => $statusCounts['replied'] ?? 0,
            'archived' => $statusCounts['archived'] ?? 0
        ];
        
        // Contacts récents (7 derniers jours)
        $stmt = $db->query("
            SELECT COUNT(*) 
            FROM verified_contacts 
            WHERE verified_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stats['recent_week'] = $stmt->fetchColumn();
        
        // Taux de réponse
        $total = $stats['total_verified'];
        $replied = $stats['by_status']['replied'];
        $stats['response_rate'] = $total > 0 ? round(($replied / $total) * 100, 1) : 0;
        
        // Récupérer les contacts
        $stmt = $db->query("
            SELECT 
                id, name, email, subject, message, contact_category,
                submitted_at, verified_at, processed_at, status,
                admin_notes, replied_by, replied_at
            FROM verified_contacts 
            ORDER BY verified_at DESC 
        ");
        
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'contacts' => $contacts,
            'stats' => $stats
        ];
        
    } catch (Exception $e) {
        logError("Erreur récupération contacts vérifiés: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'contacts' => [],
            'stats' => [
                'total_verified' => 0,
                'by_status' => ['verified' => 0, 'processed' => 0, 'replied' => 0, 'archived' => 0],
                'recent_week' => 0,
                'response_rate' => 0
            ]
        ];
    }
}

/**
 * Répondre à un contact vérifié
 */
function replyToVerifiedContact() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Si pas de JSON, essayer FormData
    if (!$data) {
        $data = $_POST;
    }
    
    $contact_id = $data['contact_id'] ?? '';
    $reply_message = $data['reply_message'] ?? '';
    $admin_user = $_SESSION['admin_user'] ?? 'Admin';
    
    if (empty($contact_id) || empty($reply_message)) {
        throw new Exception('Contact ID et message de réponse requis');
    }
    
    try {
        $db = getDB('main');
        
        // Récupérer les infos du contact
        $stmt = $db->prepare("SELECT * FROM verified_contacts WHERE id = ?");
        $stmt->execute([$contact_id]);
        $contact = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$contact) {
            throw new Exception("Contact non trouvé");
        }
        
        // Envoyer la réponse par email
        $subject = "Re: " . ($contact['subject'] ?: 'Votre message') . " - TechEssentials Pro";
        $email_content = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
                .reply-box { background: white; padding: 20px; border-left: 4px solid #28a745; margin: 20px 0; border-radius: 4px; }
                .original-message { background: #e9ecef; padding: 15px; border-radius: 4px; margin-top: 20px; font-style: italic; }
                .footer { text-align: center; color: #666; font-size: 0.9rem; margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Réponse à votre message</h1>
                </div>
                <div class='content'>
                    <p>Bonjour " . htmlspecialchars($contact['name']) . ",</p>
                    
                    <p>Nous vous remercions pour votre message. Voici notre réponse :</p>
                    
                    <div class='reply-box'>
                        " . nl2br(htmlspecialchars($reply_message)) . "
                    </div>
                    
                    <div class='original-message'>
                        <strong>Votre message original :</strong><br>
                        " . nl2br(htmlspecialchars($contact['message'])) . "
                    </div>
                    
                    <p>N'hésitez pas à nous recontacter si vous avez d'autres questions.</p>
                    
                    <p>Cordialement,<br>
                    L'équipe TechEssentials Pro</p>
                </div>
                <div class='footer'>
                    <p>Cet email a été envoyé par TechEssentials Pro</p>
                </div>
            </div>
        </body>
        </html>";
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: TechEssentials Pro <noreply@techessentialspro.com>',
            'Reply-To: hello@techessentialspro.com'
        ];
        
        $email_sent = mail($contact['email'], $subject, $email_content, implode("\r\n", $headers));
        
        if ($email_sent) {
            // Mettre à jour le statut
            $stmt = $db->prepare("
                UPDATE verified_contacts 
                SET status = 'replied', 
                    replied_at = NOW(),
                    replied_by = ?,
                    admin_notes = CONCAT(COALESCE(admin_notes, ''), '\n\nRéponse envoyée le ', NOW(), ': ', ?)
                WHERE id = ?
            ");
            $stmt->execute([$admin_user, $reply_message, $contact_id]);
            
            return [
                'success' => true, 
                'message' => 'Réponse envoyée avec succès'
            ];
        } else {
            throw new Exception("Erreur lors de l'envoi de l'email");
        }
        
    } catch (Exception $e) {
        logError("Erreur réponse contact vérifié: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Marquer un contact comme traité
 */
function markContactAsProcessed() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Si pas de JSON, essayer FormData
    if (!$data) {
        $data = $_POST;
    }
    
    $contact_id = $data['contact_id'] ?? '';
    $admin_notes = $data['admin_notes'] ?? 'Marqué comme traité';
    
    if (empty($contact_id)) {
        throw new Exception('Contact ID requis');
    }
    
    try {
        $db = getDB('main');
        $stmt = $db->prepare("
            UPDATE verified_contacts 
            SET status = 'processed', 
                processed_at = NOW(),
                admin_notes = CONCAT(COALESCE(admin_notes, ''), '\n\n', NOW(), ': ', ?)
            WHERE id = ?
        ");
        $success = $stmt->execute([$admin_notes, $contact_id]);
        
        if ($success) {
            return [
                'success' => true,
                'message' => 'Contact marqué comme traité'
            ];
        } else {
            throw new Exception('Erreur lors de la mise à jour');
        }
        
    } catch (Exception $e) {
        logError("Erreur marquage contact traité: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Archiver un contact vérifié
 */
function archiveVerifiedContact() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Si pas de JSON, essayer FormData
    if (!$data) {
        $data = $_POST;
    }
    
    $contact_id = $data['contact_id'] ?? '';
    
    if (empty($contact_id)) {
        throw new Exception('Contact ID requis');
    }
    
    try {
        $db = getDB('main');
        $stmt = $db->prepare("
            UPDATE verified_contacts 
            SET status = 'archived',
                admin_notes = CONCAT(COALESCE(admin_notes, ''), '\n\n', NOW(), ': Archivé')
            WHERE id = ?
        ");
        $success = $stmt->execute([$contact_id]);
        
        if ($success) {
            return [
                'success' => true,
                'message' => 'Contact archivé avec succès'
            ];
        } else {
            throw new Exception('Erreur lors de l\'archivage');
        }
        
    } catch (Exception $e) {
        logError("Erreur archivage contact: " . $e->getMessage());
        throw $e;
    }
}

/**
 * MISE À JOUR DE LA FONCTION handleAPIRequest() 
 * Ajouter ces cases dans le switch existant :
 */
/*
case 'getVerifiedContacts':
    $response = getVerifiedContacts();
    break;

case 'replyToVerifiedContact':
    $response = replyToVerifiedContact();
    break;

case 'markContactAsProcessed':
    $response = markContactAsProcessed();
    break;

case 'archiveVerifiedContact':
    $response = archiveVerifiedContact();
    break;
*/
?>