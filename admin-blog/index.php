<?php
// admin-blog/index.php - Système de connexion blog sécurisé
session_start();

// Ligne 6-7, changez :
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

// Vérifier si déjà connecté
if (isset($_SESSION['blog_admin_logged']) && $_SESSION['blog_admin_logged'] === true) {
    header('Location: dashboard.php');
    exit;
} 

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

// Traitement du formulaire
$error = '';
$db_status = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$blocked) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } 
    elseif ($username === $BLOG_ADMIN_USERNAME && password_verify($password, $BLOG_ADMIN_PASSWORD_HASH)) {
        // Test connexion base avant connexion
        $db_status = testBlogDBConnection($DB_CONFIG);
        
        if ($db_status['success']) {
            // Connexion réussie
            $_SESSION['blog_admin_logged'] = true;
            $_SESSION['blog_admin_user'] = $username;
            $_SESSION['blog_admin_login_time'] = time();
            $_SESSION['blog_admin_ip'] = $client_ip;
            
            // Nettoyer les tentatives pour cette IP
            $login_attempts = array_filter($login_attempts, function($attempt) use ($client_ip) {
                return $attempt['ip'] !== $client_ip;
            });
            saveLoginAttempts($attempts_file, $login_attempts);
            
            // Redirection
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Erreur de connexion à la base de données blog';
        }
    } 
    else {
        // Échec de connexion
        $error = 'Identifiants incorrects';
        
        // Enregistrer la tentative
        $login_attempts[] = [
            'ip' => $client_ip,
            'time' => time(),
            'username' => $username,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
        
        saveLoginAttempts($attempts_file, $login_attempts);
        
        // Vérifier limite atteinte
        $ip_attempts = array_filter($login_attempts, function($attempt) use ($client_ip) {
            return $attempt['ip'] === $client_ip;
        });
        
        if (count($ip_attempts) >= $max_attempts) {
            $error = "Trop de tentatives. Accès bloqué pour " . ceil($lockout_time / 60) . " minutes.";
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
    <title>Blog Admin - TechEssentials Pro</title>
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
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 420px;
            position: relative;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .logo h1 {
            color: #667eea;
            font-size: 1.9rem;
            margin-bottom: 8px;
            font-weight: 700;
        }
        
        .logo p {
            color: #6c757d;
            font-size: 1rem;
            font-weight: 500;
        }
        
        .blog-badge {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-top: 8px;
            display: inline-block;
        }
        
        .security-info {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 25px;
            font-size: 0.9rem;
        }
        
        .security-info .shield {
            color: #0066cc;
            font-size: 1.3rem;
            margin-right: 8px;
        }
        
        .db-status {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            text-align: center;
        }
        
        .db-status.success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .db-status.error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-group input:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn-login:active:not(:disabled) {
            transform: translateY(0);
        }
        
        .btn-login:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #f5c6cb;
        }
        
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #ffeaa7;
        }
        
        .attempts-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .attempts-info strong {
            color: #495057;
        }
        
        .countdown {
            font-weight: bold;
            color: #dc3545;
            font-size: 1.1rem;
        }
        
        .version-info {
            position: absolute;
            bottom: -25px;
            left: 0;
            right: 0;
            text-align: center;
            color: rgba(255,255,255,0.7);
            font-size: 0.8rem;
        }
        
        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                padding: 30px;
            }
            
            .logo h1 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>📝 Blog Admin</h1>
            <p>TechEssentials Pro</p>
            <div class="blog-badge">Interface de gestion blog</div>
        </div>
        
        <div class="security-info">
            <span class="shield">🛡️</span> <strong>Connexion sécurisée</strong><br>
            Accès dédié à l'administration du blog<br>
            Protection anti-force brute active
        </div>
        
        <?php if ($db_status): ?>
            <div class="db-status <?php echo $db_status['success'] ? 'success' : 'error'; ?>">
                <?php echo $db_status['message']; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($blocked): ?>
            <div class="error">
                🚫 Accès temporairement bloqué<br>
                <span class="countdown" id="countdown"><?php echo gmdate("i:s", $time_remaining); ?></span> restantes
            </div>
        <?php elseif ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (count($ip_attempts) > 0 && count($ip_attempts) < $max_attempts && !$blocked): ?>
            <div class="warning">
                ⚠️ Tentative <?php echo count($ip_attempts); ?>/<?php echo $max_attempts; ?><br>
                Plus que <?php echo ($max_attempts - count($ip_attempts)); ?> tentatives
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">👤 Nom d'utilisateur</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required 
                    placeholder="adams_blog_admin"
                    <?php echo $blocked ? 'disabled' : ''; ?>
                >
            </div>
            
            <div class="form-group">
                <label for="password">🔐 Mot de passe</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required 
                    placeholder="Votre mot de passe sécurisé"
                    <?php echo $blocked ? 'disabled' : ''; ?>
                >
            </div>
            
            <button 
                type="submit" 
                class="btn-login" 
                <?php echo $blocked ? 'disabled' : ''; ?>
            >
                <?php echo $blocked ? '🚫 Accès bloqué' : '🔓 Accéder au blog admin'; ?>
            </button>
        </form>
        
        <div class="attempts-info">
            <strong>🔒 Sécurité :</strong> <?php echo $max_attempts; ?> tentatives max par IP<br>
            <strong>⏱️ Blocage :</strong> <?php echo ($lockout_time / 60); ?> minutes<br>
            <strong>🌐 Votre IP :</strong> <?php echo htmlspecialchars($client_ip); ?>
            <br><br>
            <strong>📊 Base :</strong> techessentials_blog<br>
            <strong>👥 Admin :</strong> adams_blog_admin
        </div>
        
        <div class="version-info">
            TechEssentials Pro Blog Admin v1.0
        </div>
    </div>

    <?php if ($blocked && $time_remaining > 0): ?>
    <script>
        let timeRemaining = <?php echo $time_remaining; ?>;
        const countdownEl = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            timeRemaining--;
            
            if (timeRemaining <= 0) {
                clearInterval(timer);
                location.reload();
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