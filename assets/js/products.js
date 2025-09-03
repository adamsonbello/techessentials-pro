//let currentLang = localStorage.getItem("lang") || "en";

//function switchLanguage(lang) {
  // toggle bouton actif
  //document.querySelectorAll(".lang-btn").forEach(btn => btn.classList.remove("active"));
  //const activeBtn = document.querySelector(`.lang-btn[onclick="switchLanguage('${lang}')"]`);
  //if (activeBtn) activeBtn.classList.add("active");

  //currentLang = lang;
  //localStorage.setItem("lang", lang);
  //loadProducts();
//}

//async function loadProducts() {
  //try {
    //const res = await fetch("data/products.json");
    //if (!res.ok) throw new Error("❌ Cannot load products.json");
    //const products = await res.json();

    // ✅ Debug logs
    //console.log("Lang active:", currentLang);
    //console.log("Produits chargés:", Object.keys(products));

    // ----- Catalogue -----
    //const listContainer = document.getElementById("products-list");
   // if (listContainer) {
     // listContainer.innerHTML = Object.keys(products).map(slug => {
       // const p = products[slug];
       // return `
         // <div class="product-card">
           // <div class="product-image">
           //   <img src="assets/images/products/${p.image}" alt="${p.name[currentLang]}">
             // <span class="product-badge">${p.badge}</span>
           // </div>
           // <h3 class="product-title">${p.name[currentLang]}</h3>
           // <p class="product-description">${p.description[currentLang].substring(0, 80)}...</p>
           // <div class="product-price">€${p.price.amazon}</div>
          //  <a href="product.html?id=${slug}" class="affiliate-button">
            //  ${currentLang === "fr" ? "Voir le détail →" : "View Details →"}
          //  </a>
          //</div>
      //  `;
    //  }).join("");
    //}

    // ----- Fiche produit -----
   // const params = new URLSearchParams(window.location.search);
   // const id = params.get("id");
    //if (id && products[id]) {
     // const p = products[id];
     // document.title = `${p.name[currentLang]} - TechEssentials Pro`;
     // document.getElementById("product-name").textContent = p.name[currentLang];
     // document.getElementById("product-image").src = `assets/images/products/${p.image}`;
     // document.getElementById("product-badge").textContent = p.badge;
     // document.getElementById("product-description").textContent = p.description[currentLang];
     // document.getElementById("product-features").innerHTML =
      //  p.features[currentLang].map(f => `<li>${f}</li>`).join("");

     // document.getElementById("price-table").innerHTML = `
      //  <tr><td><img src="assets/images/vendors/amazon.png" width="80"></td>
          //  <td>€${p.price.amazon}</td>
           // <td><a href="${p.links.amazon}" class="affiliate-btn" target="_blank">${currentLang === "fr" ? "Acheter" : "Buy"}</a></td></tr>
       // <tr><td><img src="assets/images/vendors/fnac.png" width="80"></td>
          //  <td>€${p.price.fnac}</td>
          //  <td><a href="${p.links.fnac}" class="affiliate-btn" target="_blank">${currentLang === "fr" ? "Acheter" : "Buy"}</a></td></tr>
        //<tr><td><img src="assets/images/vendors/bestbuy.png" width="80"></td>
           // <td>€${p.price.bestbuy}</td>
           // <td><a href="${p.links.bestbuy}" class="affiliate-btn" target="_blank">${currentLang === "fr" ? "Acheter" : "Buy"}</a></td></tr>
     // `;

      //document.getElementById("sticky-cta-link").href = p.links.amazon;
      //document.getElementById("sticky-cta-link").textContent =
      //  currentLang === "fr" ? "🔥 Obtenir le meilleur deal" : "🔥 Get Best Deal Now";
   // }
  //}// catch (err) {
  //  console.error("⚠️ Error loading products:", err);
//  }
//}

//document.addEventListener("DOMContentLoaded", loadProducts);


// Hero + breadcrumbs
////document.getElementById("hero-product-name").textContent = p.name[currentLang];
//document.getElementById("breadcrumb-product").textContent = p.name[currentLang];

// Sticky CTA mobile
//document.getElementById("sticky-cta-link-mobile").href = p.links.amazon;
//document.getElementById("sticky-cta-link-mobile").textContent =
  //currentLang === "fr" ? "🔥 Obtenir le meilleur deal" : "🔥 Get Best Deal Now";

//const breadcrumbHome = currentLang === "fr" ? "Accueil" : "Home";
//const breadcrumbProducts = currentLang === "fr" ? "Produits" : "Products";

//document.querySelector(".breadcrumbs").innerHTML = `
/// <a href="index.html">${breadcrumbHome}</a> › 
//  <a href="products.html">${breadcrumbProducts}</a> › 
  //<span id="breadcrumb-product">${p.name[currentLang]}</span>
//`;



let currentLang = localStorage.getItem("lang") || "en";

function switchLanguage(lang) {
  document.querySelectorAll(".lang-btn").forEach(btn => btn.classList.remove("active"));
  const activeBtn = document.querySelector(`.lang-btn[onclick="switchLanguage('${lang}')"]`);
  if (activeBtn) activeBtn.classList.add("active");

  currentLang = lang;
  localStorage.setItem("lang", lang);
  loadProducts();
}

async function loadProducts() {
  try {
    const res = await fetch("data/products.json");
    if (!res.ok) throw new Error("❌ Cannot load products.json");
    const products = await res.json();

    console.log("Lang active:", currentLang);
    console.log("Produits chargés:", Object.keys(products));

    // ----- Catalogue (products.html) -----
    const listContainer = document.getElementById("products-list");
    if (listContainer) {
      listContainer.innerHTML = Object.keys(products).map(slug => {
        const p = products[slug];
        return `
          <div class="product-card">
            <div class="product-image">
              <img src="assets/images/products/${p.image}" alt="${p.name[currentLang]}">
              <span class="product-badge">${p.badge}</span>
            </div>
            <h3 class="product-title">${p.name[currentLang]}</h3>
            <p class="product-description">${p.description[currentLang].substring(0, 80)}...</p>
            <div class="product-price">€${p.price.amazon}</div>
            <a href="product.html?id=${slug}" class="affiliate-button">
              ${currentLang === "fr" ? "Voir le détail →" : "View Details →"}
            </a>
          </div>
        `;
      }).join("");
    }

    // ----- Deals (deals.html) -----
    const listDeals = document.getElementById("deals-list");
    if (listDeals) {
      listDeals.innerHTML = Object.keys(products)
        .filter(slug => products[slug].deal)
        .map(slug => {
          const p = products[slug];
          return `
            <div class="product-card">
              <div class="product-image">
                <img src="assets/images/products/${p.image}" alt="${p.name[currentLang]}">
                <span class="product-badge">${p.badge}</span>
              </div>
              <h3 class="product-title">${p.name[currentLang]}</h3>
              <p class="product-description">${p.description[currentLang].substring(0, 80)}...</p>
              <div class="product-price">
                ${p.oldPrice ? `<span class="price-old">€${p.oldPrice}</span>` : ""}
                €${p.price.amazon}
              </div>
              <a href="product.html?id=${slug}" class="affiliate-button">
                ${currentLang === "fr" ? "Voir le détail →" : "View Details →"}
              </a>
            </div>
          `;
        }).join("");
    }

    // ----- Home Page (6 vedettes) -----
    const homeFeaturedList = document.getElementById("home-featured-list");
    if (homeFeaturedList) {
      const featured = Object.keys(products).slice(0, 6);
      homeFeaturedList.innerHTML = featured.map(slug => {
        const p = products[slug];
        return `
          <div class="product-card">
            <div class="product-image">
              <img src="assets/images/products/${p.image}" alt="${p.name[currentLang]}">
              <span class="product-badge">${p.badge}</span>
            </div>
            <h3 class="product-title">${p.name[currentLang]}</h3>
            <p class="product-description">${p.description[currentLang].substring(0, 80)}...</p>
            <div class="product-price">€${p.price.amazon}</div>
            <a href="product.html?id=${slug}" class="affiliate-button">
              ${currentLang === "fr" ? "Voir le détail →" : "View Details →"}
            </a>
          </div>
        `;
      }).join("");
    }

    // ----- Détail produit (product.html) -----
    const params = new URLSearchParams(window.location.search);
    const id = params.get("id");
    if (id && products[id]) {
      const p = products[id];

      document.title = `${p.name[currentLang]} - TechEssentials Pro`;
      document.getElementById("hero-product-name").textContent = p.name[currentLang];

      const breadcrumbHome = currentLang === "fr" ? "Accueil" : "Home";
      const breadcrumbProducts = currentLang === "fr" ? "Produits" : "Products";
      document.querySelector(".breadcrumbs").innerHTML = `
        <a href="index.html">${breadcrumbHome}</a> › 
        <a href="products.html">${breadcrumbProducts}</a> › 
        <span>${p.name[currentLang]}</span>
      `;

      document.getElementById("product-name").textContent = p.name[currentLang];
      document.getElementById("product-image").src = `assets/images/products/${p.image}`;
      document.getElementById("product-image").alt = p.name[currentLang];
      document.getElementById("product-badge").textContent = p.badge;
      document.getElementById("product-description").textContent = p.description[currentLang];
      document.getElementById("product-features").innerHTML =
        p.features[currentLang].map(f => `<li>${f}</li>`).join("");

      // Comparateur prix bilingue
      const priceComparisonTitle = document.getElementById("price-comparison-title");
      if (priceComparisonTitle) {
        priceComparisonTitle.textContent =
          currentLang === "fr" ? "Comparateur de prix" : "Price Comparison";
      }

      document.getElementById("price-table").innerHTML = `
        <tr>
          <td><img src="assets/images/vendors/amazon.png" width="80"></td>
          <td>€${p.price.amazon}</td>
          <td><a href="${p.links.amazon}" class="affiliate-btn" target="_blank">
            ${currentLang === "fr" ? "Acheter" : "Buy"}
          </a></td>
        </tr>
        <tr>
          <td><img src="assets/images/vendors/fnac.png" width="80"></td>
          <td>€${p.price.fnac}</td>
          <td><a href="${p.links.fnac}" class="affiliate-btn" target="_blank">
            ${currentLang === "fr" ? "Acheter" : "Buy"}
          </a></td>
        </tr>
        <tr>
          <td><img src="assets/images/vendors/bestbuy.png" width="80"></td>
          <td>€${p.price.bestbuy}</td>
          <td><a href="${p.links.bestbuy}" class="affiliate-btn" target="_blank">
            ${currentLang === "fr" ? "Acheter" : "Buy"}
          </a></td>
        </tr>
      `;

      // ✅ CTA bilingues
      document.getElementById("sticky-cta-link").href = p.links.amazon;
      document.getElementById("sticky-cta-link").textContent =
        currentLang === "fr" ? "🔥 Voir le meilleur prix" : "🔥 Get Best Deal";

      document.getElementById("sticky-cta-link-mobile").href = p.links.amazon;
      document.getElementById("sticky-cta-link-mobile").textContent =
        currentLang === "fr" ? "🔥 Obtenir l’offre maintenant" : "🔥 Get Deal Now";
    }

    // ✅ Initialiser QuickView après le rendu des produits
    initQuickView();

  } catch (err) {
    console.error("⚠️ Error loading products:", err);
  }
}

document.addEventListener("DOMContentLoaded", loadProducts);

// ===============================
// Quick View Popup (image only)
// ===============================
// Popup QuickView
function initQuickView() {
  const overlay = document.getElementById("quickview-overlay");
  const closeBtn = document.getElementById("quickview-close");
  const quickImg = document.getElementById("quickview-img");

  document.querySelectorAll(".product-card img").forEach(img => {
    img.addEventListener("click", () => {
      quickImg.src = img.src;
      quickImg.alt = img.alt;
      overlay.style.display = "flex"; // ✅ IMPORTANT
    });
  });

  closeBtn.addEventListener("click", () => overlay.style.display = "none");
  overlay.addEventListener("click", e => {
    if (e.target === overlay) overlay.style.display = "none";
  });
}

document.addEventListener("DOMContentLoaded", initQuickView);

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
