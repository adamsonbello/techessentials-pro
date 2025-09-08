// ===============================
// main.js - Gestion globale avec Newsletter
// ===============================
let currentLanguage = localStorage.getItem("lang") || "en";
let siteTranslations = {}; // on charge depuis JSON

// ===============================
// Fonction pour charger toutes les traductions
// ===============================
async function loadTranslations(lang) {
  try {
    const res = await fetch("data/translations.json");
    siteTranslations = await res.json();

    // ---- Navigation ----
    if (siteTranslations.nav) {
      if (document.getElementById("nav-home"))
        document.getElementById("nav-home").textContent = siteTranslations.nav.home[lang];
      if (document.getElementById("nav-products"))
        document.getElementById("nav-products").textContent = siteTranslations.nav.products[lang];
      if (document.getElementById("nav-reviews"))
        document.getElementById("nav-reviews").textContent = siteTranslations.nav.reviews[lang];
      if (document.getElementById("nav-deals"))
        document.getElementById("nav-deals").textContent = siteTranslations.nav.deals[lang];
      if (document.getElementById("nav-contact"))
        document.getElementById("nav-contact").textContent = siteTranslations.nav.contact[lang];
    }

    // ---- Footer ----
    if (siteTranslations.footer) {
      if (document.getElementById("footer-about-title"))
        document.getElementById("footer-about-title").textContent = siteTranslations.footer.aboutTitle[lang];
      if (document.getElementById("footer-about-text"))
        document.getElementById("footer-about-text").textContent = siteTranslations.footer.aboutText[lang];
      if (document.getElementById("footer-quicklinks"))
        document.getElementById("footer-quicklinks").textContent = siteTranslations.footer.quickLinks[lang];
      if (document.getElementById("footer-link-reviews"))
        document.getElementById("footer-link-reviews").textContent = siteTranslations.footer.linkReviews[lang];
      if (document.getElementById("footer-link-deals"))
        document.getElementById("footer-link-deals").textContent = siteTranslations.footer.linkDeals[lang];
      if (document.getElementById("footer-link-contact"))
        document.getElementById("footer-link-contact").textContent = siteTranslations.footer.linkContact[lang];
      if (document.getElementById("footer-link-privacy"))
        document.getElementById("footer-link-privacy").textContent = siteTranslations.footer.linkPrivacy[lang];
      if (document.getElementById("footer-contact-title"))
        document.getElementById("footer-contact-title").textContent = siteTranslations.footer.contactTitle[lang];
      if (document.getElementById("footer-contact-email"))
        document.getElementById("footer-contact-email").textContent = siteTranslations.footer.contactEmail[lang];
      if (document.getElementById("footer-contact-time"))
        document.getElementById("footer-contact-time").textContent = siteTranslations.footer.contactTime[lang];
      if (document.getElementById("footer-contact-based"))
        document.getElementById("footer-contact-based").textContent = siteTranslations.footer.contactBased[lang];
      if (document.getElementById("footer-legal"))
        document.getElementById("footer-legal").textContent = siteTranslations.footer.legal[lang];
    }

    // ---- Hero ----
    if (siteTranslations.hero) {
      if (document.getElementById("hero-title"))
        document.getElementById("hero-title").textContent = siteTranslations.hero.title[lang];
      if (document.getElementById("hero-subtitle"))
        document.getElementById("hero-subtitle").textContent = siteTranslations.hero.subtitle[lang];
      if (document.getElementById("hero-cta"))
        document.getElementById("hero-cta").textContent = siteTranslations.hero.cta[lang];
    }

    // ---- Sections (Home page) ----
    if (siteTranslations.sections) {
      if (document.getElementById("products-title"))
        document.getElementById("products-title").textContent = siteTranslations.sections.productsTitle[lang];
      if (document.getElementById("products-subtitle"))
        document.getElementById("products-subtitle").textContent = siteTranslations.sections.productsSubtitle[lang];
      if (document.getElementById("trusted-title"))
        document.getElementById("trusted-title").textContent = siteTranslations.sections.trustedTitle[lang];
      if (document.getElementById("trusted-subtitle"))
        document.getElementById("trusted-subtitle").textContent = siteTranslations.sections.trustedSubtitle[lang];
    }

    // ---- Buttons ----
    if (siteTranslations.buttons) {
      if (document.getElementById("btn-view-details"))
        document.getElementById("btn-view-details").textContent = siteTranslations.buttons.viewDetails[lang];
      if (document.getElementById("btn-best-deal"))
        document.getElementById("btn-best-deal").textContent = siteTranslations.buttons.bestDeal[lang];
    }

    // ---- Deals Page ----
    if (siteTranslations.dealsPage) {
      if (document.getElementById("deals-title"))
        document.getElementById("deals-title").textContent = siteTranslations.dealsPage.title[lang];
      if (document.getElementById("deals-subtitle"))
        document.getElementById("deals-subtitle").textContent = siteTranslations.dealsPage.subtitle[lang];
    }

    // ---- Newsletter ----
    loadNewsletterTranslations(lang);

    console.log("✅ Translations applied for:", lang);
  } catch (error) {
    console.error("Error loading translations:", error);
  }
}

// ===============================
// Fonction pour charger les traductions newsletter
// ===============================
async function loadNewsletterTranslations(lang) {
  try {
    // Mise à jour des éléments newsletter
    const newsletterTitle = document.querySelector(".newsletter h2");
    const newsletterSubtitle = document.querySelector(".newsletter p");
    const newsletterInput = document.querySelector(".newsletter-input");
    const newsletterButton = document.querySelector(".newsletter-button");

    if (newsletterTitle && siteTranslations.newsletter?.title) {
      newsletterTitle.textContent = siteTranslations.newsletter.title[lang];
    }
    
    if (newsletterSubtitle && siteTranslations.newsletter?.subtitle) {
      newsletterSubtitle.textContent = siteTranslations.newsletter.subtitle[lang];
    }
    
    if (newsletterInput && siteTranslations.newsletter?.placeholder) {
      newsletterInput.placeholder = siteTranslations.newsletter.placeholder[lang];
    }
    
    if (newsletterButton && siteTranslations.newsletter?.button) {
      newsletterButton.textContent = siteTranslations.newsletter.button[lang];
    }

  } catch (error) {
    console.error("❌ Error loading newsletter translations:", error);
  }
}

// ===============================
// Fonction principale d'inscription newsletter
// ===============================
async function subscribeNewsletter(event, language = 'en') {
  event.preventDefault();
  
  const form = event.target;
  const emailInput = form.querySelector('.newsletter-input');
  const submitButton = form.querySelector('.newsletter-button');
  const email = emailInput.value.trim();

  // Validation email côté client
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    showNewsletterMessage('error', getNewsletterMessage('errorInvalidEmail', language));
    return;
  }

  // État de chargement
  const originalButtonText = submitButton.textContent;
  submitButton.textContent = getNewsletterMessage('submitting', language);
  submitButton.disabled = true;
  emailInput.disabled = true;

  try {
    const response = await fetch(`${API_URL}?action=subscribeNewsletter`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        email: email,
        language: language
      })
    });

    const data = await response.json();

    if (data.success) {
      showNewsletterMessage('success', data.message);
      emailInput.value = ''; // Clear le champ email
      
      // Optionnel: tracking analytics
      if (typeof gtag !== 'undefined') {
        gtag('event', 'newsletter_subscribe', {
          'event_category': 'engagement',
          'event_label': language
        });
      }
    } else {
      showNewsletterMessage('error', data.message || getNewsletterMessage('errorGeneric', language));
    }

  } catch (error) {
    console.error('❌ Newsletter subscription error:', error);
    showNewsletterMessage('error', getNewsletterMessage('errorGeneric', language));
  } finally {
    // Restaurer l'état du formulaire
    submitButton.textContent = originalButtonText;
    submitButton.disabled = false;
    emailInput.disabled = false;
  }
}

// ===============================
// Fonction pour afficher les messages (succès/erreur)
// ===============================
function showNewsletterMessage(type, message) {
  // Supprimer les anciens messages
  const existingMessage = document.querySelector('.newsletter-message');
  if (existingMessage) {
    existingMessage.remove();
  }

  // Créer le nouveau message
  const messageDiv = document.createElement('div');
  messageDiv.className = `newsletter-message newsletter-message--${type}`;
  messageDiv.textContent = message;

  // Styles inline pour le message
  messageDiv.style.cssText = `
    margin-top: 10px;
    padding: 12px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    text-align: center;
    animation: slideInUp 0.3s ease-out;
    ${type === 'success' 
      ? 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' 
      : 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'
    }
  `;

  // Ajouter le message après le formulaire
  const form = document.querySelector('.newsletter-form');
  if (form) {
    form.insertAdjacentElement('afterend', messageDiv);

    // Auto-suppression après 5 secondes
    setTimeout(() => {
      if (messageDiv.parentNode) {
        messageDiv.style.animation = 'slideOutDown 0.3s ease-in forwards';
        setTimeout(() => messageDiv.remove(), 300);
      }
    }, 5000);
  }
}

// ===============================
// Fonction helper pour récupérer les messages traduits
// ===============================
function getNewsletterMessage(key, language) {
  if (siteTranslations.newsletter && siteTranslations.newsletter[key]) {
    return siteTranslations.newsletter[key][language] || siteTranslations.newsletter[key]['en'];
  }
  
  // Messages de fallback
  const fallbackMessages = {
    'submitting': { 'en': 'Subscribing...', 'fr': 'Inscription...' },
    'errorInvalidEmail': { 'en': 'Please enter a valid email address.', 'fr': 'Veuillez entrer une adresse email valide.' },
    'errorGeneric': { 'en': 'An error occurred. Please try again.', 'fr': 'Une erreur s\'est produite. Veuillez réessayer.' }
  };
  
  return fallbackMessages[key] ? fallbackMessages[key][language] || fallbackMessages[key]['en'] : 'Error';
}

// ===============================
// Changement de langue
// ===============================
function switchLanguage(lang) {
  currentLanguage = lang;
  localStorage.setItem("lang", lang);

  if (typeof loadProducts === "function") {
    loadProducts();
  }

  loadTranslations(lang);

  document.querySelectorAll(".lang-btn").forEach(btn => btn.classList.remove("active"));
  const activeBtn = document.querySelector(`.lang-btn[onclick="switchLanguage('${lang}')"]`);
  if (activeBtn) activeBtn.classList.add("active");

  if (lang === "fr") {
    document.title = "TechEssentials Pro - Meilleur Tech pour Télétravail | Best Tech for Remote Workers";
  } else {
    document.title = "TechEssentials Pro - Best Tech for Remote Workers | Meilleur Tech pour Télétravail";
  }

  initScrollAnimations();
}

// ===============================
// Scroll animations
// ===============================
function initScrollAnimations() {
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) entry.target.classList.add("revealed");
    });
  }, { threshold: 0.1 });

  document.querySelectorAll(".scroll-reveal").forEach(element => observer.observe(element));
}

// ===============================
// Smooth scrolling
// ===============================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();
    const targetId = this.getAttribute("href");
    const targetElement = document.querySelector(targetId);
    if (targetElement) {
      targetElement.scrollIntoView({ behavior: "smooth" });
    }
  });
});

// ===============================
// Initialize on page load
// ===============================
document.addEventListener("DOMContentLoaded", function () {
  console.log("🌟 TechEssentials Pro - Loaded");
  loadTranslations(currentLanguage);
  initScrollAnimations();
  
  // Injecter les styles CSS pour la newsletter
  injectNewsletterStyles();
});

// ===============================
// Injecter les styles CSS pour la newsletter
// ===============================
function injectNewsletterStyles() {
  if (!document.getElementById('newsletter-styles')) {
    const newsletterStyles = `
    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes slideOutDown {
      from {
        opacity: 1;
        transform: translateY(0);
      }
      to {
        opacity: 0;
        transform: translateY(20px);
      }
    }

    .newsletter-form {
      position: relative;
    }

    .newsletter-button:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }

    .newsletter-input:disabled {
      opacity: 0.7;
      background-color: #f5f5f5;
    }
    `;

    const styleSheet = document.createElement('style');
    styleSheet.id = 'newsletter-styles';
    styleSheet.textContent = newsletterStyles;
    document.head.appendChild(styleSheet);
  }
}

// ✅ Mise à jour de l'état visuel des boutons EN/FR
function updateLanguageButtons(lang) {
  document.querySelectorAll(".lang-btn").forEach(btn => {
    btn.classList.remove("active");
  });
  const activeBtn = document.querySelector(`.lang-btn[onclick="switchLanguage('${lang}')"]`);
  if (activeBtn) activeBtn.classList.add("active");
}