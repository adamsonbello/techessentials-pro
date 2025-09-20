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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? ($translations['site_title'] . ' - ' . $translations['tagline']) ?></title>
    <meta name="description" content="<?= $page_description ?? $translations['tagline'] ?>">
    
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
<header class="header">
    <nav class="nav container">
        <div class="logo"><?= $translations['site_title'] ?></div>
        
        <ul class="nav-links">
            <li><a href="<?= BASE_URL ?>index.php?lang=<?= $lang ?>"><?= ucfirst($lang == 'fr' ? 'Accueil' : 'Home') ?></a></li>
            <li><a href="<?= BASE_URL ?>products.php?lang=<?= $lang ?>"><?= $lang == 'fr' ? 'Produits' : 'Products' ?></a></li>
            <li><a href="<?= BASE_URL ?>reviews.php?lang=<?= $lang ?>"><?= $lang == 'fr' ? 'Tests' : 'Reviews' ?></a></li>
            <li><a href="<?= BASE_URL ?>deals.php?lang=<?= $lang ?>"><?= $lang == 'fr' ? 'Promotions' : 'Deals' ?></a></li>
            <li><a href="<?= BASE_URL ?>blog.php?lang=<?= $lang ?>"><?= $lang == 'fr' ? 'Blog' : 'Blog' ?></a></li>
            <li><a href="<?= BASE_URL ?>contact.php?lang=<?= $lang ?>"><?= $lang == 'fr' ? 'Contact' : 'Contact' ?></a></li>
        </ul>

        <div class="lang-switch">
            <a href="<?= $_SERVER['PHP_SELF'] ?>?lang=fr<?= isset($_GET) && count($_GET) > 1 ? '&' . http_build_query(array_diff_key($_GET, ['lang' => ''])) : '' ?>" 
               class="<?= $lang === 'fr' ? 'active' : '' ?>">FR</a>
            <a href="<?= $_SERVER['PHP_SELF'] ?>?lang=en<?= isset($_GET) && count($_GET) > 1 ? '&' . http_build_query(array_diff_key($_GET, ['lang' => ''])) : '' ?>" 
               class="<?= $lang === 'en' ? 'active' : '' ?>">EN</a>
        </div>
    </nav>
</header>

<!-- Main Content Start -->
<div class="main-content">