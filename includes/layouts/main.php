<?php
/**
 * TechEssentials Pro - Layout Principal
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 */

// Empêcher l'accès direct
if (!defined('TECHESSENTIALS_PRO')) {
    die('Direct access not allowed');
}

$page_title = $page_title ?? __('site.title');
$page_description = $page_description ?? __('site.description');
$page_image = $page_image ?? $SEO_CONFIG['og_image'];
$current_url = getCurrentURL();
?>
<!DOCTYPE html>
<html lang="<?= $current_lang ?>" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    
    <?= generateSEOMeta($page_title, $page_description, '', $page_image, $current_url) ?>
    
    <!-- Preload des ressources critiques -->
    <link rel="preload" href="<?= asset('fonts/inter-var.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= asset('css/critical.css') ?>" as="style">
    
    <!-- CSS Critique inline pour éviter le FOUC -->
    <style>
        /* CSS critique minimal */
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; margin: 0; }
        .loading { opacity: 0; transition: opacity 0.3s; }
        .loaded { opacity: 1; }
        header { position: sticky; top: 0; background: white; z-index: 100; }
    </style>
    
    <!-- CSS principal -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>" media="all">
    
    <!-- Favicon et icônes -->
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
    <link rel="icon" type="image/png" href="<?= asset('images/favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= asset('images/apple-touch-icon.png') ?>">
    
    <!-- Web App Manifest -->
    <link rel="manifest" href="<?= asset('manifest.json') ?>">
    
    <!-- Couleur de thème -->
    <meta name="theme-color" content="#667eea">
    
    <!-- Données structurées JSON-LD -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "<?= __('site.title') ?>",
        "description": "<?= __('site.description') ?>",
        "url": "<?= url() ?>",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "<?= url('search') ?>?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    
    <?php if (ENV === 'production'): ?>
    <!-- Google Analytics / Tag Manager -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $TRACKING_CONFIG['google_analytics'] ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= $TRACKING_CONFIG['google_analytics'] ?>');
    </script>
    <?php endif; ?>
</head>

<body class="loading <?= $current_route ?>-page lang-<?= $current_lang ?>" data-route="<?= $current_route ?>">
    
    <!-- Skip to main content pour l'accessibilité -->
    <a href="#main" class="skip-link">Skip to main content</a>
    
    <!-- Header -->
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <div class="site-logo">
                    <a href="<?= url() ?>" aria-label="<?= __('site.title') ?>">
                        <img src="<?= asset('images/logo.svg') ?>" 
                             alt="<?= __('site.title') ?>" 
                             width="200" height="40">
                    </a>
                </div>
                
                <!-- Navigation principale -->
                <nav class="main-nav" aria-label="Main navigation">
                    <ul class="nav-list">
                        <li class="nav-item<?= isActiveURL('') ? ' active' : '' ?>">
                            <a href="<?= url() ?>"><?= __('navigation.home') ?></a>
                        </li>
                        <li class="nav-item<?= isActiveURL('reviews') ? ' active' : '' ?>">
                            <a href="<?= url('reviews') ?>"><?= __('navigation.reviews') ?></a>
                        </li>
                        <li class="nav-item<?= isActiveURL('blog') ? ' active' : '' ?>">
                            <a href="<?= url('blog') ?>"><?= __('navigation.blog') ?></a>
                        </li>
                        <li class="nav-item<?= isActiveURL('deals') ? ' active' : '' ?>">
                            <a href="<?= url('deals') ?>"><?= __('navigation.deals') ?></a>
                        </li>
                        <li class="nav-item<?= isActiveURL('contact') ? ' active' : '' ?>">
                            <a href="<?= url('contact') ?>"><?= __('navigation.contact') ?></a>
                        </li>
                    </ul>
                </nav>
                
                <!-- Actions header -->
                <div class="header-actions">
                    <!-- Recherche -->
                    <div class="search-toggle">
                        <button type="button" aria-label="Open search" data-toggle="search">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Sélecteur de langue -->
                    <div class="language-switcher">
                        <button type="button" class="lang-toggle" aria-label="Switch language">
                            <?= strtoupper($current_lang) ?>
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="lang-menu">
                            <?php foreach (AVAILABLE_LANGS as $lang): ?>
                            <?php if ($lang !== $current_lang): ?>
                            <a href="?lang=<?= $lang ?>" class="lang-option"><?= strtoupper($lang) ?></a>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Menu mobile -->
                    <button type="button" class="mobile-menu-toggle" aria-label="Toggle menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Barre de recherche -->
        <div class="search-bar" id="search-bar">
            <div class="container">
                <form action="<?= url('search') ?>" method="get" class="search-form">
                    <input type="search" 
                           name="q" 
                           placeholder="<?= __('common.search') ?>..." 
                           class="search-input"
                           autocomplete="off">
                    <button type="submit" class="search-submit">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </header>
    
    <!-- Contenu principal -->
    <main id="main" class="main-content">
        <?php
        // Inclure le template de page spécifique
        $template_file = INCLUDES_PATH . "pages/{$template}.php";
        if (file_exists($template_file)) {
            include $template_file;
        } else {
            echo "<div class='container'><h1>Template not found: {$template}</h1></div>";
        }
        ?>
    </main>
    
    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <!-- Newsletter signup -->
            <section class="newsletter-section">
                <div class="newsletter-content">
                    <h2><?= __('newsletter.title') ?></h2>
                    <p><?= __('newsletter.description') ?></p>
                </div>
                <form class="newsletter-form" data-action="api/newsletter/subscribe" method="post">
                    <div class="form-group">
                        <input type="email" 
                               name="email" 
                               placeholder="<?= __('newsletter.form.email_placeholder') ?>"
                               required>
                        <input type="text" 
                               name="name" 
                               placeholder="<?= __('newsletter.form.name_placeholder') ?>">
                        <input type="hidden" name="language" value="<?= $current_lang ?>">
                        <input type="hidden" name="source" value="footer">
                        <button type="submit"><?= __('newsletter.form.subscribe_button') ?></button>
                    </div>
                    <p class="privacy-notice"><?= __('newsletter.form.privacy_text') ?></p>
                </form>
            </section>
            
            <!-- Footer content -->
            <div class="footer-content">
                <div class="footer-grid">
                    <!-- À propos -->
                    <div class="footer-column">
                        <h3><?= __('footer.about.title') ?></h3>
                        <p><?= __('footer.about.description') ?></p>
                        <div class="social-links">
                            <a href="#" aria-label="Twitter"><svg><!-- Twitter icon --></svg></a>
                            <a href="#" aria-label="YouTube"><svg><!-- YouTube icon --></svg></a>
                            <a href="#" aria-label="LinkedIn"><svg><!-- LinkedIn icon --></svg></a>
                        </div>
                    </div>
                    
                    <!-- Liens rapides -->
                    <div class="footer-column">
                        <h3><?= __('footer.quick_links.title') ?></h3>
                        <ul class="footer-links">
                            <li><a href="<?= url('about') ?>"><?= __('navigation.about') ?></a></li>
                            <li><a href="<?= url('privacy') ?>"><?= __('footer.quick_links.privacy') ?></a></li>
                            <li><a href="<?= url('terms') ?>"><?= __('footer.quick_links.terms') ?></a></li>
                            <li><a href="<?= url('sitemap') ?>"><?= __('footer.quick_links.sitemap') ?></a></li>
                        </ul>
                    </div>
                    
                    <!-- Catégories -->
                    <div class="footer-column">
                        <h3><?= __('footer.categories.title') ?></h3>
                        <ul class="footer-links">
                            <li><a href="<?= url('reviews/category/audio') ?>"><?= __('footer.categories.audio') ?></a></li>
                            <li><a href="<?= url('reviews/category/monitors') ?>"><?= __('footer.categories.monitors') ?></a></li>
                            <li><a href="<?= url('reviews/category/keyboards') ?>"><?= __('footer.categories.keyboards') ?></a></li>
                            <li><a href="<?= url('reviews/category/webcams') ?>"><?= __('footer.categories.webcams') ?></a></li>
                        </ul>
                    </div>
                    
                    <!-- Statistiques -->
                    <div class="footer-column">
                        <h3>Site Stats</h3>
                        <div class="site-stats">
                            <div class="stat-item">
                                <strong><?= number_format($site_stats['reviews']) ?></strong>
                                <span><?= __('stats.reviews') ?></span>
                            </div>
                            <div class="stat-item">
                                <strong><?= number_format($site_stats['subscribers']) ?></strong>
                                <span><?= __('stats.subscribers') ?></span>
                            </div>
                            <div class="stat-item">
                                <strong><?= $site_stats['avg_rating'] ?>/5</strong>
                                <span><?= __('stats.rating') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="footer-bottom">
                <div class="copyright">
                    <p>&copy; <?= date('Y') ?> <?= __('site.title') ?>. <?= __('site.copyright') ?>.</p>
                </div>
                <div class="footer-meta">
                    <p>Made with ❤️ by Adams (Fred) - CTO</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Overlay pour mobile -->
    <div class="mobile-overlay"></div>
    
    <!-- Scripts -->
    <script>
        // Configuration globale JavaScript
        window.TechEssentials = {
            baseUrl: '<?= BASE_URL ?>',
            apiUrl: '<?= BASE_URL ?>api/',
            currentLang: '<?= $current_lang ?>',
            debug: <?= DEBUG ? 'true' : 'false' ?>,
            csrfToken: '<?= generateCSRF() ?>',
            translations: {
                loading: '<?= __('common.loading') ?>',
                error: '<?= __('common.error') ?>',
                success: '<?= __('common.success') ?>',
                newsletterSuccess: '<?= __('newsletter.success') ?>',
                newsletterError: '<?= __('newsletter.error') ?>'
            }
        };
    </script>
    
    <!-- JavaScript principal -->
    <script src="<?= asset('js/main.js') ?>" defer></script>
    
    <?php if (ENV === 'production'): ?>
    <!-- Service Worker pour le cache -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js');
            });
        }
    </script>
    <?php endif; ?>
    
    <!-- Marquer le DOM comme chargé -->
    <script>
        document.body.classList.remove('loading');
        document.body.classList.add('loaded');
    </script>
</body>
</html>