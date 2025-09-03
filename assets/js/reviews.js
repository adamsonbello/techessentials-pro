let currentLang = localStorage.getItem("lang") || "en";

function loadReviews(lang) {
  const reviewsGrid = document.getElementById("reviews-grid");

  fetch("data/reviews.json")
    .then(res => res.json())
    .then(data => {
      reviewsGrid.innerHTML = Object.keys(data).map(key => {
        const review = data[key];
        return `
          <div class="review-card">
            <img src="assets/images/products/${review.image}" 
                 alt="${review.title[lang]}" 
                 style="width:100%; height:120px; object-fit:contain; background:#fff; border-radius:6px; margin-bottom:10px;">
            
            <h3>${review.title[lang]}</h3>
            <p>${review.excerpt[lang]}</p>
            
            <a href="review-detail.html?id=${key}" class="cta-button">
              ${lang === "fr" ? "Lire la suite →" : "Read More →"}
            </a>
          </div>
        `;
      }).join("");
    })
    .catch(err => {
      console.error("⚠️ Error loading reviews:", err);
      reviewsGrid.innerHTML = `<p>⚠️ Unable to load reviews at the moment.</p>`;
    });
}

document.addEventListener("DOMContentLoaded", () => {
  loadReviews(currentLang);
});

// 🔄 Mise à jour quand la langue change
function switchLanguage(lang) {
  localStorage.setItem("lang", lang);
  currentLang = lang;
  loadReviews(currentLang);
}

function switchLanguage(lang) {
  // Sauvegarde et mise à jour
  localStorage.setItem("lang", lang);
  currentLang = lang;

  // Mise à jour visuelle des boutons
  document.querySelectorAll(".lang-btn").forEach(btn => {
    btn.classList.remove("active");
  });
  const activeBtn = document.querySelector(`.lang-btn[onclick="switchLanguage('${lang}')"]`);
  if (activeBtn) activeBtn.classList.add("active");

  // Recharge le contenu
  loadReview(currentLang);
}
