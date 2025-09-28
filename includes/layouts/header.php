<?php
/**
 * TechEssentials Pro - Header Layout
 * Include commun pour toutes les pages
 */

// S'assurer que les variables de langue sont définies
if (!isset($lang)) {
    $lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'fr';
}

if (!isset($translations)) {
    $translations = [
        'site_title' => 'TechEssentials Pro',
        'tagline' => $lang === 'fr' ? 'Les meilleurs produits tech pour les professionnels' : 'Best tech products for professionals'
    ];
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<?php
// Déterminer la page actuelle
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Métas spécifiques par page
switch($current_page) {
    case 'products':
        $page_title = "Équipement Tech Télétravail - Micros, Écrans, Accessoires Bureau | TechEssentials Pro";
        $page_description = "Catalogue complet d'équipements tech pour télétravail : micros professionnels, écrans ergonomiques, éclairage bureau, accessoires productivité.";
        $page_keywords = "micro télétravail, écran home office, éclairage bureau domicile, webcam professionnelle, casque visioconférence";
        break;
        
    case 'reviews':
        $page_title = "Tests Équipement Télétravail - Avis Experts & Comparatifs | TechEssentials Pro";
        $page_description = "Tests approfondis d'équipements tech pour télétravail. Avis d'experts, comparatifs détaillés pour optimiser votre setup remote.";
        $page_keywords = "test micro télétravail, comparatif écran home office, avis équipement remote work, review tech télétravail";
        break;
        
    case 'deals':
        $page_title = "Bons Plans Tech Télétravail - Promotions Équipement Bureau | TechEssentials Pro";
        $page_description = "Meilleures promotions sur l'équipement tech pour télétravail. Deals exclusifs, codes promo sur micros, écrans, accessoires bureau.";
        $page_keywords = "promo télétravail, bon plan home office, réduction équipement bureau, deals tech remote work";
        break;
        
    case 'contact':
        $page_title = "Contact - Conseils Équipement Télétravail | TechEssentials Pro";
        $page_description = "Contactez nos experts en équipement télétravail. Conseils personnalisés pour votre setup home office.";
        $page_keywords = "conseil télétravail, expert home office, aide setup bureau, consultation tech remote work";
        break;
        
    default: // index.php
        $page_title = "TechEssentials Pro - Équipement Tech pour Télétravail & Nomades Digitaux";
        $page_description = "Découvrez les meilleurs outils tech pour télétravailleurs à domicile et nomades digitaux. Tests d'experts, recommandations et deals exclusifs.";
        $page_keywords = "télétravail, home office, nomade digital, remote work, équipement bureau domicile";
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <meta name="description" content="<?= $page_description ?>">
    <meta name="keywords" content="<?= $page_keywords ?>">
    <meta name="author" content="TechEssentials Pro">
    <!-- Rest of head content -->

    <!-- 7. STRUCTURE JSON-LD POUR RICH SNIPPETS -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "TechEssentials Pro",
  "description": "Équipement tech pour télétravailleurs et nomades digitaux",
  "url": "https://techessentialspro.com",
  "publisher": {
    "@type": "Organization",
    "name": "TechEssentials Pro",
    "description": "Spécialiste équipement tech pour télétravail"
  },
  "audience": {
    "@type": "Audience",
    "audienceType": "Remote Workers, Digital Nomads, Télétravailleurs"
  },
  "mainEntity": {
    "@type": "Product",
    "category": "Technology Equipment for Remote Work"
  }
}
</script>
</head>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header & Navigation */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            background: linear-gradient(45deg, #4facfe, #00f2fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #4facfe;
        }

        .lang-switch {
            display: flex;
            gap: 0.5rem;
        }

        .lang-switch a {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            text-decoration: none;
            color: #666;
            transition: all 0.3s ease;
        }

        .lang-switch a.active {
            background: #4facfe;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
        }

        /* Main Content Spacing */
        .main-content {
            margin-top: 70px;
        }
    </style>
</head>

<body>
   <!-- Header -->

<!-- 2. NOUVEAU HEADER PRINCIPAL UNIFIÉ (pour includes/layouts/header.php) -->

<header class="site-header">
    <div class="header-content">
        <link rel="stylesheet" href="assets/css/header-unified.css">
        <a href="<?= BASE_URL ?>index.php?lang=<?= $lang ?>" class="logo">TechEssentials Pro</a>
        <nav>
            <ul class="nav-menu">
                <li><a href="<?= BASE_URL ?>index.php?lang=<?= $lang ?>" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">🏠 <?= $lang === 'fr' ? 'Accueil' : 'Home' ?></a></li>
                <li><a href="<?= BASE_URL ?>products.php?lang=<?= $lang ?>" class="<?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : '' ?>">📱 <?= $lang === 'fr' ? 'Produits' : 'Products' ?></a></li>
                <li><a href="<?= BASE_URL ?>reviews.php?lang=<?= $lang ?>" class="<?= basename($_SERVER['PHP_SELF']) === 'reviews.php' ? 'active' : '' ?>">⭐ <?= $lang === 'fr' ? 'Tests' : 'Reviews' ?></a></li>
                <li><a href="<?= BASE_URL ?>blog/?lang=<?= $lang ?>" class="<?= strpos($_SERVER['REQUEST_URI'], '/blog/') !== false ? 'active' : '' ?>">📝 Blog</a></li>
                <li><a href="<?= BASE_URL ?>deals.php?lang=<?= $lang ?>" class="<?= basename($_SERVER['PHP_SELF']) === 'deals.php' ? 'active' : '' ?>">💰 <?= $lang === 'fr' ? 'Bons Plans' : 'Deals' ?></a></li>
                <li><a href="<?= BASE_URL ?>contact.php?lang=<?= $lang ?>" class="<?= basename($_SERVER['PHP_SELF']) === 'contact.php' ? 'active' : '' ?>">📞 Contact</a></li>
            </ul>
        </nav>
        
        <div class="lang-switch">
            <a href="<?= $_SERVER['PHP_SELF'] ?>?lang=fr<?= isset($_GET) && count($_GET) > 1 ? '&' . http_build_query(array_diff_key($_GET, ['lang' => ''])) : '' ?>" 
               class="<?= $lang === 'fr' ? 'active' : '' ?>">FR</a>
            <a href="<?= $_SERVER['PHP_SELF'] ?>?lang=en<?= isset($_GET) && count($_GET) > 1 ? '&' . http_build_query(array_diff_key($_GET, ['lang' => ''])) : '' ?>" 
               class="<?= $lang === 'en' ? 'active' : '' ?>">EN</a>
        </div>
    </div>
</header>

<!-- Main Content Start -->
<div class="main-content">