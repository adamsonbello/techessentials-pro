// ===============================
// main.js - Gestion globale
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

    // ---- Hero ----
    if (siteTranslations.hero) {
      if (document.getElementById("hero-title"))
        document.getElementById("hero-title").textContent = siteTranslations.hero.title[lang];
      if (document.getElementById("hero-subtitle"))
        document.getElementById("hero-subtitle").textContent = siteTranslations.hero.subtitle[lang];
      if (document.getElementById("hero-cta"))
        document.getElementById("hero-cta").textContent = siteTranslations.hero.cta[lang];
    }

    // ---- Sections ----
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

    // ---- Footer ----
    if (siteTranslations.footer) {
      document.getElementById("footer-about-title").textContent = siteTranslations.footer.aboutTitle[lang];
      document.getElementById("footer-about-text").textContent = siteTranslations.footer.aboutText[lang];
      document.getElementById("footer-quicklinks").textContent = siteTranslations.footer.quickLinks[lang];
      document.getElementById("footer-link-reviews").textContent = siteTranslations.footer.linkReviews[lang];
      document.getElementById("footer-link-deals").textContent = siteTranslations.footer.linkDeals[lang];
      document.getElementById("footer-link-contact").textContent = siteTranslations.footer.linkContact[lang];
      document.getElementById("footer-link-privacy").textContent = siteTranslations.footer.linkPrivacy[lang];
      document.getElementById("footer-contact-title").textContent = siteTranslations.footer.contactTitle[lang];
      document.getElementById("footer-contact-email").textContent = siteTranslations.footer.contactEmail[lang];
      document.getElementById("footer-contact-time").textContent = siteTranslations.footer.contactTime[lang];
      document.getElementById("footer-contact-based").textContent = siteTranslations.footer.contactBased[lang];
      document.getElementById("footer-legal").textContent = siteTranslations.footer.legal[lang];
    }

    console.log("✅ Translations applied for:", lang);
  } catch (error) {
    console.error("Error loading translations:", error);
  }
}

// ===============================
// Changement de langue
// ===============================
function switchLanguage(lang) {
  currentLanguage = lang;
  localStorage.setItem("lang", lang);

  // Recharge produits si dispo
  if (typeof loadProducts === "function") {
    loadProducts();
  }

  // Recharge textes fixes
  loadTranslations(lang);

  // Update boutons
  document.querySelectorAll('.lang-btn').forEach(btn => {
    btn.classList.remove('active');
  });
  const activeBtn = document.querySelector(`.lang-btn[onclick="switchLanguage('${lang}')"]`);
  if (activeBtn) activeBtn.classList.add('active');

  // Update page title
  if (lang === 'fr') {
    document.title = 'TechEssentials Pro - Meilleur Tech pour Télétravail | Best Tech for Remote Workers';
  } else {
    document.title = 'TechEssentials Pro - Best Tech for Remote Workers | Meilleur Tech pour Télétravail';
  }

  console.log(`🌍 Language switched to: ${lang}`);
  initScrollAnimations();
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
    const targetElement = document.querySelector(targetId);
    if (targetElement) {
      targetElement.scrollIntoView({ behavior: 'smooth' });
    }
  });
});

// ===============================
// Initialize on page load
// ===============================
document.addEventListener('DOMContentLoaded', function() {
  console.log('🌟 TechEssentials Pro - Bilingual Affiliate Site Loaded');
  console.log('🎯 Revenue Projection: €1500-3200/month (bilingual expansion)');

  initScrollAnimations();

  // Charge les traductions pour la langue sauvegardée
  loadTranslations(currentLanguage);
});

// ===============================
// Produits vedettes - Page d'accueil
// ===============================
async function loadFeaturedProducts() {
  try {
    const res = await fetch("data/products.json");
    if (!res.ok) throw new Error("❌ Cannot load products.json");
    const products = await res.json();

    // Sélection fixe de 6 produits vedettes (slugs dans products.json)
    const featuredSlugs = [
      "anker-powercore",
      "sony-headphones",
      "logitech-mouse",
      "asus-monitor",
      "anker-powerstrip",
      "benq-screenbar"
    ];

    const grid = document.getElementById("featured-products-grid");
    if (!grid) return;

    grid.innerHTML = featuredSlugs.map(slug => {
      const p = products[slug];
      if (!p) return "";
      return `
        <div class="product-card">
          <div class="product-image">
            <img src="assets/images/products/${p.image}" alt="${p.name[currentLanguage]}">
            <span class="product-badge">${p.badge}</span>
          </div>
          <h3 class="product-title">${p.name[currentLanguage]}</h3>
          <ul class="product-features">
            ${p.features[currentLanguage].slice(0,3).map(f => `<li>${f}</li>`).join("")}
          </ul>
          <p class="product-description">
            ${p.description[currentLanguage].substring(0, 100)}...
          </p>
          <div class="product-price">€${p.price.amazon}</div>
          <a href="product.html?id=${slug}" class="affiliate-button">
            ${siteTranslations.buttons.viewDetails[currentLanguage]}
          </a>
        </div>
      `;
    }).join("");

    console.log("✨ Featured products loaded:", featuredSlugs);
  } catch (err) {
    console.error("⚠️ Error loading featured products:", err);
  }
}

// ===============================
// Initialisation - page d'accueil
// ===============================
document.addEventListener("DOMContentLoaded", function() {
  console.log("🌟 TechEssentials Pro - Home Page Loaded");

  // Charger les traductions et les produits vedettes
  loadTranslations(currentLanguage);
  loadFeaturedProducts();
});
