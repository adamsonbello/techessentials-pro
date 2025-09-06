// assets/js/review-detail.js
let currentLang = localStorage.getItem("lang") || "en";
const params = new URLSearchParams(window.location.search);
const reviewId = params.get("id"); // ex: ?id=anker-737-review

// ---------------------------
// Charger la review + ratings
// ---------------------------
async function loadReview(lang) {
  if (!reviewId) {
    document.querySelector("main").innerHTML = `<p>❌ No review ID provided.</p>`;
    return;
  }

  try {
    const res = await fetch(`api.php?action=getReview&id=${reviewId}`);
    const data = await res.json();

    if (data.error) {
      document.querySelector("main").innerHTML = `<p>❌ ${data.error}</p>`;
      return;
    }

    const r = data.review;

    // 🎯 Hero + contenu
    document.title = `${r[`title_${lang}`]} | TechEssentials Pro`;
    document.getElementById("review-image").src = `assets/images/products/${r.image}`;
    document.getElementById("review-image").alt = r[`title_${lang}`];
    document.getElementById("review-content").innerHTML = r[`content_${lang}`];

    // Pros & Cons
    document.getElementById("review-pros").innerHTML =
      (r[`pros_${lang}`] || "").split("||").map(p => `<li>${p}</li>`).join("");
    document.getElementById("review-cons").innerHTML =
      (r[`cons_${lang}`] || "").split("||").map(c => `<li>${c}</li>`).join("");

    // Verdict
    let verdictDiv = document.querySelector(".review-verdict");
    if (!verdictDiv) {
      verdictDiv = document.createElement("div");
      verdictDiv.classList.add("review-verdict");
      document.querySelector("main").appendChild(verdictDiv);
    }
    verdictDiv.innerHTML = `
      <h3>${lang === "fr" ? "Verdict" : "Verdict"}</h3>
      <p>${r[`verdict_${lang}`]}</p>
    `;

    // Lien produit
    const cta = document.getElementById("review-product-link");
    if (cta) {
      cta.href = `product.html?id=${r.slug}`;
      cta.textContent = lang === "fr" ? "Voir le produit →" : "See Product →";
    }

    // Notes utilisateurs
    renderRatings(data.ratings, data.average, lang);

  } catch (err) {
    console.error("⚠️ Error loading review:", err);
  }
}

// ---------------------------
// Affichage des avis utilisateurs
// ---------------------------
let reviewsPerPage = 3;
let currentPage = 1;

function renderRatings(ratings, average, lang) {
  const container = document.getElementById("user-reviews");
  const title = document.getElementById("user-reviews-title");

  title.textContent = lang === "fr" ? "Avis des utilisateurs" : "User Reviews";

  if (!ratings || ratings.length === 0) {
    container.innerHTML = `<p>${lang === "fr" ? "Aucun avis pour le moment." : "No reviews yet."}</p>`;
    return;
  }

  // Pagination
  const start = 0;
  const end = currentPage * reviewsPerPage;
  const visible = ratings.slice(start, end);

  container.innerHTML = `
    <p><strong>${lang === "fr" ? "Note moyenne" : "Average rating"}:</strong> 
       ${average ?? "N/A"} ⭐ (${ratings.length} ${lang === "fr" ? "avis" : "reviews"})</p>
    <ul class="reviews-list">
      ${visible.map(r => `
        <li class="review-item">
          <p><strong>${r.name}</strong> - ${r.rating}⭐</p>
          <p>${r.comment}</p>
          <small>${new Date(r.created_at).toLocaleDateString()}</small>
        </li>
      `).join("")}
    </ul>
  `;

  // Bouton "Voir plus / moins"
  const loadMoreBtn = document.getElementById("load-more-reviews");
  if (ratings.length > end) {
    loadMoreBtn.style.display = "inline-block";
    loadMoreBtn.textContent = lang === "fr" ? "Voir plus d’avis" : "Load more reviews";
    loadMoreBtn.onclick = () => {
      currentPage++;
      renderRatings(ratings, average, lang);
    };
  } else if (ratings.length > reviewsPerPage) {
    loadMoreBtn.style.display = "inline-block";
    loadMoreBtn.textContent = lang === "fr" ? "Voir moins" : "See less";
    loadMoreBtn.onclick = () => {
      currentPage = 1;
      renderRatings(ratings, average, lang);
    };
  } else {
    loadMoreBtn.style.display = "none";
  }
}

// ---------------------------
// Ajouter un avis utilisateur
// ---------------------------
async function submitReview(e) {
  e.preventDefault();

  const name = document.getElementById("reviewer-name").value.trim();
  const rating = document.getElementById("reviewer-rating").value;
  const comment = document.getElementById("reviewer-comment").value.trim();

  if (!name || !rating || !comment) {
    alert(currentLang === "fr" ? "Veuillez remplir tous les champs." : "Please fill in all fields.");
    return;
  }

  try {
    const res = await fetch("api.php?action=addRating", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ review_id: reviewId, name, rating, comment })
    });

    const data = await res.json();
    if (data.success) {
      alert(currentLang === "fr" ? "Avis ajouté avec succès !" : "Review added successfully!");
      document.getElementById("user-review-form").reset();
      loadReview(currentLang); // recharge les avis
    } else {
      alert("⚠️ " + (data.error || "Unknown error"));
    }
  } catch (err) {
    console.error("Error submitting review:", err);
  }
}

// ---------------------------
// Gestion du multilingue
// ---------------------------
function updateReviewFormLanguage() {
  if (currentLang === "fr") {
    document.getElementById("user-reviews-title").textContent = "Avis Utilisateurs";
    document.getElementById("review-form-title").textContent = "Laisser un avis";
    document.getElementById("reviewer-name").placeholder = "Votre nom";
    document.getElementById("reviewer-rating").options[0].textContent = "Sélectionnez une note";
    document.getElementById("reviewer-comment").placeholder = "Votre commentaire";
    document.getElementById("review-submit").textContent = "Envoyer";
  } else {
    document.getElementById("user-reviews-title").textContent = "User Reviews";
    document.getElementById("review-form-title").textContent = "Leave a Review";
    document.getElementById("reviewer-name").placeholder = "Your Name";
    document.getElementById("reviewer-rating").options[0].textContent = "Select Rating";
    document.getElementById("reviewer-comment").placeholder = "Your Comment";
    document.getElementById("review-submit").textContent = "Submit";
  }
}

// ---------------------------
// Init
// ---------------------------
document.addEventListener("DOMContentLoaded", () => {
  if (reviewId) {
    loadReview(currentLang);
  }

  const form = document.getElementById("user-review-form");
  if (form) form.addEventListener("submit", submitReview);

  updateReviewFormLanguage();
});


