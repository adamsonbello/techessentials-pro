// ===============================
// Language switching functionality
// ===============================
let currentLanguage = localStorage.getItem("lang") || "en";

const translations = {
    en: {
        'Home': 'Home',
        'Products': 'Products', 
        'Reviews': 'Reviews',
        'Deals': 'Deals',
        'Contact': 'Contact'
    },
    fr: {
        'Home': 'Accueil',
        'Products': 'Produits',
        'Reviews': 'Tests', 
        'Deals': 'Offres',
        'Contact': 'Contact'
    }
};

function switchLanguage(lang) {
    currentLanguage = lang;
    localStorage.setItem("lang", lang);

    // Recharge produits si dispo
    if (typeof loadProducts === "function") {
        loadProducts();
    }
    
    // Hide all content (si tu utilises encore .lang-content ailleurs)
    document.querySelectorAll('.lang-content').forEach(content => {
        content.classList.remove('active');
    });

    // Show selected language content (si .lang-content est encore utilisé)
    const contentBlock = document.getElementById(lang + '-content');
    if (contentBlock) contentBlock.classList.add('active');

    // Update language buttons
    document.querySelectorAll('.lang-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    // ⚠️ Utilise querySelector, pas event.target pour éviter erreurs
    const activeBtn = document.querySelector(`.lang-btn[onclick="switchLanguage('${lang}')"]`);
    if (activeBtn) activeBtn.classList.add('active');
    
    // Update navigation text
    updateNavigation(lang);
    
    // Update page title and meta
    if (lang === 'fr') {
        document.title = 'TechEssentials Pro - Meilleur Tech pour Télétravail | Best Tech for Remote Workers';
    } else {
        document.title = 'TechEssentials Pro - Best Tech for Remote Workers | Meilleur Tech pour Télétravail';
    }
    
    console.log(`🌍 Language switched to: ${lang}`);
    
    // Trigger scroll animations again
    initScrollAnimations();

    // ✅ Mettre à jour le footer aussi
    loadFooterTranslations(lang);
}

function updateNavigation(lang) {
    const navLinks = document.querySelectorAll('.nav-link');
    const keys = ['Home', 'Products', 'Reviews', 'Deals', 'Contact'];
    
    navLinks.forEach((link, index) => {
        if (translations[lang] && translations[lang][keys[index]]) {
            link.textContent = translations[lang][keys[index]];
        }
    });
}

// ===============================
// Analytics and conversion tracking
// ===============================
const analytics = {
    clicks: {},
    subscribers: {en: 0, fr: 0},
    languageUsage: {en: 0, fr: 0},
    pageViews: 0
};

function trackClick(productId) {
    const clickKey = `${productId}_${currentLanguage}`;
    analytics.clicks[clickKey] = (analytics.clicks[clickKey] || 0) + 1;
    console.log(`Tracked click for ${clickKey}:`, analytics.clicks[clickKey]);
    
    // Simulate affiliate redirect
    const message = currentLanguage === 'fr' 
        ? `Redirection vers le meilleur prix pour ${productId}...\n\n🎯 En production: Lien d'affiliation Amazon/Fnac`
        : `Redirecting to best price for ${productId}...\n\n🎯 In production: Amazon/Best Buy affiliate link`;
    
    alert(message);
    updateMetrics();
}

function subscribeNewsletter(event, lang) {
    event.preventDefault();
    analytics.subscribers[lang]++;
    console.log(`New ${lang} subscriber! Total:`, analytics.subscribers[lang]);
    
    const message = lang === 'fr' 
        ? '🎉 Merci ! Vous recevrez nos recommandations tech hebdomadaires + accès prioritaire aux offres.'
        : '🎉 Thanks! You\'ll receive weekly tech recommendations + early access to deals.';
        
    alert(message);
    event.target.reset();
    updateMetrics();
}

function updateMetrics() {
    const totalClicks = Object.values(analytics.clicks).reduce((a, b) => a + b, 0);
    const totalSubscribers = analytics.subscribers.en + analytics.subscribers.fr;
    
    console.log('🚀 PERFORMANCE METRICS:', {
        totalClicks: totalClicks,
        englishSubscribers: analytics.subscribers.en,
        frenchSubscribers: analytics.subscribers.fr,
        totalSubscribers: totalSubscribers,
        estimatedMonthlyRevenue: '€' + (totalClicks * 25).toFixed(2),
        conversionRate: (totalClicks / analytics.pageViews * 100).toFixed(2) + '%',
        currentLanguage: currentLanguage
    });
}

// ===============================
// Scroll animations
// ===============================
function initScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('revealed');
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.scroll-reveal').forEach(element => {
        observer.observe(element);
    });
}

// ===============================
// Smooth scrolling
// ===============================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        const activeContent = document.querySelector('.lang-content.active');
        const targetElement = activeContent ? activeContent.querySelector(targetId) : null;
        if (targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// ===============================
// Initialize on page load
// ===============================
document.addEventListener('DOMContentLoaded', function() {
    analytics.pageViews++;
    console.log('🌟 TechEssentials Pro - Bilingual Affiliate Site Loaded');
    console.log('🎯 Revenue Projection: €1500-3200/month (bilingual expansion)');
    
    // Initialize scroll animations
    initScrollAnimations();

    // ✅ Charger le footer avec la langue sauvegardée
    const savedLang = localStorage.getItem("lang") || "en";
    loadFooterTranslations(savedLang);

    // Track initial language usage
    analytics.languageUsage[savedLang]++;
});

// ===============================
// Page visibility tracking
// ===============================
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        console.log('👁️ User returned to page - High engagement signal');
    }
});

// ===============================
// Footer translations loader
// ===============================
async function loadFooterTranslations(lang) {
    try {
        const res = await fetch("data/translations.json");
        const translations = await res.json();
        const t = translations.footer;

        document.getElementById("footer-about-title").textContent = t.aboutTitle[lang];
        document.getElementById("footer-about-text").textContent = t.aboutText[lang];
        document.getElementById("footer-quicklinks").textContent = t.quickLinks[lang];
        document.getElementById("footer-link-reviews").textContent = t.linkReviews[lang];
        document.getElementById("footer-link-deals").textContent = t.linkDeals[lang];
        document.getElementById("footer-link-contact").textContent = t.linkContact[lang];
        document.getElementById("footer-link-privacy").textContent = t.linkPrivacy[lang];
        document.getElementById("footer-contact-title").textContent = t.contactTitle[lang];
        document.getElementById("footer-contact-email").textContent = t.contactEmail[lang];
        document.getElementById("footer-contact-time").textContent = t.contactTime[lang];
        document.getElementById("footer-contact-based").textContent = t.contactBased[lang];
        document.getElementById("footer-legal").textContent = t.legal[lang];

        console.log("✅ Footer updated to:", lang);
    } catch (error) {
        console.error("Error loading translations:", error);
    }
}


