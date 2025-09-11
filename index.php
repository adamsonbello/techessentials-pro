<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechEssentials Pro - Best Tech for Remote Workers | Meilleur Tech pour Télétravail</title>
  <meta name="description" content="Discover the essential tech accessories and productivity tools for remote workers. Expert reviews, honest recommendations, and exclusive deals. | Découvrez les accessoires tech essentiels et outils de productivité pour le télétravail.">
  <meta name="keywords" content="remote work, tech accessories, home office, télétravail, tech reviews, productivity tools">
  <meta name="author" content="TechEssentials Pro">

  <link rel="stylesheet" href="assets/css/styles.css">
  ="author" content="TechEssentials Pro">

  <style>
    /* 👉 On garde exactement ton CSS original */
    /* === ton CSS original copié intégralement ici === */
    /* (je n’ai rien modifié pour ne pas casser ton design) */
     * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            flex-wrap: wrap;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-content {
            display: flex;
            align-items: center;
            gap: 2rem;
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
            transition: color 0.3s;
            position: relative;
        }
        
        .nav-links a:hover {
            color: #667eea;
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: #667eea;
            transition: width 0.3s;
        }
        
        .nav-links a:hover::after {
            width: 100%;
        }
        
        .language-switcher {
            display: flex;
            background: #f8f9fa;
            border-radius: 20px;
            padding: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .lang-btn {
            padding: 8px 16px;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 15px;
            font-weight: 500;
            transition: all 0.3s;
            color: #666;
        }
        
        .lang-btn.active {
            background: #667eea;
            color: white;
            box-shadow: 0 2px 5px rgba(102, 126, 234, 0.3);
        }
        
        .lang-btn:hover:not(.active) {
            background: #e9ecef;
        }
        
        .lang-content {
            display: none;
            animation: fadeIn 0.5s ease-in;
        }
        
        .lang-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .hero {
            padding: 120px 0 80px;
            text-align: center;
            color: white;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            font-weight: 700;
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .cta-button {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }
        
        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255,107,107,0.5);
        }
        
        .featured-products {
            background: white;
            padding: 80px 0;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #333;
            font-weight: 700;
        }
        
        .section-subtitle {
            text-align: center;
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border: 1px solid #f0f0f0;
            position: relative;
            overflow: hidden;
        }
        
        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
            transition: left 0.5s;
        }
        
        .product-card:hover::before {
            left: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

       
        
        #home-featured .product-image {
            width: 100%;
            height: 250px;
            background: #fff; /* on enlève le grisâtre */
            border-radius: 10px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            position: relative;
            overflow: hidden;
        }

        
        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff6b6b;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        
        .product-title {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            color: #333;
            font-weight: 600;
        }
        
        .product-description {
            color: #666;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .product-features {
            list-style: none;
            margin-bottom: 1.5rem;
        }
        
        .product-features li {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            position: relative;
            padding-left: 20px;
        }
        
        .product-features li::before {
            content: '✓';
            color: #28a745;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        .product-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .price-old {
            text-decoration: line-through;
            color: #999;
            font-size: 1.1rem;
            margin-right: 0.5rem;
        }
        
        .affiliate-button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
        }
        
        .affiliate-button:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
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
        
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 3rem 0;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .footer-section h3 {
            margin-bottom: 1rem;
            color: #667eea;
        }
        
        .footer-section p, .footer-section a {
            color: #ccc;
            text-decoration: none;
            line-height: 1.6;
        }
        
        .footer-section a:hover {
            color: #667eea;
        }
        
        .trust-badges {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 2rem 0;
            flex-wrap: wrap;
        }
        
        .trust-badge {
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            font-size: 0.9rem;
            backdrop-filter: blur(5px);
        }
        
        .stats-section {
            background: linear-gradient(45deg, #4facfe 0%, #00f2fe 100%);
            padding: 60px 0;
            color: white;
            text-align: center;
        }
        
        .stats-section h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .stat-card {
            padding: 1rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            opacity: 0.9;
            font-size: 1rem;
        }
        
        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .nav-links {
                display: none;
            }
            
            .newsletter-form {
                flex-direction: column;
                border-radius: 15px;
            }
            
            .newsletter-input, .newsletter-button {
                border-radius: 10px;
            }
            
            .nav-content {
                flex-direction: column;
                gap: 1rem;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        /* Loading Animation */
        .loading {
            opacity: 0;
            animation: fadeInUp 0.8s ease forwards;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Scroll Animations */
        .scroll-reveal {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.6s ease;
        }
        
        .scroll-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }
    
  </style>
</head>
<body>
  <!-- HEADER -->
  <header>
    <div class="container">
      <nav>
        <div class="logo">TechEssentials Pro</div>
        <div class="nav-content">
         <ul class="nav-links">
             <li><a href="index.php" id="nav-home"></a></li>
             <li><a href="products.html" id="nav-products"></a></li>
             <li><a href="reviews.html" id="nav-reviews"></a></li>
             <li><a href="blog/" id="nav-blog">📝 Blog</a></li>
             <li><a href="deals.html" id="nav-deals"></a></li>
             <li><a href="contact.html" id="nav-contact"></a></li>
         </ul>
          <div class="language-switcher">
            <button class="lang-btn active" onclick="switchLanguage('en')">EN</button>
            <button class="lang-btn" onclick="switchLanguage('fr')">FR</button>
          </div>
        </div>
      </nav>
    </div>
  </header>

  <!-- HERO -->
  <section class="hero loading" id="home">
    <div class="container">
      <h1 id="hero-title"></h1>
      <p id="hero-subtitle"></p>
      <a href="#home-featured" id="hero-cta" class="cta-button"></a>

      <div class="trust-badges">
        <div class="trust-badge" id="trust-1">✓ Expert Tested</div>
        <div class="trust-badge" id="trust-2">✓ Honest Reviews</div>
        <div class="trust-badge" id="trust-3">✓ Best Prices</div>
        <div class="trust-badge" id="trust-4">✓ Free Shipping</div>
      </div>
    </div>
  </section>

  <!-- TRUST SECTION -->
  <section class="stats-section scroll-reveal">
    <div class="container">
      <h2 id="trusted-title"></h2>
      <p id="trusted-subtitle"></p>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-number">15K+</div>
          <div class="stat-label">Happy Customers</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">500+</div>
          <div class="stat-label">Products Tested</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">98%</div>
          <div class="stat-label">Satisfaction Rate</div>
        </div>
        <div class="stat-card">
          <div class="stat-number">€2M+</div>
          <div class="stat-label">Money Saved</div>
        </div>
      </div>
    </div>
  </section>

  <!-- FEATURED PRODUCTS -->
  <section class="featured-products scroll-reveal" id="home-featured">
    <div class="container">
      <h2 id="products-title" class="section-title"></h2>
      <p id="products-subtitle" class="section-subtitle"></p>
      <div class="products-grid" id="home-featured-list"></div>
    </div>
  </section>

  <!-- NEWSLETTER SECTION BILINGUE -->
<section class="newsletter scroll-reveal">
  <div class="container">
    <h2>Get Exclusive Tech Deals & Reviews</h2>
    <p>Join 15,000+ remote workers getting weekly recommendations and early access to deals</p>
    <form class="newsletter-form" onsubmit="subscribeNewsletter(event, currentLanguage)">
      <input 
        type="email" 
        class="newsletter-input" 
        placeholder="Enter your email address" 
        required
        autocomplete="email"
        name="email"
      >
      <button type="submit" class="newsletter-button">Subscribe Free</button>
    </form>
  </div>
</section>

<?php include 'blog_widget.php'; ?>

  <!-- FOOTER -->
  <footer>
    <div class="container">
      <div class="footer-content">
        <div class="footer-section">
          <h3 id="footer-about-title"></h3>
          <p id="footer-about-text"></p>
        </div>
        <div class="footer-section">
          <h3 id="footer-quicklinks"></h3>
          <p><a href="#reviews" id="footer-link-reviews"></a></p>
          <p><a href="#deals" id="footer-link-deals"></a></p>
          <p><a href="#contact" id="footer-link-contact"></a></p>
          <p><a href="#privacy" id="footer-link-privacy"></a></p>
        </div>
        <div class="footer-section">
          <h3 id="footer-contact-title"></h3>
          <p id="footer-contact-email"></p>
          <p id="footer-contact-time"></p>
          <p id="footer-contact-based"></p>
        </div>
      </div>
      <div id="footer-legal"></div>
    </div>
  </footer>

  <!-- SCRIPTS -->
  <script src="assets/js/products.js"></script>
  <script src="assets/js/main.js" defer></script>
  <script>
// Si vous n'avez pas encore de fichier config.js inclus, assurez-vous que API_URL est défini
if (typeof API_URL === 'undefined') {
  const API_URL = "http://localhost/TechEssentialsPro/api.php";
}

// Si vous n'avez pas encore de variable currentLanguage globale
if (typeof currentLanguage === 'undefined') {
  let currentLanguage = localStorage.getItem("lang") || "en";
}
</script>
</body>
</html>
