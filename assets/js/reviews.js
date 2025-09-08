// assets/js/reviews.js

// Variable globale pour la langue
let currentLang = localStorage.getItem("lang") || "en";

// Charger toutes les reviews pour la page reviews.html
async function loadReviews() {
  try {
    console.log("🔍 Loading reviews from API...");
    const res = await fetch(`${API_URL}?action=getAllReviews`);
    
    if (!res.ok) {
      throw new Error(`HTTP error! status: ${res.status}`);
    }
    
    const data = await res.json();
    console.log("✅ Reviews loaded:", data);

    const reviewsGrid = document.getElementById("reviews-grid");
    if (!reviewsGrid) {
      console.warn("⚠️ reviews-grid element not found");
      return;
    }

    if (data.error) {
      reviewsGrid.innerHTML = `<p>❌ ${data.error}</p>`;
      return;
    }

    if (!data || data.length === 0) {
      reviewsGrid.innerHTML = `
        <p>${currentLang === "fr" ? "Aucune review disponible." : "No reviews available."}</p>
      `;
      return;
    }

    // Générer les cartes de reviews
    reviewsGrid.innerHTML = data.map(r => `
      <div class="review-card">
        <img src="assets/images/products/${r.image}" 
             alt="${r[`title_${currentLang}`]}" 
             onerror="this.src='assets/images/products/placeholder.jpg'">
        
        <h3>${r[`title_${currentLang}`]}</h3>
        <p>${r[`excerpt_${currentLang}`]}</p>
        
        <div style="margin-top: 15px;">
          <small style="color: #eaeaea;">
            ${currentLang === "fr" ? "Par" : "By"} ${r.author} - ${new Date(r.date).toLocaleDateString()}
          </small>
        </div>
        
        <a href="review-detail.html?id=${r.slug}" class="cta-button" style="margin-top: 10px; display: inline-block;">
          ${currentLang === "fr" ? "Lire la suite →" : "Read More →"}
        </a>
      </div>
    `).join("");

    console.log(`✅ ${data.length} reviews rendered`);

  } catch (err) {
    console.error("❌ Error loading reviews:", err);
    const reviewsGrid = document.getElementById("reviews-grid");
    if (reviewsGrid) {
      reviewsGrid.innerHTML = `
        <p>❌ ${currentLang === "fr" ? "Erreur de chargement des reviews." : "Error loading reviews."}</p>
      `;
    }
  }
}

// Fonction pour mettre à jour la langue
function updateLanguage(lang) {
  currentLang = lang;
  localStorage.setItem("lang", lang);
  loadReviews(); // Recharger avec la nouvelle langue
}

// Initialisation
document.addEventListener("DOMContentLoaded", () => {
  console.log("🚀 Reviews page initialized");
  console.log("🔗 API_URL:", API_URL);
  console.log("🌍 Current language:", currentLang);
  
  loadReviews();
});

// Export pour utilisation globale
window.loadReviews = loadReviews;
window.updateReviewsLanguage = updateLanguage;