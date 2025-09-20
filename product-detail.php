<?php
/**
 * TechEssentials Pro - Page Détail Produit
 * Version corrigée et organisée
 */

// 1. Variables de base (une seule fois)
$lang = $_GET['lang'] ?? 'fr';
$product_id = $_GET['id'] ?? '';

// 2. Array des produits (avec $lang défini correctement)
$all_products = [
    'anker-737' => [
        'id' => 'anker-737',
        'image' => 'anker-737.jpg',
        'category' => 'chargers',
        'name' => $lang === 'fr' ? 'Anker 737 PowerCore 24K' : 'Anker 737 PowerCore 24K',
        'description' => $lang === 'fr' ? 'Batterie externe haute capacité 24000mAh avec sortie 140W pour ordinateurs portables et appareils.' : 'High-capacity 24,000mAh power bank with 140W output for laptops and devices.',
        'long_description' => $lang === 'fr' ? 'Le PowerCore 737 d\'Anker offre une capacité exceptionnelle de 24 000mAh avec une puissance de sortie de 140W. Idéal pour charger ordinateurs portables, tablettes et smartphones simultanément. Technologie PowerIQ 4.0 pour une charge optimisée et écran numérique pour monitorer la charge restante.' : 'Anker\'s PowerCore 737 delivers exceptional 24,000mAh capacity with 140W output power. Perfect for charging laptops, tablets and smartphones simultaneously. PowerIQ 4.0 technology for optimized charging and digital display to monitor remaining charge.',
        'specifications' => [
            $lang === 'fr' ? 'Capacité' : 'Capacity' => '24,000mAh / 86.4Wh',
            $lang === 'fr' ? 'Puissance de sortie' : 'Output Power' => '140W (max)',
            $lang === 'fr' ? 'Ports' : 'Ports' => '2× USB-C, 1× USB-A',
            $lang === 'fr' ? 'Charge rapide' : 'Fast Charging' => 'PowerIQ 4.0, PD 3.1',
            $lang === 'fr' ? 'Poids' : 'Weight' => '650g',
            $lang === 'fr' ? 'Dimensions' : 'Dimensions' => '159 × 54 × 50mm',
            $lang === 'fr' ? 'Écran' : 'Display' => $lang === 'fr' ? 'LED numérique' : 'Digital LED',
            $lang === 'fr' ? 'Garantie' : 'Warranty' => $lang === 'fr' ? '24 mois' : '24 months'
        ],
        'features' => [
            $lang === 'fr' ? 'Capacité 24000mAh pour plusieurs charges complètes' : '24000mAh capacity for multiple full charges',
            $lang === 'fr' ? 'Sortie 140W pour charger les ordinateurs portables' : '140W output for laptop charging',
            $lang === 'fr' ? 'Écran numérique affichant la charge restante' : 'Digital display showing remaining charge',
            $lang === 'fr' ? 'Technologie PowerIQ 4.0 pour charge optimisée' : 'PowerIQ 4.0 technology for optimized charging',
            $lang === 'fr' ? 'Compatible avec MacBook, iPad, iPhone et plus' : 'Compatible with MacBook, iPad, iPhone and more',
            $lang === 'fr' ? 'Sécurité avancée avec protection surtension' : 'Advanced safety with surge protection'
        ],
        'price' => 139.99,
        'original_price' => 159.99,
        'discount_percentage' => 13,
        'rating' => 4.8,
        'reviews_count' => 1247,
        'in_stock' => true,
        'amazon_url' => 'https://amazon.fr/anker-737',
        'fnac_url' => 'https://fnac.com/anker-737',
        'gallery_images' => ['anker-737.jpg', 'anker-dock.jpg']
    ],
    'asus-proart-pa248qv' => [
        'id' => 'asus-proart-pa248qv',
        'image' => 'asus-proart-pa248QV.jpg',
        'category' => 'monitors',
        'name' => $lang === 'fr' ? 'ASUS ProArt PA248QV' : 'ASUS ProArt PA248QV',
        'description' => $lang === 'fr' ? 'Moniteur professionnel 24.1" avec précision colorimétrique exceptionnelle et certification Pantone.' : 'Professional 24.1" monitor with exceptional color accuracy and Pantone certification.',
        'long_description' => $lang === 'fr' ? 'Le ProArt PA248QV est un moniteur professionnel conçu pour les créatifs exigeants. Avec sa couverture colorimétrique 100% sRGB et Rec.709, il offre une précision exceptionnelle. Calibré en usine et certifié Pantone, il garantit des couleurs fidèles pour la retouche photo, le design graphique et la vidéo.' : 'The ProArt PA248QV is a professional monitor designed for demanding creatives. With 100% sRGB and Rec.709 color coverage, it offers exceptional accuracy. Factory calibrated and Pantone certified, it ensures true colors for photo editing, graphic design and video.',
        'specifications' => [
            $lang === 'fr' ? 'Taille' : 'Size' => '24.1" (61.2cm)',
            $lang === 'fr' ? 'Résolution' : 'Resolution' => '1920 × 1200 (WUXGA)',
            $lang === 'fr' ? 'Type de dalle' : 'Panel Type' => 'IPS',
            $lang === 'fr' ? 'Couverture couleur' : 'Color Gamut' => '100% sRGB, 100% Rec.709',
            $lang === 'fr' ? 'Luminosité' : 'Brightness' => '300 cd/m²',
            $lang === 'fr' ? 'Contraste' : 'Contrast' => '1000:1',
            $lang === 'fr' ? 'Connectivité' : 'Connectivity' => 'HDMI, DisplayPort, USB-C',
            $lang === 'fr' ? 'Réglages' : 'Adjustments' => $lang === 'fr' ? 'Hauteur, inclinaison, pivot' : 'Height, tilt, pivot'
        ],
        'features' => [
            $lang === 'fr' ? 'Précision colorimétrique Delta E < 2' : 'Color accuracy Delta E < 2',
            $lang === 'fr' ? 'Calibration en usine et certification Pantone' : 'Factory calibration and Pantone certification',
            $lang === 'fr' ? 'Connectivité USB-C avec Power Delivery 65W' : 'USB-C connectivity with 65W Power Delivery',
            $lang === 'fr' ? 'Hub USB intégré pour périphériques' : 'Built-in USB hub for peripherals',
            $lang === 'fr' ? 'Support VESA 100x100mm' : 'VESA 100x100mm mount support',
            $lang === 'fr' ? 'Garantie 3 ans avec échange express' : '3-year warranty with advance exchange'
        ],
        'price' => 449.99,
        'original_price' => 599.99,
        'discount_percentage' => 25,
        'rating' => 4.9,
        'reviews_count' => 892,
        'in_stock' => true,
        'amazon_url' => 'https://amazon.fr/asus-proart',
        'fnac_url' => 'https://fnac.com/asus-proart',
        'gallery_images' => ['asus-proart-pa248QV.jpg', 'dell-ultrasharp-u2720q.jpg']
    ],
    'logitech-mx-master-3s' => [
        'id' => 'logitech-mx-master-3s',
        'image' => 'logitech-mx-master-3s.jpg',
        'category' => 'mice',
        'name' => $lang === 'fr' ? 'Logitech MX Master 3S' : 'Logitech MX Master 3S',
        'description' => $lang === 'fr' ? 'Souris sans fil avancée avec molette MagSpeed et suivi de précision.' : 'Advanced wireless mouse with MagSpeed scroll wheel and precision tracking.',
        'long_description' => $lang === 'fr' ? 'La MX Master 3S redéfinit la productivité avec son capteur 8000 DPI ultra-précis, sa molette MagSpeed silencieuse et sa connectivité multi-appareils. Conçue pour les professionnels créatifs, elle offre une ergonomie parfaite et une autonomie exceptionnelle de 70 jours.' : 'The MX Master 3S redefines productivity with its ultra-precise 8000 DPI sensor, silent MagSpeed wheel and multi-device connectivity. Designed for creative professionals, it offers perfect ergonomics and exceptional 70-day battery life.',
        'specifications' => [
            $lang === 'fr' ? 'Capteur' : 'Sensor' => '8000 DPI Darkfield',
            $lang === 'fr' ? 'Connectivité' : 'Connectivity' => 'Bluetooth, USB-A Unifying',
            $lang === 'fr' ? 'Autonomie' : 'Battery Life' => $lang === 'fr' ? '70 jours' : '70 days',
            $lang === 'fr' ? 'Charge' : 'Charging' => 'USB-C',
            $lang === 'fr' ? 'Appareils' : 'Devices' => $lang === 'fr' ? '3 simultanés' : '3 simultaneous',
            $lang === 'fr' ? 'Boutons' : 'Buttons' => '7 boutons programmables',
            $lang === 'fr' ? 'Poids' : 'Weight' => '141g',
            $lang === 'fr' ? 'Compatibilité' : 'Compatibility' => 'Windows, Mac, Linux, Chrome OS'
        ],
        'features' => [
            $lang === 'fr' ? 'Molette MagSpeed ultra-silencieuse et précise' : 'Ultra-quiet and precise MagSpeed wheel',
            $lang === 'fr' ? 'Suivi sur toute surface même le verre' : 'Tracking on any surface including glass',
            $lang === 'fr' ? 'Connexion Flow entre 3 ordinateurs' : 'Flow connection between 3 computers',
            $lang === 'fr' ? 'Boutons personnalisables via Logitech Options+' : 'Customizable buttons via Logitech Options+',
            $lang === 'fr' ? 'Ergonomie optimisée pour longue utilisation' : 'Optimized ergonomics for extended use',
            $lang === 'fr' ? 'Charge rapide 1 min = 3h d\'utilisation' : 'Quick charge 1 min = 3h usage'
        ],
        'price' => 99.99,
        'original_price' => 129.99,
        'discount_percentage' => 23,
        'rating' => 4.7,
        'reviews_count' => 2156,
        'in_stock' => true,
        'amazon_url' => 'https://amazon.fr/logitech-mx-master',
        'fnac_url' => 'https://fnac.com/logitech-mx-master',
        'gallery_images' => ['logitech-mx-master-3s.jpg', 'logitech-mx-keys.jpg']
    ],
    'bose-qc45' => [
        'id' => 'bose-qc45',
        'image' => 'bose-qc45.jpg',
        'category' => 'headphones',
        'name' => $lang === 'fr' ? 'Bose QuietComfort 45' : 'Bose QuietComfort 45',
        'description' => $lang === 'fr' ? 'Casque sans fil premium avec réduction de bruit de pointe et qualité sonore exceptionnelle.' : 'Premium wireless headphones with industry-leading noise cancellation and exceptional sound quality.',
        'long_description' => $lang === 'fr' ? 'Les QuietComfort 45 établissent la nouvelle référence en matière de réduction de bruit. Avec leur technologie de pointe et leur confort exceptionnel, ils offrent une expérience d\'écoute immersive pendant 24 heures d\'autonomie. Parfaits pour le travail, les voyages et la détente.' : 'The QuietComfort 45 set the new standard for noise cancellation. With cutting-edge technology and exceptional comfort, they deliver an immersive listening experience for 24 hours of battery life. Perfect for work, travel and relaxation.',
        'specifications' => [
            $lang === 'fr' ? 'Réduction de bruit' : 'Noise Cancellation' => $lang === 'fr' ? 'Active adaptative' : 'Active adaptive',
            $lang === 'fr' ? 'Autonomie' : 'Battery Life' => $lang === 'fr' ? '24 heures' : '24 hours',
            $lang === 'fr' ? 'Connectivité' : 'Connectivity' => 'Bluetooth 5.1',
            $lang === 'fr' ? 'Codecs audio' : 'Audio Codecs' => 'SBC, AAC',
            $lang === 'fr' ? 'Poids' : 'Weight' => '238g',
            $lang === 'fr' ? 'Charge' : 'Charging' => 'USB-C',
            $lang === 'fr' ? 'Microphones' : 'Microphones' => $lang === 'fr' ? '6 micros pour appels' : '6 mics for calls',
            $lang === 'fr' ? 'Contrôles' : 'Controls' => $lang === 'fr' ? 'Tactiles et boutons' : 'Touch and buttons'
        ],
        'features' => [
            $lang === 'fr' ? 'Réduction de bruit leader du marché' : 'Industry-leading noise cancellation',
            $lang === 'fr' ? 'Mode Aware pour entendre l\'environnement' : 'Aware mode to hear your surroundings',
            $lang === 'fr' ? 'Qualité d\'appel exceptionnelle avec 6 micros' : 'Exceptional call quality with 6 microphones',
            $lang === 'fr' ? 'Charge rapide 15 min = 3h d\'écoute' : 'Quick charge 15 min = 3h listening',
            $lang === 'fr' ? 'Application Bose Music pour personnalisation' : 'Bose Music app for customization',
            $lang === 'fr' ? 'Confort premium pour port prolongé' : 'Premium comfort for extended wear'
        ],
        'price' => 329.99,
        'original_price' => 379.99,
        'discount_percentage' => 13,
        'rating' => 4.8,
        'reviews_count' => 1683,
        'in_stock' => true,
        'amazon_url' => 'https://amazon.fr/bose-qc45',
        'fnac_url' => 'https://fnac.com/bose-qc45',
        'gallery_images' => ['bose-qc45.jpg', 'anker-soundcore-q30.jpg']
    ],
    'dell-ultrasharp-u2720q' => [
        'id' => 'dell-ultrasharp-u2720q',
        'image' => 'dell-ultrasharp-u2720q.jpg',
        'category' => 'monitors',
        'name' => $lang === 'fr' ? 'Dell UltraSharp U2720Q' : 'Dell UltraSharp U2720Q',
        'description' => $lang === 'fr' ? 'Moniteur 4K 27" avec connectivité USB-C et précision colorimétrique premium.' : 'Professional 27" 4K monitor with USB-C connectivity and premium color accuracy.',
        'long_description' => $lang === 'fr' ? 'L\'UltraSharp U2720Q offre une expérience 4K exceptionnelle avec sa dalle IPS de 27 pouces. Conçu pour les professionnels créatifs, il propose une connectivité USB-C complète, une couverture colorimétrique étendue et un design sans bordures élégant.' : 'The UltraSharp U2720Q delivers exceptional 4K experience with its 27-inch IPS panel. Designed for creative professionals, it offers comprehensive USB-C connectivity, extended color coverage and elegant borderless design.',
        'specifications' => [
            $lang === 'fr' ? 'Taille' : 'Size' => '27" (68.6cm)',
            $lang === 'fr' ? 'Résolution' : 'Resolution' => '3840 × 2160 (4K UHD)',
            $lang === 'fr' ? 'Type de dalle' : 'Panel Type' => 'IPS',
            $lang === 'fr' ? 'Couverture couleur' : 'Color Gamut' => '95% DCI-P3, 99% sRGB',
            $lang === 'fr' ? 'Luminosité' : 'Brightness' => '350 cd/m²',
            $lang === 'fr' ? 'Temps de réponse' : 'Response Time' => '5ms (GtG)',
            $lang === 'fr' ? 'Connectivité' : 'Connectivity' => 'USB-C 90W, HDMI, DisplayPort',
            $lang === 'fr' ? 'Hub USB' : 'USB Hub' => '4× USB 3.2'
        ],
        'features' => [
            $lang === 'fr' ? 'Résolution 4K pour détails ultra-nets' : '4K resolution for ultra-sharp details',
            $lang === 'fr' ? 'USB-C avec Power Delivery 90W' : 'USB-C with 90W Power Delivery',
            $lang === 'fr' ? 'Hub USB 4 ports pour périphériques' : '4-port USB hub for peripherals',
            $lang === 'fr' ? 'Design InfinityEdge quasi sans bordures' : 'InfinityEdge virtually borderless design',
            $lang === 'fr' ? 'Réglages hauteur, inclinaison, pivot' : 'Height, tilt, swivel, pivot adjustments',
            $lang === 'fr' ? 'Dell Display Manager pour gestion fenêtres' : 'Dell Display Manager for window management'
        ],
        'price' => 699.99,
        'original_price' => 899.99,
        'discount_percentage' => 22,
        'rating' => 4.6,
        'reviews_count' => 743,
        'in_stock' => true,
        'amazon_url' => 'https://amazon.fr/dell-ultrasharp',
        'fnac_url' => 'https://fnac.com/dell-ultrasharp',
        'gallery_images' => ['dell-ultrasharp-u2720q.jpg', 'asus-proart-pa248QV.jpg']
    ],
    'logitech-mx-keys' => [
        'id' => 'logitech-mx-keys',
        'image' => 'logitech-mx-keys.jpg',
        'category' => 'keyboards',
        'name' => $lang === 'fr' ? 'Logitech MX Keys' : 'Logitech MX Keys',
        'description' => $lang === 'fr' ? 'Clavier sans fil avancé avec rétroéclairage intelligent et frappe de précision.' : 'Advanced wireless keyboard with smart illumination and precision typing.',
        'long_description' => $lang === 'fr' ? 'Le MX Keys révolutionne la frappe avec ses touches sphériques parfaitement sculptées et son rétroéclairage intelligent. Conçu pour les professionnels exigeants, il offre une connectivité multi-appareils et une autonomie exceptionnelle pour une productivité maximale.' : 'MX Keys revolutionizes typing with perfectly sculpted spherical keys and smart backlighting. Designed for demanding professionals, it offers multi-device connectivity and exceptional battery life for maximum productivity.',
        'specifications' => [
            $lang === 'fr' ? 'Type de touches' : 'Key Type' => $lang === 'fr' ? 'Sphériques à ciseau' : 'Spherical dished scissor',
            $lang === 'fr' ? 'Rétroéclairage' : 'Backlighting' => $lang === 'fr' ? 'LED intelligent' : 'Smart LED',
            $lang === 'fr' ? 'Connectivité' : 'Connectivity' => 'Bluetooth, USB-A Unifying',
            $lang === 'fr' ? 'Autonomie' : 'Battery Life' => $lang === 'fr' ? '10 jours avec rétroéclairage' : '10 days with backlighting',
            $lang === 'fr' ? 'Appareils' : 'Devices' => $lang === 'fr' ? '3 simultanés' : '3 simultaneous',
            $lang === 'fr' ? 'Layout' : 'Layout' => 'AZERTY/QWERTY',
            $lang === 'fr' ? 'Poids' : 'Weight' => '810g',
            $lang === 'fr' ? 'Dimensions' : 'Dimensions' => '430 × 131 × 20.5mm'
        ],
        'features' => [
            $lang === 'fr' ? 'Touches sphériques pour frappe confortable' : 'Spherical keys for comfortable typing',
            $lang === 'fr' ? 'Rétroéclairage adaptatif selon éclairage ambiant' : 'Adaptive backlighting based on ambient light',
            $lang === 'fr' ? 'Basculement fluide entre 3 appareils' : 'Smooth switching between 3 devices',
            $lang === 'fr' ? 'Touches de raccourcis personnalisables' : 'Customizable shortcut keys',
            $lang === 'fr' ? 'Charge USB-C pour 5 mois d\'autonomie' : 'USB-C charging for 5 months battery',
            $lang === 'fr' ? 'Compatible Flow avec souris MX Master' : 'Flow compatible with MX Master mice'
        ],
        'price' => 109.99,
        'original_price' => 139.99,
        'discount_percentage' => 21,
        'rating' => 4.5,
        'reviews_count' => 1534,
        'in_stock' => true,
        'amazon_url' => 'https://amazon.fr/logitech-mx-keys',
        'fnac_url' => 'https://fnac.com/logitech-mx-keys',
        'gallery_images' => ['logitech-mx-keys.jpg', 'logitech-ergo-k860.jpg']
    ]
];

// 3. Vérification produit
if (!isset($all_products[$product_id])) {
    echo "<script>window.location.href = 'products.php?lang=$lang';</script>";
    exit;
}

$product = $all_products[$product_id];

// 4. Variables pour header (maintenant $product existe)
$page_title = htmlspecialchars($product['name']) . ' - TechEssentials Pro';
$page_description = htmlspecialchars($product['description']);

// 5. Include header (en dernier)
include 'includes/layouts/header.php';
?>

<style>
/* Styles pour la page product-detail */
.product-detail-container {
    padding: 60px 0;
    background: #f8fafc;
}

.product-detail {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 3rem;
}

.product-breadcrumb {
    padding: 20px 30px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    font-size: 0.9rem;
}

.product-breadcrumb a {
    color: white;
}

.tab-content {
    padding: 2rem;
}

.tab-panel {
    display: none;
}

.tab-panel.active {
    display: block;
}

.specifications-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.spec-item {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f1f3f4;
}

.spec-label {
    font-weight: 600;
    color: #4a5568;
}

.spec-value {
    color: #718096;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.feature-icon {
    color: #667eea;
    font-size: 1.2rem;
    margin-top: 2px;
}

.zoom-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 10000;
    cursor: zoom-out;
}

.zoom-overlay img {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
    border-radius: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .product-main {
        grid-template-columns: 1fr;
        gap: 2rem;
        padding: 2rem;
    }
    
    .product-info h1 {
        font-size: 1.5rem;
    }
    
    .current-price {
        font-size: 2rem;
    }
    
    .tab-navigation {
        flex-wrap: wrap;
    }
    
    .tab-btn {
        flex: none;
        min-width: 120px;
    }
    
    .specifications-grid {
        grid-template-columns: 1fr;
    }
    
    .vendor-comparison {
        grid-template-columns: 1fr;
    }
}

#667eea;
    text-decoration: none;
}

.product-breadcrumb a:hover {
    text-decoration: underline;
}

.product-main {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    padding: 3rem;
}

.product-gallery {
    position: relative;
}

.main-image {
    width: 100%;
    height: 400px;
    background: #f8f9fa;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 1rem;
    cursor: zoom-in;
}

.main-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 20px;
}

.thumbnail-gallery {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
}

.thumbnail {
    width: 80px;
    height: 80px;
    background: #f8f9fa;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: border-color 0.3s ease;
}

.thumbnail.active,
.thumbnail:hover {
    border-color: #667eea;
}

.thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 8px;
}

.product-info h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 0.5rem;
}

.product-category-badge {
    display: inline-block;
    background: #667eea;
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 1rem;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stars {
    color: #ffd700;
    font-size: 1.2rem;
}

.rating-details {
    font-size: 0.9rem;
    color: #6c757d;
}

.product-price {
    margin-bottom: 2rem;
}

.current-price {
    font-size: 2.5rem;
    font-weight: 700;
    color: #667eea;
    margin-right: 1rem;
}

.original-price {
    font-size: 1.2rem;
    color: #a0aec0;
    text-decoration: line-through;
    margin-right: 0.5rem;
}

.savings {
    background: #e53e3e;
    color: white;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
}

.stock-status {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 2rem;
    font-weight: 600;
    color: #28a745;
}

.stock-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #28a745;
}

.product-description {
    font-size: 1.1rem;
    line-height: 1.6;
    color: #4a5568;
    margin-bottom: 2rem;
}

.vendor-comparison {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.vendor-card {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
}

.vendor-card:hover {
    border-color: #667eea;
    transform: translateY(-2px);
}

.vendor-logo {
    font-size: 1.2rem;
    font-weight: 700;
    color: #667eea;
    margin-bottom: 0.5rem;
}

.vendor-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 1rem;
}

.vendor-btn {
    width: 100%;
    padding: 10px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: block;
    transition: background 0.3s ease;
}

.vendor-btn:hover {
    background: #5a6fd8;
}

.product-tabs {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.tab-navigation {
    display: flex;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.tab-btn {
    flex: 1;
    padding: 15px 20px;
    background: none;
    border: none;
    cursor: pointer;
    font-weight: 600;
    color: #6c757d;
    transition: all 0.3s ease;
}

.tab-btn.active,
.tab-btn:hover {
    background: #667eea;
    color: white;
}
</style>

<!-- Product Detail Container -->
<div class="product-detail-container">
    <div class="container">
        <!-- Breadcrumb -->
        <div class="product-detail">
            <div class="product-breadcrumb">
                <a href="index.php?lang=<?= $lang ?>"><?= $lang === 'fr' ? 'Accueil' : 'Home' ?></a> &gt; 
                <a href="products.php?lang=<?= $lang ?>"><?= $lang === 'fr' ? 'Produits' : 'Products' ?></a> &gt; 
                <?= htmlspecialchars($product['name']) ?>
            </div>

            <!-- Product Main Section -->
            <div class="product-main">
                <!-- Product Gallery -->
                <div class="product-gallery">
                    <div class="main-image" onclick="openZoom('assets/images/products/<?= $product['image'] ?>')">
                        <img id="mainProductImage" src="assets/images/products/<?= $product['image'] ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>">
                    </div>
                    
                    <div class="thumbnail-gallery">
                        <?php foreach ($product['gallery_images'] as $index => $image): ?>
                            <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" 
                                 onclick="changeMainImage('assets/images/products/<?= $image ?>', this)">
                                <img src="assets/images/products/<?= $image ?>" alt="<?= htmlspecialchars($product['name']) ?> - Image <?= $index + 1 ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <div class="product-category-badge">
                        <?= ucfirst(str_replace('_', ' ', $product['category'])) ?>
                    </div>
                    
                    <h1><?= htmlspecialchars($product['name']) ?></h1>
                    
                    <div class="product-rating">
                        <div class="stars">
                            <?php
                            $rating = $product['rating'];
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $rating ? '★' : '☆';
                            }
                            ?>
                        </div>
                        <div class="rating-details">
                            <?= $product['rating'] ?>/5 (<?= $product['reviews_count'] ?> <?= $lang === 'fr' ? 'avis' : 'reviews' ?>)
                        </div>
                    </div>
                    
                    <div class="product-price">
                        <span class="current-price">€<?= number_format($product['price'], 2) ?></span>
                        <?php if ($product['original_price'] > $product['price']): ?>
                            <span class="original-price">€<?= number_format($product['original_price'], 2) ?></span>
                            <span class="savings">
                                <?= $lang === 'fr' ? 'Économisez' : 'Save' ?> €<?= number_format($product['original_price'] - $product['price'], 2) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="stock-status">
                        <div class="stock-indicator"></div>
                        <?= $product['in_stock'] ? ($lang === 'fr' ? 'En stock' : 'In stock') : ($lang === 'fr' ? 'Rupture de stock' : 'Out of stock') ?>
                    </div>
                    
                    <div class="product-description">
                        <?= htmlspecialchars($product['long_description']) ?>
                    </div>
                    
                    <!-- Multi-Vendor Purchase Options -->
                    <div class="vendor-comparison">
                        <div class="vendor-card">
                            <div class="vendor-logo">Amazon</div>
                            <div class="vendor-price">€<?= number_format($product['price'], 2) ?></div>
                            <a href="<?= htmlspecialchars($product['amazon_url']) ?>" 
                               class="vendor-btn" target="_blank" rel="noopener">
                                <?= $lang === 'fr' ? 'Acheter sur Amazon' : 'Buy on Amazon' ?>
                            </a>
                        </div>
                        
                        <div class="vendor-card">
                            <div class="vendor-logo">Fnac</div>
                            <div class="vendor-price">€<?= number_format($product['price'] + 5, 2) ?></div>
                            <a href="<?= htmlspecialchars($product['fnac_url'] ?? '#') ?>" 
                               class="vendor-btn" target="_blank" rel="noopener">
                                <?= $lang === 'fr' ? 'Acheter sur Fnac' : 'Buy on Fnac' ?>
                            </a>
                        </div>
                        
                        <div class="vendor-card">
                            <div class="vendor-logo">BestBuy</div>
                            <div class="vendor-price">€<?= number_format($product['price'] + 10, 2) ?></div>
                            <a href="#" class="vendor-btn" target="_blank" rel="noopener">
                                <?= $lang === 'fr' ? 'Acheter sur BestBuy' : 'Buy on BestBuy' ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Tabs -->
        <div class="product-tabs">
            <div class="tab-navigation">
                <button class="tab-btn active" onclick="showTab('specifications')">
                    <?= $lang === 'fr' ? 'Spécifications' : 'Specifications' ?>
                </button>
                <button class="tab-btn" onclick="showTab('features')">
                    <?= $lang === 'fr' ? 'Caractéristiques' : 'Features' ?>
                </button>
                <button class="tab-btn" onclick="showTab('reviews')">
                    <?= $lang === 'fr' ? 'Avis' : 'Reviews' ?> (<?= $product['reviews_count'] ?>)
                </button>
            </div>
            
            <div class="tab-content">
                <!-- Specifications Tab -->
                <div id="specifications" class="tab-panel active">
                    <div class="specifications-grid">
                        <?php foreach ($product['specifications'] as $label => $value): ?>
                            <div class="spec-item">
                                <span class="spec-label"><?= htmlspecialchars($label) ?></span>
                                <span class="spec-value"><?= htmlspecialchars($value) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Features Tab -->
                <div id="features" class="tab-panel">
                    <div class="features-grid">
                        <?php foreach ($product['features'] as $feature): ?>
                            <div class="feature-item">
                                <div class="feature-icon">✓</div>
                                <div><?= htmlspecialchars($feature) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Reviews Tab -->
                <div id="reviews" class="tab-panel">
                    <div style="text-align: center; padding: 2rem;">
                        <h3><?= $lang === 'fr' ? 'Avis clients' : 'Customer Reviews' ?></h3>
                        <p><?= $lang === 'fr' ? 'Section des avis en cours de développement.' : 'Reviews section under development.' ?></p>
                        <div style="margin: 2rem 0;">
                            <div class="stars" style="font-size: 2rem;">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $product['rating'] ? '★' : '☆';
                                }
                                ?>
                            </div>
                            <p><strong><?= $product['rating'] ?>/5</strong> - <?= $product['reviews_count'] ?> <?= $lang === 'fr' ? 'avis vérifiés' : 'verified reviews' ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Zoom Overlay -->
<div class="zoom-overlay" id="zoomOverlay" onclick="closeZoom()">
    <img id="zoomImage" src="" alt="Zoom">
</div>

<script>
// Image Gallery Functions
function changeMainImage(imageSrc, thumbnail) {
    document.getElementById('mainProductImage').src = imageSrc;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
    thumbnail.classList.add('active');
}

// Zoom Functions
function openZoom(imageSrc) {
    document.getElementById('zoomImage').src = imageSrc;
    document.getElementById('zoomOverlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeZoom() {
    document.getElementById('zoomOverlay').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Tab Functions
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(tabName).classList.add('active');
    event.target.classList.add('active');
}

// Keyboard navigation for zoom
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeZoom();
    }
});
</script>

<?php 
// Include newsletter et footer
include 'includes/layouts/newsletter.php';
include 'includes/layouts/footer.php';
?> 