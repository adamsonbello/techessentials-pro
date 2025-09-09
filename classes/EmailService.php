<?php
// classes/EmailService.php - Service d'envoi d'emails avec Composer

// Inclure l'autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $config;
    private $mailer;
    
    public function __construct() {
        // Charger la configuration
        $this->config = include __DIR__ . '/../config/email.php';
        $this->initializeMailer();
    }
    
    /**
     * Initialiser PHPMailer avec la configuration SMTP
     */
    private function initializeMailer() {
        $this->mailer = new PHPMailer(true);
        
        try {
            // Configuration SMTP
            $this->mailer->isSMTP();
            $this->mailer->Host       = $this->config['smtp']['host'];
            $this->mailer->SMTPAuth   = $this->config['smtp']['auth'];
            $this->mailer->Username   = $this->config['smtp']['username'];
            $this->mailer->Password   = $this->config['smtp']['password'];
            $this->mailer->SMTPSecure = $this->config['smtp']['secure'];
            $this->mailer->Port       = $this->config['smtp']['port'];
            
            // Paramètres généraux
            $this->mailer->CharSet    = $this->config['settings']['charset'];
            $this->mailer->Timeout    = $this->config['settings']['timeout'];
            $this->mailer->SMTPAutoTLS = $this->config['settings']['auto_tls'];
            
            // Debug pour développement
            if ($this->config['settings']['debug']) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            }
            
            // Désactiver la vérification SSL pour localhost
            if ($this->config['settings']['verify_peer'] === false) {
                $this->mailer->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
            }
            
            // Expéditeur par défaut
            $this->mailer->setFrom(
                $this->config['from']['email'], 
                $this->config['from']['name']
            );
            
        } catch (Exception $e) {
            error_log("EmailService initialization failed: " . $e->getMessage());
        }
    }
    
    /**
     * Envoyer un email de bienvenue à un nouvel abonné
     */
    public function sendWelcomeEmail($email, $language = 'en') {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email);
            
            // Contenu selon la langue
            $subjects = $this->config['templates']['welcome']['subject'];
            $subject = $subjects[$language] ?? $subjects['en'];
            
            $this->mailer->Subject = $subject;
            $this->mailer->isHTML(true);
            
            // Charger le template HTML
            $htmlContent = $this->loadTemplate('welcome', $language, [
                'email' => $email,
                'unsubscribe_url' => $this->getUnsubscribeUrl($email)
            ]);
            
            $this->mailer->Body = $htmlContent;
            
            // Version texte alternative
            $this->mailer->AltBody = strip_tags($htmlContent);
            
            $result = $this->mailer->send();
            
            if ($result) {
                $this->logEmailSent($email, 'welcome', $language);
                return ['success' => true, 'message' => 'Welcome email sent successfully'];
            }
            
        } catch (Exception $e) {
            error_log("Welcome email failed for {$email}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
        
        return ['success' => false, 'error' => 'Unknown error'];
    }
    
    /**
     * Envoyer une newsletter à tous les abonnés actifs
     */
    public function sendNewsletter($subject, $content, $language = null) {
        try {
            // Récupérer les abonnés
            $subscribers = $this->getActiveSubscribers($language);
            $results = ['sent' => 0, 'failed' => 0, 'errors' => []];
            
            foreach ($subscribers as $subscriber) {
                $this->mailer->clearAddresses();
                $this->mailer->addAddress($subscriber['email']);
                
                $this->mailer->Subject = $subject;
                $this->mailer->isHTML(true);
                
                // Personnaliser le contenu
                $personalizedContent = $this->personalizeContent($content, $subscriber);
                $this->mailer->Body = $personalizedContent;
                $this->mailer->AltBody = strip_tags($personalizedContent);
                
                try {
                    if ($this->mailer->send()) {
                        $results['sent']++;
                        $this->logEmailSent($subscriber['email'], 'newsletter', $subscriber['language']);
                    } else {
                        $results['failed']++;
                        $results['errors'][] = "Failed for {$subscriber['email']}";
                    }
                } catch (Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Failed for {$subscriber['email']}: " . $e->getMessage();
                }
                
                // Délai pour éviter le spam
                usleep(100000); // 0.1 seconde
            }
            
            return $results;
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Charger un template email
     */
    private function loadTemplate($template, $language, $variables = []) {
        $templatePath = __DIR__ . "/../templates/emails/{$template}_{$language}.html";
        
        if (!file_exists($templatePath)) {
            $templatePath = __DIR__ . "/../templates/emails/{$template}_en.html";
        }
        
        if (file_exists($templatePath)) {
            $content = file_get_contents($templatePath);
            
            // Remplacer les variables
            foreach ($variables as $key => $value) {
                $content = str_replace("{{" . $key . "}}", $value, $content);
            }
            
            return $content;
        }
        
        // Template par défaut si fichier non trouvé
        return $this->getDefaultTemplate($template, $language, $variables);
    }
    
    /**
     * Template par défaut en cas de fichier manquant
     */
    private function getDefaultTemplate($template, $language, $variables) {
        $isEnglish = $language === 'en';
        
        if ($template === 'welcome') {
            $title = $isEnglish ? 'Welcome to TechEssentials Pro!' : 'Bienvenue chez TechEssentials Pro !';
            $greeting = $isEnglish ? 'Hello!' : 'Bonjour !';
            $message = $isEnglish 
                ? 'Thank you for subscribing to our newsletter. You\'ll receive weekly tech deals and expert reviews.'
                : 'Merci de vous être abonné à notre newsletter. Vous recevrez des offres tech et avis d\'experts hebdomadaires.';
            $unsubText = $isEnglish ? 'Unsubscribe' : 'Se désabonner';
            
            $benefits = $isEnglish ? [
                'Weekly exclusive tech deals',
                'Expert product reviews', 
                'Remote work productivity tips',
                'Early access to new products'
            ] : [
                'Offres tech exclusives hebdomadaires',
                'Avis d\'experts sur les produits',
                'Conseils de productivité en télétravail', 
                'Accès anticipé aux nouveaux produits'
            ];
            
            $benefitsList = '';
            foreach ($benefits as $benefit) {
                $benefitsList .= "<li style='margin-bottom: 8px;'>✅ {$benefit}</li>";
            }
        } else {
            $title = $isEnglish ? 'TechEssentials Pro Newsletter' : 'Newsletter TechEssentials Pro';
            $greeting = $isEnglish ? 'Hello!' : 'Bonjour !';
            $message = $variables['content'] ?? '';
            $unsubText = $isEnglish ? 'Unsubscribe' : 'Se désabonner';
            $benefitsList = '';
        }
        
        return "
        <!DOCTYPE html>
        <html lang='{$language}'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$title}</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;'>
            <div style='max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
                
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center;'>
                    <h1 style='font-size: 2rem; margin-bottom: 10px; margin-top: 0;'>{$title}</h1>
                    <p style='font-size: 1.1rem; margin: 0; opacity: 0.9;'>Best Tech for Remote Workers</p>
                </div>
                
                <!-- Content -->
                <div style='padding: 30px 20px;'>
                    <p style='font-size: 1.1rem; margin-bottom: 20px;'>{$greeting} 👋</p>
                    <p style='margin-bottom: 20px;'>{$message}</p>
                    
                    " . ($benefitsList ? "
                    <div style='background: #e7f3ff; border-radius: 8px; padding: 20px; margin: 20px 0;'>
                        <h3 style='color: #0066cc; margin-bottom: 15px; margin-top: 0;'>What you can expect:</h3>
                        <ul style='list-style: none; padding: 0; margin: 0;'>
                            {$benefitsList}
                        </ul>
                    </div>
                    " : "") . "
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://localhost/TechEssentialsPro/' style='display: inline-block; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 6px; font-weight: bold;'>
                            🚀 Visit Our Website
                        </a>
                    </div>
                    
                    <p style='margin-bottom: 10px;'>Best regards,<br><strong>The TechEssentials Pro Team</strong></p>
                </div>
                
                <!-- Footer -->
                <div style='background: #f8f9fa; padding: 20px; text-align: center; color: #666; font-size: 0.9rem;'>
                    <p style='margin-bottom: 10px;'>You're receiving this email because you subscribed to TechEssentials Pro newsletter.</p>
                    <p style='margin-bottom: 15px;'>Email: {$variables['email']}</p>
                    <p style='margin: 0;'>
                        <a href='{$variables['unsubscribe_url']}' style='color: #999; text-decoration: none;'>{$unsubText}</a>
                    </p>
                    <p style='margin-top: 15px; font-size: 0.8rem; margin-bottom: 0;'>
                        TechEssentials Pro - Best Tech for Remote Workers<br>
                        © 2025 All rights reserved.
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Personnaliser le contenu pour un abonné
     */
    private function personalizeContent($content, $subscriber) {
        $content = str_replace('{{email}}', $subscriber['email'], $content);
        $content = str_replace('{{unsubscribe_url}}', $this->getUnsubscribeUrl($subscriber['email']), $content);
        return $content;
    }
    
    /**
     * Générer URL de désabonnement
     */
    private function getUnsubscribeUrl($email) {
        $token = base64_encode($email . '|' . time());
        return "http://localhost/TechEssentialsPro/unsubscribe.php?token=" . urlencode($token);
    }
    
    /**
     * Récupérer les abonnés actifs
     */
    private function getActiveSubscribers($language = null) {
        // Configuration DB (à adapter selon votre setup)
        $host = "localhost";
        $db   = "techessentials";
        $user = "root"; 
        $pass = "";
        
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = "SELECT email, language FROM newsletter_subscribers WHERE status = 'active'";
            $params = [];
            
            if ($language) {
                $sql .= " AND language = ?";
                $params[] = $language;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Database error in getActiveSubscribers: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Logger les emails envoyés
     */
    private function logEmailSent($email, $type, $language) {
        error_log("Email sent: {$type} to {$email} in {$language}");
    }
    
    /**
     * Tester la configuration email
     */
    public function testConnection() {
        try {
            $this->mailer->smtpConnect();
            $this->mailer->smtpClose();
            return ['success' => true, 'message' => 'SMTP connection successful'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>