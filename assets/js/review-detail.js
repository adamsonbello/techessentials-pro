// assets/js/review-detail.js - SOLUTION FINALE

// Variables globales
let currentReviewId = null;
let currentLang = localStorage.getItem("lang") || "fr";
let allUserRatings = [];
let reviewsPerPage = 3;
let currentPage = 1;

// Charger la review et ses avis
async function loadReview(lang = currentLang) {
  const params = new URLSearchParams(window.location.search);
  const slug = params.get("id");

  if (!slug) {
    document.querySelector("main").innerHTML = `<p>❌ No review ID provided</p>`;
    return;
  }

  try {
    console.log(`🔍 Loading review: ${slug}`);
    const res = await fetch(`${API_URL}?action=getReview&id=${slug}`);
    const data = await res.json();

    if (data.error) {
      document.querySelector("main").innerHTML = `<p>${data.error}</p>`;
      return;
    }

    const r = data.review;
    currentReviewId = r.id;
    allUserRatings = data.ratings || [];

    console.log("✅ Review loaded:", r);

    // Titre de la page
    document.title = `${r[`title_${lang}`]} | TechEssentials Pro`;

    // Image principale
    const imgEl = document.getElementById("review-image");
    if (imgEl && r.image) {
      imgEl.src = `assets/images/products/${r.image}`;
      imgEl.alt = r[`title_${lang}`];
      imgEl.style.display = "block";
    }

    // Contenu principal
    const contentEl = document.getElementById("review-content");
    if (contentEl && r[`content_${lang}`]) {
      contentEl.innerHTML = r[`content_${lang}`];
    }

    // Pros & Cons
    renderProsAndCons(r, lang);

    // Verdict
    renderVerdict(r, lang);

    // Affichage du rating moyen
    renderAverageRating(data.average, data.count, lang);

    // CTA produit
    const cta = document.getElementById("review-product-link");
    if (cta) {
      cta.href = `product.html?id=${r.slug}`;
      cta.textContent = lang === "fr" ? "Voir le produit →" : "See Product →";
    }

    // Avis utilisateurs
    renderUserReviews(lang);

  } catch (err) {
    console.error("⚠️ Error loading review:", err);
    document.querySelector("main").innerHTML = `<p>❌ Error loading review</p>`;
  }
}

// Afficher les pros et cons
function renderProsAndCons(review, lang) {
  try {
    const prosEl = document.getElementById("review-pros");
    const consEl = document.getElementById("review-cons");
    
    if (prosEl) {
      const pros = JSON.parse(review[`pros_${lang}`] || "[]");
      prosEl.innerHTML = pros.map(p => `<li>${p}</li>`).join("");
    }

    if (consEl) {
      const cons = JSON.parse(review[`cons_${lang}`] || "[]");
      consEl.innerHTML = cons.map(c => `<li>${c}</li>`).join("");
    }
  } catch (err) {
    console.error("Error parsing pros/cons:", err);
  }
}

// Afficher le verdict
function renderVerdict(review, lang) {
  let verdictDiv = document.querySelector(".review-verdict");
  if (verdictDiv && review[`verdict_${lang}`]) {
    verdictDiv.innerHTML = `
      <h3>${lang === "fr" ? "Verdict" : "Verdict"}</h3>
      <p>${review[`verdict_${lang}`]}</p>
    `;
  }
}

// Afficher le rating moyen
function renderAverageRating(average, count, lang) {
  const ratingEl = document.getElementById("review-rating");
  if (ratingEl) {
    if (average && count > 0) {
      const stars = "⭐".repeat(Math.round(average));
      ratingEl.innerHTML = `
        <strong>${average}/5 ${stars}</strong><br>
        <small>${count} ${lang === "fr" ? "avis" : "reviews"}</small>
      `;
    } else {
      ratingEl.innerHTML = `<p>${lang === "fr" ? "Aucun avis pour le moment" : "No reviews yet"}</p>`;
    }
  }
}

// Afficher les avis utilisateurs avec pagination
function renderUserReviews(lang) {
  const container = document.getElementById("user-reviews");
  const title = document.getElementById("user-reviews-title");
  
  if (title) {
    title.textContent = lang === "fr" ? "Avis des utilisateurs" : "User Reviews";
  }

  if (!container) return;

  if (!allUserRatings || allUserRatings.length === 0) {
    container.innerHTML = `<p>${lang === "fr" ? "Aucun avis pour le moment." : "No reviews yet."}</p>`;
    hideButton("load-more-reviews");
    hideButton("load-less-reviews");
    return;
  }

  // Afficher les avis selon la pagination
  const start = 0;
  const end = currentPage * reviewsPerPage;
  const visibleReviews = allUserRatings.slice(start, end);

  container.innerHTML = visibleReviews.map(r => `
    <div class="user-review">
      <strong>${r.name}</strong>
      <div class="stars">${"⭐".repeat(r.rating)}</div>
      <p>${r.comment}</p>
      <small>${new Date(r.created_at).toLocaleDateString()}</small>
    </div>
  `).join("");

  // Gestion des boutons pagination
  managePaginationButtons(lang);
}

function hideButton(buttonId) {
  const btn = document.getElementById(buttonId);
  if (btn) btn.style.display = "none";
}

function managePaginationButtons(lang) {
  const loadMoreBtn = document.getElementById("load-more-reviews");
  const loadLessBtn = document.getElementById("load-less-reviews");
  
  const totalReviews = allUserRatings.length;
  const visibleCount = currentPage * reviewsPerPage;

  if (loadMoreBtn) {
    if (totalReviews > visibleCount) {
      loadMoreBtn.style.display = "block";
      loadMoreBtn.textContent = lang === "fr" ? "Voir plus d'avis" : "Load more reviews";
      loadMoreBtn.onclick = () => {
        currentPage++;
        renderUserReviews(lang);
      };
    } else {
      loadMoreBtn.style.display = "none";
    }
  }

  if (loadLessBtn) {
    if (currentPage > 1) {
      loadLessBtn.style.display = "block";
      loadLessBtn.textContent = lang === "fr" ? "Voir moins" : "Show less";
      loadLessBtn.onclick = () => {
        currentPage = 1;
        renderUserReviews(lang);
      };
    } else {
      loadLessBtn.style.display = "none";
    }
  }
}

// Soumission d'un avis utilisateur - VERSION CORRIGÉE
async function submitUserReview(e) {
  e.preventDefault();
  
  console.log("🚀 Form submission started");

  // SOLUTION: Utiliser les noms des champs au lieu des IDs pour éviter les conflits
  const form = e.target;
  const formData = new FormData(form);
  
  // Récupération via FormData (plus robuste)
  const name = formData.get("review-name") ? formData.get("review-name").trim() : "";
  const rating = formData.get("user-rating") || "";
  const comment = formData.get("review-comment") ? formData.get("review-comment").trim() : "";

  console.log("📝 Form data extracted:", { name, rating, comment });

  // Fallback: essayer aussi par les IDs
  if (!name || !rating || !comment) {
    console.log("🔄 Trying fallback method with IDs...");
    const nameEl = document.querySelector('input[name="review-name"]') || document.getElementById("review-name");
    const ratingEl = document.querySelector('select[name="user-rating"]') || document.getElementById("user-rating");
    const commentEl = document.querySelector('textarea[name="review-comment"]') || document.getElementById("review-comment");

    const fallbackName = nameEl ? nameEl.value.trim() : "";
    const fallbackRating = ratingEl ? ratingEl.value : "";
    const fallbackComment = commentEl ? commentEl.value.trim() : "";

    console.log("📝 Fallback values:", { 
      name: fallbackName, 
      rating: fallbackRating, 
      comment: fallbackComment 
    });

    // Utiliser les valeurs de fallback si nécessaire
    const finalName = name || fallbackName;
    const finalRating = rating || fallbackRating;
    const finalComment = comment || fallbackComment;

    console.log("📝 Final values:", { 
      name: finalName, 
      rating: finalRating, 
      comment: finalComment 
    });

    // Validation finale
    if (!finalName) {
      alert(currentLang === "fr" ? "Veuillez saisir votre nom." : "Please enter your name.");
      return;
    }

    if (!finalRating || finalRating === "" || finalRating === "Select Rating") {
      alert(currentLang === "fr" ? "Veuillez sélectionner une note." : "Please select a rating.");
      return;
    }

    if (!finalComment) {
      alert(currentLang === "fr" ? "Veuillez saisir un commentaire." : "Please enter a comment.");
      return;
    }

    // Validation du rating numérique
    const ratingNum = parseInt(finalRating);
    if (isNaN(ratingNum) || ratingNum < 1 || ratingNum > 5) {
      alert(currentLang === "fr" ? "Veuillez sélectionner une note valide (1-5)." : "Please select a valid rating (1-5).");
      return;
    }

    // Envoyer avec les valeurs finales
    await sendReviewToAPI(finalName, ratingNum, finalComment);
    
  } else {
    // Validation normale
    const ratingNum = parseInt(rating);
    if (isNaN(ratingNum) || ratingNum < 1 || ratingNum > 5) {
      alert(currentLang === "fr" ? "Veuillez sélectionner une note valide (1-5)." : "Please select a valid rating (1-5).");
      return;
    }

    await sendReviewToAPI(name, ratingNum, comment);
  }
}

// Fonction séparée pour envoyer à l'API
async function sendReviewToAPI(name, rating, comment) {
  const params = new URLSearchParams(window.location.search);
  const reviewSlug = params.get("id");

  if (!reviewSlug) {
    alert("❌ Erreur: ID de review manquant");
    return;
  }

  try {
    console.log("📤 Sending review to API...");
    
    const payload = { 
      review_slug: reviewSlug, 
      name: name, 
      rating: rating, 
      comment: comment 
    };
    
    console.log("Payload:", payload);

    const response = await fetch(`${API_URL}?action=addRating`, {
      method: "POST",
      headers: { 
        "Content-Type": "application/json" 
      },
      body: JSON.stringify(payload)
    });

    const result = await response.json();
    console.log("📥 API Response:", result);

    if (result.success) {
      alert(currentLang === "fr" ? "✅ Avis ajouté avec succès !" : "✅ Review added successfully!");
      
      // Réinitialiser le formulaire
      document.getElementById("review-form").reset();
      
      // Recharger les données
      await loadReview(currentLang);
    } else {
      alert("❌ Erreur: " + (result.error || "Erreur inconnue"));
    }
    
  } catch (err) {
    console.error("❌ Network error:", err);
    alert(currentLang === "fr" ? 
      "❌ Erreur de connexion. Veuillez réessayer." : 
      "❌ Connection error. Please try again.");
  }
}

// Initialisation
document.addEventListener("DOMContentLoaded", function() {
  console.log("🚀 Review detail page initialized");
  console.log("🔗 API_URL:", typeof API_URL !== 'undefined' ? API_URL : "❌ Not defined");
  console.log("🌍 Language:", currentLang);
  
  // Charger la review
  loadReview(currentLang);
  
  // Attacher l'événement au formulaire
  const reviewForm = document.getElementById("review-form");
  if (reviewForm) {
    reviewForm.addEventListener("submit", submitUserReview);
    console.log("✅ Form event listener attached");
  } else {
    console.warn("⚠️ Review form not found");
  }
});

// Export pour utilisation globale
window.loadReview = loadReview;


