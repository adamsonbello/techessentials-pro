<?php
/**
 * TechEssentials Pro - Politique de Confidentialité
 * Conforme RGPD et Amazon Associates
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
$page_title = $lang === 'fr' ? 'Politique de Confidentialité - TechEssentials Pro' : 'Privacy Policy - TechEssentials Pro';
$page_description = $lang === 'fr' ? 'Notre politique de confidentialité et protection des données personnelles.' : 'Our privacy policy and personal data protection.';

// Include header
include 'includes/layouts/header.php';
?>

<style>
.legal-container {
    padding: 60px 0;
    background: #f8fafc;
}

.legal-content {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    padding: 40px;
    max-width: 800px;
    margin: 0 auto;
}

.legal-header {
    text-align: center;
    margin-bottom: 3rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid #667eea;
}

.legal-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 1rem;
}

.legal-updated {
    color: #6c757d;
    font-size: 1rem;
}

.legal-section {
    margin-bottom: 2.5rem;
}

.section-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 1rem;
    border-left: 4px solid #667eea;
    padding-left: 1rem;
}

.section-content {
    line-height: 1.7;
    color: #4a5568;
    margin-bottom: 1rem;
}

.section-content p {
    margin-bottom: 1rem;
}

.section-content ul {
    margin: 1rem 0;
    padding-left: 2rem;
}

.section-content li {
    margin-bottom: 0.5rem;
}

.highlight-box {
    background: #f0f4ff;
    border: 2px solid #667eea;
    border-radius: 10px;
    padding: 1.5rem;
    margin: 1.5rem 0;
}

.contact-info {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    margin: 1.5rem 0;
}

.legal-nav {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.legal-nav h3 {
    margin-bottom: 1rem;
    color: #2d3748;
}

.legal-nav ul {
    list-style: none;
    padding: 0;
}

.legal-nav li {
    margin-bottom: 0.5rem;
}

.legal-nav a {
    color: #667eea;
    text-decoration: none;
}

.legal-nav a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .legal-content {
        padding: 20px;
        margin: 10px;
    }
    
    .legal-title {
        font-size: 2rem;
    }
}
</style>

<div class="legal-container">
    <div class="container">
        <div class="legal-content">
            <div class="legal-header">
                <h1 class="legal-title">
                    <?= $lang === 'fr' ? 'Politique de Confidentialité' : 'Privacy Policy' ?>
                </h1>
                <div class="legal-updated">
                    <?= $lang === 'fr' ? 'Dernière mise à jour : 21 septembre 2025' : 'Last updated: September 21, 2025' ?>
                </div>
            </div>

            <!-- Navigation -->
            <div class="legal-nav">
                <h3><?= $lang === 'fr' ? 'Sommaire' : 'Table of Contents' ?></h3>
                <ul>
                    <li><a href="#introduction"><?= $lang === 'fr' ? '1. Introduction' : '1. Introduction' ?></a></li>
                    <li><a href="#data-collection"><?= $lang === 'fr' ? '2. Collecte des données' : '2. Data Collection' ?></a></li>
                    <li><a href="#data-usage"><?= $lang === 'fr' ? '3. Utilisation des données' : '3. Data Usage' ?></a></li>
                    <li><a href="#cookies"><?= $lang === 'fr' ? '4. Cookies et tracking' : '4. Cookies and Tracking' ?></a></li>
                    <li><a href="#affiliates"><?= $lang === 'fr' ? '5. Liens d\'affiliation' : '5. Affiliate Links' ?></a></li>
                    <li><a href="#rights"><?= $lang === 'fr' ? '6. Vos droits RGPD' : '6. Your GDPR Rights' ?></a></li>
                    <li><a href="#contact"><?= $lang === 'fr' ? '7. Contact' : '7. Contact' ?></a></li>
                </ul>
            </div>

            <!-- Introduction -->
            <div id="introduction" class="legal-section">
                <h2 class="section-title"><?= $lang === 'fr' ? '1. Introduction' : '1. Introduction' ?></h2>
                <div class="section-content">
                    <?php if ($lang === 'fr'): ?>
                        <p>TechEssentials Pro s'engage à protéger votre vie privée et vos données personnelles. Cette politique de confidentialité explique comment nous collectons, utilisons et protégeons vos informations lorsque vous visitez notre site web.</p>
                        <p>En utilisant notre site, vous acceptez les pratiques décrites dans cette politique.</p>
                    <?php else: ?>
                        <p>TechEssentials Pro is committed to protecting your privacy and personal data. This privacy policy explains how we collect, use and protect your information when you visit our website.</p>
                        <p>By using our site, you agree to the practices described in this policy.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Data Collection -->
            <div id="data-collection" class="legal-section">
                <h2 class="section-title"><?= $lang === 'fr' ? '2. Collecte des Données' : '2. Data Collection' ?></h2>
                <div class="section-content">
                    <?php if ($lang === 'fr'): ?>
                        <p>Nous collectons les types de données suivants :</p>
                        <ul>
                            <li><strong>Données techniques :</strong> Adresse IP, type de navigateur, pages visitées, durée de visite</li>
                            <li><strong>Newsletter :</strong> Adresse email si vous vous abonnez volontairement</li>
                            <li><strong>Commentaires :</strong> Nom et contenu des avis que vous laissez</li>
                            <li><strong>Cookies :</strong> Préférences de langue, analytics de navigation</li>
                        </ul>
                        <div class="highlight-box">
                            <strong>Important :</strong> Nous ne collectons jamais d'informations de paiement. Tous les achats sont effectués directement chez nos partenaires (Amazon, Fnac, etc.).
                        </div>
                    <?php else: ?>
                        <p>We collect the following types of data:</p>
                        <ul>
                            <li><strong>Technical data:</strong> IP address, browser type, pages visited, visit duration</li>
                            <li><strong>Newsletter:</strong> Email address if you voluntarily subscribe</li>
                            <li><strong>Comments:</strong> Name and content of reviews you leave</li>
                            <li><strong>Cookies:</strong> Language preferences, navigation analytics</li>
                        </ul>
                        <div class="highlight-box">
                            <strong>Important:</strong> We never collect payment information. All purchases are made directly with our partners (Amazon, Fnac, etc.).
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Data Usage -->
            <div id="data-usage" class="legal-section">
                <h2 class="section-title"><?= $lang === 'fr' ? '3. Utilisation des Données' : '3. Data Usage' ?></h2>
                <div class="section-content">
                    <?php if ($lang === 'fr'): ?>
                        <p>Nous utilisons vos données pour :</p>
                        <ul>
                            <li>Améliorer l'expérience utilisateur de notre site</li>
                            <li>Envoyer notre newsletter (uniquement si vous vous êtes abonné)</li>
                            <li>Analyser le trafic et optimiser notre contenu</li>
                            <li>Répondre à vos questions et commentaires</li>
                            <li>Respecter nos obligations légales</li>
                        </ul>
                        <p><strong>Nous ne vendons jamais vos données à des tiers.</strong></p>
                    <?php else: ?>
                        <p>We use your data to:</p>
                        <ul>
                            <li>Improve the user experience of our site</li>
                            <li>Send our newsletter (only if you subscribed)</li>
                            <li>Analyze traffic and optimize our content</li>
                            <li>Respond to your questions and comments</li>
                            <li>Comply with our legal obligations</li>
                        </ul>
                        <p><strong>We never sell your data to third parties.</strong></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cookies -->
            <div id="cookies" class="legal-section">
                <h2 class="section-title"><?= $lang === 'fr' ? '4. Cookies et Tracking' : '4. Cookies and Tracking' ?></h2>
                <div class="section-content">
                    <?php if ($lang === 'fr'): ?>
                        <p>Notre site utilise des cookies pour :</p>
                        <ul>
                            <li><strong>Cookies essentiels :</strong> Préférences de langue, sessions utilisateur</li>
                            <li><strong>Cookies analytics :</strong> Google Analytics pour comprendre l'usage du site</li>
                            <li><strong>Cookies publicitaires :</strong> Tracking des clics d'affiliation (anonyme)</li>
                        </ul>
                        <p>Vous pouvez désactiver les cookies dans votre navigateur, mais certaines fonctionnalités du site pourraient être limitées.</p>
                    <?php else: ?>
                        <p>Our site uses cookies for:</p>
                        <ul>
                            <li><strong>Essential cookies:</strong> Language preferences, user sessions</li>
                            <li><strong>Analytics cookies:</strong> Google Analytics to understand site usage</li>
                            <li><strong>Advertising cookies:</strong> Affiliate click tracking (anonymous)</li>
                        </ul>
                        <p>You can disable cookies in your browser, but some site features may be limited.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Affiliate Links -->
            <div id="affiliates" class="legal-section">
                <h2 class="section-title"><?= $lang === 'fr' ? '5. Liens d\'Affiliation' : '5. Affiliate Links' ?></h2>
                <div class="section-content">
                    <?php if ($lang === 'fr'): ?>
                        <div class="highlight-box">
                            <strong>Transparence :</strong> TechEssentials Pro contient des liens d'affiliation. Nous pouvons recevoir une commission si vous achetez un produit via nos liens, sans coût supplémentaire pour vous.
                        </div>
                        <p>Nos partenaires d'affiliation incluent :</p>
                        <ul>
                            <li>Amazon Associates (Europe)</li>
                            <li>Programme partenaire Fnac</li>
                            <li>Autres retailers tech sélectionnés</li>
                        </ul>
                        <p>Ces commissions nous permettent de maintenir le site gratuit et de continuer à produire du contenu de qualité.</p>
                    <?php else: ?>
                        <div class="highlight-box">
                            <strong>Transparency:</strong> TechEssentials Pro contains affiliate links. We may receive a commission if you purchase a product through our links, at no additional cost to you.
                        </div>
                        <p>Our affiliate partners include:</p>
                        <ul>
                            <li>Amazon Associates (Europe)</li>
                            <li>Fnac Partner Program</li>
                            <li>Other selected tech retailers</li>
                        </ul>
                        <p>These commissions allow us to keep the site free and continue producing quality content.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- GDPR Rights -->
            <div id="rights" class="legal-section">
                <h2 class="section-title"><?= $lang === 'fr' ? '6. Vos Droits RGPD' : '6. Your GDPR Rights' ?></h2>
                <div class="section-content">
                    <?php if ($lang === 'fr'): ?>
                        <p>Selon le RGPD, vous avez le droit de :</p>
                        <ul>
                            <li><strong>Accès :</strong> Demander quelles données nous avons sur vous</li>
                            <li><strong>Rectification :</strong> Corriger des données incorrectes</li>
                            <li><strong>Suppression :</strong> Demander l'effacement de vos données</li>
                            <li><strong>Portabilité :</strong> Récupérer vos données dans un format lisible</li>
                            <li><strong>Opposition :</strong> Vous opposer au traitement de vos données</li>
                            <li><strong>Limitation :</strong> Limiter l'utilisation de vos données</li>
                        </ul>
                        <p>Pour exercer ces droits, contactez-nous à l'adresse indiquée ci-dessous.</p>
                    <?php else: ?>
                        <p>Under GDPR, you have the right to:</p>
                        <ul>
                            <li><strong>Access:</strong> Request what data we have about you</li>
                            <li><strong>Rectification:</strong> Correct incorrect data</li>
                            <li><strong>Erasure:</strong> Request deletion of your data</li>
                            <li><strong>Portability:</strong> Retrieve your data in a readable format</li>
                            <li><strong>Objection:</strong> Object to processing of your data</li>
                            <li><strong>Restriction:</strong> Limit the use of your data</li>
                        </ul>
                        <p>To exercise these rights, contact us at the address below.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contact -->
            <div id="contact" class="legal-section">
                <h2 class="section-title"><?= $lang === 'fr' ? '7. Contact' : '7. Contact' ?></h2>
                <div class="contact-info">
                    <?php if ($lang === 'fr'): ?>
                        <p><strong>Pour toute question concernant cette politique de confidentialité :</strong></p>
                        <p>Email : <a href="mailto:privacy@techessentialspro.com">privacy@techessentialspro.com</a></p>
                        <p>Réponse sous 72 heures</p>
                        <p>Basé à Paris, France & Toronto, Canada</p>
                    <?php else: ?>
                        <p><strong>For any questions regarding this privacy policy:</strong></p>
                        <p>Email: <a href="mailto:privacy@techessentialspro.com">privacy@techessentialspro.com</a></p>
                        <p>Response within 72 hours</p>
                        <p>Based in Paris, France & Toronto, Canada</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Footer note -->
            <div class="legal-section">
                <div class="highlight-box">
                    <?= $lang === 'fr' ? 
                        'Cette politique peut être mise à jour. Nous vous notifierons de tout changement significatif.' :
                        'This policy may be updated. We will notify you of any significant changes.' 
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Include newsletter et footer
include 'includes/layouts/newsletter.php';
include 'includes/layouts/footer.php';
?>