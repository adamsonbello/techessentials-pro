<?php
/**
 * TechEssentials Pro - Newsletter Section Layout
 * Include commun pour toutes les pages
 */

// S'assurer que les variables de langue sont définies
if (!isset($lang)) {
    $lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'fr';
}

// Traductions par défaut si pas définies
$newsletter_translations = [
    'fr' => [
        'title' => 'Restez informé - obtenez des offres et des avis tech exclusifs',
        'subtitle' => 'Ne manquez pas notre newsletter bimestrielle. Rejoignez plus de 15 000 télétravailleurs et accédez en avant-première aux offres.',
        'placeholder' => 'Votre email',
        'button' => "S'abonner"
    ],
    'en' => [
        'title' => 'Stay Updated - Get Exclusive Tech Deals & Reviews',
        'subtitle' => 'Never Miss Out On Our Bi-monthly Newsletter. Join 15 000+ Remote Workers And Have Early Access To Deals',
        'placeholder' => 'Your email',
        'button' => 'Subscribe'
    ]
];

$t_newsletter = $newsletter_translations[$lang];
?>

<style>
/* Newsletter Section Styles */
.newsletter {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 60px 0;
    text-align: center;
}

.newsletter h2 {
    font-size: 2rem;
    margin-bottom: 1rem;
    color: #333;
}

.newsletter p {
    color: #666;
    margin-bottom: 2rem;
    font-size: 1.1rem;
}

.newsletter-form {
    max-width: 500px;
    margin: 2rem auto;
    display: flex;
    gap: 1rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border-radius: 50px;
    overflow: hidden;
    background: white;
}

.newsletter-input {
    flex: 1;
    padding: 15px 20px;
    border: none;
    font-size: 1rem;
    outline: none;
}

.newsletter-button {
    padding: 15px 30px;
    background: #667eea;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: bold;
    transition: background 0.3s;
}

.newsletter-button:hover {
    background: #5a6fd8;
}

/* Messages de feedback */
.newsletter-message {
    max-width: 500px;
    margin: 0 auto 20px;
    padding: 15px;
    border-radius: 8px;
    font-weight: 500;
    text-align: center;
    transition: opacity 0.5s ease-out;
}

.newsletter-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.newsletter-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Responsive */
@media (max-width: 768px) {
    .newsletter-form {
        flex-direction: column;
        border-radius: 15px;
    }
    
    .newsletter-button {
        border-radius: 0 0 15px 15px;
    }
}
</style>

<!-- Newsletter Section -->
<section class="newsletter scroll-reveal">
    <div class="container">
        <h2><?= $t_newsletter['title'] ?></h2>
        <p><?= $t_newsletter['subtitle'] ?></p>
        
        <!-- Messages de feedback -->
        <?php if (isset($_GET['newsletter_success'])): ?>
            <div class="newsletter-message newsletter-success">
                ✓ <?= htmlspecialchars($_GET['newsletter_success']) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['newsletter_error'])): ?>
            <div class="newsletter-message newsletter-error">
                ✗ <?= htmlspecialchars($_GET['newsletter_error']) ?>
            </div>
        <?php endif; ?>
        
       <?php
      // Détecter si on est dans le blog pour ajuster le chemin
        $newsletter_action = (strpos($_SERVER['REQUEST_URI'], '/blog/') !== false) 
        ? '../newsletter-subscribe.php' 
        : 'newsletter-subscribe.php';
        ?>
       <form class="newsletter-form" action="<?= $newsletter_action ?>" method="POST">
            <input type="hidden" name="lang" value="<?= $lang ?>">
            <input type="email" class="newsletter-input" name="email" 
                   placeholder="<?= $t_newsletter['placeholder'] ?>" required autocomplete="email">
            <button type="submit" class="newsletter-button"><?= $t_newsletter['button'] ?></button>
        </form>
    </div>
</section>

<script>


document.addEventListener('DOMContentLoaded', function() {
    // Auto-scroll vers le message de newsletter si présent
    const newsletterMessage = document.querySelector('.newsletter-message');
    if (newsletterMessage) {
        setTimeout(function() {
            newsletterMessage.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
        }, 100); // Petit délai pour laisser la page se charger
    }
    
    // Auto-hide des messages après 5 secondes
    const messages = document.querySelectorAll('.newsletter-message');
    messages.forEach(function(message) {
        setTimeout(function() {
            message.style.opacity = '0';
            setTimeout(function() {
                message.style.display = 'none';
            }, 500);
        }, 5000);
    });
});

// Auto-hide success/error messages après 5 secondes
document.addEventListener('DOMContentLoaded', function() {
    const messages = document.querySelectorAll('.newsletter-message');
    messages.forEach(function(message) {
        setTimeout(function() {
            message.style.opacity = '0';
            setTimeout(function() {
                message.style.display = 'none';
            }, 500);
        }, 5000); // 5 secondes d'affichage + 0.5s de fade-out
    });
});
</script>