<?php
header("Content-Type: text/html; charset=utf-8");

// ⚡ Connexion à MySQL
$dsn = "mysql:host=localhost;dbname=techessentials;charset=utf8mb4";
$user = "root";  // adapte si besoin
$pass = "";      // adapte si besoin

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ Connexion réussie à la base techessentials<br>";
} catch (PDOException $e) {
    die("❌ Erreur de connexion : " . $e->getMessage());
}

// Charger le JSON
$jsonFile = __DIR__ . "/data/reviews.json";
if (!file_exists($jsonFile)) {
    die("❌ Fichier reviews.json introuvable");
}

$reviews = json_decode(file_get_contents($jsonFile), true);
if (!$reviews) {
    die("❌ Erreur de parsing du JSON");
}

// Préparer la requête (sans `id`)
$stmt = $pdo->prepare("
    INSERT INTO reviews 
      (slug, title_en, title_fr, author, date, image, excerpt_en, excerpt_fr, content_en, content_fr, verdict_en, verdict_fr)
    VALUES 
      (:slug, :title_en, :title_fr, :author, :date, :image, :excerpt_en, :excerpt_fr, :content_en, :content_fr, :verdict_en, :verdict_fr)
    ON DUPLICATE KEY UPDATE
      title_en = VALUES(title_en),
      title_fr = VALUES(title_fr),
      author = VALUES(author),
      date = VALUES(date),
      image = VALUES(image),
      excerpt_en = VALUES(excerpt_en),
      excerpt_fr = VALUES(excerpt_fr),
      content_en = VALUES(content_en),
      content_fr = VALUES(content_fr),
      verdict_en = VALUES(verdict_en),
      verdict_fr = VALUES(verdict_fr)
");

$count = 0;
foreach ($reviews as $slug => $r) {
    $stmt->execute([
        ":slug"       => $slug,
        ":title_en"   => $r["title"]["en"] ?? null,
        ":title_fr"   => $r["title"]["fr"] ?? null,
        ":author"     => $r["author"] ?? null,
        ":date"       => $r["date"] ?? null,
        ":image"      => $r["image"] ?? null,
        ":excerpt_en" => $r["excerpt"]["en"] ?? null,
        ":excerpt_fr" => $r["excerpt"]["fr"] ?? null,
        ":content_en" => $r["content"]["en"] ?? null,
        ":content_fr" => $r["content"]["fr"] ?? null,
        ":verdict_en" => $r["verdict"]["en"] ?? null,
        ":verdict_fr" => $r["verdict"]["fr"] ?? null
    ]);
    $count++;
}

echo "✅ $count reviews importées avec succès dans MySQL.<br>";
