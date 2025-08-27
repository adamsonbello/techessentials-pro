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
});

