<?php
/**
 * TechEssentials Pro - Footer Template
 * Footer responsive avec newsletter et liens bilingues
 */

// Sécurité
if (!defined('TECHESSENTIALS_PRO')) {
    die('Accès direct interdit');
}

// Stats pour le footer (peut être overridé)
$footer_stats = $footer_stats ?? [
    'customers' => '15K+',
    'products' => '500+',
    'satisfaction' => '98%',
    'saved' => '€2M+'
];
?>

    </main>
    <!-- Main Content End -->
    
    <!-- Newsletter Section -->
    <?php if (!isset($hide_newsletter) || !$hide_newsletter): ?>
    <section class="newsletter-section" id="newsletter">
        <div class="container">
            <div class="newsletter-content">
                <div class="newsletter-text">
                    <h2><?php _e('newsletter.title'); ?></h2>
                    <p><?php _e('newsletter.subtitle'); ?></p>
                    
                    <div class="newsletter-benefits">
                        <span class="benefit-item">✓ <?php _e('newsletter.benefit_deals'); ?></span>
                        <span class="benefit-item">✓ <?php _e('newsletter.benefit_reviews'); ?></span>
                        <span class="benefit-item">✓ <?php _e('newsletter.benefit_guides'); ?></span>
                    </div>
                </div>
                
                <form class="newsletter-form" id="newsletter-form" method="POST" action="<?php echo url('api/newsletter'); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRF(); ?>">
                    <input type="hidden" name="language" value="<?php echo getLang(); ?>">
                    
                    <div class="form-group">
                        <input type="email" 
                               name="email" 
                               class="newsletter-input" 
                               placeholder="<?php _e('newsletter.placeholder'); ?>" 
                               required
                               aria-label="<?php _e('newsletter.email_label'); ?>">
                        <button type="submit" class="newsletter-button">
                            <span class="button-text"><?php _e('newsletter.button'); ?></span>
                            <span class="button-loading" style="display:none;">
                                <svg class="spinner" viewBox="0 0 50 50">
                                    <circle cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                                </svg>
                            </span>
                        </button>
                    </div>
                    
                    <div class="newsletter-message" id="newsletter-message"></div>
                    
                    <small class="newsletter-privacy">
                        <?php _e('newsletter.privacy_text'); ?>
                        <a href="<?php echo url('privacy'); ?>"><?php _e('newsletter.privacy_link'); ?></a>
                    </small>
                </form>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <!-- Trust Section -->
    <section class="trust-section">
        <div class="container">
            <div class="trust-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo $footer_stats['customers']; ?></span>
                    <span class="stat-label"><?php _e('stats.happy_customers'); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $footer_stats['products']; ?></span>
                    <span class="stat-label"><?php _e('stats.products_tested'); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $footer_stats['satisfaction']; ?></span>
                    <span class="stat-label"><?php _e('stats.satisfaction_rate'); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo $footer_stats['saved']; ?></span>
                    <span class="stat-label"><?php _e('stats.money_saved'); ?></span>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Main Footer -->
    <footer class="site-footer" role="contentinfo">
        <div class="container">
            <div class="footer-content">
                <!-- About Section -->
                <div class="footer-section footer-about">
                    <h3><?php _e('footer.aboutTitle'); ?></h3>
                    <p><?php _e('footer.aboutText'); ?></p>
                    
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook" class="social-link">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="Twitter" class="social-link">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="LinkedIn" class="social-link">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                        <a href="#" aria-label="YouTube" class="social-link">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="footer-section footer-links">
                    <h3><?php _e('footer.quickLinks'); ?></h3>
                    <ul>
                        <li><a href="<?php echo url('reviews'); ?>"><?php _e('footer.linkReviews'); ?></a></li>
                        <li><a href="<?php echo url('deals'); ?>"><?php _e('footer.linkDeals'); ?></a></li>
                        <li><a href="<?php echo url('blog'); ?>"><?php _e('footer.linkBlog'); ?></a></li>
                        <li><a href="<?php echo url('about'); ?>"><?php _e('footer.linkAbout'); ?></a></li>
                        <li><a href="<?php echo url('faq'); ?>"><?php _e('footer.linkFAQ'); ?></a></li>
                    </ul>
                </div>
                
                <!-- Categories -->
                <div class="footer-section footer-categories">
                    <h3><?php _e('footer.categories'); ?></h3>
                    <ul>
                        <li><a href="<?php echo url('category/webcams'); ?>"><?php _e('categories.webcams'); ?></a></li>
                        <li><a href="<?php echo url('category/headphones'); ?>"><?php _e('categories.headphones'); ?></a></li>
                        <li><a href="<?php echo url('category/keyboards'); ?>"><?php _e('categories.keyboards'); ?></a></li>
                        <li><a href="<?php echo url('category/monitors'); ?>"><?php _e('categories.monitors'); ?></a></li>
                        <li><a href="<?php echo url('category/accessories'); ?>"><?php _e('categories.accessories'); ?></a></li>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div class="footer-section footer-contact">
                    <h3><?php _e('footer.contactTitle'); ?></h3>
                    <p class="contact-item">
                        <svg width="16" height="16" fill="currentColor">
                            <path d="M2 4a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V4zm2-1a1 1 0 00-1 1v.217l7 4.2 7-4.2V4a1 1 0 00-1-1H4zm9 2.383L6.203 9.242a.5.5 0 01-.52 0L1 5.383V12a1 1 0 001 1h8a1 1 0 001-1V5.383z"/>
                        </svg>
                        <?php _e('footer.contactEmail'); ?>
                    </p>
                    <p class="contact-item">
                        <svg width="16" height="16" fill="currentColor">
                            <path d="M8 16s6-5.686 6-10A6 6 0 002 6c0 4.314 6 10 6 10z"/>
                            <path d="M8 8a2 2 0 110-4 2 2 0 010 4z"/>
                        </svg>
                        <?php _e('footer.contactBased'); ?>
                    </p>
                    <p class="contact-item">
                        <svg width="16" height="16" fill="currentColor">
                            <path d="M8 3.5a.5.5 0 00-1 0V9a.5.5 0 00.252.434l3.5 2a.5.5 0 00.496-.868L8 8.71V3.5z"/>
                            <path d="M8 16A8 8 0 108 0a8 8 0 000 16zm7-8A7 7 0 111 8a7 7 0 0114 0z"/>
                        </svg>
                        <?php _e('footer.contactTime'); ?>
                    </p>
                    
                    <div class="footer-cta">
                        <a href="<?php echo url('contact'); ?>" class="footer-cta-button">
                            <?php _e('footer.contactCTA'); ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="footer-legal">
                    <p><?php _e('footer.legal'); ?></p>
                    <div class="legal-links">
                        <a href="<?php echo url('privacy'); ?>"><?php _e('footer.linkPrivacy'); ?></a>
                        <span class="separator">|</span>
                        <a href="<?php echo url('terms'); ?>"><?php _e('footer.linkTerms'); ?></a>
                        <span class="separator">|</span>
                        <a href="<?php echo url('cookies'); ?>"><?php _e('footer.linkCookies'); ?></a>
                        <span class="separator">|</span>
                        <a href="<?php echo url('affiliate-disclosure'); ?>"><?php _e('footer.linkAffiliate'); ?></a>
                    </div>
                </div>
                
                <div class="footer-payments">
                    <span><?php _e('footer.secure_payments'); ?></span>
                    <div class="payment-icons">
                        <img src="<?php echo asset('images/payments/visa.svg'); ?>" alt="Visa">
                        <img src="<?php echo asset('images/payments/mastercard.svg'); ?>" alt="Mastercard">
                        <img src="<?php echo asset('images/payments/paypal.svg'); ?>" alt="PayPal">
                        <img src="<?php echo asset('images/payments/stripe.svg'); ?>" alt="Stripe">
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top -->
    <button id="back-to-top" class="back-to-top" aria-label="<?php _e('accessibility.back_to_top'); ?>">
        <svg width="24" height="24" fill="currentColor">
            <path d="M7 14l5-5 5 5z"/>
        </svg>
    </button>
    
    <!-- Scripts -->
    <script>
        // Configuration globale JS
        window.TechEssentials = {
            baseUrl: '<?php echo url(); ?>',
            apiUrl: '<?php echo url('api/'); ?>',
            currentLang: '<?php echo getLang(); ?>',
            csrfToken: '<?php echo generateCSRF(); ?>',
            translations: <?php echo json_encode(__('js')); ?>
        };
    </script>
    
    <!-- Main JavaScript -->
    <script src="<?php echo asset('js/main.js?v=' . time()); ?>"></script>
    
    <!-- Page Specific Scripts -->
    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?php echo asset($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Inline Scripts -->
    <?php if (isset($inline_scripts)): ?>
        <script><?php echo $inline_scripts; ?></script>
    <?php endif; ?>
    
    <!-- Google Analytics -->
    <?php if (!empty($TRACKING_CONFIG['google_analytics']) && ENV === 'production'): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $TRACKING_CONFIG['google_analytics']; ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo $TRACKING_CONFIG['google_analytics']; ?>');
    </script>
    <?php endif; ?>
    
    <!-- Facebook Pixel -->
    <?php if (!empty($TRACKING_CONFIG['facebook_pixel']) && ENV === 'production'): ?>
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '<?php echo $TRACKING_CONFIG['facebook_pixel']; ?>');
        fbq('track', 'PageView');
    </script>
    <noscript>
        <img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id=<?php echo $TRACKING_CONFIG['facebook_pixel']; ?>&ev=PageView&noscript=1"/>
    </noscript>
    <?php endif; ?>
</body>
</html>