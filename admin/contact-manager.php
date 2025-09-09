<?php
// admin/contact-manager.php - Gestionnaire de messages de contact
session_start();

// Vérifier l'authentification
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: index.php');
    exit;
}

$admin_user = isset($_SESSION['admin_user']) ? $_SESSION['admin_user'] : 'Admin';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Manager - TechEssentials Pro</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
        }
        
        .admin-header {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-header h1 {
            color: #667eea;
            font-size: 1.5rem;
        }
        
        .admin-nav {
            display: flex;
            gap: 15px;
        }
        
        .nav-link {
            color: #667eea;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background 0.2s;
        }
        
        .nav-link:hover, .nav-link.active {
            background: #e7f3ff;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-left: 4px solid #667eea;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 1rem;
        }
        
        .messages-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .section-header {
            background: #667eea;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-group label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 500;
        }
        
        .filter-group select, .filter-group input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .messages-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .messages-table th,
        .messages-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .messages-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
        }
        
        .messages-table tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-new {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-read {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-replied {
            background: #d4edda;
            color: #155724;
        }
        
        .status-closed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .priority-high {
            color: #dc3545;
            font-weight: bold;
        }
        
        .priority-urgent {
            color: #dc3545;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .message-preview {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .action-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            margin: 2px;
            transition: background 0.2s;
        }
        
        .action-btn:hover {
            background: #5a6fd8;
        }
        
        .action-btn.reply {
            background: #28a745;
        }
        
        .action-btn.reply:hover {
            background: #218838;
        }
        
        .action-btn.close {
            background: #dc3545;
        }
        
        .action-btn.close:hover {
            background: #c82333;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            background: #667eea;
            color: white;
            padding: 20px;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .close-modal {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            background: none;
        }
        
        .close-modal:hover {
            opacity: 0.7;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .message-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .detail-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .detail-value {
            color: #333;
        }
        
        .message-content {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            margin: 20px 0;
        }
        
        .reply-form {
            margin-top: 20px;
            padding: 20px;
            background: #e7f3ff;
            border-radius: 8px;
        }
        
        .reply-form textarea {
            width: 100%;
            height: 150px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            resize: vertical;
        }
        
        .form-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .no-messages {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-messages h3 {
            color: #667eea;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
                gap: 10px;
            }
            
            .messages-table {
                font-size: 0.9rem;
            }
            
            .message-details {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .modal-content {
                width: 95%;
                margin: 2% auto;
            }
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>📧 Contact Manager</h1>
        <div class="admin-nav">
            <a href="newsletter.php" class="nav-link">📊 Stats</a>
            <a href="email-manager.php" class="nav-link">📧 Emails</a>
            <a href="contact-manager.php" class="nav-link active">💬 Messages</a>
        </div>
        <div class="admin-user">
            <span>Connecté : <?php echo htmlspecialchars($admin_user); ?></span>
            <a href="?logout=1" class="logout-btn">Déconnexion</a>
        </div>
    </div>

    <div class="container">
        <!-- Statistiques -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-number" id="totalMessages">-</div>
                <div class="stat-label">Total Messages</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="newMessages">-</div>
                <div class="stat-label">Nouveaux</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="pendingReplies">-</div>
                <div class="stat-label">En Attente</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="avgResponseTime">-</div>
                <div class="stat-label">Temps Réponse (h)</div>
            </div>
        </div>

        <!-- Messages Section -->
        <div class="messages-section">
            <div class="section-header">
                <h2>📬 Messages de Contact</h2>
                <button onclick="loadMessages()" class="action-btn">🔄 Actualiser</button>
            </div>

            <!-- Filtres -->
            <div class="filters">
                <div class="filter-group">
                    <label>Statut</label>
                    <select id="filterStatus" onchange="applyFilters()">
                        <option value="">Tous</option>
                        <option value="new">Nouveaux</option>
                        <option value="read">Lus</option>
                        <option value="replied">Répondus</option>
                        <option value="closed">Fermés</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Sujet</label>
                    <select id="filterSubject" onchange="applyFilters()">
                        <option value="">Tous</option>
                        <option value="general">Général</option>
                        <option value="partnership">Partenariat</option>
                        <option value="review">Test Produit</option>
                        <option value="support">Support</option>
                        <option value="feedback">Commentaires</option>
                        <option value="other">Autre</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Langue</label>
                    <select id="filterLanguage" onchange="applyFilters()">
                        <option value="">Toutes</option>
                        <option value="en">🇺🇸 Anglais</option>
                        <option value="fr">🇫🇷 Français</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Date</label>
                    <input type="date" id="filterDate" onchange="applyFilters()">
                </div>
            </div>

            <!-- Table des messages -->
            <div id="messagesContainer">
                <div class="loading">Chargement des messages...</div>
            </div>
        </div>
    </div>

    <!-- Modal pour voir/répondre aux messages -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Détails du Message</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>

    <script>
        const API_URL = "../api.php";
        let allMessages = [];
        let currentMessage = null;

        // Charger les messages
        async function loadMessages() {
            try {
                document.getElementById('messagesContainer').innerHTML = '<div class="loading">Chargement des messages...</div>';
                
                const response = await fetch(`${API_URL}?action=getContactMessages`);
                const data = await response.json();
                
                if (data.success) {
                    allMessages = data.messages || [];
                    displayMessages(allMessages);
                    updateStats(data.stats || {});
                } else {
                    document.getElementById('messagesContainer').innerHTML = `<div class="no-messages"><h3>Erreur</h3><p>${data.error}</p></div>`;
                }
                
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('messagesContainer').innerHTML = '<div class="no-messages"><h3>Erreur de connexion</h3><p>Impossible de charger les messages.</p></div>';
            }
        }

        // Afficher les messages
        function displayMessages(messages) {
            const container = document.getElementById('messagesContainer');
            
            if (messages.length === 0) {
                container.innerHTML = '<div class="no-messages"><h3>📭 Aucun message</h3><p>Aucun message de contact trouvé.</p></div>';
                return;
            }
            
            let tableHTML = `
                <table class="messages-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Sujet</th>
                            <th>Aperçu</th>
                            <th>Langue</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            messages.forEach(message => {
                const statusClass = `status-${message.status}`;
                const priorityClass = message.priority !== 'normal' ? `priority-${message.priority}` : '';
                const langFlag = message.language === 'fr' ? '🇫🇷' : '🇺🇸';
                const date = new Date(message.created_at).toLocaleDateString('fr-FR');
                
                tableHTML += `
                    <tr class="${priorityClass}">
                        <td>#${message.id}</td>
                        <td>${message.first_name} ${message.last_name}</td>
                        <td><a href="mailto:${message.email}">${message.email}</a></td>
                        <td>${getSubjectLabel(message.subject)}</td>
                        <td class="message-preview">${message.message}</td>
                        <td>${langFlag}</td>
                        <td><span class="status-badge ${statusClass}">${getStatusLabel(message.status)}</span></td>
                        <td>${date}</td>
                        <td>
                            <button class="action-btn" onclick="viewMessage(${message.id})">👁️ Voir</button>
                            <button class="action-btn reply" onclick="replyMessage(${message.id})">✉️ Répondre</button>
                            ${message.status !== 'closed' ? `<button class="action-btn close" onclick="closeMessage(${message.id})">❌ Fermer</button>` : ''}
                        </td>
                    </tr>
                `;
            });
            
            tableHTML += '</tbody></table>';
            container.innerHTML = tableHTML;
        }

        // Mettre à jour les statistiques
        function updateStats(stats) {
            document.getElementById('totalMessages').textContent = stats.total || 0;
            document.getElementById('newMessages').textContent = stats.new || 0;
            document.getElementById('pendingReplies').textContent = stats.pending || 0;
            document.getElementById('avgResponseTime').textContent = stats.avg_response_time || '-';
        }

        // Labels des sujets
        function getSubjectLabel(subject) {
            const labels = {
                'general': 'Général',
                'partnership': 'Partenariat',
                'review': 'Test Produit',
                'support': 'Support',
                'feedback': 'Commentaires',
                'other': 'Autre'
            };
            return labels[subject] || subject;
        }

        // Labels des statuts
        function getStatusLabel(status) {
            const labels = {
                'new': 'Nouveau',
                'read': 'Lu',
                'replied': 'Répondu',
                'closed': 'Fermé'
            };
            return labels[status] || status;
        }

        // Appliquer les filtres
        function applyFilters() {
            const statusFilter = document.getElementById('filterStatus').value;
            const subjectFilter = document.getElementById('filterSubject').value;
            const languageFilter = document.getElementById('filterLanguage').value;
            const dateFilter = document.getElementById('filterDate').value;
            
            let filteredMessages = allMessages.filter(message => {
                if (statusFilter && message.status !== statusFilter) return false;
                if (subjectFilter && message.subject !== subjectFilter) return false;
                if (languageFilter && message.language !== languageFilter) return false;
                if (dateFilter) {
                    const messageDate = new Date(message.created_at).toISOString().split('T')[0];
                    if (messageDate !== dateFilter) return false;
                }
                return true;
            });
            
            displayMessages(filteredMessages);
        }

        // Voir un message
        async function viewMessage(messageId) {
            const message = allMessages.find(m => m.id === messageId);
            if (!message) return;
            
            currentMessage = message;
            
            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = `
                <div class="message-details">
                    <div class="detail-item">
                        <div class="detail-label">Contact</div>
                        <div class="detail-value">${message.first_name} ${message.last_name}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><a href="mailto:${message.email}">${message.email}</a></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Sujet</div>
                        <div class="detail-value">${getSubjectLabel(message.subject)}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Date</div>
                        <div class="detail-value">${new Date(message.created_at).toLocaleString('fr-FR')}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Langue</div>
                        <div class="detail-value">${message.language === 'fr' ? '🇫🇷 Français' : '🇺🇸 English'}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Statut</div>
                        <div class="detail-value"><span class="status-badge status-${message.status}">${getStatusLabel(message.status)}</span></div>
                    </div>
                </div>
                
                <div class="message-content">
                    <div class="detail-label">Message :</div>
                    <div style="margin-top: 10px; white-space: pre-wrap;">${message.message}</div>
                </div>
                
                <div class="form-actions">
                    <button class="btn btn-primary" onclick="replyMessage(${messageId})">✉️ Répondre</button>
                    <button class="btn btn-secondary" onclick="markAsRead(${messageId})">👁️ Marquer comme lu</button>
                    ${message.status !== 'closed' ? `<button class="btn btn-secondary" onclick="closeMessage(${messageId})">❌ Fermer</button>` : ''}
                </div>
            `;
            
            document.getElementById('modalTitle').textContent = `Message #${messageId}`;
            document.getElementById('messageModal').style.display = 'block';
            
            // Marquer comme lu automatiquement
            if (message.status === 'new') {
                markAsRead(messageId);
            }
        }

        // Répondre à un message
        function replyMessage(messageId) {
            const message = allMessages.find(m => m.id === messageId);
            if (!message) return;
            
            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = `
                <div class="message-details">
                    <div class="detail-item">
                        <div class="detail-label">À</div>
                        <div class="detail-value">${message.first_name} ${message.last_name} (${message.email})</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Sujet Original</div>
                        <div class="detail-value">${getSubjectLabel(message.subject)}</div>
                    </div>
                </div>
                
                <div class="message-content">
                    <div class="detail-label">Message original :</div>
                    <div style="margin-top: 10px; white-space: pre-wrap; font-style: italic;">${message.message}</div>
                </div>
                
                <div class="reply-form">
                    <div class="detail-label">Votre réponse :</div>
                    <textarea id="replyText" placeholder="Tapez votre réponse ici..."></textarea>
                    <div class="form-actions">
                        <button class="btn btn-primary" onclick="sendReply(${messageId})">📤 Envoyer Réponse</button>
                        <button class="btn btn-secondary" onclick="closeModal()">❌ Annuler</button>
                    </div>
                </div>
            `;
            
            document.getElementById('modalTitle').textContent = `Répondre à #${messageId}`;
            document.getElementById('messageModal').style.display = 'block';
        }

        // Envoyer une réponse
        async function sendReply(messageId) {
            const replyText = document.getElementById('replyText').value.trim();
            
            if (!replyText) {
                alert('Veuillez saisir une réponse');
                return;
            }
            
            if (!confirm('Envoyer cette réponse ?')) {
                return;
            }
            
            try {
                // Simuler l'envoi (vous devez implémenter cette API)
                console.log('Sending reply to message', messageId, ':', replyText);
                alert('Réponse envoyée ! (Simulation - implémentez l\'API sendReply)');
                closeModal();
                loadMessages(); // Recharger les messages
                
            } catch (error) {
                alert('Erreur lors de l\'envoi : ' + error.message);
            }
        }

        // Marquer comme lu
        async function markAsRead(messageId) {
            try {
                // Simuler la mise à jour (vous devez implémenter cette API)
                console.log('Marking message as read:', messageId);
                
                // Mettre à jour localement
                const message = allMessages.find(m => m.id === messageId);
                if (message) {
                    message.status = 'read';
                    loadMessages(); // Recharger pour voir les changements
                }
                
            } catch (error) {
                console.error('Erreur:', error);
            }
        }

        // Fermer un message
        async function closeMessage(messageId) {
            if (!confirm('Fermer définitivement ce message ?')) {
                return;
            }
            
            try {
                // Simuler la fermeture (vous devez implémenter cette API)
                console.log('Closing message:', messageId);
                
                const message = allMessages.find(m => m.id === messageId);
                if (message) {
                    message.status = 'closed';
                    loadMessages();
                }
                
                closeModal();
                
            } catch (error) {
                console.error('Erreur:', error);
            }
        }

        // Fermer la modal
        function closeModal() {
            document.getElementById('messageModal').style.display = 'none';
            currentMessage = null;
        }

        // Fermer modal en cliquant à l'extérieur
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('messageModal');
            if (event.target === modal) {
                closeModal();
            }
        });

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            loadMessages();
            console.log('📧 Contact Manager chargé');
        });
    </script>
</body>
</html>