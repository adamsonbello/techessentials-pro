<?php
// reset_reviews.php

$host = "localhost";
$user = "root";      // adapte si tu as un mot de passe
$pass = "";
$dbname = "techessentials";

// Connexion
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("❌ Connexion échouée : " . $conn->connect_error);
}

// Désactiver les contraintes
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Vider review_ratings d'abord (sinon FK bloque)
$conn->query("TRUNCATE TABLE review_ratings");

// Vider reviews
$conn->query("TRUNCATE TABLE reviews");

// Réactiver les contraintes
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

echo "✅ Tables `reviews` et `review_ratings` ont été réinitialisées avec succès.";

$conn->close();
