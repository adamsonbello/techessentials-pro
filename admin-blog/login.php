<?php
// admin-blog/login.php - Page de connexion pure (sans redirection automatique)
session_start();

$BLOG_ADMIN_USERNAME = 'adams_blog_admin';
$BLOG_ADMIN_PASSWORD_HASH = '$2y$10$aQhs.woYrqCI5J6g88E4DuBRhfX4/mBVNeDaFmTckufsJsBhq417m';

// Configuration de la base de données blog
$DB_CONFIG = [
    'host' => 'localhost',
    'dbname' => 'techessentials_blog',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

// Sécurité anti-bruteforce
$max_attempts = 5;
$lockout_time = 900; // 15 minutes
$attempts_file = __DIR__ . '/login_attempts_blog.json';

// Fonctions de sécurité
function getLoginAttempts($file) {
    if (!file_exists($file)) return [];
    $content = file_get_contents($file);
    return $content ? json_decode($content, true) : [];
}

function saveLoginAttempts($file, $attempts) {
    file_put_contents($file, json_encode($attempts));
}

function cleanOldAttempts($attempts, $lockout_time) {
    $current_time = time();
    return array_filter($attempts, function($attempt) use ($current_time, $lockout_time) {
        return ($current_time - $attempt['time']) < $lockout_time;
    });
}

// Test de connexion à la base blog
function testBlogDBConnection($config) {
    try {
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Test simple
        $stmt = $pdo->query("SELECT COUNT(*) FROM admin_users");
        return ['success' => true, 'message' => 'Connexion base blog OK'];
        
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Erreur base blog: ' . $e->getMessage()];
    }
}

// PAS DE REDIRECTION AUTOMATIQUE - c'est la différence avec index.php
// La ligne suivante est SUPPRIMÉE volontairement :
// if (isset($_SESSION['blog_admin_logged']) && $_SESSION['blog_admin_logged'] === true) {
//     header('Location: dashboard.php');
//     exit;
// }

// Récupérer l'IP client
$client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Charger les tentatives
$login_attempts = getLoginAttempts($attempts_file);
$login_attempts = cleanOldAttempts($login_attempts, $lockout_time);

// Vérifier si IP bloquée
$ip_attempts = array_filter($login_attempts, function($attempt) use ($client_ip) {
    return $attempt['ip'] === $client_ip;
});

$blocked = count($ip_attempts) >= $max_attempts;
$time_remaining = 0;

if ($blocked && !empty($ip_attempts)) {
    $last_attempt = max(array_column($ip_attempts, 'time'));
    $time_remaining = $lockout_time - (time() - $last_attempt);
}

$error = '';
$success = '';
$db_status = testBlogDBConnection($DB_CONFIG);

// Traitement de la connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$blocked) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $BLOG_ADMIN_USERNAME && password_verify($password, $BLOG_ADMIN_PASSWORD_HASH)) {
        $_SESSION['blog_admin_logged'] = true;
        $_SESSION['blog_admin_username'] = $username;
        $_SESSION['blog_login_time'] = time();
        
        // Nettoyer les tentatives pour cette IP
        $login_attempts = array_filter($login_attempts, function($attempt) use ($client_ip) {
            return $attempt['ip'] !== $client_ip;
        });
        saveLoginAttempts($attempts_file, $login_attempts);
        
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Identifiants incorrects';
        
        // Enregistrer la tentative échouée
        $login_attempts[] = [
            'ip' => $client_ip,
            'time' => time(),
            'username' => $username
        ];
        saveLoginAttempts($attempts_file, $login_attempts);
        
        // Recalculer le blocage
        $ip_attempts = array_filter($login_attempts, function($attempt) use ($client_ip) {
            return $attempt['ip'] === $client_ip;
        });
        
        if (count($ip_attempts) >= $max_attempts) {
            $blocked = true;
            $time_remaining = $lockout_time;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Panel Blog Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        
        .login-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        
        .logo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        h1 {
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 2rem;
            font-size: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .login-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .alert-error {
            background: #fee;
            color: #c53030;
            border: 1px solid #feb2b2;
        }
        
        .alert-success {
            background: #f0fff4;
            color: #276749;
            border: 1px solid #9ae6b4;
        }
        
        .alert-info {
            background: #ebf8ff;
            color: #2a69ac;
            border: 1px solid #90cdf4;
        }
        
        .status-indicator {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-left: 0.5rem;
        }
        
        .status-success {
            background: #48bb78;
        }
        
        .status-error {
            background: #f56565;
        }
        
        .blocked-message {
            background: #fed7d7;
            color: #c53030;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .countdown {
            font-weight: bold;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">📝</div>
        <h1>Panel Blog Admin</h1>
        <p class="subtitle">Connexion sécurisée</p>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($blocked): ?>
            <div class="blocked-message">
                <strong>🔒 Accès temporairement bloqué</strong><br>
                Trop de tentatives de connexion échouées.<br>
                Temps restant: <span class="countdown" id="countdown"><?= $time_remaining ?></span> secondes
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                
                <button type="submit" class="login-btn">
                    Se connecter
                </button>
            </form>
        <?php endif; ?>
        
        <!-- Statut de la base de données -->
        <div class="status-indicator">
            <span>Base de données blog</span>
            <span>
                <?= $db_status['message'] ?>
                <span class="status-dot <?= $db_status['success'] ? 'status-success' : 'status-error' ?>"></span>
            </span>
        </div>
    </div>
    
    <?php if ($blocked): ?>
    <script>
        let timeRemaining = <?= $time_remaining ?>;
        const countdownEl = document.getElementById('countdown');
        
        const updateCountdown = () => {
            countdownEl.textContent = timeRemaining;
            timeRemaining--;
            
            if (timeRemaining < 0) {
                location.reload();
            }
        };
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
    </script>
    <?php endif; ?>
</body>
</html>