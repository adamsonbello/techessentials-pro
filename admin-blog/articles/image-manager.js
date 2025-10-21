/**
 * Gestionnaire d'images optimisées pour l'éditeur d'articles
 * Fichier : admin-blog/articles/image-manager.js
 */

class ImageManager {
    constructor() {
        this.modal = null;
        this.currentEditor = null;
        this.selectedFile = null;
        this.init();
    }
    
    init() {
        // Créer le modal HTML
        this.createModal();
        this.modal = document.getElementById('image-modal');
        this.setupEventListeners();
    }
    
    createModal() {
        const modalHTML = `
            <div id="image-modal" class="img-modal">
                <div class="img-modal-content">
                    <div class="img-modal-header">
                        <h3>Gestionnaire d'images</h3>
                        <button class="img-close-modal">&times;</button>
                    </div>
                    <div class="img-modal-body">
                        <div class="img-upload-zone" id="img-upload-zone">
                            <div style="font-size:48px;margin-bottom:10px;">📁</div>
                            <div style="font-size:18px;color:#2c3e50;margin-bottom:10px;">Glissez une image ou cliquez</div>
                            <div style="color:#7f8c8d;font-size:14px;">(JPEG, PNG, GIF, WebP - Max 10 MB)</div>
                            <input type="file" id="img-file-input" accept="image/*" style="display:none;">
                        </div>
                        
                        <div class="img-preview-area" style="display:none;">
                            <img id="img-preview" src="" alt="Preview">
                            <div class="img-preview-stats"></div>
                        </div>
                        
                        <div class="img-form-group">
                            <label>Texte alternatif (ALT) :</label>
                            <input type="text" id="img-alt-text" placeholder="Description de l'image">
                        </div>
                        
                        <div class="img-upload-progress" style="display:none;">
                            <div class="img-progress-bar">
                                <div class="img-progress-fill"></div>
                            </div>
                            <p>Optimisation en cours...</p>
                        </div>
                        
                        <button id="img-upload-btn" class="img-btn-primary">
                            📤 Optimiser et insérer
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        const styleHTML = `
                <style>
                .img-modal {
                    display: none;
                    position: fixed;
                    z-index: 10000;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.7);
                    align-items: center;
                    justify-content: center;
                }
                .img-modal.active {
                    display: flex !important;
                }
                .img-modal-content {
                    background: white;
                    border-radius: 12px;
                    width: 90%;
                    max-width: 700px;
                    max-height: 90vh;
                    overflow: auto;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                }
                .img-modal-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 20px;
                    border-bottom: 1px solid #e0e0e0;
                }
                .img-modal-header h3 {
                    margin: 0;
                    color: #2c3e50;
                }
                .img-close-modal {
                    background: none;
                    border: none;
                    font-size: 28px;
                    cursor: pointer;
                    color: #7f8c8d;
                    line-height: 1;
                }
                .img-close-modal:hover {
                    color: #2c3e50;
                }
                .img-modal-body {
                    padding: 20px;
                }
                .img-upload-zone {
                    border: 2px dashed #3498db;
                    border-radius: 8px;
                    padding: 40px;
                    text-align: center;
                    background: #f8f9fa;
                    cursor: pointer;
                    margin-bottom: 20px;
                    transition: all 0.3s;
                }
                .img-upload-zone:hover {
                    border-color: #2980b9;
                    background: #e8f4f8;
                }
                .img-preview-area {
                    margin: 20px 0;
                    padding: 20px;
                    background: #f8f9fa;
                    border-radius: 8px;
                }
                .img-preview-area img {
                    max-width: 100%;
                    height: auto;
                    border-radius: 8px;
                    margin-bottom: 15px;
                }
                .img-preview-stats {
                    color: #7f8c8d;
                    font-size: 14px;
                }
                .img-form-group {
                    margin: 20px 0;
                }
                .img-form-group label {
                    display: block;
                    margin-bottom: 8px;
                    color: #2c3e50;
                    font-weight: 500;
                }
                .img-form-group input {
                    width: 100%;
                    padding: 10px;
                    border: 1px solid #ddd;
                    border-radius: 6px;
                    box-sizing: border-box;
                }
                .img-upload-progress {
                    margin: 20px 0;
                    text-align: center;
                }
                .img-progress-bar {
                    width: 100%;
                    height: 8px;
                    background: #e0e0e0;
                    border-radius: 4px;
                    overflow: hidden;
                    margin-bottom: 10px;
                }
                .img-progress-fill {
                    height: 100%;
                    background: linear-gradient(90deg, #3498db, #2ecc71);
                    animation: img-progress 1.5s infinite;
                }
                @keyframes img-progress {
                    0% { width: 0%; }
                    50% { width: 100%; }
                    100% { width: 0%; }
                }
                .img-btn-primary {
                    width: 100%;
                    background: #3498db;
                    color: white;
                    padding: 14px;
                    border: none;
                    border-radius: 6px;
                    font-size: 16px;
                    cursor: pointer;
                    transition: background 0.3s;
                }
                .img-btn-primary:hover {
                    background: #2980b9;
                }
                .img-btn-primary:disabled {
                    background: #95a5a6;
                    cursor: not-allowed;
                }
            </style>
        `;
        
        document.body.insertAdjacentHTML('beforeend', styleHTML);
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
    
    setupEventListeners() {
        // Bouton ouvrir modal
        const openBtn = document.getElementById('insert-image-btn');
        if (openBtn) {
            openBtn.addEventListener('click', () => this.open());
        }
        
        // Bouton fermer modal
        const closeBtn = document.querySelector('.img-close-modal');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close());
        }
        
        // Fermer si clic en dehors
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) this.close();
        });
        
        // Upload zone
        const uploadZone = document.getElementById('img-upload-zone');
        const fileInput = document.getElementById('img-file-input');
        
        if (uploadZone && fileInput) {
            uploadZone.addEventListener('click', () => fileInput.click());
            
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    this.selectedFile = e.target.files[0];
                    this.previewFile(e.target.files[0]);
                }
            });
            
            // Drag & Drop
            uploadZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadZone.style.borderColor = '#27ae60';
            });
            
            uploadZone.addEventListener('dragleave', () => {
                uploadZone.style.borderColor = '#3498db';
            });
            
            uploadZone.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadZone.style.borderColor = '#3498db';
                if (e.dataTransfer.files.length > 0) {
                    this.selectedFile = e.dataTransfer.files[0];
                    fileInput.files = e.dataTransfer.files;
                    this.previewFile(e.dataTransfer.files[0]);
                }
            });
        }
        
        // Bouton upload
        const uploadBtn = document.getElementById('img-upload-btn');
        if (uploadBtn) {
            uploadBtn.addEventListener('click', () => this.uploadAndInsert());
        }
    }
    
    open() {
        this.currentEditor = document.querySelector('textarea[name="content"]');
        if (this.modal) {
            this.modal.classList.add('active');
        }
    }
    
    close() {
        if (this.modal) {
            this.modal.classList.remove('active');
        }
    }
    
    previewFile(file) {
        if (!file.type.startsWith('image/')) {
            alert('Veuillez sélectionner une image valide');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            const previewArea = document.querySelector('.img-preview-area');
            const previewImg = document.getElementById('img-preview');
            
            if (previewImg && previewArea) {
                previewImg.src = e.target.result;
                previewArea.style.display = 'block';
                
                const stats = document.querySelector('.img-preview-stats');
                if (stats) {
                    stats.innerHTML = `
                        <strong>Fichier :</strong> ${file.name}<br>
                        <strong>Taille :</strong> ${(file.size / 1024).toFixed(0)} KB<br>
                        <strong>Type :</strong> ${file.type}
                    `;
                }
            }
        };
        reader.readAsDataURL(file);
    }
    
    async uploadAndInsert() {
        if (!this.selectedFile) {
            alert('Veuillez sélectionner une image');
            return;
        }
        
        const altInput = document.getElementById('img-alt-text');
        const altText = altInput ? altInput.value : '';
        
        // Afficher progression
        const progress = document.querySelector('.img-upload-progress');
        const uploadBtn = document.getElementById('img-upload-btn');
        
        if (progress) progress.style.display = 'block';
        if (uploadBtn) uploadBtn.disabled = true;
        
        const formData = new FormData();
        formData.append('image', this.selectedFile);
        formData.append('alt_text', altText);
        
        try {
            const response = await fetch('upload-image-api.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                const htmlCode = `<picture>
    <source srcset="${result.urls.medium.webp}" type="image/webp">
    <img src="${result.urls.medium.jpeg}" alt="${altText}" loading="lazy" class="article-image">
</picture>`;
                
                this.insertIntoEditor(htmlCode);
                this.close();
                this.resetForm();
                alert('Image optimisée et insérée avec succès !');
            } else {
                alert('Erreur : ' + (result.error || 'Erreur inconnue'));
            }
        } catch (error) {
            alert('Erreur lors de l\'upload : ' + error.message);
            console.error('Upload error:', error);
        } finally {
            if (progress) progress.style.display = 'none';
            if (uploadBtn) uploadBtn.disabled = false;
        }
    }
    
    insertIntoEditor(html) {
        if (!this.currentEditor) return;
        
        const start = this.currentEditor.selectionStart;
        const end = this.currentEditor.selectionEnd;
        const text = this.currentEditor.value;
        
        this.currentEditor.value = text.substring(0, start) + '\n\n' + html + '\n\n' + text.substring(end);
        this.currentEditor.focus();
        
        // Positionner le curseur après l'insertion
        const newPos = start + html.length + 4;
        this.currentEditor.setSelectionRange(newPos, newPos);
    }
    
    resetForm() {
        const fileInput = document.getElementById('img-file-input');
        const altInput = document.getElementById('img-alt-text');
        const previewArea = document.querySelector('.img-preview-area');
        
        if (fileInput) fileInput.value = '';
        if (altInput) altInput.value = '';
        if (previewArea) previewArea.style.display = 'none';
        
        this.selectedFile = null;
    }
}

// Initialiser au chargement
document.addEventListener('DOMContentLoaded', () => {
    new ImageManager();
});