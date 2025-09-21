<?php
/**
 * TechEssentials Pro - Conditions Générales d'Utilisation
 * Conditions légales pour l'utilisation du site
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
$page_title = $lang === 'fr' ? 'Conditions Générales d\'Utilisation - TechEssentials Pro' : 'Terms of Service - TechEssentials Pro';
$page_description = $lang === 'fr' ? 'Conditions générales d\'utilisation du site TechEssentials Pro.' : 'Terms of service for TechEssentials Pro website.';

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

.warning-box {
    background: #fff3cd;
    border: 2px solid #ffc107;
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
                    <?= $lang === 'fr' ? 'Conditions Générales d\'Utilisation' : 'Terms of Service' ?>
                </h1>
                <div class="legal-updated">
                    <?= $lang === 'fr' ? 'Dernière mise à jour : 21 septembre 2025' : 'Last updated: September 21, 2025' ?>
                </div>
            </div>

            <!-- Navigation -->
            <div class="legal-nav">
                <h3><?= $lang === 'fr' ? 'Sommaire' : 'Table of Contents' ?></h3>
                <ul>
                    <li><a href="#acceptance"><?= $lang === 'fr' ? '1. Acceptation des conditions' : '1. Acceptance of Terms' ?></a></li>
                    <li><a href="#description"><?= $lang === 'fr' ? '2. Description du service' : '2. Service Description' ?></a></li>
                    <li><a href="#usage"><?= $lang === 'fr' ? '3. Utilisation du site' : '3. Site Usage' ?></a></li>
                    <li><a href="#content"><?= $lang === 'fr' ? '4. Contenu et propriété intellectuelle' : '4. Content and Intellectual Property' ?></a></li>
                    <li><a href="#affiliate"><?= $lang === 'fr' ? '5. Liens d\'affiliation' : '5. Affiliate Links' ?></a></li>
                    <li><a href="#disclaimer"><?= $lang === 'fr' ? '6. Limitations de responsabilité' : '6. Disclaimer of Liability' ?></a></li>
                    <li><a href="#modifications"><?= $lang === 'fr' ? '7. Modifications' : '7. Modifications' ?></a></li>
                    <li><a href="#governing-law"><?= $lang === 'fr' ? '8. Droit applicable' : '8. Governing Law' ?></a></li>
                </ul>
            </div>

            <!-- Acceptance -->
            <div id="acceptance" class="legal-section">
                <h2 class="section-title"><?= $lang === 'fr' ? '1. Acceptation des Conditions' : '1. Acceptance of Terms' ?></h2>
                <div class="section-content">
                    <?php if ($lang === 'fr'): ?>
                        <p>En accédant et en utilisant le site web TechEssentials Pro, vous acceptez d'être lié par ces conditions générales d'utilisation. Si vous n'acceptez pas ces conditions, veuillez ne pas utiliser notre site.</p>
                        <div class="highlight-box">
                            <strong>Important :</strong> Ces conditions constituent un accord légal entre vous et TechEssentials Pro.
                        </div>
                    <?php else: ?>
                        <p>By accessing and using the TechEssentials Pro website, you agree to be bound by these terms of service. If you do not agree to these terms, please do not use our site.</p>
                        <div class="highlight-box">
                            <strong>Important:</strong> These terms constitute a legal agreement between you and TechEssentials Pro.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Description -->
            <div id="description" class="legal-section">
                <h2 class="section-title"><?= $lang === 'fr' ? '2. Description du Service' : '2. Service Description' ?></h2>
                <div class="section-content">
                    <?php if ($lang === 'fr'): ?>
                        <p>TechEssentials Pro est un site web d'information et de recommandation de produits technologiques destiné aux professionnels. Nous proposons :</p>
                        <ul>
                            <li>Des tests et avis détaillés de produits tech</li>
                            <li>Des comparatifs et guides d'achat</li>
                            <li>Des recommandations de produits</li>
                            <li>Des liens vers des marchands partenaires</li>
                        </ul>
                        <p>Notre service est gratuit et financé par des commissions d'affiliation.</p>
                    <?php else: ?>
                        <p>TechEssentials Pro is a technology product information and recommendation website for professionals. We offer:</p>
                        <ul>
                            <li>Detailed product tests and reviews</li>
                            <li>Comparisons and buying guides</li>
                            <li>Product recommendations</li>
                            <li>Links to partner merchants</li>
                        </ul>
                        <p>Our service is free and funded by affiliate commissions.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Usage -->
            <div id="usage" class="legal-section">
                <h2 class="section-title"><?= $lang === 'fr' ? '3. Utilisation du Site' : '3. Site Usage' ?></h2>
                <div class="section-content">
                    <?php if ($lang === 'fr'): ?>
                        <p>Vous vous engagez à utiliser notre site de manière légale et appropriée. Il est interdit de :</p>
                        <ul>
                            <li>Utiliser le site à des fins illégales ou non autorisées</li>
                            <li>Tenter de pirater ou d'endommager le site</li>
                            <li>Copier ou reproduire notre contenu sans autorisation</li>
                            <li>Harceler d'autres utilisateurs ou publier du contenu offensant</li>
                            <li>Utiliser des robots ou scripts automatisés</li>
                        </ul>
                        <div class="warning-box">
                            <strong>Attention :</strong> Tout usage abusif peut entraîner une interdiction d'accès au site.
                        </div>
                    <?php else: ?>
                        <p>You agree to use our site legally and appropriately. It is forbidden to:</p>
                        <ul>
                            <li>Use the site for illegal or unauthorized purposes</li>
                            <li>Attempt to hack or damage the site</li>
                            <li>Copy or reproduce our content without authorization</li>
                            <li>Harass other users or publish offensive content</li>
                            <li>Use bots or automated scripts</li>
                        </ul>
                        <div class="warning-box">
                            <strong>Warning:</strong> Any misuse may result in a ban from accessing the site.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Content -->
            <div id="content" class="legal-section">
                <h2 class="section-title"><?= $lang === 'fr' ? '4. Contenu et Propriété Intellectuelle' : '4. Content and Intellectual Property' ?></h2>
                <div class="section-content">
                    <?php if ($lang === 'fr'): ?>
                        <p>Tout le contenu de TechEssentials Pro (textes, images, logos, design) est protégé par le droit d'auteur et appartient à TechEssentials Pro ou à ses partenaires.</p>
                        <p><strong>Droits accordés :</strong></p>
                        <ul>
                            <li>Consultation du contenu à des fins personnelles</li>
                            <li>Partage de liens vers nos articles</li>
                            <li>Citation de courts extraits avec attribution</li>
                        </ul>
                        <p><strong>Droits réservés :</strong></p>
                        <ul>
                            <li>Reproduction intégrale du contenu</li>
                            <li>Usage commercial sans autorisation</li>
                            <li>Modification du contenu</li>
                        </ul>
                    <?php else: ?>
                        <p>All TechEssentials Pro content (text, images, logos, design) is protected by copyright and belongs to TechEssentials Pro or its partners.</p>
                        <p><strong>Rights granted:</strong></p>
                        <ul>
                            <li>Personal viewing of content</li>
                            <li>Sharing links to our articles</li>
                            <li>Quoting short excerpts with attribution</li>
                        </ul>
                        <p><strong>Rights reserved:</strong></p>
                        <ul>
                            <li>Full reproduction of content</li>
                            <li>Commercial use without authorization</li>
                            <li>Modification of content</li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Affiliate -->
            <div id="affiliate" class="legal-section">
                <h2 class="section-title"><?= $lang === 'fr' ? '5. Liens d\'Affiliation' : '5. Affiliate Links' ?></h2>
                <div class="section-content">
                    <?php if ($lang === 'fr'): ?>
                        <div class="highlight-box">
                            <strong>Divulgation importante :</strong> TechEssentials Pro participe à des programmes d'affiliation. Nous recevons des commissions sur les achats effectués via nos liens, sans coût supplémentaire pour vous.
                        </div>
                        <p>Nos programmes d'affiliation incluent :</p>
                        <ul>
                            <li>Amazon Associates Program</li>
                            <li>Fnac Partner Program</li>
                            <li>Autres partenaires retailers</li>
                        </ul>
                        <p><strong>Nos engagements :</strong></p>
                        <ul>
                            <li>Recommandations basées sur des tests réels</li>
                            <li>Transparence sur nos partenariats</li>
                            <li>Indépendance éditoriale maintenue</li>
                            <li>Avis honnêtes, commissions ou pas</li>
                        </ul>
                    <?php else: ?>
                        <div class="highlight-box">
                            <strong>Important disclosure:</strong> TechEssentials Pro participates in affiliate programs. We receive commissions on purchases made through our links, at no additional cost to you.
                        </div>
                        <p>Our affiliate programs include:</p>
                        <ul>
                            <li>Amazon Associates Program</li>
                            <li>Fnac Partner Program</li>
                            <li>Other retail partners</li>
                        </ul>
                        <p><strong>Our commitments:</strong></p>
                        <ul>
                            <li>Recommendations based on real testing</li>
                            <li>Transparency about partnerships</li>
                            <li>Editorial independence maintained</li>
                            <li>Honest opinions, commission or not</li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Disclaimer -->
            <div id="disclaimer" class="legal-section">
                <h2 class="section-title"><?= $lang === 'fr' ? '6. Limitations de Responsabilité' : '6. Disclaimer of Liability' ?></h2>
                <div class="section-content">
                    <?php if ($lang === 'fr'): ?>
                        <div class="warning-box">
                            <strong>Clause de non-responsabilité :</strong> TechEssentials Pro fournit des informations à titre informatif uniquement.
                        </div>
                        <p>Nous déclinons toute responsabilité concernant :</p>
                        <ul>
                            <li>Les décisions d'achat basées sur nos recommandations</li>
                            <li>Les problèmes avec les produits achetés chez nos partenaires</li>
                            <li>Les variations de prix ou disponibilité</li>
                            <li>Les interruptions de service du site</li>
                            <li>Les erreurs dans le contenu (malgré nos efforts)</li>
                        </ul>
                        <p><strong>Responsabilité de l'utilisateur :</strong></p>
                        <ul>
                            <li>Vérifier les spécifications avant achat</li>
                            <li>Lire les conditions des marchands</li>
                            <li>Exercer votre jugement personnel</li>
                        </ul>
                    <?php else: ?>
                        <div class="warning-box">
                            <strong>Disclaimer:</strong> TechEssentials Pro provides information for informational purposes only.
                        </div>
                        <p>We disclaim any responsibility regarding:</p>
                        <ul>
                            <li>Purchase decisions based on our recommendations</li>
                            <li>Issues with products bought from our partners</li>
                            <li>Price or availability variations</li>
                            <li>Site service interruptions</li>
                            <li>Content errors (despite our efforts)</li>
                        </ul>
                        <p><strong>User responsibility:</strong></p>
                        <ul>
                            <li>Verify specifications before purchase</li>
                            <li>Read merchant terms and conditions</li>
                            <li>Exercise personal judgment</li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Modifications -->
            <div id="modifications" class="legal-section">
                <h2 class="section-title"><?= $lang === 'fr' ? '7. Modifications' : '7. Modifications' ?></h2>
                <div class="section-content">
                    <?php if ($lang === 'fr'): ?>
                        <p>TechEssentials Pro se réserve le droit de modifier ces conditions à tout moment. Les modifications prendront effet immédiatement après publication sur le site.</p>
                        <p>Il est de votre responsabilité de consulter régulièrement ces conditions. L'utilisation continue du site après modification constitue votre acceptation des nouvelles conditions.</p>
                        <p>En cas de modification substantielle, nous nous efforcerons de vous en informer par email si vous êtes abonné à notre newsletter.</p>
                    <?php else: ?>
                        <p>TechEssentials Pro reserves the right to modify these terms at any time. Modifications will take effect immediately after publication on the site.</p>
                        <p>It is your responsibility to regularly review these terms. Continued use of the site after modification constitutes your acceptance of the new terms.</p>
                        <p>In case of substantial modification, we will endeavor to inform you by email if you subscribe to our newsletter.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Governing Law -->
            <div id="governing-law" class="legal-section">
                <h2 class="section-title"><?= $lang === 'fr' ? '8. Droit Applicable' : '8. Governing Law' ?></h2>
                <div class="section-content">
                    <?php if ($lang === 'fr'): ?>
                        <p>Ces conditions sont régies par le droit français et canadien, selon votre localisation.</p>
                        <p>En cas de litige, les tribunaux compétents seront :</p>
                        <ul>
                            <li>Tribunaux de Paris, France (pour les utilisateurs européens)</li>
                            <li>Tribunaux de Toronto, Canada (pour les utilisateurs nord-américains)</li>
                        </ul>
                        <p>Avant tout recours judiciaire, nous encourageons une résolution amiable par médiation.</p>
                    <?php else: ?>
                        <p>These terms are governed by French and Canadian law, depending on your location.</p>
                        <p>In case of dispute, competent courts will be:</p>
                        <ul>
                            <li>Courts of Paris, France (for European users)</li>
                            <li>Courts of Toronto, Canada (for North American users)</li>
                        </ul>
                        <p>Before any legal action, we encourage amicable resolution through mediation.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contact -->
            <div class="legal-section">
                <h2 class="section-title"><?= $lang === 'fr' ? 'Contact' : 'Contact' ?></h2>
                <div class="contact-info">
                    <?php if ($lang === 'fr'): ?>
                        <p><strong>Pour toute question concernant ces conditions :</strong></p>
                        <p>Email : <a href="mailto:legal@techessentialspro.com">legal@techessentialspro.com</a></p>
                        <p>Réponse sous 72 heures</p>
                        <p>TechEssentials Pro<br>
                        Basé à Paris, France & Toronto, Canada</p>
                    <?php else: ?>
                        <p><strong>For any questions regarding these terms:</strong></p>
                        <p>Email: <a href="mailto:legal@techessentialspro.com">legal@techessentialspro.com</a></p>
                        <p>Response within 72 hours</p>
                        <p>TechEssentials Pro<br>
                        Based in Paris, France & Toronto, Canada</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Footer note -->
            <div class="legal-section">
                <div class="highlight-box">
                    <?= $lang === 'fr' ? 
                        'En utilisant TechEssentials Pro, vous reconnaissez avoir lu, compris et accepté ces conditions dans leur intégralité.' :
                        'By using TechEssentials Pro, you acknowledge having read, understood and accepted these terms in their entirety.' 
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