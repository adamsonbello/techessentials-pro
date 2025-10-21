<?php
/**
 * Interface d'upload d'images pour l'éditeur blog
 * Fichier : admin-blog/articles/upload-image.php
 */

session_start();
require_once '../includes/config.php';
require_once '../includes/image-optimizer.php';

// Vérifier authentification
if (!isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Non autorisé');
}

// Traiter l'upload si formulaire soumis
$upload_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $optimizer = new ImageOptimizer();
    $upload_result = $optimizer->optimize($_FILES['image']);
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload d'image - Blog Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        
        .upload-zone {
            border: 2px dashed #3498db;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .upload-zone:hover {
            border-color: #2980b9;
            background: #e8f4f8;
        }
        
        .upload-zone.dragover {
            border-color: #27ae60;
            background: #d5f4e6;
        }
        
        .upload-icon {
            font-size: 48px;
            color: #3498db;
            margin-bottom: 15px;
        }
        
        .upload-text {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .upload-hint {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        input[type="file"] {
            display: none;
        }
        
        .form-group {
            margin: 20px 0;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        input[type="text"], textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .btn {
            background: #3498db;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #2980b9;
        }
        
        .btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }
        
        .preview {
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .preview-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .result-success {
            background: #d5f4e6;
            border-left: 4px solid #27ae60;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        
        .result-error {
            background: #fadbd8;
            border-left: 4px solid #e74c3c;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .stat-value {
            color: #2c3e50;
            font-size: 24px;
            font-weight: bold;
        }
        
        .code-block {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 15px 0;
        }
        
        .copy-btn {
            background: #95a5a6;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            float: right;
            margin-bottom: 10px;
        }
        
        .copy-btn:hover {
            background: #7f8c8d;
        }
        
        .sizes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .size-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .size-name {
            font-weight: bold;
            color: #3498db;
            margin-bottom: 8px;
        }
        
        .size-dims {
            color: #7f8c8d;
            font-size: 12px;
            margin-bottom: 8px;
        }
        
        .size-saving {
            color: #27ae60;
            font-size: 14px;
            font-weight: bold;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .loading.active {
            display: block;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📸 Upload et Optimisation d'Image</h1>
        <p class="subtitle">Optimisation automatique : Compression + WebP + Thumbnails multiples</p>
        
        <?php if ($upload_result): ?>
            
            <?php if ($upload_result['success']): ?>
                <div class="result-success">
                    <h3>✅ Image optimisée avec succès !</h3>
                    
                    <div class="stats">
                        <div class="stat-card">
                            <div class="stat-label">Taille originale</div>
                            <div class="stat-value"><?= number_format($upload_result['original_size'] / 1024, 0) ?> KB</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Économie</div>
                            <div class="stat-value"><?= $upload_result['total_saving_percent'] ?>%</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Gain</div>
                            <div class="stat-value"><?= number_format($upload_result['total_saving'] / 1024, 0) ?> KB</div>
                        </div>
                    </div>
                    
                    <h4>📐 Versions générées :</h4>
                    <div class="sizes-grid">
                        <?php foreach ($upload_result['optimized_sizes'] as $size_name => $size_info): ?>
                            <div class="size-card">
                                <div class="size-name"><?= ucfirst($size_name) ?></div>
                                <div class="size-dims"><?= $size_info['width'] ?>×<?= $size_info['height'] ?>px</div>
                                <div class="size-saving">-<?= $size_info['compression_ratio'] ?>%</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <h4>🖼️ Aperçu (Medium) :</h4>
                    <div class="preview">
                        <img src="<?= $upload_result['urls']['medium']['jpeg'] ?>" class="preview-image" alt="Preview">
                    </div>
                    
                    <h4>📋 Code HTML à copier dans votre article :</h4>
                    <button class="copy-btn" onclick="copyCode('html-code')">📋 Copier</button>
                    <div class="code-block" id="html-code"><?php
                    $optimizer = new ImageOptimizer();
                    echo htmlspecialchars($optimizer->generateHtmlCode($upload_result['urls'], '', 'medium', true));
                    ?></div>
                    
                    <h4>🔗 URLs générées :</h4>
                    <div class="code-block">
<?php foreach ($upload_result['urls'] as $size => $urls): ?>
// <?= ucfirst($size) ?>

WebP: <?= $urls['webp'] ?>

JPEG: <?= $urls['jpeg'] ?>


<?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="result-error">
                    <h3>❌ Erreur</h3>
                    <p><?= htmlspecialchars($upload_result['error']) ?></p>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" id="upload-form">
            <div class="upload-zone" id="upload-zone">
                <div class="upload-icon">📁</div>
                <div class="upload-text">Cliquez pour sélectionner une image</div>
                <div class="upload-hint">ou glissez-déposez une image ici</div>
                <div class="upload-hint">(JPEG, PNG, GIF, WebP - Max 10 MB)</div>
                <input type="file" id="file-input" name="image" accept="image/*" required>
            </div>
            
            <div class="form-group">
                <label for="alt-text">Texte alternatif (ALT) :</label>
                <input type="text" id="alt-text" name="alt_text" placeholder="Description de l'image pour l'accessibilité et le SEO">
            </div>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Optimisation en cours...</p>
            </div>
            
            <button type="submit" class="btn" id="submit-btn">🚀 Optimiser l'image</button>
        </form>
    </div>
    
    <script>
        const uploadZone = document.getElementById('upload-zone');
        const fileInput = document.getElementById('file-input');
        const uploadForm = document.getElementById('upload-form');
        const loading = document.getElementById('loading');
        const submitBtn = document.getElementById('submit-btn');
        
        // Clic sur la zone ouvre le sélecteur
        uploadZone.addEventListener('click', () => fileInput.click());
        
        // Afficher nom du fichier sélectionné
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                const fileName = e.target.files[0].name;
                uploadZone.querySelector('.upload-text').textContent = `Fichier : ${fileName}`;
            }
        });
        
        // Drag & Drop
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
        
        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });
        
        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                const fileName = e.dataTransfer.files[0].name;
                uploadZone.querySelector('.upload-text').textContent = `Fichier : ${fileName}`;
            }
        });
        
        // Afficher loading pendant upload
        uploadForm.addEventListener('submit', () => {
            loading.classList.add('active');
            submitBtn.disabled = true;
        });
        
        // Fonction copier code
        function copyCode(elementId) {
            const code = document.getElementById(elementId).textContent;
            navigator.clipboard.writeText(code).then(() => {
                alert('✅ Code copié dans le presse-papiers !');
            });
        }
    </script>
</body>
</html>