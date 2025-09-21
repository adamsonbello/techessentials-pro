<?php
/**
 * TechEssentials Pro - Footer Layout
 * Include commun pour toutes les pages
 */

// S'assurer que les variables de langue sont définies
if (!isset($lang)) {
    $lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'fr';
}
?>

<style>
/* Footer Styles */
footer {
    background: #333;
    color: white;
    text-align: center;
    padding: 3rem 0;
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
    text-align: left;
}

.footer-section h3 {
    margin-bottom: 1rem;
    color: #667eea;
}

.footer-section p,
.footer-section a {
    color: #ccc;
    text-decoration: none;
    line-height: 1.6;
}

.footer-section a:hover {
    color: #667eea;
}

.admin-access {
    text-align: center;
    padding: 15px 0;
    border-top: 1px solid #555;
    margin-top: 20px;
}

.admin-access a {
    color: #667eea;
    text-decoration: none;
    margin: 0 15px;
    font-size: 0.9rem;
}

.admin-access a:hover {
    color: #5a6fd8;
}
</style>

    </div> <!-- End Main Content -->

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?= $lang === 'fr' ? 'À Propos' : 'About Us' ?></h3>
                    <p><?= $lang === 'fr' ? 'Votre guide ultime pour les produits tech essentiels. Nous testons et recommandons les meilleurs équipements pour professionnels.' : 'Your ultimate guide to essential tech products. We test and recommend the best equipment for professionals.' ?></p>
                </div>
                <div class="footer-section">
                    <h3><?= $lang === 'fr' ? 'Liens Rapides' : 'Quick Links' ?></h3>
                    <p><a href="reviews.php?lang=<?= $lang ?>"><?= $lang === 'fr' ? 'Tests' : 'Reviews' ?></a></p>
                    <p><a href="deals.php?lang=<?= $lang ?>"><?= $lang === 'fr' ? 'Bons Plans' : 'Deals' ?></a></p>
                    <p><a href="contact.php?lang=<?= $lang ?>"><?= $lang === 'fr' ? 'Contact' : 'Contact' ?></a></p>
                    <p><a href="privacy.php?lang=<?= $lang ?>"><?= $lang === 'en' ? 'Privacy Policy' : 'Politique de Confidentialité' ?></a></p>
                   <p><a href="cgu.php?lang=<?= $lang ?>"><?= $lang === 'en' ? 'Terms of Service' : 'CGU' ?></a></p>
                </div>
                <div class="footer-section">
                    <h3><?= $lang === 'fr' ? 'Contact' : 'Contact' ?></h3>
                    <p><?= $lang === 'en' ? 'Email: contact@techessentialspro.com' : 'Email: contact@techessentialspro.com' ?></p>
                    <p><?= $lang === 'en' ? 'Response time: 24h' : 'Délai de réponse: 24h' ?></p>
                    <p><?= $lang === 'en' ? 'Based in Paris, France & Toronto, Canada' : 'Basé à Paris, France & Toronto, Canada' ?></p>
                </div>
            </div>

            <!-- Admin Access Links -->
            <div class="admin-access">
                <a href="/techessentialspro/admin/">
                    <?= $lang === 'en' ? 'Main Panel' : 'Panel Principal' ?>
                </a>
                <span style="color: #ccc;">|</span>
                <a href="/techessentialspro/admin-blog/login.php">
                    <?= $lang === 'en' ? 'Blog Panel' : 'Panel Blog' ?>
                </a>
            </div>

            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #555;">
                <p>&copy; 2025 TechEssentials Pro. <?= $lang === 'fr' ? 'Tous droits réservés' : 'All rights reserved' ?>.</p>
            </div>
        </div>
    </footer>
</body>
</html>