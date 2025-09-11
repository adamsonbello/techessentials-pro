<?php
// admin-blog/media/upload.php - Gestionnaire de médias et upload d'images
session_start();

// Vérification auth
if (!isset($_SESSION['blog_admin_logged']) || $_SESSION['blog_admin_logged'] !== true) {
    header('Location: ../index.php');
    exit;
}

// Configuration
$DB_CONFIG = [
    'host' => 'localhost',
    'dbname' => 'techessentials_blog',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

// Connexion BDD
try {
    $dsn = "mysql:host={$DB_CONFIG['host']};dbname={$DB_CONFIG['dbname']};charset={$DB_CONFIG['charset']}";
    $blogDB = new PDO($dsn, $DB_CONFIG['username'], $DB_CONFIG['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Erreur BDD: " . $e->getMessage());
}

// Configuration upload
$upload_dir = '../../uploads/blog/'; // Dossier uploads à la racine
$upload_url = '/techessentialspro/uploads/blog/';
$allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$max_file_size = 5 * 1024 * 1024; // 5MB

// Créer dossier s'il n'existe pas
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$message = '';
$error = '';

// Traitement upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    try {
        $file = $_FILES['media_file'];
        
        // Vérifications basiques
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erreur upload: " . $file['error']);
        }
        
        if ($file['size'] > $max_file_size) {
            throw new Exception("Fichier trop volumineux (max 5MB)");
        }
        
        // Vérification type
        $file_info = pathinfo($file['name']);
        $extension = strtolower($file_info['extension']);
        
        if (!in_array($extension, $allowed_types)) {
            throw new Exception("Type de fichier non autorisé. Autorisés: " . implode(', ', $allowed_types));
        }
        
        // Vérification image
        $image_info = getimagesize($file['tmp_name']);
        if (!$image_info) {
            throw new Exception("Le fichier n'est pas une image valide");
        }
        
        // Génération nom unique
        $filename = time() . '_' . uniqid() . '.' . $extension;
        $file_path = $upload_dir . $filename;
        $file_url = $upload_url . $filename;
        
        // Upload
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            throw new Exception("Erreur lors de la sauvegarde du fichier");
        }
        
        // Enregistrement en BDD
        $alt_text = trim($_POST['alt_text'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        $stmt = $blogDB->prepare("
            INSERT INTO media (filename, original_name, mime_type, file_size, width, height, alt_text, description, uploaded_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $filename,
            $file['name'],
            $image_info['mime'],
            $file['size'],
            $image_info[0], // width
            $image_info[1], // height
            $alt_text,
            $description,
            $_SESSION['blog_admin_user'] ?? 'Admin'
        ]);
        
        $message = "Image uploadée avec succès ! URL: " . $file_url;
        
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

// Récupération des médias existants
$media_list = $blogDB->query("
    SELECT * FROM media 
    ORDER BY created_at DESC 
    LIMIT 50
")->fetchAll();

$admin_user = $_SESSION['blog_admin_user'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Médiathèque - Blog Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #4CAF50;
            --error-color: #f44336;
            --text-color: #333;
            --text-light: #666;
            --border-color: #e1e5e9;
            --background-light: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background-light);
            color: var(--text-color);
        }

        .header {
            background: var(--white);
            padding: 15px 30px;
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .back-btn {
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: var(--secondary-color);
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .upload-section {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .upload-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 30px;
        }

        .upload-content {
            padding: 30px;
        }

        .upload-area {
            border: 2px dashed var(--border-color);
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-area:hover,
        .upload-area.dragover {
            border-color: var(--primary-color);
            background: rgba(102, 126, 234, 0.05);
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--text-light);
            margin-bottom: 15px;
        }

        .upload-text {
            font-size: 1.1rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .upload-help {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .message.success {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .message.error {
            background: rgba(244, 67, 54, 0.1);
            color: var(--error-color);
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

        .media-gallery {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .gallery-header {
            padding: 20px 30px;
            border-bottom: 1px solid var(--border-color);
        }

        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 30px;
        }

        .media-item {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .media-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .media-preview {
            width: 100%;
            height: 180px;
            background: var(--background-light);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .media-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .media-item:hover .media-preview img {
            transform: scale(1.05);
        }

        .media-info {
            padding: 15px;
        }

        .media-name {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 0.9rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .media-meta {
            font-size: 0.8rem;
            color: var(--text-light);
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .media-url {
            background: var(--background-light);
            padding: 8px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.75rem;
            color: var(--text-light);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .media-url:hover {
            background: var(--primary-color);
            color: white;
        }

        .empty-gallery {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        /* Modal pour prévisualisation */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
        }

        .modal-content {
            position: relative;
            margin: 5% auto;
            background: var(--white);
            border-radius: 8px;
            max-width: 90%;
            max-height: 90%;
            overflow: hidden;
        }

        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-close {
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--text-light);
        }

        .modal-body {
            padding: 20px;
            text-align: center;
        }

        .modal-image {
            max-width: 100%;
            max-height: 70vh;
            border-radius: 4px;
        }

        .modal-info {
            margin-top: 20px;
            text-align: left;
        }

        @media (max-width: 768px) {
            .media-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
                padding: 20px;
            }

            .container {
                padding: 0 15px;
            }

            .upload-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <a href="../dashboard.php" class="back-btn">← Dashboard</a>
            <h1>Médiathèque</h1>
        </div>
        <div>Connecté: <?php echo htmlspecialchars($admin_user); ?></div>
    </div>

    <div class="container">
        <!-- Section Upload -->
        <div class="upload-section">
            <div class="upload-header">
                <h2>📁 Upload d'images</h2>
                <p>Téléchargez des images pour vos articles</p>
            </div>

            <?php if ($message): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="upload-content">
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="upload-area" onclick="document.getElementById('media_file').click()">
                        <div class="upload-icon">📷</div>
                        <div class="upload-text">Cliquez ou glissez-déposez vos images ici</div>
                        <div class="upload-help">JPG, PNG, GIF, WebP • Max 5MB</div>
                        <input type="file" id="media_file" name="media_file" accept="image/*" style="display: none;" onchange="handleFileSelect()">
                    </div>

                    <div id="fileInfo" style="display: none; margin-top: 20px;">
                        <div class="form-group">
                            <label for="alt_text">Texte alternatif (Alt)</label>
                            <input type="text" id="alt_text" name="alt_text" placeholder="Description de l'image pour l'accessibilité">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3" placeholder="Description détaillée de l'image"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">📤 Uploader l'image</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Galerie -->
        <div class="media-gallery">
            <div class="gallery-header">
                <h2>🖼️ Images disponibles</h2>
                <p><?php echo count($media_list); ?> image(s) dans votre médiathèque</p>
            </div>

            <div class="media-grid">
                <?php if (empty($media_list)): ?>
                    <div class="empty-gallery">
                        <div style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;">🖼️</div>
                        <h3>Aucune image</h3>
                        <p>Uploadez votre première image ci-dessus</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($media_list as $media): ?>
                        <div class="media-item" onclick="showMediaModal('<?php echo $media['id']; ?>')">
                            <div class="media-preview">
                                <img src="<?php echo $upload_url . htmlspecialchars($media['filename']); ?>" 
                                     alt="<?php echo htmlspecialchars($media['alt_text']); ?>"
                                     loading="lazy">
                            </div>
                            <div class="media-info">
                                <div class="media-name"><?php echo htmlspecialchars($media['original_name']); ?></div>
                                <div class="media-meta">
                                    <span><?php echo $media['width']; ?>×<?php echo $media['height']; ?></span>
                                    <span><?php echo round($media['file_size']/1024); ?> KB</span>
                                </div>
                                <div class="media-url" onclick="event.stopPropagation(); copyToClipboard('<?php echo $upload_url . htmlspecialchars($media['filename']); ?>')" title="Cliquer pour copier l'URL">
                                    <?php echo $upload_url . htmlspecialchars($media['filename']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal prévisualisation -->
    <div id="mediaModal" class="modal" onclick="closeModal()">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3 id="modalTitle">Prévisualisation</h3>
                <span class="modal-close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>

    <script>
        // Gestion drag & drop
        const uploadArea = document.querySelector('.upload-area');
        
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                document.getElementById('media_file').files = files;
                handleFileSelect();
            }
        });

        // Affichage infos fichier sélectionné
        function handleFileSelect() {
            const fileInput = document.getElementById('media_file');
            const fileInfo = document.getElementById('fileInfo');
            
            if (fileInput.files.length > 0) {
                const file = fileInput.files[0];
                fileInfo.style.display = 'block';
                
                // Suggestion alt text basée sur le nom du fichier
                const altField = document.getElementById('alt_text');
                if (altField.value === '') {
                    const fileName = file.name.replace(/\.[^/.]+$/, "").replace(/[-_]/g, ' ');
                    altField.value = fileName;
                }
            }
        }

        // Copier URL dans le presse-papier
        function copyToClipboard(url) {
            navigator.clipboard.writeText(url).then(() => {
                alert('URL copiée dans le presse-papier !');
            }).catch(() => {
                // Fallback pour navigateurs plus anciens
                const textArea = document.createElement('textarea');
                textArea.value = url;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                alert('URL copiée !');
            });
        }

        // Modal de prévisualisation
        const mediaData = <?php echo json_encode($media_list); ?>;

        function showMediaModal(mediaId) {
            const media = mediaData.find(m => m.id == mediaId);
            if (!media) return;

            const modal = document.getElementById('mediaModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');

            modalTitle.textContent = media.original_name;
            
            const imageUrl = '<?php echo $upload_url; ?>' + media.filename;
            
            modalBody.innerHTML = `
                <img src="${imageUrl}" class="modal-image" alt="${media.alt_text}">
                <div class="modal-info">
                    <p><strong>Dimensions:</strong> ${media.width} × ${media.height} pixels</p>
                    <p><strong>Taille:</strong> ${Math.round(media.file_size/1024)} KB</p>
                    <p><strong>Type:</strong> ${media.mime_type}</p>
                    <p><strong>Uploadé le:</strong> ${new Date(media.created_at).toLocaleDateString('fr-FR')}</p>
                    ${media.alt_text ? `<p><strong>Alt text:</strong> ${media.alt_text}</p>` : ''}
                    ${media.description ? `<p><strong>Description:</strong> ${media.description}</p>` : ''}
                    <br>
                    <p><strong>URL:</strong></p>
                    <div style="background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; word-break: break-all; cursor: pointer;" onclick="copyToClipboard('${imageUrl}')" title="Cliquer pour copier">
                        ${imageUrl}
                    </div>
                </div>
            `;

            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('mediaModal').style.display = 'none';
        }

        // Fermeture modal avec Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>