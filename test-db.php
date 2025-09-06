<?php
$dsn = "mysql:host=localhost;dbname=techessentials;charset=utf8mb4";
$user = "root";
$pass = "";

try {
    $pdo = new PDO($dsn, $user, $pass);
    echo "✅ Connexion réussie à la base techessentials !";
} catch (Exception $e) {
    echo "❌ Erreur connexion : " . $e->getMessage();
}
