<?php
/**
 * TechEssentials Pro - Contact adapté à la table existante
 * Utilise la structure contact_messages du blog-admin
 */

// Configuration
define('TECHESSENTIALS_PRO', true);
require_once __DIR__ . '/includes/config.php';
require_once INCLUDES_PATH . 'functions.php';

// Variables de base
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'fr';
if (in_array($lang, ['fr', 'en'])) {
    $_SESSION['lang'] = $lang;
}

$page_title = $lang === 'fr' ? 'Contactez-nous - TechEssentials Pro' : 'Contact Us - TechEssentials Pro';
$page_description = $lang === 'fr' ? 'Contactez notre équipe TechEssentials Pro pour toute question.' : 'Contact our TechEssentials Pro team for any questions.';

// Fonction d'envoi d'email de confirmation
function sendVerificationEmail($email, $first_name, $token, $lang) {
    $subject = $lang === 'fr' ? 'TechEssentials Pro - Confirmez votre message' : 'TechEssentials Pro - Confirm your message';
    $verification_link = "https://techessentialspro.com/verify-contact.php?token=" . $token . "&lang=" . $lang;
    
    $html_body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { display: inline-block; background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
            .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>TechEssentials Pro</h1>
                <p>" . ($lang === 'fr' ? 'Confirmez votre message de contact' : 'Confirm your contact message') . "</p>
            </div>
            <div class='content'>
                <h2>" . ($lang === 'fr' ? 'Bonjour ' : 'Hello ') . htmlspecialchars($first_name) . ",</h2>
                
                <p>" . ($lang === 'fr' ? 
                    'Merci d\'avoir contacté TechEssentials Pro. Pour finaliser l\'envoi de votre message et éviter le spam, veuillez cliquer sur le bouton ci-dessous dans les 24 heures :' :
                    'Thank you for contacting TechEssentials Pro. To finalize sending your message and prevent spam, please click the button below within 24 hours:'
                ) . "</p>
                
                <div style='text-align: center;'>
                    <a href='" . $verification_link . "' class='button'>
                        " . ($lang === 'fr' ? 'Confirmer mon message' : 'Confirm my message') . "
                    </a>
                </div>
                
                <p><small>" . ($lang === 'fr' ? 
                    'Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :' :
                    'If the button doesn\'t work, copy this link to your browser:'
                ) . "<br>
                <a href='" . $verification_link . "'>" . $verification_link . "</a></small></p>
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
    
    return mail($email, $subject, $html_body, implode("\r\n", $headers));
}

// Fonctions de validation
function isValidEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
    
    $domain = substr(strrchr($email, "@"), 1);
    $disposable_domains = ['10minutemail.com', 'tempmail.org', 'guerrillamail.com', 'mailinator.com', 'yopmail.com'];
    
    if (in_array(strtolower($domain), $disposable_domains)) return false;
    return checkdnsrr($domain, "MX");
}

function checkRateLimit($ip) {
    $rate_file = __DIR__ . '/tmp/contact_rate_' . date('Y-m-d-H') . '.json';
    $max_attempts = 3;
    
    if (!is_dir(__DIR__ . '/tmp')) mkdir(__DIR__ . '/tmp', 0755, true);
    
    $attempts = [];
    if (file_exists($rate_file)) {
        $attempts = json_decode(file_get_contents($rate_file), true) ?: [];
    }
    
    $ip_attempts = $attempts[$ip] ?? 0;
    if ($ip_attempts >= $max_attempts) return false;
    
    $attempts[$ip] = $ip_attempts + 1;
    file_put_contents($rate_file, json_encode($attempts));
    return true;
}

// Mappage des sujets vers les valeurs ENUM existantes
function mapSubjectToEnum($subject_value) {
    $mapping = [
        'product_question' => 'general',
        'review_request' => 'review',
        'partnership' => 'partnership',
        'technical_issue' => 'support',
        'other' => 'other'
    ];
    return $mapping[$subject_value] ?? 'general';
}

// Variables de formulaire
$form_pending_verification = false;
$form_errors = [];

// Traitement du formulaire
if ($_POST && isset($_POST['submit_contact'])) {
    error_log("DEBUG: Formulaire soumis");
    
            // Vérification CSRF
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                error_log("DEBUG: Erreur CSRF");
                $form_errors[] = $lang === 'fr' ? 'Erreur de sécurité. Veuillez réessayer.' : 'Security error. Please try again.';
            }
            
            // Honeypot
            if (!empty($_POST['website'])) {
                error_log("DEBUG: Bot détecté");
                $form_errors[] = 'Bot detected.';
            }
            
            error_log("DEBUG: Récupération des données POST");
            // ... vos validations existantes ...
            
            // Après toutes les validations, ajoutez :
            if (empty($form_errors)) {
                error_log("DEBUG: Aucune erreur, tentative insertion DB");
                error_log("DEBUG: Email = " . $email);
                error_log("DEBUG: Full name = " . trim($first_name . ' ' . $last_name));
            } else {
                error_log("DEBUG: Erreurs trouvées: " . implode(', ', $form_errors));
            }
        

    
            // Récupération des données
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $subject_key = $_POST['subject'] ?? '';
            $custom_subject = trim($_POST['custom_subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $consent = isset($_POST['consent']);
            
            // Validation
            if (empty($first_name)) {
                $form_errors[] = $lang === 'fr' ? 'Le prénom est requis.' : 'First name is required.';
            }
            
            if (empty($last_name)) {
                $form_errors[] = $lang === 'fr' ? 'Le nom est requis.' : 'Last name is required.';
            }
            
            if (empty($email)) {
                $form_errors[] = $lang === 'fr' ? 'L\'email est requis.' : 'Email is required.';
            } elseif (!isValidEmail($email)) {
                $form_errors[] = $lang === 'fr' ? 'Adresse email invalide ou temporaire.' : 'Invalid or temporary email address.';
            }
            
            if (empty($subject_key)) {
                $form_errors[] = $lang === 'fr' ? 'Le sujet est requis.' : 'Subject is required.';
            }
            
            if (empty($message) || strlen($message) < 10) {
                $form_errors[] = $lang === 'fr' ? 'Le message doit contenir au moins 10 caractères.' : 'Message must contain at least 10 characters.';
            }
            
            if (!$consent) {
                $form_errors[] = $lang === 'fr' ? 'Vous devez accepter le traitement de vos données.' : 'You must consent to data processing.';
            }
        
                    
            // Vérifications avancées
            if (empty($form_errors)) {
                $user_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                
                if (!checkRateLimit($user_ip)) {
                    $form_errors[] = $lang === 'fr' ? 'Trop de tentatives. Veuillez patienter une heure.' : 'Too many attempts. Please wait an hour.';
                }
            }
            
            // Si pas d'erreurs, créer la demande en attente
            if (empty($form_errors)) {
                try {
                    $db = getDB('main');
            
            // Vérifier doublons récents
            $stmt = $db->prepare("
                SELECT id FROM pending_contacts 
                WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $form_errors[] = $lang === 'fr' ? 'Un message similaire est déjà en cours de validation.' : 'A similar message is already being verified.';
            } else {
                $verification_token = bin2hex(random_bytes(32));
                
                // Insérer en pending_contacts avec la structure complète
                $stmt = $db->prepare("
                    INSERT INTO pending_contacts 
                    (name, email, subject, message, verification_token, ip_address, lang, expires_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))
                ");
                
                // Concaténer prénom + nom pour la compatibilité
                $full_name = trim($first_name . ' ' . $last_name);
                
                // Préparer le sujet final (ENUM + custom si "other")
                $final_subject = $subject_key === 'other' ? $custom_subject : $subject_key;
                
                $stmt->execute([
                    $full_name, $email, $final_subject, $message, 
                    $verification_token, $user_ip, $lang
                ]);
                
               // TEST TEMPORAIRE - ignorer l'email
               $form_pending_verification = true;
               error_log("TEST - Données sauvées : Email=$email, Token=$verification_token");

      // Vider complètement les données POST
               $first_name = $last_name = $email = $subject_key = $custom_subject = $message = '';
               unset($_POST);
            }
              } catch (Exception $e) {
               error_log("Contact form error: " . $e->getMessage());
               $form_errors[] = $lang === 'fr' ? 'Erreur technique.' : 'Technical error.';
        }
    }

}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
include 'includes/layouts/header.php';
?>

<style>
/* Styles identiques au précédent */
.contact-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 80px 0 60px;
    text-align: center;
}

.contact-hero h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.contact-section {
    padding: 60px 0;
    background: #f8fafc;
}

.contact-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.contact-form-section {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding: 2.5rem;
}

.section-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

.form-label.required::after {
    content: ' *';
    color: #e74c3c;
    font-weight: bold;
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    font-family: inherit;
    background: white;
    box-sizing: border-box;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: #fafbfc;
}

.form-textarea {
    resize: vertical;
    min-height: 120px;
    line-height: 1.5;
}

.form-select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    padding-right: 40px;
}

.form-pending {
    background: #fff3cd;
    color: #856404;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #ffeaa7;
    margin-bottom: 1.5rem;
    font-weight: 500;
}

.form-errors {
    background: #f8d7da;
    color: #721c24;
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #f5c6cb;
    margin-bottom: 1.5rem;
}

.consent-section {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border-left: 4px solid #667eea;
    margin-bottom: 1.5rem;
}

.consent-section label {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    cursor: pointer;
    font-size: 0.95rem;
    line-height: 1.4;
    font-weight: normal;
}

.form-checkbox {
    margin-right: 0.75rem;
    transform: scale(1.2);
    accent-color: #667eea;
}

.submit-btn {
    background: #667eea;
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
}

.submit-btn:hover {
    background: #5a6fd8;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.honeypot {
    position: absolute;
    left: -9999px;
    opacity: 0;
}

.conditional-field {
    display: none;
    animation: slideDown 0.3s ease-in-out;
}

.conditional-field.show {
    display: block;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.contact-info-section {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.contact-info-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding: 2rem;
}

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.info-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.info-content h3 {
    margin: 0 0 0.5rem;
    color: #2d3748;
    font-weight: 600;
}

.info-content p {
    margin: 0;
    color: #4a5568;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .contact-container {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .contact-hero h1 {
        font-size: 2rem;
    }
    
    .contact-form-section {
        padding: 1.5rem;
    }
}
</style>

<!-- Hero Section -->
<section class="contact-hero">
    <div class="container">
        <h1><?= $lang === 'fr' ? 'Contactez-nous' : 'Contact Us' ?></h1>
        <p><?= $lang === 'fr' ? 'Notre équipe est là pour répondre à toutes vos questions sur les produits tech' : 'Our team is here to answer all your questions about tech products' ?></p>
    </div>
</section>

<section class="contact-section">
    <div class="container">
        <div class="contact-container">
            <div class="contact-form-section">
                <h2 class="section-title"><?= $lang === 'fr' ? 'Envoyez-nous un message' : 'Send us a message' ?></h2>
                
                <?php if ($form_pending_verification): ?>
                    <div class="form-pending">
                        <strong><?= $lang === 'fr' ? 'Email de confirmation envoyé !' : 'Confirmation email sent!' ?></strong><br>
                        <?= $lang === 'fr' ? 'Vérifiez votre boîte mail et cliquez sur le lien de confirmation dans les 24 heures.' : 'Check your email and click the confirmation link within 24 hours.' ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($form_errors)): ?>
                    <div class="form-errors">
                        <strong><?= $lang === 'fr' ? 'Erreurs détectées :' : 'Errors detected:' ?></strong><br>
                        <?php foreach ($form_errors as $error): ?>
                            • <?= htmlspecialchars($error) ?><br>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="honeypot">
                        <input type="text" name="website" tabindex="-1">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name" class="form-label required">
                                <?= $lang === 'fr' ? 'Prénom' : 'First name' ?>
                            </label>
                            <input type="text" id="first_name" name="first_name" class="form-input" 
                                   value="<?= $form_pending_verification ? '' : htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name" class="form-label required">
                                <?= $lang === 'fr' ? 'Nom' : 'Last name' ?>
                            </label>
                            <input type="text" id="last_name" name="last_name" class="form-input" 
                                   value="<?= $form_pending_verification ? '' : htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label required">
                            <?= $lang === 'fr' ? 'Adresse email' : 'Email address' ?>
                        </label>
                        <input type="email" id="email" name="email" class="form-input" 
                               value="<?= $form_pending_verification ? '' : htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject" class="form-label required">
                            <?= $lang === 'fr' ? 'Sujet' : 'Subject' ?>
                        </label>
                        <select id="subject" name="subject" class="form-select" required>
                            <option value=""><?= $lang === 'fr' ? 'Choisissez un sujet' : 'Choose a subject' ?></option>
                            <option value="product_question" <?= (!$form_pending_verification && ($_POST['subject'] ?? '') === 'product_question') ? 'selected' : '' ?>>
                                <?= $lang === 'fr' ? 'Question sur un produit' : 'Product question' ?>
                            </option>
                            <option value="review_request" <?= (!$form_pending_verification && ($_POST['subject'] ?? '') === 'review_request') ? 'selected' : '' ?>>
                                <?= $lang === 'fr' ? 'Demande de test' : 'Review request' ?>
                            </option>
                            <option value="partnership" <?= (!$form_pending_verification && ($_POST['subject'] ?? '') === 'partnership') ? 'selected' : '' ?>>
                                <?= $lang === 'fr' ? 'Partenariat' : 'Partnership' ?>
                            </option>
                            <option value="technical_issue" <?= (!$form_pending_verification && ($_POST['subject'] ?? '') === 'technical_issue') ? 'selected' : '' ?>>
                                <?= $lang === 'fr' ? 'Problème technique' : 'Technical issue' ?>
                            </option>
                            <option value="other" <?= (!$form_pending_verification && ($_POST['subject'] ?? '') === 'other') ? 'selected' : '' ?>>
                                <?= $lang === 'fr' ? 'Autre' : 'Other' ?>
                            </option>
                        </select>
                    </div>
                    
                    <div class="form-group conditional-field" id="custom-subject-field">
                        <label for="custom_subject" class="form-label">
                            <?= $lang === 'fr' ? 'Précisez le sujet' : 'Specify subject' ?>
                        </label>
                        <input type="text" id="custom_subject" name="custom_subject" class="form-input" 
                               value="<?= $form_pending_verification ? '' : htmlspecialchars($_POST['custom_subject'] ?? '') ?>"
                               placeholder="<?= $lang === 'fr' ? 'Décrivez brièvement votre demande...' : 'Briefly describe your request...' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="form-label required">
                            <?= $lang === 'fr' ? 'Votre message' : 'Your message' ?>
                        </label>
                        <textarea id="message" name="message" class="form-textarea" 
                                  placeholder="<?= $lang === 'fr' ? 'Décrivez votre demande en détail...' : 'Describe your request in detail...' ?>" 
                                  required><?= $form_pending_verification ? '' : htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="consent-section">
                        <label>
                            <input type="checkbox" name="consent" class="form-checkbox" required 
                                   <?= isset($_POST['consent']) ? 'checked' : '' ?>>
                            <?= $lang === 'fr' ? 
                                'J\'accepte que mes données soient traitées après validation de mon email.' :
                                'I agree that my data may be processed after email validation.'
                            ?>
                        </label>
                    </div>
                    
                    <button type="submit" name="submit_contact" class="submit-btn">
                        <?= $lang === 'fr' ? 'Envoyer le message' : 'Send message' ?>
                    </button>
                </form>
            </div>
            
            <div class="contact-info-section">
                <div class="contact-info-card">
                    <h2 class="section-title"><?= $lang === 'fr' ? 'Comment ça marche ?' : 'How it works?' ?></h2>
                    
                    <div class="info-item">
                        <div class="info-icon">1</div>
                        <div class="info-content">
                            <h3><?= $lang === 'fr' ? 'Remplissez le formulaire' : 'Fill the form' ?></h3>
                            <p><?= $lang === 'fr' ? 'Saisissez votre message avec une adresse email valide' : 'Enter your message with a valid email address' ?></p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">2</div>
                        <div class="info-content">
                            <h3><?= $lang === 'fr' ? 'Confirmez votre email' : 'Confirm your email' ?></h3>
                            <p><?= $lang === 'fr' ? 'Cliquez sur le lien reçu par email dans les 24h' : 'Click the link received by email within 24h' ?></p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">3</div>
                        <div class="info-content">
                            <h3><?= $lang === 'fr' ? 'Message traité' : 'Message processed' ?></h3>
                            <p><?= $lang === 'fr' ? 'Une fois validé, votre message entre dans notre système' : 'Once validated, your message enters our system' ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Gestion du champ conditionnel "Autre"
document.getElementById('subject').addEventListener('change', function() {
    const customField = document.getElementById('custom-subject-field');
    if (this.value === 'other') {
        customField.classList.add('show');
        document.getElementById('custom_subject').required = true;
    } else {
        customField.classList.remove('show');
        document.getElementById('custom_subject').required = false;
        document.getElementById('custom_subject').value = '';
    }
});

// Validation côté client
document.querySelector('form').addEventListener('submit', function(e) {
    const consent = document.querySelector('input[name="consent"]');
    const email = document.getElementById('email').value;
    const message = document.getElementById('message').value;
    const subject = document.getElementById('subject').value;
    const customSubject = document.getElementById('custom_subject').value;
    
    if (!consent.checked) {
        e.preventDefault();
        alert('<?= $lang === 'fr' ? 'Vous devez accepter le traitement de vos données.' : 'You must consent to data processing.' ?>');
        return false;
    }
    
    if (message.length < 10) {
        e.preventDefault();
        alert('<?= $lang === 'fr' ? 'Le message doit contenir au moins 10 caractères.' : 'Message must contain at least 10 characters.' ?>');
        return false;
    }
    
    if (subject === 'other' && customSubject.trim().length < 3) {
        e.preventDefault();
        alert('<?= $lang === 'fr' ? 'Veuillez préciser le sujet de votre demande.' : 'Please specify the subject of your request.' ?>');
        return false;
    }
    
    // Vérifier domaines jetables
    const disposableDomains = ['10minutemail.com', 'tempmail.org', 'guerrillamail.com', 'mailinator.com', 'yopmail.com'];
    const domain = email.split('@')[1];
    
    if (disposableDomains.includes(domain.toLowerCase())) {
        e.preventDefault();
        alert('<?= $lang === 'fr' ? 'Les adresses email temporaires ne sont pas acceptées.' : 'Temporary email addresses are not accepted.' ?>');
        return false;
    }
    
    // Désactiver le bouton
    const submitBtn = this.querySelector('.submit-btn');
    submitBtn.disabled = true;
    submitBtn.textContent = '<?= $lang === 'fr' ? 'Envoi en cours...' : 'Sending...' ?>';
});

// Auto-resize textarea
document.getElementById('message').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});

// Initialiser l'état du champ conditionnel au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    const subjectSelect = document.getElementById('subject');
    const customField = document.getElementById('custom-subject-field');
    
    if (subjectSelect.value === 'other') {
        customField.classList.add('show');
        document.getElementById('custom_subject').required = true;
    }
});
</script>

<?php 
// Include newsletter et footer
include 'includes/layouts/newsletter.php';
include 'includes/layouts/footer.php';
?>


