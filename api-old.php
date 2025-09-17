<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Config DB
$host = "localhost";
$db   = "techessentials";
$user = "root"; 
$pass = "";     

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "❌ DB connection failed", "details" => $e->getMessage()]);
    exit;
}

// Inclure le service email
require_once __DIR__ . '/classes/EmailService.php';

$action = $_GET["action"] ?? "";

switch ($action) {
    // =======================
    // GET ALL REVIEWS (pour la page reviews.html)
    // =======================
    case "getAllReviews":
        try {
            $stmt = $pdo->prepare("SELECT slug, title_en, title_fr, author, date, image, excerpt_en, excerpt_fr 
                                   FROM reviews ORDER BY date DESC");
            $stmt->execute();
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($reviews);
        } catch (PDOException $e) {
            echo json_encode(["error" => "❌ Error fetching reviews", "details" => $e->getMessage()]);
        }
        break;

    // =======================
    // GET ONE REVIEW + RATINGS
    // =======================
    case "getReview":
        $slug = $_GET["id"] ?? null;
        if (!$slug) {
            echo json_encode(["error" => "❌ No review ID provided"]);
            exit;
        }

        // Récupérer la review
        $stmt = $pdo->prepare("SELECT * FROM reviews WHERE slug = ?");
        $stmt->execute([$slug]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$review) {
            echo json_encode(["error" => "❌ Review not found"]);
            exit;
        }

        $reviewId = $review["id"];

        // Récupérer les avis liés
        $stmt = $pdo->prepare("SELECT name, rating, comment, created_at 
                               FROM review_ratings WHERE review_id = ? ORDER BY created_at DESC");
        $stmt->execute([$reviewId]);
        $ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcul moyenne
        $average = null;
        if (count($ratings) > 0) {
            $total = array_sum(array_column($ratings, "rating"));
            $average = round($total / count($ratings), 1);
        }

        echo json_encode([
            "review"   => $review,
            "ratings"  => $ratings,
            "average"  => $average,
            "count"    => count($ratings)
        ]);
        break;

    // =======================
    // ADD NEW RATING
    // =======================
    case "addRating":
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data["review_slug"], $data["name"], $data["rating"], $data["comment"])) {
            echo json_encode(["error" => "Missing fields"]);
            exit;
        }

        // Convertir slug en ID numérique
        $stmt = $pdo->prepare("SELECT id FROM reviews WHERE slug = ?");
        $stmt->execute([$data["review_slug"]]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$review) {
            echo json_encode(["error" => "Invalid review slug"]);
            exit;
        }

        $reviewId = $review["id"];

        // Validation du rating
        $rating = intval($data["rating"]);
        if ($rating < 1 || $rating > 5) {
            echo json_encode(["error" => "Rating must be between 1 and 5"]);
            exit;
        }

        // Insertion de l'avis utilisateur
        try {
            $stmt = $pdo->prepare("INSERT INTO review_ratings (review_id, name, rating, comment, created_at) 
                                   VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([
                $reviewId,
                htmlspecialchars(trim($data["name"])),
                $rating,
                htmlspecialchars(trim($data["comment"]))
            ]);

            echo json_encode(["success" => true, "message" => "Review added successfully"]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "❌ Error adding review", "details" => $e->getMessage()]);
        }
        break;

    // =======================
    // NEWSLETTER SUBSCRIPTION AVEC EMAIL
    // =======================
    case "subscribeNewsletter":
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data["email"])) {
            echo json_encode(["error" => "Email is required"]);
            exit;
        }

        $email = trim(strtolower($data["email"]));
        $language = $data["language"] ?? "en";
        $sendWelcomeEmail = $data["sendWelcomeEmail"] ?? true;

        // Validation email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["error" => "Invalid email format"]);
            exit;
        }

        // Validation langue
        if (!in_array($language, ["en", "fr"])) {
            $language = "en";
        }

        // Récupération des infos utilisateur
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        try {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
            $stmt->execute([$email]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            $emailResult = null;

            if ($existing) {
                if ($existing["status"] === "active") {
                    echo json_encode([
                        "success" => false, 
                        "message" => $language === "fr" ? "Cet email est déjà inscrit à notre newsletter" : "This email is already subscribed to our newsletter"
                    ]);
                    exit;
                } else {
                    // Réactiver l'abonnement
                    $stmt = $pdo->prepare("UPDATE newsletter_subscribers 
                                           SET status = 'active', language = ?, updated_at = NOW(), ip_address = ?, user_agent = ?
                                           WHERE email = ?");
                    $stmt->execute([$language, $ip_address, $user_agent, $email]);

                    // Log de l'action
                    $stmt = $pdo->prepare("INSERT INTO newsletter_logs (email, action, ip_address, user_agent) 
                                           VALUES (?, 'resubscribe', ?, ?)");
                    $stmt->execute([$email, $ip_address, $user_agent]);

                    // Envoyer email de bienvenue pour réabonnement
                    if ($sendWelcomeEmail) {
                        try {
                            $emailService = new EmailService();
                            $emailResult = $emailService->sendWelcomeEmail($email, $language);
                        } catch (Exception $e) {
                            error_log("Welcome email failed: " . $e->getMessage());
                        }
                    }

                    echo json_encode([
                        "success" => true,
                        "message" => $language === "fr" ? "Votre abonnement a été réactivé avec succès !" : "Your subscription has been reactivated successfully!",
                        "email_sent" => $emailResult ? $emailResult['success'] : false
                    ]);
                }
            } else {
                // Nouvel abonnement
                $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, language, ip_address, user_agent) 
                                       VALUES (?, ?, ?, ?)");
                $stmt->execute([$email, $language, $ip_address, $user_agent]);

                // Log de l'action
                $stmt = $pdo->prepare("INSERT INTO newsletter_logs (email, action, ip_address, user_agent) 
                                       VALUES (?, 'subscribe', ?, ?)");
                $stmt->execute([$email, $ip_address, $user_agent]);

                // Envoyer email de bienvenue
                if ($sendWelcomeEmail) {
                    try {
                        $emailService = new EmailService();
                        $emailResult = $emailService->sendWelcomeEmail($email, $language);
                    } catch (Exception $e) {
                        error_log("Welcome email failed: " . $e->getMessage());
                        $emailResult = ['success' => false, 'error' => $e->getMessage()];
                    }
                }

                echo json_encode([
                    "success" => true,
                    "message" => $language === "fr" ? "Merci ! Vous êtes maintenant inscrit à notre newsletter." : "Thank you! You're now subscribed to our newsletter.",
                    "email_sent" => $emailResult ? $emailResult['success'] : false,
                    "email_error" => $emailResult && !$emailResult['success'] ? $emailResult['error'] : null
                ]);
            }

        } catch (PDOException $e) {
            echo json_encode(["error" => "❌ Error subscribing to newsletter", "details" => $e->getMessage()]);
        }
        break;

    // =======================
    // NEWSLETTER UNSUBSCRIBE
    // =======================
    case "unsubscribeNewsletter":
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data["email"])) {
            echo json_encode(["error" => "Email is required"]);
            exit;
        }

        $email = trim(strtolower($data["email"]));
        $language = $data["language"] ?? "en";

        try {
            $stmt = $pdo->prepare("UPDATE newsletter_subscribers 
                                   SET status = 'unsubscribed', updated_at = NOW() 
                                   WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                // Log de l'action
                $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                
                $stmt = $pdo->prepare("INSERT INTO newsletter_logs (email, action, ip_address, user_agent) 
                                       VALUES (?, 'unsubscribe', ?, ?)");
                $stmt->execute([$email, $ip_address, $user_agent]);

                echo json_encode([
                    "success" => true,
                    "message" => $language === "fr" ? "Vous avez été désabonné de notre newsletter." : "You have been unsubscribed from our newsletter."
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => $language === "fr" ? "Cet email n'est pas inscrit à notre newsletter." : "This email is not subscribed to our newsletter."
                ]);
            }

        } catch (PDOException $e) {
            echo json_encode(["error" => "❌ Error unsubscribing from newsletter", "details" => $e->getMessage()]);
        }
        break;

    // =======================
    // ENVOYER NEWSLETTER À TOUS
    // =======================
    case "sendNewsletterBroadcast":
        // Vérification admin (simple check - à améliorer en production)
        session_start();
        if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            echo json_encode(["error" => "Unauthorized"]);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data["subject"], $data["content"])) {
            echo json_encode(["error" => "Subject and content are required"]);
            exit;
        }

        try {
            $emailService = new EmailService();
            $results = $emailService->sendNewsletter(
                $data["subject"], 
                $data["content"], 
                $data["language"] ?? null
            );

            echo json_encode([
                "success" => true,
                "results" => $results
            ]);

        } catch (Exception $e) {
            echo json_encode(["error" => "❌ Error sending newsletter", "details" => $e->getMessage()]);
        }
        break;

    // =======================
    // TESTER CONFIGURATION EMAIL
    // =======================
    case "testEmailConfig":
        session_start();
        if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            echo json_encode(["error" => "Unauthorized"]);
            exit;
        }

        try {
            $emailService = new EmailService();
            $testResult = $emailService->testConnection();
            echo json_encode($testResult);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
        break;

    // =======================
    // ENVOYER EMAIL DE TEST
    // =======================
    case "sendTestEmail":
        session_start();
        if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            echo json_encode(["error" => "Unauthorized"]);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $testEmail = $data["email"] ?? "admin@example.com";
        $language = $data["language"] ?? "en";

        try {
            $emailService = new EmailService();
            $result = $emailService->sendWelcomeEmail($testEmail, $language);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
        break;

    // =======================
    // GET NEWSLETTER STATS
    // =======================
    case "getNewsletterStats":
        try {
            $stats = [];

            // Total abonnés actifs
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = 'active'");
            $stats['total_active'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Par langue
            $stmt = $pdo->query("SELECT language, COUNT(*) as count 
                                FROM newsletter_subscribers 
                                WHERE status = 'active' 
                                GROUP BY language");
            $stats['by_language'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Nouvelles inscriptions cette semaine
            $stmt = $pdo->query("SELECT COUNT(*) as count 
                                FROM newsletter_subscribers 
                                WHERE status = 'active' 
                                AND subscribed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stats['this_week'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            // Nouvelles inscriptions aujourd'hui
            $stmt = $pdo->query("SELECT COUNT(*) as count 
                                FROM newsletter_subscribers 
                                WHERE status = 'active' 
                                AND DATE(subscribed_at) = CURDATE()");
            $stats['today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            echo json_encode($stats);

        } catch (PDOException $e) {
            echo json_encode(["error" => "❌ Error fetching newsletter stats", "details" => $e->getMessage()]);
        }
        break;

    // =======================
    // GET RECENT SUBSCRIBERS (pour l'admin)
    // =======================
    case "getRecentSubscribers":
        session_start();
        if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            echo json_encode(["error" => "Unauthorized"]);
            exit;
        }

        $limit = $_GET["limit"] ?? 50;

        try {
            $stmt = $pdo->prepare("SELECT email, language, status, subscribed_at, ip_address 
                                   FROM newsletter_subscribers 
                                   ORDER BY subscribed_at DESC 
                                   LIMIT ?");
            $stmt->execute([intval($limit)]);
            $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($subscribers);

        } catch (PDOException $e) {
            echo json_encode(["error" => "❌ Error fetching subscribers", "details" => $e->getMessage()]);
        }
        break;

    // =======================
    // EXPORT SUBSCRIBERS CSV
    // =======================
    case "exportSubscribers":
        session_start();
        if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            echo json_encode(["error" => "Unauthorized"]);
            exit;
        }

        try {
            $stmt = $pdo->query("SELECT email, language, status, subscribed_at, ip_address 
                                FROM newsletter_subscribers 
                                ORDER BY subscribed_at DESC");
            $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Préparer le CSV
            $csvContent = "Email,Language,Status,Subscribed At,IP Address\n";
            foreach ($subscribers as $subscriber) {
                $csvContent .= sprintf(
                    "%s,%s,%s,%s,%s\n",
                    $subscriber['email'],
                    $subscriber['language'],
                    $subscriber['status'],
                    $subscriber['subscribed_at'],
                    $subscriber['ip_address']
                );
            }

            // Headers pour téléchargement CSV
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="newsletter_subscribers_' . date('Y-m-d') . '.csv"');
            echo $csvContent;
            exit;

        } catch (PDOException $e) {
            echo json_encode(["error" => "❌ Error exporting subscribers", "details" => $e->getMessage()]);
        }
        break;

    // =======================
    // FORMULAIRE DE CONTACT
    // =======================
    case "sendContactForm":
        $data = json_decode(file_get_contents("php://input"), true);

        // Validation des données requises
        if (!$data || !isset($data["firstName"], $data["lastName"], $data["email"], $data["message"], $data["rgpdConsent"])) {
            echo json_encode(["error" => "Missing required fields"]);
            exit;
        }

        // Validation du consentement RGPD
        if (!$data["rgpdConsent"]) {
            echo json_encode(["error" => "RGPD consent is required"]);
            exit;
        }

        // Nettoyage et validation des données
        $firstName = htmlspecialchars(trim($data["firstName"]));
        $lastName = htmlspecialchars(trim($data["lastName"]));
        $email = trim(strtolower($data["email"]));
        $subject = isset($data["subject"]) ? htmlspecialchars($data["subject"]) : 'general';
        $message = htmlspecialchars(trim($data["message"]));
        $language = $data["language"] ?? "en";

        // Validation email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["error" => "Invalid email format"]);
            exit;
        }

        // Validation longueurs
        if (strlen($firstName) < 1 || strlen($firstName) > 50) {
            echo json_encode(["error" => "First name must be between 1 and 50 characters"]);
            exit;
        }

        if (strlen($lastName) < 1 || strlen($lastName) > 50) {
            echo json_encode(["error" => "Last name must be between 1 and 50 characters"]);
            exit;
        }

        if (strlen($message) < 10 || strlen($message) > 1000) {
            echo json_encode(["error" => "Message must be between 10 and 1000 characters"]);
            exit;
        }

        // Protection anti-spam basique
        $spamWords = ['viagra', 'casino', 'lottery', 'winner', 'congratulations', 'click here', 'free money'];
        $messageText = strtolower($message . ' ' . $firstName . ' ' . $lastName);
        
        foreach ($spamWords as $spamWord) {
            if (strpos($messageText, $spamWord) !== false) {
                echo json_encode(["error" => "Message detected as spam"]);
                exit;
            }
        }

        // Informations additionnelles
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $timestamp = date('Y-m-d H:i:s');
        $referrer = $data["referrer"] ?? 'direct';

        try {
            // 1. Sauvegarder en base de données
            $stmt = $pdo->prepare("INSERT INTO contact_messages 
                                   (first_name, last_name, email, subject, message, language, ip_address, user_agent, referrer, created_at) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            
            $stmt->execute([
                $firstName, 
                $lastName, 
                $email, 
                $subject, 
                $message, 
                $language, 
                $ip_address, 
                $user_agent, 
                $referrer
            ]);

            $contactId = $pdo->lastInsertId();

            // 2. Envoyer email de notification à l'admin
            $adminNotificationSent = sendContactNotificationEmail([
                'id' => $contactId,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'subject' => $subject,
                'message' => $message,
                'language' => $language,
                'ip_address' => $ip_address,
                'timestamp' => $timestamp
            ]);

            // 3. Envoyer email de confirmation à l'utilisateur
            $userConfirmationSent = sendContactConfirmationEmail($email, $firstName, $language);

            // Réponse de succès
            echo json_encode([
                "success" => true,
                "message" => $language === "fr" 
                    ? "Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais."
                    : "Your message has been sent successfully. We'll get back to you as soon as possible.",
                "contact_id" => $contactId,
                "admin_notified" => $adminNotificationSent,
                "user_confirmed" => $userConfirmationSent
            ]);

        } catch (PDOException $e) {
            error_log("Contact form database error: " . $e->getMessage());
            echo json_encode([
                "error" => $language === "fr" 
                    ? "Une erreur s'est produite lors de l'envoi de votre message. Veuillez réessayer."
                    : "An error occurred while sending your message. Please try again."
            ]);
        }
        break;

    // =======================
    // GET CONTACT MESSAGES (pour l'admin)
    // =======================
    case "getContactMessages":
        session_start();
        if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
            echo json_encode(["error" => "Unauthorized"]);
            exit;
        }

        try {
            // Récupérer les messages
            $stmt = $pdo->query("SELECT id, first_name, last_name, email, subject, 
                                 LEFT(message, 100) as message, language, status, priority, created_at 
                                 FROM contact_messages 
                                 ORDER BY created_at DESC 
                                 LIMIT 100");
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Récupérer les statistiques
            $statsQuery = $pdo->query("SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new,
                SUM(CASE WHEN status IN ('read', 'new') THEN 1 ELSE 0 END) as pending,
                AVG(CASE WHEN replied_at IS NOT NULL 
                    THEN TIMESTAMPDIFF(HOUR, created_at, replied_at) 
                    ELSE NULL END) as avg_response_time
                FROM contact_messages 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stats = $statsQuery->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                "success" => true,
                "messages" => $messages,
                "stats" => $stats
            ]);

        } catch (PDOException $e) {
            echo json_encode(["error" => "❌ Error fetching contact messages", "details" => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(["error" => "❌ Invalid action"]);
        break;
} // ← ACCOLADE FERMANTE DU SWITCH PRINCIPAL

// =======================
// FONCTIONS HELPER POUR CONTACT
// =======================

// Fonction pour envoyer notification à l'admin
function sendContactNotificationEmail($contactData) {
    try {
        // Configuration email
        $to = 'contact@techessentialspro.com';
        $subject = '🔔 Nouveau message de contact - TechEssentials Pro';
        
        // Mapping des sujets
        $subjectMap = [
            'general' => 'Question Générale',
            'partnership' => 'Partenariat', 
            'review' => 'Demande de Test Produit',
            'support' => 'Support Technique',
            'feedback' => 'Commentaires',
            'other' => 'Autre'
        ];
        
        $subjectText = $subjectMap[$contactData['subject']] ?? 'Non spécifié';
        
        $htmlContent = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; color: #667eea; }
                .value { background: white; padding: 10px; border-radius: 4px; margin-top: 5px; }
                .message-box { background: white; padding: 20px; border-left: 4px solid #667eea; margin: 20px 0; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📧 Nouveau Message de Contact</h1>
                    <p>ID: #{$contactData['id']}</p>
                </div>
                
                <div class='content'>
                    <div class='field'>
                        <div class='label'>👤 Contact:</div>
                        <div class='value'>{$contactData['firstName']} {$contactData['lastName']}</div>
                    </div>
                    
                    <div class='field'>
                        <div class='label'>📧 Email:</div>
                        <div class='value'><a href='mailto:{$contactData['email']}'>{$contactData['email']}</a></div>
                    </div>
                    
                    <div class='field'>
                        <div class='label'>📋 Sujet:</div>
                        <div class='value'>{$subjectText}</div>
                    </div>
                    
                    <div class='field'>
                        <div class='label'>🌍 Langue:</div>
                        <div class='value'>" . ($contactData['language'] === 'fr' ? '🇫🇷 Français' : '🇺🇸 English') . "</div>
                    </div>
                    
                    <div class='message-box'>
                        <div class='label'>💬 Message:</div>
                        <div style='margin-top: 10px;'>" . nl2br($contactData['message']) . "</div>
                    </div>
                    
                    <div class='field'>
                        <div class='label'>🕐 Date:</div>
                        <div class='value'>{$contactData['timestamp']}</div>
                    </div>
                    
                    <div class='field'>
                        <div class='label'>🌐 IP:</div>
                        <div class='value'>{$contactData['ip_address']}</div>
                    </div>
                </div>
                
                <div class='footer'>
                    <p>TechEssentials Pro - Système de Contact</p>
                    <p>Pour répondre, utilisez directement l'email: {$contactData['email']}</p>
                </div>
            </div>
        </body>
        </html>";

        $textContent = "Nouveau message de contact - TechEssentials Pro

ID: #{$contactData['id']}
Contact: {$contactData['firstName']} {$contactData['lastName']}
Email: {$contactData['email']}
Sujet: {$subjectText}
Langue: " . ($contactData['language'] === 'fr' ? 'Français' : 'English') . "

Message:
{$contactData['message']}

Date: {$contactData['timestamp']}
IP: {$contactData['ip_address']}

Pour répondre, utilisez: {$contactData['email']}";

        // Utiliser votre service email existant (PHPMailer ou simple)
        return sendHtmlEmail($to, $subject, $htmlContent, $textContent);

    } catch (Exception $e) {
        error_log("Contact notification email failed: " . $e->getMessage());
        return false;
    }
}

// Fonction pour envoyer confirmation à l'utilisateur
function sendContactConfirmationEmail($email, $firstName, $language) {
    try {
        $isEnglish = $language === 'en';
        
        $subject = $isEnglish 
            ? "Message received - TechEssentials Pro"
            : "Message reçu - TechEssentials Pro";

        $greeting = $isEnglish ? "Hello" : "Bonjour";
        $thankYou = $isEnglish 
            ? "Thank you for contacting TechEssentials Pro!"
            : "Merci d'avoir contacté TechEssentials Pro !";
            
        $responseTime = $isEnglish
            ? "We typically respond within 24 hours during business days."
            : "Nous répondons généralement dans les 24 heures pendant les jours ouvrables.";
            
        $signature = $isEnglish
            ? "Best regards,<br>The TechEssentials Pro Team"
            : "Cordialement,<br>L'équipe TechEssentials Pro";

        $htmlContent = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
                .message-box { background: white; padding: 20px; border-left: 4px solid #28a745; margin: 20px 0; }
                .cta { text-align: center; margin: 30px 0; }
                .cta a { background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; }
                .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>{$thankYou}</h1>
                </div>
                
                <div class='content'>
                    <p><strong>{$greeting} {$firstName},</strong></p>
                    
                    <div class='message-box'>
                        <p>{$responseTime}</p>
                    </div>
                    
                    <div class='cta'>
                        <a href='http://localhost/TechEssentialsPro/'>
                            " . ($isEnglish ? "Visit Our Website" : "Visiter Notre Site") . "
                        </a>
                    </div>
                    
                    <p>{$signature}</p>
                </div>
                
                <div class='footer'>
                    <p>TechEssentials Pro - Best Tech for Remote Workers</p>
                    <p>" . ($isEnglish ? "This is an automated confirmation email." : "Ceci est un email de confirmation automatique.") . "</p>
                </div>
            </div>
        </body>
        </html>";

        $textContent = "{$thankYou}

{$greeting} {$firstName},

{$responseTime}

{$signature}

TechEssentials Pro - Best Tech for Remote Workers";

        return sendHtmlEmail($email, $subject, $htmlContent, $textContent);

    } catch (Exception $e) {
        error_log("Contact confirmation email failed: " . $e->getMessage());
        return false;
    }
}

// Fonction helper pour envoyer emails HTML
function sendHtmlEmail($to, $subject, $htmlContent, $textContent) {
    // Headers pour email HTML
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="boundary456"',
        'From: TechEssentials Pro <contact@techessentialspro.com>',
        'Reply-To: contact@techessentialspro.com',
        'X-Mailer: TechEssentials Pro Contact System',
        'X-Priority: 3'
    ];
    
    // Corps de l'email multipart
    $body = "--boundary456\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $body .= $textContent . "\r\n\r\n";
    
    $body .= "--boundary456\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $body .= $htmlContent . "\r\n\r\n";
    $body .= "--boundary456--";
    
    // Envoyer l'email
    return mail($to, $subject, $body, implode("\r\n", $headers));
}
?>