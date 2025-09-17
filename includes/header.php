<?php
/**
 * TechEssentials Pro - Header Template
 * Header responsive avec navigation bilingue
 */

// Sécurité
if (!defined('TECHESSENTIALS_PRO')) {
    die('Accès direct interdit');
}

// Variables de page (peuvent être overridées)
$page_title = $page_title ?? __('seo.default_title');
$page_description = $page_description ?? __('seo.default_description');
$page_keywords = $page_keywords ?? __('seo.default_keywords');
$current_page = $current_page ?? 'home';

// SEO Config
global $SEO_CONFIG, $TRACKING_CONFIG;
?>
<!DOCTYPE html>
<html <?php htmlLang(); ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- SEO Meta Tags -->
    <title><?php echo clean($page_title); ?></title>
    <meta name="description" content="<?php echo clean($page_description); ?>">
    <meta name="keywords" content="<?php echo clean($page_keywords); ?>">
    <meta name="author" content="<?php echo $SEO_CONFIG['author']; ?>">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo clean($page_title); ?>">
    <meta property="og:description" content="<?php echo clean($page_description); ?>">
    <meta property="og:url" content="<?php echo url(); ?>">
    <meta property="og:image" content="<?php echo $page_og_image ?? $SEO_CONFIG['og_image']; ?>">
    <meta property="og:site_name" content="TechEssentials Pro">
    <meta property="og:locale" content="<?php echo getLang() === 'fr' ? 'fr_FR' : 'en_US'; ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo clean($page_title); ?>">
    <meta name="twitter:description" content="<?php echo clean($page_description); ?>">
    <meta name="twitter:image" content="<?php echo $page_og_image ?? $SEO_CONFIG['og_image']; ?>">
    <meta name="twitter:site" content="<?php echo $SEO_CONFIG['twitter_handle']; ?>">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo url($_SERVER['REQUEST_URI']); ?>">
    
    <!-- Alternate Languages -->
    <?php foreach (AVAILABLE_LANGS as $alt_lang): ?>
        <link rel="alternate" hreflang="<?php echo $alt_lang; ?>" 
              href="<?php echo langUrl($alt_lang); ?>">
    <?php endforeach; ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo asset('images/favicon.ico'); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo asset('images/apple-touch-icon.png'); ?>">
    
    <!-- CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Main Styles -->
    <link rel="stylesheet" href="<?php echo asset('css/main.css?v=' . time()); ?>">
    
    <!-- Page Specific Styles -->
    <?php if (isset($page_styles)): ?>
        <?php foreach ($page_styles as $style): ?>
            <link rel="stylesheet" href="<?php echo asset($style); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Google Tag Manager -->
    <?php if (!empty($TRACKING_CONFIG['google_tag_manager']) && ENV === 'production'): ?>
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?php echo $TRACKING_CONFIG['google_tag_manager']; ?>');</script>
    <?php endif; ?>
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "TechEssentials Pro",
        "url": "<?php echo url(); ?>",
        "logo": "<?php echo asset('images/logo.png'); ?>",
        "description": "<?php echo clean($SEO_CONFIG['default_description']); ?>",
        "sameAs": [
            "https://twitter.com/techessentials",
            "https://facebook.com/techessentialspro",
            "https://linkedin.com/company/techessentialspro"
        ]
    }
    </script>
    
    <?php if (isset($page_schema)): ?>
        <script type="application/ld+json"><?php echo json_encode($page_schema); ?></script>
    <?php endif; ?>
</head>
<body class="page-<?php echo $current_page; ?> lang-<?php echo getLang(); ?>">
    
    <?php if (!empty($TRACKING_CONFIG['google_tag_manager']) && ENV === 'production'): ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $TRACKING_CONFIG['google_tag_manager']; ?>"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <?php endif; ?>
    
    <!-- Skip to Content (Accessibility) -->
    <a href="#main-content" class="skip-to-content"><?php _e('accessibility.skip_to_content'); ?></a>
    
    <!-- Header -->
    <header class="site-header" role="banner">
        <div class="container">
            <nav class="main-navigation" role="navigation" aria-label="<?php _e('accessibility.main_navigation'); ?>">
                <!-- Logo -->
                <a href="<?php echo url(); ?>" class="logo" aria-label="TechEssentials Pro - <?php _e('nav.home'); ?>">
                    <span class="logo-text">TechEssentials Pro</span>
                </a>
                
                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle" 
                        aria-label="<?php _e('accessibility.toggle_menu'); ?>" 
                        aria-expanded="false"
                        aria-controls="mobile-menu">
                    <span class="hamburger"></span>
                </button>
                
                <!-- Navigation Content -->
                <div class="nav-content">
                    <!-- Main Menu -->
                    <ul class="nav-links" id="main-menu">
                        <?php
                        $menu_items = [
                            'home' => ['url' => '', 'icon' => '🏠'],
                            'products' => ['url' => 'products', 'icon' => '📱'],
                            'reviews' => ['url' => 'reviews', 'icon' => '⭐'],
                            'blog' => ['url' => 'blog/', 'icon' => '📝'],
                            'deals' => ['url' => 'deals', 'icon' => '🔥'],
                            'contact' => ['url' => 'contact', 'icon' => '📞']
                        ];
                        
                        foreach ($menu_items as $key => $item):
                            $active = ($current_page === $key) ? 'active' : '';
                            $url = url($item['url']);
                        ?>
                            <li>
                                <a href="<?php echo $url; ?>" 
                                   class="nav-link <?php echo $active; ?>"
                                   <?php if ($active): ?>aria-current="page"<?php endif; ?>>
                                    <span class="nav-icon"><?php echo $item['icon']; ?></span>
                                    <span class="nav-text"><?php _e('nav.' . $key); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <!-- Language Selector -->
                    <?php languageSelector(); ?>
                    
                    <!-- CTA Button (optional) -->
                    <?php if (isset($show_header_cta) && $show_header_cta): ?>
                        <a href="<?php echo url('newsletter'); ?>" class="header-cta-btn">
                            <?php _e('header.newsletter_cta'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    
    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobile-menu-overlay"></div>
    <nav class="mobile-menu" id="mobile-menu" aria-label="<?php _e('accessibility.mobile_menu'); ?>">
        <div class="mobile-menu-header">
            <span class="mobile-menu-title">Menu</span>
            <button class="mobile-menu-close" aria-label="<?php _e('accessibility.close_menu'); ?>">
                ✕
            </button>
        </div>
        
        <ul class="mobile-nav-links">
            <?php foreach ($menu_items as $key => $item): ?>
                <li>
                    <a href="<?php echo url($item['url']); ?>" 
                       class="mobile-nav-link <?php echo ($current_page === $key) ? 'active' : ''; ?>">
                        <span><?php echo $item['icon']; ?></span>
                        <?php _e('nav.' . $key); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        
        <div class="mobile-menu-footer">
            <?php languageSelector('mobile-language-switcher'); ?>
        </div>
    </nav>
    
    <!-- Main Content Start -->
    <main id="main-content" role="main">