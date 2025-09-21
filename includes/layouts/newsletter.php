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
        'subtitle' => 'Ne manquez pas notre newsletter bimestrielle. Rejoignez plus de 15 000 télétravailleurs et accédez en avant-première aux offres.',
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
        <form class="newsletter-form" action="newsletter-subscribe.php" method="POST">
            <input type="hidden" name="lang" value="<?= $lang ?>">
            <input type="email" class="newsletter-input" name="email" 
                   placeholder="<?= $t_newsletter['placeholder'] ?>" required autocomplete="email">
            <button type="submit" class="newsletter-button"><?= $t_newsletter['button'] ?></button>
        </form>
    </div>
</section>