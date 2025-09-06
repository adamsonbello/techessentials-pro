<?php
header("Content-Type: application/json; charset=UTF-8");

// Config DB
$host = "localhost";
$db   = "techessentials";
$user = "root"; // adapte si besoin
$pass = "";     // adapte si besoin

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "❌ DB connection failed", "details" => $e->getMessage()]);
    exit;
}

// Récupération paramètre action
$action = $_GET["action"] ?? "";

switch ($action) {
    // =======================
    // GET ONE REVIEW + RATINGS
    // =======================
    case "getReview":
        $slug = $_GET["id"] ?? null;

        if (!$slug) {
            echo json_encode(["error" => "Missing review id"]);
            exit;
        }

        // Récupérer la review par son slug
        $stmt = $pdo->prepare("SELECT * FROM reviews WHERE slug = ?");
        $stmt->execute([$slug]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$review) {
            echo json_encode(["error" => "Review not found"]);
            exit;
        }

        $reviewId = $review["id"]; // id numérique

        // Récupérer les avis utilisateurs liés
        $stmt = $pdo->prepare("SELECT name, rating, comment, created_at 
                               FROM review_ratings WHERE review_id = ? ORDER BY created_at DESC");
        $stmt->execute([$reviewId]);
        $ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calcul moyenne
        $average = null;
        if (count($ratings) > 0) {
            $total = array_sum(array_column($ratings, "rating"));
            $average = round($total / count($ratings), 1);
        }

        echo json_encode([
            "review"   => $review,
            "ratings"  => $ratings,
            "average"  => $average,
            "count"    => count($ratings)
        ]);
        break;

// =======================
// ADD NEW RATING
// =======================
case "addRating":
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data["review_id"], $data["name"], $data["rating"], $data["comment"])) {
        echo json_encode(["error" => "Missing fields"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO review_ratings (review_id, name, rating, comment, created_at) 
                               VALUES (:review_id, :name, :rating, :comment, NOW())");
        $stmt->execute([
            ":review_id" => $data["review_id"], // slug (varchar)
            ":name"      => htmlspecialchars($data["name"]),
            ":rating"    => intval($data["rating"]),
            ":comment"   => htmlspecialchars($data["comment"])
        ]);

        echo json_encode(["success" => true, "message" => "Review added successfully"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "DB insert failed", "details" => $e->getMessage()]);
    }
    break;


    // =======================
    // GET ALL REVIEWS (list for reviews.html)
    // =======================
    case "getAllReviews":
        $stmt = $pdo->query("SELECT slug, title_en, title_fr, excerpt_en, excerpt_fr, image FROM reviews");
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($reviews);
        break;

    default:
        echo json_encode(["error" => "Invalid action"]);
}

