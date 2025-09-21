<?php
/**
 * TechEssentials Pro - Page Contact
 * Formulaire de contact conforme RGPD
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

// Variables pour header
$page_title = $lang === 'fr' ? 'Contactez-nous - TechEssentials Pro' : 'Contact Us - TechEssentials Pro';
$page_description = $lang === 'fr' ? 'Contactez notre équipe TechEssentials Pro pour toute question.' : 'Contact our TechEssentials Pro team for any questions.';

// Traitement du formulaire
$form_success = false;
$form_errors = [];

if ($_POST && isset($_POST['submit_contact'])) {
    // Vérification CSRF (simulation)
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $form_errors[] = $lang === 'fr' ? 'Erreur de sécurité. Veuillez réessayer.' : 'Security error. Please try again.';
    }
    
    // Validation des champs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $consent = isset($_POST['consent']);
    
    if (empty($name)) {
        $form_errors[] = $lang === 'fr' ? 'Le nom est requis.' : 'Name is required.';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_errors[] = $lang === 'fr' ? 'Un email valide est requis.' : 'A valid email is required.';
    }
    
    if (empty($subject)) {
        $form_errors[] = $lang === 'fr' ? 'Le sujet est requis.' : 'Subject is required.';
    }
    
    if (empty($message)) {
        $form_errors[] = $lang === 'fr' ? 'Le message est requis.' : 'Message is required.';
    }
    
    if (!$consent) {
        $form_errors[] = $lang === 'fr' ? 'Vous devez accepter le traitement de vos données.' : 'You must consent to data processing.';
    }
    
    // Si pas d'erreurs, simuler l'envoi
    if (empty($form_errors)) {
        // En production : envoyer l'email et sauvegarder en DB
        $form_success = true;
        
        // Nettoyer les variables pour éviter la resoumission
        $_POST = [];
    }
}

// Générer un token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Include header
include 'includes/layouts/header.php';
?>

<style>
/* Styles spécifiques à la page contact */
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

.contact-hero p {
    font-size: 1.2rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
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
}

.contact-form-section {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding: 2.5rem;
}

.contact-info-section {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.section-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 1.5rem;
}

.form-checkbox {
    margin-right: 0.5rem;
    transform: scale(1.2);
}

.form-error {
    color: #e74c3c;
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.form-success {
    background: #d4edda;
    color: #155724;
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #c3e6cb;
    margin-bottom: 1.5rem;
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

.consent-text {
    font-size: 0.9rem;
    line-height: 1.5;
    color: #4a5568;
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

.submit-btn:disabled {
    background: #a0aec0;
    cursor: not-allowed;
    transform: none;
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

.faq-section {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    padding: 2rem;
}

.faq-item {
    border-bottom: 1px solid #e2e8f0;
    padding: 1rem 0;
}

.faq-item:last-child {
    border-bottom: none;
}

.faq-question {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.faq-answer {
    color: #4a5568;
    line-height: 1.5;
}

.response-time {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    text-align: center;
}

.response-time h3 {
    margin: 0 0 1rem;
    font-size: 1.5rem;
}

.response-time p {
    margin: 0;
    opacity: 0.9;
}

/* Responsive */
@media (max-width: 768px) {
    .contact-container {
        grid-template-columns: 1fr;
        gap: 2rem;
        padding: 0 1rem;
    }
    
    .contact-hero h1 {
        font-size: 2rem;
    }
    
    .contact-form-section,
    .contact-info-card {
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

<!-- Contact Section -->
<section class="contact-section">
    <div class="container">
        <div class="contact-container">
            <!-- Formulaire de contact -->
            <div class="contact-form-section">
                <h2 class="section-title"><?= $lang === 'fr' ? 'Envoyez-nous un message' : 'Send us a message' ?></h2>
                
                <?php if ($form_success): ?>
                    <div class="form-success">
                        <strong><?= $lang === 'fr' ? 'Message envoyé !' : 'Message sent!' ?></strong><br>
                        <?= $lang === 'fr' ? 'Nous vous répondrons dans les 24 heures.' : 'We will respond within 24 hours.' ?>
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
                    
                    <div class="form-group">
                        <label for="name" class="form-label required">
                            <?= $lang === 'fr' ? 'Nom complet' : 'Full name' ?>
                        </label>
                        <input type="text" id="name" name="name" class="form-input" 
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label required">
                            <?= $lang === 'fr' ? 'Adresse email' : 'Email address' ?>
                        </label>
                        <input type="email" id="email" name="email" class="form-input" 
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject" class="form-label required">
                            <?= $lang === 'fr' ? 'Sujet' : 'Subject' ?>
                        </label>
                        <select id="subject" name="subject" class="form-select" required>
                            <option value=""><?= $lang === 'fr' ? 'Choisissez un sujet' : 'Choose a subject' ?></option>
                            <option value="product_question" <?= ($_POST['subject'] ?? '') === 'product_question' ? 'selected' : '' ?>>
                                <?= $lang === 'fr' ? 'Question sur un produit' : 'Product question' ?>
                            </option>
                            <option value="review_request" <?= ($_POST['subject'] ?? '') === 'review_request' ? 'selected' : '' ?>>
                                <?= $lang === 'fr' ? 'Demande de test' : 'Review request' ?>
                            </option>
                            <option value="partnership" <?= ($_POST['subject'] ?? '') === 'partnership' ? 'selected' : '' ?>>
                                <?= $lang === 'fr' ? 'Partenariat' : 'Partnership' ?>
                            </option>
                            <option value="technical_issue" <?= ($_POST['subject'] ?? '') === 'technical_issue' ? 'selected' : '' ?>>
                                <?= $lang === 'fr' ? 'Problème technique' : 'Technical issue' ?>
                            </option>
                            <option value="other" <?= ($_POST['subject'] ?? '') === 'other' ? 'selected' : '' ?>>
                                <?= $lang === 'fr' ? 'Autre' : 'Other' ?>
                            </option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="form-label required">
                            <?= $lang === 'fr' ? 'Votre message' : 'Your message' ?>
                        </label>
                        <textarea id="message" name="message" class="form-textarea" 
                                  placeholder="<?= $lang === 'fr' ? 'Décrivez votre demande en détail...' : 'Describe your request in detail...' ?>" 
                                  required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="consent-section">
                        <label class="form-label">
                            <input type="checkbox" name="consent" class="form-checkbox" required 
                                   <?= isset($_POST['consent']) ? 'checked' : '' ?>>
                            <?= $lang === 'fr' ? 
                                'J\'accepte que mes données soient traitées pour répondre à ma demande conformément à notre' :
                                'I agree that my data may be processed to respond to my request in accordance with our' 
                            ?>
                            <a href="privacy.php?lang=<?= $lang ?>" target="_blank">
                                <?= $lang === 'fr' ? 'politique de confidentialité' : 'privacy policy' ?>
                            </a>.
                        </label>
                        <div class="consent-text">
                            <?= $lang === 'fr' ? 
                                'Vos données ne seront utilisées que pour répondre à votre demande et ne seront jamais vendues à des tiers.' :
                                'Your data will only be used to respond to your request and will never be sold to third parties.'
                            ?>
                        </div>
                    </div>
                    
                    <button type="submit" name="submit_contact" class="submit-btn">
                        <?= $lang === 'fr' ? 'Envoyer le message' : 'Send message' ?>
                    </button>
                </form>
            </div>
            
            <!-- Informations de contact -->
            <div class="contact-info-section">
                <div class="contact-info-card">
                    <h2 class="section-title"><?= $lang === 'fr' ? 'Informations de contact' : 'Contact information' ?></h2>
                    
                    <div class="info-item">
                        <div class="info-icon">📧</div>
                        <div class="info-content">
                            <h3><?= $lang === 'fr' ? 'Email' : 'Email' ?></h3>
                            <p>hello@techessentialspro.com</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">🌍</div>
                        <div class="info-content">
                            <h3><?= $lang === 'fr' ? 'Localisation' : 'Location' ?></h3>
                            <p><?= $lang === 'fr' ? 'Basé à Paris, France & Toronto, Canada' : 'Based in Paris, France & Toronto, Canada' ?></p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">⏰</div>
                        <div class="info-content">
                            <h3><?= $lang === 'fr' ? 'Délai de réponse' : 'Response time' ?></h3>
                            <p><?= $lang === 'fr' ? 'Moins de 24 heures' : 'Less than 24 hours' ?></p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">🗣️</div>
                        <div class="info-content">
                            <h3><?= $lang === 'fr' ? 'Langues' : 'Languages' ?></h3>
                            <p><?= $lang === 'fr' ? 'Français et Anglais' : 'French and English' ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="response-time">
                    <h3><?= $lang === 'fr' ? 'Réponse rapide garantie' : 'Fast response guaranteed' ?></h3>
                    <p><?= $lang === 'fr' ? 'Notre équipe répond à tous les messages dans les 24 heures, 7 jours sur 7.' : 'Our team responds to all messages within 24 hours, 7 days a week.' ?></p>
                </div>
                
                <div class="faq-section">
                    <h3 class="section-title"><?= $lang === 'fr' ? 'Questions fréquentes' : 'Frequently asked questions' ?></h3>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <?= $lang === 'fr' ? 'Proposez-vous des tests personnalisés ?' : 'Do you offer custom reviews?' ?>
                        </div>
                        <div class="faq-answer">
                            <?= $lang === 'fr' ? 
                                'Oui, nous pouvons tester des produits spécifiques selon vos besoins. Contactez-nous pour discuter de votre demande.' :
                                'Yes, we can test specific products according to your needs. Contact us to discuss your request.'
                            ?>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <?= $lang === 'fr' ? 'Acceptez-vous les partenariats ?' : 'Do you accept partnerships?' ?>
                        </div>
                        <div class="faq-answer">
                            <?= $lang === 'fr' ? 
                                'Nous sommes ouverts aux partenariats avec des marques tech innovantes. Présentez-nous votre proposition.' :
                                'We are open to partnerships with innovative tech brands. Present us your proposal.'
                            ?>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            <?= $lang === 'fr' ? 'Puis-je suggérer un produit à tester ?' : 'Can I suggest a product to review?' ?>
                        </div>
                        <div class="faq-answer">
                            <?= $lang === 'fr' ? 
                                'Absolument ! Nous apprécions les suggestions de notre communauté. Utilisez le formulaire ci-dessus.' :
                                'Absolutely! We appreciate suggestions from our community. Use the form above.'
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Validation du formulaire côté client
document.querySelector('form').addEventListener('submit', function(e) {
    const consent = document.querySelector('input[name="consent"]');
    if (!consent.checked) {
        e.preventDefault();
        alert('<?= $lang === 'fr' ? 'Vous devez accepter le traitement de vos données.' : 'You must consent to data processing.' ?>');
    }
});

// Auto-resize textarea
const textarea = document.getElementById('message');
textarea.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});
</script>

<?php 
// Include newsletter et footer
include 'includes/layouts/newsletter.php';
include 'includes/layouts/footer.php';
?>group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.form-label.required::after {
    content: ' *';
    color: #e74c3c;
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
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 120px;
}

.form-