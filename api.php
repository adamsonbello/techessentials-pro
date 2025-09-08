<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Config DB
$host = "localhost";
$db   = "techessentials";
$user = "root"; 
$pass = "";     

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "❌ DB connection failed", "details" => $e->getMessage()]);
    exit;
}

$action = $_GET["action"] ?? "";

switch ($action) {
    // =======================
    // GET ALL REVIEWS (pour la page reviews.html)
    // =======================
    case "getAllReviews":
        try {
            $stmt = $pdo->prepare("SELECT slug, title_en, title_fr, author, date, image, excerpt_en, excerpt_fr 
                                   FROM reviews ORDER BY date DESC");
            $stmt->execute();
            $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($reviews);
        } catch (PDOException $e) {
            echo json_encode(["error" => "❌ Error fetching reviews", "details" => $e->getMessage()]);
        }
        break;

    // =======================
    // GET ONE REVIEW + RATINGS
    // =======================
    case "getReview":
        $slug = $_GET["id"] ?? null;
        if (!$slug) {
            echo json_encode(["error" => "❌ No review ID provided"]);
            exit;
        }

        // Récupérer la review
        $stmt = $pdo->prepare("SELECT * FROM reviews WHERE slug = ?");
        $stmt->execute([$slug]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$review) {
            echo json_encode(["error" => "❌ Review not found"]);
            exit;
        }

        $reviewId = $review["id"];

        // Récupérer les avis liés
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

        if (!$data || !isset($data["review_slug"], $data["name"], $data["rating"], $data["comment"])) {
            echo json_encode(["error" => "Missing fields"]);
            exit;
        }

        // Convertir slug en ID numérique
        $stmt = $pdo->prepare("SELECT id FROM reviews WHERE slug = ?");
        $stmt->execute([$data["review_slug"]]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$review) {
            echo json_encode(["error" => "Invalid review slug"]);
            exit;
        }

        $reviewId = $review["id"];

        // Validation du rating
        $rating = intval($data["rating"]);
        if ($rating < 1 || $rating > 5) {
            echo json_encode(["error" => "Rating must be between 1 and 5"]);
            exit;
        }

        // Insertion de l'avis utilisateur
        try {
            $stmt = $pdo->prepare("INSERT INTO review_ratings (review_id, name, rating, comment, created_at) 
                                   VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([
                $reviewId,
                htmlspecialchars(trim($data["name"])),
                $rating,
                htmlspecialchars(trim($data["comment"]))
            ]);

            echo json_encode(["success" => true, "message" => "Review added successfully"]);
        } catch (PDOException $e) {
            echo json_encode(["error" => "❌ Error adding review", "details" => $e->getMessage()]);
        }
        break;

    // =======================
    // NEWSLETTER SUBSCRIPTION
    // =======================
    case "subscribeNewsletter":
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data["email"])) {
            echo json_encode(["error" => "Email is required"]);
            exit;
        }

        $email = trim(strtolower($data["email"]));
        $language = $data["language"] ?? "en";

        // Validation email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["error" => "Invalid email format"]);
            exit;
        }

        // Validation langue
        if (!in_array($language, ["en", "fr"])) {
            $language = "en";
        }

        // Récupération des infos utilisateur
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        try {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
            $stmt->execute([$email]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                if ($existing["status"] === "active") {
                    echo json_encode([
                        "success" => false, 
                        "message" => $language === "fr" ? "Cet email est déjà inscrit à notre newsletter" : "This email is already subscribed to our newsletter"
                    ]);
                    exit;
                } else {
                    // Réactiver l'abonnement
                    $stmt = $pdo->prepare("UPDATE newsletter_subscribers 
                                           SET status = 'active', language = ?, updated_at = NOW(), ip_address = ?, user_agent = ?
                                           WHERE email = ?");
                    $stmt->execute([$language, $ip_address, $user_agent, $email]);

                    // Log de l'action
                    $stmt = $pdo->prepare("INSERT INTO newsletter_logs (email, action, ip_address, user_agent) 
                                           VALUES (?, 'resubscribe', ?, ?)");
                    $stmt->execute([$email, $ip_address, $user_agent]);

                    echo json_encode([
                        "success" => true,
                        "message" => $language === "fr" ? "Votre abonnement a été réactivé avec succès !" : "Your subscription has been reactivated successfully!"
                    ]);
                }
            } else {
                // Nouvel abonnement
                $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, language, ip_address, user_agent) 
                                       VALUES (?, ?, ?, ?)");
                $stmt->execute([$email, $language, $ip_address, $user_agent]);

                // Log de l'action
                $stmt = $pdo->prepare("INSERT INTO newsletter_logs (email, action, ip_address, user_agent) 
                                       VALUES (?, 'subscribe', ?, ?)");
                $stmt->execute([$email, $ip_address, $user_agent]);

                echo json_encode([
                    "success" => true,
                    "message" => $language === "fr" ? "Merci ! Vous êtes maintenant inscrit à notre newsletter." : "Thank you! You're now subscribed to our newsletter."
                ]);
            }

        } catch (PDOException $e) {
            echo json_encode(["error" => "❌ Error subscribing to newsletter", "details" => $e->getMessage()]);
        }
        break;

    // =======================
    // NEWSLETTER UNSUBSCRIBE
    // =======================
    case "unsubscribeNewsletter":
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$data || !isset($data["email"])) {
            echo json_encode(["error" => "Email is required"]);
            exit;
        }

        $email = trim(strtolower($data["email"]));
        $language = $data["language"] ?? "en";

        try {
            $stmt = $pdo->prepare("UPDATE newsletter_subscribers 
                                   SET status = 'unsubscribed', updated_at = NOW() 
                                   WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                // Log de l'action
                $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                
                $stmt = $pdo->prepare("INSERT INTO newsletter_logs (email, action, ip_address, user_agent) 
                                       VALUES (?, 'unsubscribe', ?, ?)");
                $stmt->execute([$email, $ip_address, $user_agent]);

                echo json_encode([
                    "success" => true,
                    "message" => $language === "fr" ? "Vous avez été désabonné de notre newsletter." : "You have been unsubscribed from our newsletter."
                ]);
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => $language === "fr" ? "Cet email n'est pas inscrit à notre newsletter." : "This email is not subscribed to our newsletter."
                ]);
            }

        } catch (PDOException $e) {
            echo json_encode(["error" => "❌ Error unsubscribing from newsletter", "details" => $e->getMessage()]);
        }
        break;

    // =======================
    // GET NEWSLETTER STATS (pour admin)
    // =======================
    case "getNewsletterStats":
        try {
            $stats = [];

            // Total abonnés actifs
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM newsletter_subscribers WHERE status = 'active'");
            $stats['total_active'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Par langue
            $stmt = $pdo->query("SELECT language, COUNT(*) as count 
                                FROM newsletter_subscribers 
                                WHERE status = 'active' 
                                GROUP BY language");
            $stats['by_language'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Nouvelles inscriptions cette semaine
            $stmt = $pdo->query("SELECT COUNT(*) as count 
                                FROM newsletter_subscribers 
                                WHERE status = 'active' 
                                AND subscribed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $stats['this_week'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

            echo json_encode($stats);

        } catch (PDOException $e) {
            echo json_encode(["error" => "❌ Error fetching newsletter stats", "details" => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(["error" => "❌ Invalid action"]);
        break;
}
?>