<?php
// admin/index.php - Page de connexion admin sécurisée
session_start();

// ===============================
// CONFIGURATION ADMIN SÉCURISÉE
// ===============================

// 🔐 CHANGEZ CES VALEURS POUR VOTRE SÉCURITÉ !
$ADMIN_USERNAME = 'adams_admin';

// Pour générer un nouveau hash, utilisez ce code PHP :
// echo password_hash('votre_nouveau_mot_de_passe', PASSWORD_DEFAULT);

// Hash sécurisé de votre mot de passe (remplacez par le vôtre)
$ADMIN_PASSWORD_HASH = '$2y$10$7VFaAz1CKiY2R4Ce0sb9YuhNBFdfXoPl2TXcWZW3HVUcp9cz33Soa'; 



// ===============================
// SÉCURITÉ ADDITIONNELLE
// ===============================

// Protection contre les attaques par force brute
$max_attempts = 5;
$lockout_time = 900; // 15 minutes

// Fichier pour stocker les tentatives (à créer dans un dossier non accessible)
$attempts_file = __DIR__ . '/login_attempts.json';

// Fonction pour lire les tentatives
function getLoginAttempts($file) {
    if (!file_exists($file)) {
        return [];
    }
    $content = file_get_contents($file);
    return $content ? json_decode($content, true) : [];
}

// Fonction pour sauvegarder les tentatives
function saveLoginAttempts($file, $attempts) {
    file_put_contents($file, json_encode($attempts));
}

// Fonction pour nettoyer les anciennes tentatives
function cleanOldAttempts($attempts, $lockout_time) {
    $current_time = time();
    return array_filter($attempts, function($attempt) use ($current_time, $lockout_time) {
        return ($current_time - $attempt['time']) < $lockout_time;
    });
}

// Vérifier si déjà connecté
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    header('Location: newsletter.php');
    exit;
}

// Récupérer l'IP du client
$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Charger les tentatives de connexion
$login_attempts = getLoginAttempts($attempts_file);
$login_attempts = cleanOldAttempts($login_attempts, $lockout_time);

// Vérifier si l'IP est bloquée
$ip_attempts = array_filter($login_attempts, function($attempt) use ($client_ip) {
    return $attempt['ip'] === $client_ip;
});

if (count($ip_attempts) >= $max_attempts) {
    $error = "Trop de tentatives de connexion. Réessayez dans " . ceil($lockout_time / 60) . " minutes.";
    $blocked = true;
} else {
    $blocked = false;
}

// Traitement du formulaire de connexion
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$blocked) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validation des champs
    if (empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } 
    // Vérification des identifiants
    elseif ($username === $ADMIN_USERNAME && password_verify($password, $ADMIN_PASSWORD_HASH)) {
        // Connexion réussie
        $_SESSION['admin_logged'] = true;
        $_SESSION['admin_user'] = $username;
        $_SESSION['admin_login_time'] = time();
        $_SESSION['admin_ip'] = $client_ip;
        
        // Nettoyer les tentatives pour cette IP
        $login_attempts = array_filter($login_attempts, function($attempt) use ($client_ip) {
            return $attempt['ip'] !== $client_ip;
        });
        saveLoginAttempts($attempts_file, $login_attempts);
        
        // Redirection
       header('Location: dashboard.php');
        exit;
    } 
    else {
        // Connexion échouée
        $error = 'Identifiants incorrects';
        
        // Enregistrer la tentative échouée
        $login_attempts[] = [
            'ip' => $client_ip,
            'time' => time(),
            'username' => $username,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
        
        saveLoginAttempts($attempts_file, $login_attempts);
        
        // Vérifier si on atteint la limite
        $ip_attempts = array_filter($login_attempts, function($attempt) use ($client_ip) {
            return $attempt['ip'] === $client_ip;
        });
        
        if (count($ip_attempts) >= $max_attempts) {
            $error = "Trop de tentatives échouées. Accès bloqué pour " . ceil($lockout_time / 60) . " minutes.";
            $blocked = true;
        }
    }
}

// Calculer le temps restant si bloqué
$time_remaining = 0;
if ($blocked && !empty($ip_attempts)) {
    $last_attempt = max(array_column($ip_attempts, 'time'));
    $time_remaining = $lockout_time - (time() - $last_attempt);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - TechEssentials Pro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #667eea;
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .logo p {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .security-info {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        
        .security-info .shield {
            color: #0066cc;
            font-size: 1.2rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group input:disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        
        .btn-login {
            width: 100%;
            background: #667eea;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn-login:hover:not(:disabled) {
            background: #5a6fd8;
        }
        
        .btn-login:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .attempts-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px;
            margin-top: 15px;
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .countdown {
            font-weight: bold;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🔐 Admin Panel</h1>
            <p>TechEssentials Pro</p>
        </div>
        
        <div class="security-info">
            <span class="shield">🛡️</span> <strong>Connexion sécurisée</strong><br>
            Protection anti force brute activée
        </div>
        
        <?php if ($blocked): ?>
            <div class="error">
                🚫 Accès temporairement bloqué<br>
                <span class="countdown" id="countdown"><?php echo gmdate("i:s", $time_remaining); ?></span> restantes
            </div>
        <?php elseif ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (count($ip_attempts) > 0 && count($ip_attempts) < $max_attempts): ?>
            <div class="warning">
                ⚠️ Tentative <?php echo count($ip_attempts); ?>/<?php echo $max_attempts; ?><br>
                Plus que <?php echo ($max_attempts - count($ip_attempts)); ?> tentatives
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required 
                    <?php echo $blocked ? 'disabled' : ''; ?>
                >
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    <?php echo $blocked ? 'disabled' : ''; ?>
                >
            </div>
            
            <button 
                type="submit" 
                class="btn-login" 
                <?php echo $blocked ? 'disabled' : ''; ?>
            >
                <?php echo $blocked ? '🚫 Bloqué' : '🔑 Se connecter'; ?>
            </button>
        </form>
        
        <div class="attempts-info">
            <strong>Sécurité :</strong> <?php echo $max_attempts; ?> tentatives max par IP<br>
            <strong>Blocage :</strong> <?php echo ($lockout_time / 60); ?> minutes<br>
            <strong>Votre IP :</strong> <?php echo htmlspecialchars($client_ip); ?>
        </div>
    </div>

    <?php if ($blocked && $time_remaining > 0): ?>
    <script>
        // Compte à rebours en temps réel
        let timeRemaining = <?php echo $time_remaining; ?>;
        const countdownEl = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            timeRemaining--;
            
            if (timeRemaining <= 0) {
                clearInterval(timer);
                location.reload(); // Recharger la page quand le temps est écoulé
                return;
            }
            
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            countdownEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }, 1000);
    </script>
    <?php endif; ?>
</body>
</html>