// ===============================
// newsletter.js - Gestion Newsletter
// ===============================

// Fonction pour charger les traductions newsletter
async function loadNewsletterTranslations(lang) {
  try {
    if (!siteTranslations.newsletter) {
      const res = await fetch("data/translations.json");
      const translations = await res.json();
      siteTranslations = { ...siteTranslations, ...translations };
    }

    // Mise à jour des éléments newsletter
    const newsletterTitle = document.querySelector(".newsletter h2");
    const newsletterSubtitle = document.querySelector(".newsletter p");
    const newsletterInput = document.querySelector(".newsletter-input");
    const newsletterButton = document.querySelector(".newsletter-button");

    if (newsletterTitle && siteTranslations.newsletter?.title) {
      newsletterTitle.textContent = siteTranslations.newsletter.title[lang];
    }
    
    if (newsletterSubtitle && siteTranslations.newsletter?.subtitle) {
      newsletterSubtitle.textContent = siteTranslations.newsletter.subtitle[lang];
    }
    
    if (newsletterInput && siteTranslations.newsletter?.placeholder) {
      newsletterInput.placeholder = siteTranslations.newsletter.placeholder[lang];
    }
    
    if (newsletterButton && siteTranslations.newsletter?.button) {
      newsletterButton.textContent = siteTranslations.newsletter.button[lang];
    }

  } catch (error) {
    console.error("❌ Error loading newsletter translations:", error);
  }
}

// Fonction principale d'inscription newsletter
async function subscribeNewsletter(event, language = 'en') {
  event.preventDefault();
  
  const form = event.target;
  const emailInput = form.querySelector('.newsletter-input');
  const submitButton = form.querySelector('.newsletter-button');
  const email = emailInput.value.trim();

  // Validation email côté client
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    showNewsletterMessage('error', getNewsletterMessage('errorInvalidEmail', language));
    return;
  }

  // État de chargement
  const originalButtonText = submitButton.textContent;
  submitButton.textContent = getNewsletterMessage('submitting', language);
  submitButton.disabled = true;
  emailInput.disabled = true;

  try {
    const response = await fetch(`${API_URL}?action=subscribeNewsletter`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        email: email,
        language: language
      })
    });

    const data = await response.json();

    if (data.success) {
      showNewsletterMessage('success', data.message);
      emailInput.value = ''; // Clear le champ email
      
      // Optionnel: tracking analytics
      if (typeof gtag !== 'undefined') {
        gtag('event', 'newsletter_subscribe', {
          'event_category': 'engagement',
          'event_label': language
        });
      }
    } else {
      showNewsletterMessage('error', data.message || getNewsletterMessage('errorGeneric', language));
    }

  } catch (error) {
    console.error('❌ Newsletter subscription error:', error);
    showNewsletterMessage('error', getNewsletterMessage('errorGeneric', language));
  } finally {
    // Restaurer l'état du formulaire
    submitButton.textContent = originalButtonText;
    submitButton.disabled = false;
    emailInput.disabled = false;
  }
}

// Fonction pour afficher les messages (succès/erreur)
function showNewsletterMessage(type, message) {
  // Supprimer les anciens messages
  const existingMessage = document.querySelector('.newsletter-message');
  if (existingMessage) {
    existingMessage.remove();
  }

  // Créer le nouveau message
  const messageDiv = document.createElement('div');
  messageDiv.className = `newsletter-message newsletter-message--${type}`;
  messageDiv.textContent = message;

  // Styles inline pour le message
  messageDiv.style.cssText = `
    margin-top: 10px;
    padding: 12px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    text-align: center;
    animation: slideInUp 0.3s ease-out;
    ${type === 'success' 
      ? 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;' 
      : 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'
    }
  `;

  // Ajouter le message après le formulaire
  const form = document.querySelector('.newsletter-form');
  if (form) {
    form.insertAdjacentElement('afterend', messageDiv);

    // Auto-suppression après 5 secondes
    setTimeout(() => {
      if (messageDiv.parentNode) {
        messageDiv.style.animation = 'slideOutDown 0.3s ease-in forwards';
        setTimeout(() => messageDiv.remove(), 300);
      }
    }, 5000);
  }
}

// Fonction helper pour récupérer les messages traduits
function getNewsletterMessage(key, language) {
  if (siteTranslations.newsletter && siteTranslations.newsletter[key]) {
    return siteTranslations.newsletter[key][language] || siteTranslations.newsletter[key]['en'];
  }
  
  // Messages de fallback
  const fallbackMessages = {
    'submitting': { 'en': 'Subscribing...', 'fr': 'Inscription...' },
    'errorInvalidEmail': { 'en': 'Please enter a valid email address.', 'fr': 'Veuillez entrer une adresse email valide.' },
    'errorGeneric': { 'en': 'An error occurred. Please try again.', 'fr': 'Une erreur s\'est produite. Veuillez réessayer.' }
  };
  
  return fallbackMessages[key] ? fallbackMessages[key][language] || fallbackMessages[key]['en'] : 'Error';
}

// CSS pour les animations (à ajouter dans votre fichier CSS)
const newsletterStyles = `
@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideOutDown {
  from {
    opacity: 1;
    transform: translateY(0);
  }
  to {
    opacity: 0;
    transform: translateY(20px);
  }
}

.newsletter-form {
  position: relative;
}

.newsletter-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.newsletter-input:disabled {
  opacity: 0.7;
  background-color: #f5f5f5;
}
`;

// Injecter les styles si ils n'existent pas déjà
if (!document.getElementById('newsletter-styles')) {
  const styleSheet = document.createElement('style');
  styleSheet.id = 'newsletter-styles';
  styleSheet.textContent = newsletterStyles;
  document.head.appendChild(styleSheet);
}

// Auto-initialisation quand le DOM est prêt
document.addEventListener('DOMContentLoaded', function() {
  // Charger les traductions newsletter pour la langue courante
  const currentLang = localStorage.getItem('lang') || 'en';
  loadNewsletterTranslations(currentLang);
});