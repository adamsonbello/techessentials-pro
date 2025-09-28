// Test simple d'envoi email

<?php
$test_email = mail(
    "bello.adams@gmail.com", 
    "Test TechEssentials", 
    "Test email depuis le serveur",
    "From: contact@techessentialspro.com"
);

if ($test_email) {
    echo "Email envoyé avec succès";
} else {
    echo "Erreur d'envoi email";
}


/**
 * Test de diagnostic email - TechEssentials Pro
 * Fichier temporaire pour diagnostiquer les problèmes d'envoi email
 */

echo "<h1>Test Email - TechEssentials Pro</h1>";
echo "<hr>";

// 1. Vérifier la configuration PHP
echo "<h2>1. Configuration PHP Mail</h2>";
echo "<strong>Sendmail Path:</strong> " . ini_get('sendmail_path') . "<br>";
echo "<strong>SMTP:</strong> " . ini_get('SMTP') . "<br>";
echo "<strong>SMTP Port:</strong> " . ini_get('smtp_port') . "<br>";
echo "<strong>Mail Function:</strong> " . (function_exists('mail') ? 'Disponible ✓' : 'Indisponible ✗') . "<br>";
echo "<hr>";

// 2. Test d'envoi simple
echo "<h2>2. Test Envoi Simple</h2>";
$to = "your-email@example.com"; // MODIFIER avec votre vraie adresse
$subject = "Test TechEssentials Pro";
$message = "Test d'envoi depuis le serveur TechEssentials Pro";
$headers = "From: noreply@techessentialspro.com\r\nReply-To: hello@techessentialspro.com";

$result = mail($to, $subject, $message, $headers);

if ($result) {
    echo "<span style='color: green;'>✓ Email simple envoyé avec succès</span><br>";
} else {
    echo "<span style='color: red;'>✗ Échec envoi email simple</span><br>";
}

echo "<hr>";

// 3. Test avec headers complets
echo "<h2>3. Test avec Headers HTML</h2>";
$html_message = "
<!DOCTYPE html>
<html>
<head><title>Test HTML</title></head>
<body>
<h1>Test Email HTML</h1>
<p>Ceci est un test d'email HTML depuis TechEssentials Pro.</p>
</body>
</html>
";

$html_headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    'From: TechEssentials Pro <noreply@techessentialspro.com>',
    'Reply-To: hello@techessentialspro.com',
    'X-Mailer: PHP/' . phpversion()
];

$html_result = mail($to, "Test HTML TechEssentials", $html_message, implode("\r\n", $html_headers));

if ($html_result) {
    echo "<span style='color: green;'>✓ Email HTML envoyé avec succès</span><br>";
} else {
    echo "<span style='color: red;'>✗ Échec envoi email HTML</span><br>";
}

echo "<hr>";

// 4. Vérifier les logs d'erreur
echo "<h2>4. Logs d'Erreur PHP</h2>";
$error_log = ini_get('error_log');
echo "<strong>Fichier de log:</strong> " . ($error_log ?: 'Par défaut') . "<br>";

if ($error_log && file_exists($error_log)) {
    $log_lines = array_slice(file($error_log), -10);
    echo "<strong>10 dernières lignes du log:</strong><br>";
    echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 200px; overflow-y: auto;'>";
    echo htmlspecialchars(implode('', $log_lines));
    echo "</pre>";
} else {
    echo "Aucun log d'erreur accessible<br>";
}

echo "<hr>";

// 5. Test de connectivité SMTP (si configuré)
echo "<h2>5. Test Connectivité</h2>";
$smtp_host = ini_get('SMTP');
if ($smtp_host && $smtp_host !== 'localhost') {
    $smtp_port = ini_get('smtp_port') ?: 25;
    $connection = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 5);
    
    if ($connection) {
        echo "<span style='color: green;'>✓ Connexion SMTP réussie vers $smtp_host:$smtp_port</span><br>";
        fclose($connection);
    } else {
        echo "<span style='color: red;'>✗ Échec connexion SMTP: $errstr ($errno)</span><br>";
    }
} else {
    echo "Configuration SMTP locale (sendmail)<br>";
}

echo "<hr>";

// 6. Recommandations
echo "<h2>6. Solutions Possibles</h2>";
echo "<ul>";
echo "<li><strong>Si aucun email reçu:</strong> Vérifier le dossier spam/courrier indésirable</li>";
echo "<li><strong>Si erreur sendmail:</strong> Installer/configurer sendmail ou postfix sur le serveur</li>";
echo "<li><strong>Pour serveur local:</strong> Utiliser un service SMTP externe (Gmail, SendGrid, etc.)</li>";
echo "<li><strong>Alternative:</strong> Utiliser PHPMailer avec authentification SMTP</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Instructions:</strong></p>";
echo "<ol>";
echo "<li>Modifier l'adresse email de test ci-dessus avec votre vraie adresse</li>";
echo "<li>Exécuter ce script: <code>http://localhost/techessentialspro/test-email.php</code></li>";
echo "<li>Vérifier votre boîte mail (et le dossier spam)</li>";
echo "<li>Si ça ne marche pas, configurer SMTP externe ou PHPMailer</li>";
echo "</ol>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1, h2 { color: #333; }
hr { margin: 20px 0; }
code { background: #f0f0f0; padding: 2px 4px; border-radius: 3px; }
ul, ol { margin-left: 20px; }
</style>

?>