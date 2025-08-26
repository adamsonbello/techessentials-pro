
let currentLang = localStorage.getItem("lang") || "en";

function switchLanguage(lang) {
  // changer bouton actif
  document.querySelectorAll(".lang-btn").forEach(btn => btn.classList.remove("active"));
  const activeBtn = document.querySelector(`.lang-btn[onclick="switchLanguage('${lang}')"]`);
  if (activeBtn) activeBtn.classList.add("active");

  currentLang = lang;
  localStorage.setItem("lang", lang);
  loadProducts(); // recharge les textes dans la langue choisie
}







async function loadDeals() {
  const res = await fetch("data/products.json");
  const products = await res.json();

  const dealsContainer = document.getElementById("deals-list");
  let dealsHTML = "";

  Object.keys(products).forEach(slug => {
    const p = products[slug];

    const amazonPrice = parseFloat(p.price.amazon);
    const fnacPrice = parseFloat(p.price.fnac);
    const bestbuyPrice = parseFloat(p.price.bestbuy);

    // Vérifie si une vraie promo existe (un autre vendeur < Amazon)
    if (fnacPrice < amazonPrice || bestbuyPrice < amazonPrice) {
      // Trouver le prix le plus bas
      const lowestPrice = Math.min(fnacPrice, bestbuyPrice, amazonPrice);

      let bestVendor = "Amazon";
      let bestLink = p.links.amazon;
      if (lowestPrice === fnacPrice) {
        bestVendor = "Fnac";
        bestLink = p.links.fnac;
      }
      if (lowestPrice === bestbuyPrice) {
        bestVendor = "BestBuy";
        bestLink = p.links.bestbuy;
      }

      dealsHTML += `
        <div class="deal-card">
          <div class="product-image">
            <img src="assets/images/products/${p.image}" alt="${p.name}">
            <span class="deal-badge">Promo</span>
          </div>
          <h3 class="product-title">${p.name}</h3>
          <p class="product-description">${p.description.substring(0, 80)}...</p>
          <div class="product-price">Now: €${lowestPrice} (${bestVendor})</div>
          <div class="old-price">Amazon: €${amazonPrice}</div>
          <a href="${bestLink}" target="_blank" class="affiliate-button">Get Deal →</a>
        </div>
      `;
    }
  });

  dealsContainer.innerHTML = dealsHTML || "<p>No current deals available 🚀</p>";
}

document.addEventListener("DOMContentLoaded", loadDeals);
