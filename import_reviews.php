<?php
// import_reviews.php
header("Content-Type: text/plain; charset=UTF-8");

$host = "localhost";
$db   = "techessentials";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connexion réussie à la base $db\n";
} catch (PDOException $e) {
    die("❌ Erreur DB : " . $e->getMessage());
}

// Charger le JSON
$json = file_get_contents(__DIR__ . "/data/reviews.json");
$reviews = json_decode($json, true);

if (!$reviews) {
    die("❌ Impossible de charger reviews.json\n");
}

// Vider les tables
$pdo->exec("DELETE FROM review_ratings");
$pdo->exec("DELETE FROM reviews");

// Importer les reviews
$stmt = $pdo->prepare("INSERT INTO reviews 
    (slug, title_en, title_fr, author, date, image, 
     excerpt_en, excerpt_fr, content_en, content_fr,
     pros_en, pros_fr, cons_en, cons_fr, verdict_en, verdict_fr)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$count = 0;
foreach ($reviews as $slug => $r) {
    $stmt->execute([
        $slug,
        $r["title"]["en"],
        $r["title"]["fr"],
        $r["author"],
        $r["date"],
        $r["image"],
        $r["excerpt"]["en"],
        $r["excerpt"]["fr"],
        $r["content"]["en"],
        $r["content"]["fr"],
        json_encode($r["pros"]["en"]),
        json_encode($r["pros"]["fr"]),
        json_encode($r["cons"]["en"]),
        json_encode($r["cons"]["fr"]),
        $r["verdict"]["en"],
        $r["verdict"]["fr"]
    ]);
    $count++;
}

echo "✅ $count reviews importées avec succès dans MySQL.\n";

