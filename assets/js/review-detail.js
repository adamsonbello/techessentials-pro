// assets/js/review-detail.js
let currentLang = localStorage.getItem("lang") || "en";

async function loadReview(lang) {
  const params = new URLSearchParams(window.location.search);
  const id = params.get("id"); // ex: ?id=anker-737-review

  try {
    const res = await fetch("data/reviews.json");
    const reviews = await res.json();

    if (id && reviews[id]) {
      const r = reviews[id];

      // Mettre le titre de la page
      document.title = `${r.title[lang]} | TechEssentials Pro`;

      // Injection contenu
      document.getElementById("review-image").src = `assets/images/products/${r.image}`;
      document.getElementById("review-image").alt = r.title[lang];
      document.getElementById("review-content").innerHTML = r.content[lang];

      // Pros & Cons
      document.getElementById("review-pros").innerHTML =
        r.pros[lang].map(p => `<li>${p}</li>`).join("");
      document.getElementById("review-cons").innerHTML =
        r.cons[lang].map(c => `<li>${c}</li>`).join("");

      // Verdict
      let verdictDiv = document.querySelector(".review-verdict");
      if (!verdictDiv) {
        verdictDiv = document.createElement("div");
        verdictDiv.classList.add("review-verdict");
        document.querySelector("main").appendChild(verdictDiv);
      }
      verdictDiv.innerHTML = `
        <h3>${lang === "fr" ? "Verdict" : "Verdict"}</h3>
        <p>${r.verdict[lang]}</p>
      `;
    } else {
      document.querySelector("main").innerHTML = `<p>❌ Review not found.</p>`;
    }
  } catch (err) {
    console.error("⚠️ Error loading review:", err);
  }
}

document.addEventListener("DOMContentLoaded", () => {
  loadReview(currentLang);
});

// 🔄 Mise à jour quand la langue change
function switchLanguage(lang) {
  localStorage.setItem("lang", lang);
  currentLang = lang;
  loadReview(currentLang);
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
