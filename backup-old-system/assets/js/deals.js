let currentLang = localStorage.getItem("lang") || "en";

async function loadDeals() {
  try {
    const res = await fetch("data/products.json");
    if (!res.ok) throw new Error("❌ Cannot load products.json");
    const products = await res.json();

    const listContainer = document.getElementById("deals-list");
    if (listContainer) {
      listContainer.innerHTML = "";

      const deals = Object.keys(products).filter(slug => products[slug].deal === true);

      if (deals.length === 0) {
        listContainer.innerHTML = `<p>${currentLang === "fr" ? "Aucun deal disponible pour l’instant." : "No deals available at the moment."}</p>`;
        return;
      }

      deals.forEach(slug => {
        const p = products[slug];
        const oldPrice = p.oldPrice ? `<span class="price-old">€${p.oldPrice}</span>` : "";

        listContainer.innerHTML += `
          <div class="product-card">
            <div class="product-image">
              <img src="assets/images/products/${p.image}" alt="${p.name[currentLang]}">
              <span class="product-badge">${p.badge || "Deal"}</span>
            </div>
            <h3 class="product-title">${p.name[currentLang]}</h3>
            <p class="product-description">${p.description[currentLang].substring(0, 100)}...</p>
            <div class="product-price">
              ${oldPrice} €${p.price.amazon}
            </div>
            <a href="${p.links.amazon}" target="_blank" class="affiliate-button">
              ${currentLang === "fr" ? "Voir le Deal" : "View Deal"}
            </a>
          </div>
        `;
      });
    }

    // Hero text
    const resT = await fetch("data/translations.json");
    const translations = await resT.json();
    const t = translations.dealsPage;

    if (document.getElementById("deals-title")) {
      document.getElementById("deals-title").textContent = t.title[currentLang];
    }
    if (document.getElementById("deals-subtitle")) {
      document.getElementById("deals-subtitle").textContent = t.subtitle[currentLang];
    }

  } catch (err) {
    console.error("⚠️ Error loading deals:", err);
  }
}

// Reload when switching language
function switchLanguage(lang) {
  currentLang = lang;
  localStorage.setItem("lang", lang);

  loadDeals();
  loadTranslations(lang);

  document.querySelectorAll('.lang-btn').forEach(btn => btn.classList.remove('active'));
  const activeBtn = document.querySelector(`.lang-btn[onclick="switchLanguage('${lang}')"]`);
  if (activeBtn) activeBtn.classList.add('active');
}

document.addEventListener("DOMContentLoaded", loadDeals);
