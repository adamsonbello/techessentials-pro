/**
 * TechEssentials Pro - JavaScript Principal
 * @author Adams (Fred) - CTO
 * @version 2.0
 * @date 2025-09-16
 */

// Configuration globale (définie dans le template)
const config = window.TechEssentials || {};

// ==========================================
// UTILITAIRES
// ==========================================

const utils = {
    /**
     * Sélecteur DOM moderne
     */
    $: (selector, context = document) => context.querySelector(selector),
    $$: (selector, context = document) => Array.from(context.querySelectorAll(selector)),

    /**
     * Débounce fonction
     */
    debounce: (func, wait, immediate) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                timeout = null;
                if (!immediate) func(...args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func(...args);
        };
    },

    /**
     * Throttle fonction
     */
    throttle: (func, limit) => {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    /**
     * Requête AJAX moderne
     */
    async fetch(url, options = {}) {
        const defaults = {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (config.csrfToken && (options.method === 'POST' || options.method === 'PUT' || options.method === 'DELETE')) {
            defaults.headers['X-CSRF-Token'] = config.csrfToken;
        }

        const mergedOptions = {
            ...defaults,
            ...options,
            headers: { ...defaults.headers, ...options.headers }
        };

        try {
            const response = await fetch(url, mergedOptions);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || `HTTP ${response.status}`);
            }
            
            return data;
        } catch (error) {
            console.error('Fetch error:', error);
            throw error;
        }
    },

    /**
     * Afficher une notification
     */
    notify(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification--${type}`;
        notification.innerHTML = `
            <div class="notification__content">
                <span class="notification__message">${message}</span>
                <button class="notification__close">&times;</button>
            </div>
        `;

        // Styles inline pour éviter les dépendances CSS
        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: type === 'error' ? '#f56565' : type === 'success' ? '#48bb78' : '#4299e1',
            color: 'white',
            padding: '16px',
            borderRadius: '8px',
            boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
            zIndex: '1000',
            maxWidth: '400px',
            transform: 'translateX(100%)',
            transition: 'transform 0.3s ease'
        });

        document.body.appendChild(notification);

        // Animation d'entrée
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);

        // Fermeture automatique
        const removeNotification = () => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        };

        // Fermeture manuelle
        const closeBtn = notification.querySelector('.notification__close');
        closeBtn.addEventListener('click', removeNotification);

        // Fermeture automatique
        if (duration > 0) {
            setTimeout(removeNotification, duration);
        }

        return notification;
    },

    /**
     * Validation d'email
     */
    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    /**
     * Formater la date
     */
    formatDate(date, locale = config.currentLang || 'en') {
        return new Intl.DateTimeFormat(locale, {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }).format(new Date(date));
    }
};

// ==========================================
// NAVIGATION & HEADER
// ==========================================

class Navigation {
    constructor() {
        this.header = utils.$('.site-header');
        this.searchBar = utils.$('.search-bar');
        this.searchToggle = utils.$('[data-toggle="search"]');
        this.mobileToggle = utils.$('.mobile-menu-toggle');
        this.langSwitcher = utils.$('.language-switcher');
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.handleScroll();
    }

    bindEvents() {
        // Toggle de recherche
        if (this.searchToggle) {
            this.searchToggle.addEventListener('click', () => this.toggleSearch());
        }

        // Toggle mobile
        if (this.mobileToggle) {
            this.mobileToggle.addEventListener('click', () => this.toggleMobileMenu());
        }

        // Scroll header
        window.addEventListener('scroll', utils.throttle(() => this.handleScroll(), 100));

        // Fermer la recherche avec Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeSearch();
            }
        });

        // Language switcher
        if (this.langSwitcher) {
            this.langSwitcher.addEventListener('click', (e) => {
                e.preventDefault();
                this.langSwitcher.classList.toggle('active');
            });
        }
    }

    toggleSearch() {
        if (this.searchBar) {
            const isActive = this.searchBar.classList.toggle('active');
            if (isActive) {
                const searchInput = this.searchBar.querySelector('.search-input');
                if (searchInput) {
                    setTimeout(() => searchInput.focus(), 100);
                }
            }
        }
    }

    closeSearch() {
        if (this.searchBar) {
            this.searchBar.classList.remove('active');
        }
    }

    toggleMobileMenu() {
        document.body.classList.toggle('mobile-menu-open');
        this.mobileToggle.classList.toggle('active');
    }

    handleScroll() {
        if (this.header) {
            const scrolled = window.scrollY > 10;
            this.header.classList.toggle('scrolled', scrolled);
        }
    }
}

// ==========================================
// FORMULAIRES
// ==========================================

class FormHandler {
    constructor() {
        this.forms = utils.$$('form[data-action]');
        this.init();
    }

    init() {
        this.forms.forEach(form => this.bindForm(form));
    }

    bindForm(form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.handleSubmit(form);
        });
    }

    async handleSubmit(form) {
        const action = form.dataset.action;
        const submitBtn = form.querySelector('button[type="submit"]');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        // État de chargement
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = config.translations.loading || 'Loading...';
        }

        // Validation côté client
        const validationErrors = this.validateForm(form, data);
        if (validationErrors.length > 0) {
            this.showValidationErrors(form, validationErrors);
            this.resetSubmitButton(submitBtn);
            return;
        }

        try {
            const response = await utils.fetch(`${config.apiUrl}${action}`, {
                method: 'POST',
                body: JSON.stringify(data)
            });

            this.handleSuccess(form, response);
            
        } catch (error) {
            this.handleError(form, error);
        } finally {
            this.resetSubmitButton(submitBtn);
        }
    }

    validateForm(form, data) {
        const errors = [];
        
        // Validation email
        const emailField = form.querySelector('input[type="email"]');
        if (emailField && emailField.value && !utils.validateEmail(emailField.value)) {
            errors.push({
                field: emailField.name,
                message: 'Please enter a valid email address'
            });
        }

        // Validation champs requis
        const requiredFields = form.querySelectorAll('input[required], textarea[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                errors.push({
                    field: field.name,
                    message: `${field.name} is required`
                });
            }
        });

        return errors;
    }

    showValidationErrors(form, errors) {
        // Supprimer les erreurs existantes
        form.querySelectorAll('.error-message').forEach(el => el.remove());
        form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));

        // Afficher les nouvelles erreurs
        errors.forEach(error => {
            const field = form.querySelector(`[name="${error.field}"]`);
            if (field) {
                field.classList.add('error');
                const errorEl = document.createElement('div');
                errorEl.className = 'error-message';
                errorEl.textContent = error.message;
                field.parentNode.appendChild(errorEl);
            }
        });
    }

    handleSuccess(form, response) {
        const message = response.message || config.translations.success;
        utils.notify(message, 'success');

        // Réinitialiser le formulaire pour la newsletter
        if (form.dataset.action.includes('newsletter')) {
            form.reset();
        }

        // Supprimer les erreurs
        form.querySelectorAll('.error-message').forEach(el => el.remove());
        form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
    }

    handleError(form, error) {
        const message = error.message || config.translations.error;
        utils.notify(message, 'error');
    }

    resetSubmitButton(button) {
        if (button) {
            button.disabled = false;
            // Restaurer le texte original (stocké dans data-attribute)
            const originalText = button.dataset.originalText || button.textContent;
            button.textContent = originalText;
        }
    }
}

// ==========================================
// LAZY LOADING IMAGES
// ==========================================

class LazyLoader {
    constructor() {
        this.images = utils.$$('img[loading="lazy"]');
        this.init();
    }

    init() {
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver(
                (entries) => this.handleIntersect(entries),
                { threshold: 0.1 }
            );
            this.images.forEach(img => this.observer.observe(img));
        } else {
            // Fallback pour les navigateurs non supportés
            this.images.forEach(img => this.loadImage(img));
        }
    }

    handleIntersect(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                this.loadImage(entry.target);
                this.observer.unobserve(entry.target);
            }
        });
    }

    loadImage(img) {
        if (img.dataset.src) {
            img.src = img.dataset.src;
            img.classList.add('loaded');
        }
    }
}

// ==========================================
// ANALYTICS & TRACKING
// ==========================================

class Analytics {
    constructor() {
        this.gtag = window.gtag;
        this.init();
    }

    init() {
        if (!this.gtag) return;

        // Tracking des clics sur liens externes
        utils.$$('a[href^="http"]').forEach(link => {
            if (!link.href.includes(window.location.hostname)) {
                link.addEventListener('click', () => {
                    this.trackEvent('click', 'external_link', link.href);
                });
            }
        });

        // Tracking des clics sur liens d'affiliation
        utils.$$('a[rel*="nofollow"]').forEach(link => {
            link.addEventListener('click', () => {
                this.trackEvent('click', 'affiliate_link', link.href);
            });
        });

        // Tracking de la newsletter
        document.addEventListener('newsletter_subscribe', (e) => {
            this.trackEvent('engagement', 'newsletter_subscribe', e.detail.email);
        });
    }

    trackEvent(action, category, label, value) {
        if (this.gtag) {
            this.gtag('event', action, {
                event_category: category,
                event_label: label,
                value: value
            });
        }
    }

    trackPageView(path) {
        if (this.gtag) {
            this.gtag('config', config.ga_tracking_id, {
                page_path: path
            });
        }
    }
}

// ==========================================
// PERFORMANCE MONITORING
// ==========================================

class PerformanceMonitor {
    constructor() {
        this.init();
    }

    init() {
        // Mesurer le temps de chargement
        window.addEventListener('load', () => {
            if ('performance' in window) {
                const navTiming = performance.getEntriesByType('navigation')[0];
                const loadTime = navTiming.loadEventEnd - navTiming.fetchStart;
                
                console.log(`Page loaded in ${loadTime}ms`);
                
                // Envoyer aux analytics si configuré
                if (window.gtag) {
                    window.gtag('event', 'timing_complete', {
                        name: 'load',
                        value: Math.round(loadTime)
                    });
                }
            }
        });

        // Détecter les erreurs JavaScript
        window.addEventListener('error', (e) => {
            console.error('JavaScript Error:', e.error);
            
            if (window.gtag) {
                window.gtag('event', 'exception', {
                    description: e.error?.message || 'Unknown error',
                    fatal: false
                });
            }
        });
    }
}

// ==========================================
// SERVICE WORKER
// ==========================================

class ServiceWorkerManager {
    constructor() {
        this.init();
    }

    async init() {
        if ('serviceWorker' in navigator && config.environment === 'production') {
            try {
                const registration = await navigator.serviceWorker.register('/sw.js');
                console.log('SW registered:', registration);
                
                // Écouter les mises à jour
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            this.showUpdateNotification();
                        }
                    });
                });
                
            } catch (error) {
                console.error('SW registration failed:', error);
            }
        }
    }

    showUpdateNotification() {
        utils.notify(
            'A new version is available. Refresh to update.',
            'info',
            10000
        );
    }
}

// ==========================================
// THEME MANAGER
// ==========================================

class ThemeManager {
    constructor() {
        this.currentTheme = localStorage.getItem('theme') || 'auto';
        this.init();
    }

    init() {
        this.applyTheme();
        this.bindEvents();
    }

    bindEvents() {
        // Écouter les changements de préférence système
        if (window.matchMedia) {
            window.matchMedia('(prefers-color-scheme: dark)')
                .addEventListener('change', () => this.applyTheme());
        }
    }

    applyTheme() {
        let theme = this.currentTheme;
        
        if (theme === 'auto') {
            theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        
        document.documentElement.setAttribute('data-theme', theme);
    }

    toggleTheme() {
        const themes = ['light', 'dark', 'auto'];
        const currentIndex = themes.indexOf(this.currentTheme);
        const nextIndex = (currentIndex + 1) % themes.length;
        
        this.currentTheme = themes[nextIndex];
        localStorage.setItem('theme', this.currentTheme);
        this.applyTheme();
        
        return this.currentTheme;
    }
}

// ==========================================
// INITIALISATION PRINCIPALE
// ==========================================

class TechEssentialsApp {
    constructor() {
        this.modules = {};
        this.init();
    }

    async init() {
        // Attendre que le DOM soit prêt
        if (document.readyState === 'loading') {
            await new Promise(resolve => {
                document.addEventListener('DOMContentLoaded', resolve);
            });
        }

        try {
            // Initialiser les modules
            this.modules.navigation = new Navigation();
            this.modules.formHandler = new FormHandler();
            this.modules.lazyLoader = new LazyLoader();
            this.modules.analytics = new Analytics();
            this.modules.performanceMonitor = new PerformanceMonitor();
            this.modules.serviceWorker = new ServiceWorkerManager();
            this.modules.themeManager = new ThemeManager();

            // Initialiser les composants spécifiques à la page
            this.initPageSpecific();

            // Marquer l'app comme initialisée
            document.body.classList.add('app-initialized');
            
            console.log('TechEssentials App initialized successfully');
            
        } catch (error) {
            console.error('Failed to initialize TechEssentials App:', error);
        }
    }

    initPageSpecific() {
        const pageType = document.body.dataset.route;
        
        switch (pageType) {
            case 'home':
                this.initHomePage();
                break;
            case 'reviews':
                this.initReviewsPage();
                break;
            case 'blog':
                this.initBlogPage();
                break;
            default:
                break;
        }
    }

    initHomePage() {
        // Animations d'entrée pour les éléments
        this.animateOnScroll();
        
        // Statistiques animées
        this.animateCounters();
    }

    initReviewsPage() {
        // Filtres et tri
        this.initFilters();
        
        // Comparaison de produits
        this.initProductComparison();
    }

    initBlogPage() {
        // Partage social
        this.initSocialSharing();
        
        // Table des matières
        this.initTableOfContents();
    }

    animateOnScroll() {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                    }
                });
            }, { threshold: 0.1 });

            utils.$$('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });
        }
    }

    animateCounters() {
        const counters = utils.$$('.stat-number');
        
        counters.forEach(counter => {
            const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
            const duration = 2000;
            const step = target / (duration / 16);
            let current = 0;

            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    counter.textContent = target.toLocaleString();
                    clearInterval(timer);
                } else {
                    counter.textContent = Math.floor(current).toLocaleString();
                }
            }, 16);
        });
    }

    initFilters() {
        // Implémentation des filtres pour les pages de liste
        const filterButtons = utils.$$('[data-filter]');
        const items = utils.$$('.filterable-item');

        filterButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const filter = button.dataset.filter;
                
                // Mise à jour des boutons actifs
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                // Filtrage des éléments
                items.forEach(item => {
                    if (filter === 'all' || item.dataset.category === filter) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    }

    initProductComparison() {
        // Fonctionnalité de comparaison de produits
        const compareCheckboxes = utils.$$('input[data-compare]');
        const compareButton = utils.$('#compare-button');
        
        if (compareCheckboxes.length > 0 && compareButton) {
            compareCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', this.updateCompareButton.bind(this));
            });
        }
    }

    updateCompareButton() {
        const selected = utils.$$('input[data-compare]:checked');
        const button = utils.$('#compare-button');
        
        if (button) {
            button.textContent = `Compare (${selected.length})`;
            button.disabled = selected.length < 2;
        }
    }

    initSocialSharing() {
        const shareButtons = utils.$$('[data-share]');
        
        shareButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const platform = button.dataset.share;
                const url = encodeURIComponent(window.location.href);
                const title = encodeURIComponent(document.title);
                
                let shareUrl;
                switch (platform) {
                    case 'twitter':
                        shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
                        break;
                    case 'facebook':
                        shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                        break;
                    case 'linkedin':
                        shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
                        break;
                    default:
                        return;
                }
                
                window.open(shareUrl, 'share', 'width=600,height=400');
            });
        });
    }

    initTableOfContents() {
        const headings = utils.$$('h2, h3, h4');
        const tocContainer = utils.$('#table-of-contents');
        
        if (headings.length > 0 && tocContainer) {
            const tocList = document.createElement('ul');
            
            headings.forEach((heading, index) => {
                // Ajouter un ID si pas présent
                if (!heading.id) {
                    heading.id = `heading-${index}`;
                }
                
                const tocItem = document.createElement('li');
                const tocLink = document.createElement('a');
                tocLink.href = `#${heading.id}`;
                tocLink.textContent = heading.textContent;
                tocItem.appendChild(tocLink);
                tocList.appendChild(tocItem);
            });
            
            tocContainer.appendChild(tocList);
        }
    }
}

// ==========================================
// DÉMARRAGE DE L'APPLICATION
// ==========================================

// Initialisation immédiate
const app = new TechEssentialsApp();

// Exposition globale pour le debug
if (config.debug) {
    window.TechEssentialsApp = app;
    window.utils = utils;
}