<?php
/**
 * Test du système de rating
 */
define('TECHESSENTIALS_PRO', true);
require_once 'includes/config.php';

echo "<h2>Test du système de rating TechEssentials Pro</h2>";

try {
    $db = getDB('main');
    
    // 1. Vérifier que la table existe
    echo "<h3>1. Vérification de la table product_ratings</h3>";
    $stmt = $db->query("SHOW TABLES LIKE 'product_ratings'");
    if ($stmt->fetch()) {
        echo "✅ Table product_ratings existe<br>";
        
        // Structure
        echo "<br><strong>Structure de la table :</strong><br>";
        $stmt = $db->query("DESCRIBE product_ratings");
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ Table product_ratings n'existe pas<br>";
    }
    
    // 2. Compter les ratings existants
    echo "<h3>2. Ratings existants</h3>";
    $stmt = $db->query("SELECT COUNT(*) as total FROM product_ratings");
    $count = $stmt->fetchColumn();
    echo "Nombre total d'avis : <strong>$count</strong><br>";
    
    if ($count > 0) {
        echo "<br><strong>Détails des avis :</strong><br>";
        $stmt = $db->query("
            SELECT 
                product_id,
                COUNT(*) as count,
                ROUND(AVG(rating), 1) as average
            FROM product_ratings 
            GROUP BY product_id
        ");
        
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Product ID</th><th>Nombre d'avis</th><th>Moyenne</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>{$row['product_id']}</td>";
            echo "<td>{$row['count']}</td>";
            echo "<td>{$row['average']}/5</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // 3. Vérifier les fichiers API
    echo "<h3>3. Vérification des fichiers API</h3>";
    
    $files = [
        'api/submit-rating.php' => 'Submit Rating API',
        'api/get-rating.php' => 'Get Rating API'
    ];
    
    foreach ($files as $file => $name) {
        if (file_exists($file)) {
            echo "✅ $name existe ($file)<br>";
        } else {
            echo "❌ $name manquant ($file)<br>";
        }
    }
    
    // 4. Test d'un produit spécifique
    echo "<h3>4. Test avec un produit réel</h3>";
    $stmt = $db->query("SELECT id, name FROM products LIMIT 1");
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        echo "Produit test : <strong>{$product['name']}</strong> (ID: {$product['id']})<br>";
        
        // Stats de ce produit
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total,
                COALESCE(ROUND(AVG(rating), 1), 0) as average
            FROM product_ratings 
            WHERE product_id = ?
        ");
        $stmt->execute([$product['id']]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "Avis pour ce produit : {$stats['total']}<br>";
        echo "Moyenne : {$stats['average']}/5<br>";
        
        // Lien de test
        echo "<br><a href='product-detail.php?id={$product['id']}' target='_blank' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>
            🔗 Tester le système de rating sur ce produit
        </a>";
    } else {
        echo "⚠️ Aucun produit trouvé dans la base de données<br>";
    }
    
    // 5. Instructions
    echo "<h3>5. Instructions d'utilisation</h3>";
    echo "<ol>";
    echo "<li>Créez le dossier <code>/api/</code> à la racine si inexistant</li>";
    echo "<li>Placez les fichiers <code>submit-rating.php</code> et <code>get-rating.php</code> dans <code>/api/</code></li>";
    echo "<li>Intégrez le code HTML/CSS/JS dans <code>product-detail.php</code></li>";
    echo "<li>Testez en cliquant sur le lien ci-dessus</li>";
    echo "</ol>";
    
    echo "<h3>✅ Système prêt à l'emploi !</h3>";
    echo "<p>Le système de rating est maintenant opérationnel. Il permet de :</p>";
    echo "<ul>";
    echo "<li>Noter les produits de 1 à 5 étoiles</li>";
    echo "<li>Ajouter un commentaire optionnel</li>";
    echo "<li>Limiter à 1 avis par IP par produit</li>";
    echo "<li>Calculer automatiquement la moyenne</li>";
    echo "<li>Afficher les avis récents</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Erreur : " . $e->getMessage() . "</p>";
}