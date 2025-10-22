<?php
/**
 * Configuration Brevo (Sendinblue)
 * API Key et paramètres d'envoi
 */

// API Configuration
define('BREVO_API_KEY', '7Jz1k6EhGDXMCSqy'); // ← REMPLACE par ta vraie clé
define('BREVO_API_URL', 'https://api.brevo.com/v3');

// Sender Configuration
define('BREVO_SENDER_EMAIL', 'newsletter@techessentialspro.com'); // ← Ton email
define('BREVO_SENDER_NAME', 'TechEssentials Pro');

/**
 * Envoyer un email via Brevo
 */
function sendEmailViaBrevo($to_email, $to_name, $subject, $html_content, $language = 'fr') {
    $data = [
        'sender' => [
            'name' => BREVO_SENDER_NAME,
            'email' => BREVO_SENDER_EMAIL
        ],
        'to' => [
            [
                'email' => $to_email,
                'name' => $to_name
            ]
        ],
        'subject' => $subject,
        'htmlContent' => $html_content,
        'tags' => ['newsletter', $language]
    ];
    
    $ch = curl_init(BREVO_API_URL . '/smtp/email');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'content-type: application/json',
        'api-key: ' . BREVO_API_KEY
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 201) {
        return ['success' => true, 'response' => json_decode($response, true)];
    } else {
        return ['success' => false, 'error' => json_decode($response, true)];
    }
}

/**
 * Envoyer newsletter à tous les abonnés
 */
function sendNewsletterViaBrevo($subject, $html_content, $language_filter = null) {
    require_once __DIR__ . '/../includes/config.php';
    
    $db = getDB('main');
    
    // Requête pour récupérer les abonnés
    $where = "status = 'active'";
    if ($language_filter) {
        $where .= " AND language = :language";
    }
    
    $stmt = $db->prepare("SELECT email, language FROM newsletter_subscribers WHERE $where");
    if ($language_filter) {
        $stmt->bindParam(':language', $language_filter);
    }
    $stmt->execute();
    $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $results = [
        'total' => count($subscribers),
        'sent' => 0,
        'failed' => 0,
        'errors' => []
    ];
    
    foreach ($subscribers as $subscriber) {
        // Personnaliser le contenu
        $personalized_content = str_replace(
            ['{{email}}', '{{unsubscribe_url}}'],
            [$subscriber['email'], 'https://techessentialspro.com/unsubscribe.php?email=' . urlencode($subscriber['email'])],
            $html_content
        );
        
        $result = sendEmailViaBrevo(
            $subscriber['email'],
            $subscriber['email'],
            $subject,
            $personalized_content,
            $subscriber['language']
        );
        
        if ($result['success']) {
            $results['sent']++;
        } else {
            $results['failed']++;
            $results['errors'][] = $subscriber['email'] . ': ' . ($result['error']['message'] ?? 'Unknown error');
        }
        
        // Pause pour éviter rate limit (300/jour = 1 toutes les 5 minutes)
        usleep(500000); // 0.5 seconde entre chaque email
    }
    
    return $results;
}

/**
 * Envoyer email de bienvenue
 */
function sendWelcomeEmailViaBrevo($email, $language = 'fr') {
    $template_file = __DIR__ . "/newsletters/templates/welcome_{$language}.html";
    
    if (!file_exists($template_file)) {
        return ['success' => false, 'error' => 'Template not found'];
    }
    
    $html_content = file_get_contents($template_file);
    
    // Remplacer les variables
    $html_content = str_replace(
        ['{{email}}', '{{year}}', '{{unsubscribe_url}}'],
        [$email, date('Y'), 'https://techessentialspro.com/unsubscribe.php?email=' . urlencode($email)],
        $html_content
    );
    
    $subject = $language === 'fr' 
        ? '🎉 Bienvenue chez TechEssentials Pro !' 
        : '🎉 Welcome to TechEssentials Pro!';
    
    return sendEmailViaBrevo($email, $email, $subject, $html_content, $language);
}
?>