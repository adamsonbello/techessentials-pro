// assets/js/config.js
// ⚡ Centralisation du point d'accès API
const API_URL = "http://localhost/TechEssentialsPro/api.php";

// ✅ Test rapide en console pour vérifier que API_URL est bien défini
if (typeof API_URL === "undefined" || !API_URL) {
  console.error("❌ API_URL is not defined in config.js !");
} else {
  console.log("✅ API_URL loaded:", API_URL);
}
