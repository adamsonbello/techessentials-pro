<?php
/**
 * TechEssentials Pro - Template Erreur 404
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 */

// Empêcher l'accès direct
if (!defined('TECHESSENTIALS_PRO')) {
    die('Direct access not allowed');
}
?>

<section class="error-page">
    <div class="container">
        <div class="error-content">
            <!-- Error Illustration -->
            <div class="error-illustration">
                <div class="error-code">404</div>
                <div class="error-animation">
                    <div class="floating-elements">
                        <div class="floating-element"></div>
                        <div class="floating-element"></div>
                        <div class="floating-element"></div>
                    </div>
                </div>
            </div>
            
            <!-- Error Message -->
            <div class="error-message">
                <h1><?= Language::get('errors.404.title') ?></h1>
                <p><?= Language::get('errors.404.message') ?></p>
                <p class="error-description">
                    The page you're looking for might have been moved, deleted, or you might have mistyped the URL.
                </p>
            </div>
            
            <!-- Search Box -->
            <div class="error-search">
                <form action="<?= url('search') ?>" method="get" class="search-form">
                    <div class="search-input-group">
                        <input type="search" 
                               name="q" 
                               placeholder="Search for reviews, articles, or products..."
                               class="search-input"
                               autocomplete="off">
                        <button type="submit" class="search-btn">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Quick Actions -->
            <div class="error-actions">
                <a href="<?= url() ?>" class="btn btn-primary btn-lg">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <?= Language::get('errors.404.back_home') ?>
                </a>
                
                <button class="btn btn-outline btn-lg" onclick="history.back()">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Go Back
                </button>
            </div>
        </div>
        
        <!-- Popular Content -->
        <div class="popular-content">
            <h2>Popular Pages</h2>
            <div class="popular-links">
                <div class="popular-section">
                    <h3>Top Reviews</h3>
                    <ul>
                        <li><a href="<?= url('reviews') ?>">All Reviews</a></li>
                        <li><a href="<?= url('reviews/category/audio') ?>">Audio & Headphones</a></li>
                        <li><a href="<?= url('reviews/category/monitors') ?>">Monitors & Displays</a></li>
                        <li><a href="<?= url('reviews/category/keyboards') ?>">Keyboards & Mice</a></li>
                    </ul>
                </div>
                
                <div class="popular-section">
                    <h3>Recent Articles</h3>
                    <ul>
                        <li><a href="<?= url('blog') ?>">Latest Articles</a></li>
                        <li><a href="<?= url('blog/category/guides') ?>">Setup Guides</a></li>
                        <li><a href="<?= url('blog/category/tips') ?>">Productivity Tips</a></li>
                        <li><a href="<?= url('blog/category/news') ?>">Tech News</a></li>
                    </ul>
                </div>
                
                <div class="popular-section">
                    <h3>Other Pages</h3>
                    <ul>
                        <li><a href="<?= url('deals') ?>">Best Deals</a></li>
                        <li><a href="<?= url('contact') ?>">Contact Us</a></li>
                        <li><a href="<?= url('about') ?>">About</a></li>
                        <li><a href="<?= url('newsletter') ?>">Newsletter</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Help Section -->
<section class="help-section">
    <div class="container">
        <div class="help-content">
            <h2>Need Help?</h2>
            <p>If you believe this is an error or if you were looking for something specific, please let us know.</p>
            
            <div class="help-actions">
                <a href="<?= url('contact') ?>" class="btn btn-outline">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Report This Issue
                </a>
                
                <div class="social-links">
                    <span>Follow us:</span>
                    <a href="#" class="social-link" aria-label="Twitter">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                        </svg>
                    </a>
                    <a href="#" class="social-link" aria-label="YouTube">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.error-page {
    padding: var(--space-20) 0;
    text-align: center;
}

.error-content {
    max-width: 800px;
    margin: 0 auto var(--space-16);
}

.error-illustration {
    position: relative;
    margin-bottom: var(--space-12);
}

.error-code {
    font-size: 10rem;
    font-weight: 900;
    color: var(--primary);
    line-height: 1;
    margin-bottom: var(--space-4);
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.error-animation {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 200px;
    height: 200px;
    pointer-events: none;
}

.floating-elements {
    position: relative;
    width: 100%;
    height: 100%;
}

.floating-element {
    position: absolute;
    width: 20px;
    height: 20px;
    background: var(--accent);
    border-radius: 50%;
    opacity: 0.6;
    animation: float 3s ease-in-out infinite;
}

.floating-element:nth-child(1) {
    top: 20%;
    left: 20%;
    animation-delay: -1s;
}

.floating-element:nth-child(2) {
    top: 60%;
    right: 20%;
    animation-delay: -2s;
}

.floating-element:nth-child(3) {
    bottom: 30%;
    left: 60%;
    animation-delay: -0.5s;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

.error-message h1 {
    font-size: var(--font-size-3xl);
    color: var(--gray-900);
    margin-bottom: var(--space-4);
}

.error-message p {
    font-size: var(--font-size-lg);
    color: var(--gray-600);
    margin-bottom: var(--space-2);
}

.error-description {
    max-width: 600px;
    margin: 0 auto var(--space-8);
    line-height: 1.6;
}

.error-search {
    margin: var(--space-8) 0;
}

.search-input-group {
    display: flex;
    max-width: 500px;
    margin: 0 auto;
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: var(--box-shadow-lg);
}

.search-input {
    flex: 1;
    padding: var(--space-4) var(--space-6);
    border: none;
    font-size: var(--font-size-lg);
    background: white;
}

.search-input:focus {
    outline: none;
}

.search-btn {
    padding: var(--space-4) var(--space-6);
    background: var(--primary);
    color: white;
    border: none;
    cursor: pointer;
    transition: var(--transition);
}

.search-btn:hover {
    background: var(--primary-dark);
}

.error-actions {
    display: flex;
    justify-content: center;
    gap: var(--space-4);
    flex-wrap: wrap;
}

.popular-content {
    background: var(--gray-50);
    border-radius: var(--border-radius-lg);
    padding: var(--space-8);
    text-align: left;
}

.popular-content h2 {
    text-align: center;
    margin-bottom: var(--space-8);
    color: var(--gray-900);
}

.popular-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--space-8);
}

.popular-section h3 {
    color: var(--primary);
    margin-bottom: var(--space-4);
    font-size: var(--font-size-lg);
}

.popular-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.popular-section li {
    margin-bottom: var(--space-3);
}

.popular-section a {
    color: var(--gray-700);
    text-decoration: none;
    transition: var(--transition);
    display: flex;
    align-items: center;
    padding: var(--space-2) 0;
}

.popular-section a:hover {
    color: var(--primary);
    padding-left: var(--space-3);
}

.help-section {
    background: var(--gray-900);
    color: white;
    padding: var(--space-16) 0;
    text-align: center;
}

.help-content h2 {
    color: white;
    margin-bottom: var(--space-4);
}

.help-content p {
    color: var(--gray-300);
    margin-bottom: var(--space-8);
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.help-actions {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: var(--space-8);
    flex-wrap: wrap;
}

.social-links {
    display: flex;
    align-items: center;
    gap: var(--space-4);
    color: var(--gray-400);
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: var(--gray-800);
    border-radius: 50%;
    color: var(--gray-400);
    transition: var(--transition);
}

.social-link:hover {
    background: var(--primary);
    color: white;
}

@media (max-width: 768px) {
    .error-code {
        font-size: 6rem;
    }
    
    .error-actions {
        flex-direction: column;
    }
    
    .search-input-group {
        flex-direction: column;
        border-radius: var(--border-radius);
    }
    
    .search-btn {
        border-top: 1px solid var(--gray-200);
    }
    
    .help-actions {
        flex-direction: column;
        gap: var(--space-6);
    }
    
    .popular-links {
        grid-template-columns: 1fr;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .error-code {
        font-size: 4rem;
    }
    
    .btn-lg {
        padding: var(--space-3) var(--space-6);
        font-size: var(--font-size-base);
    }
}
</style>

<script>
// Analytics pour les erreurs 404
if (window.gtag) {
    gtag('event', 'page_view', {
        page_title: 'Error 404',
        page_location: window.location.href,
        custom_parameter: 'error_page'
    });
}

// Auto-focus sur le champ de recherche
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        // Focus après un court délai pour éviter les conflits
        setTimeout(() => {
            searchInput.focus();
        }, 500);
    }
});

// Suggestions de recherche basées sur l'URL
const suggestSearchFromURL = () => {
    const path = window.location.pathname;
    const segments = path.split('/').filter(segment => segment.length > 0);
    
    if (segments.length > 0) {
        const lastSegment = segments[segments.length - 1];
        // Nettoyer et suggérer comme terme de recherche
        const suggestion = lastSegment
            .replace(/-/g, ' ')
            .replace(/[0-9]/g, '')
            .trim();
            
        if (suggestion.length > 2) {
            const searchInput = document.querySelector('.search-input');
            if (searchInput && !searchInput.value) {
                searchInput.placeholder = `Try searching for "${suggestion}"`;
            }
        }
    }
};

suggestSearchFromURL();
</script>