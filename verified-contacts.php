<?php
// admin/verified-contacts.php - Gestionnaire de contacts vérifiés
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
    <title>Contacts Vérifiés - TechEssentials Pro</title>
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
            border-left: 4px solid #28a745;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 1rem;
        }
        
        .verified-badge {
            background: #28a745;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .messages-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .section-header {
            background: #28a745;
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
        
        .status-verified {
            background: #d4edda;
            color: #155724;
        }
        
        .status-processed {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-replied {
            background: #d4edda;
            color: #155724;
        }
        
        .status-archived {
            background: #f8d7da;
            color: #721c24;
        }
        
        .message-preview {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .action-btn {
            background: #28a745;
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
            background: #218838;
        }
        
        .action-btn.reply {
            background: #667eea;
        }
        
        .action-btn.reply:hover {
            background: #5a6fd8;
        }
        
        .action-btn.archive {
            background: #6c757d;
        }
        
        .action-btn.archive:hover {
            background: #5a6268;
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
            background: #28a745;
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
            color: #28a745;
            margin-bottom: 5px;
        }
        
        .detail-value {
            color: #333;
        }
        
        .message-content {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
            margin: 20px 0;
        }
        
        .verification-info {
            background: #d4edda;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
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
            background: #28a745;
            color: white;
        }
        
        .btn-primary:hover {
            background: #218838;
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
            color: #28a745;
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
        <h1>✅ Contacts Vérifiés</h1>
        <div class="admin-nav">
            <a href="dashboard.php" class="nav-link">🏠 Dashboard</a>
            <a href="newsletter.php" class="nav-link">📊 Stats</a>
            <a href="email-manager.php" class="nav-link">📧 Emails</a>
            <a href="contact-manager.php" class="nav-link">💬 Messages</a>
            <a href="verified-contacts.php" class="nav-link active">✅ Vérifiés</a>
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
                <div class="stat-number" id="totalVerified">-</div>
                <div class="stat-label">Total Vérifiés</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="pendingProcessing">-</div>
                <div class="stat-label">En Attente</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="recentWeek">-</div>
                <div class="stat-label">Cette Semaine</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="responseRate">-</div>
                <div class="stat-label">Taux Réponse (%)</div>
            </div>
        </div>

        <!-- Messages Section -->
        <div class="messages-section">
            <div class="section-header">
                <h2>📬 Contacts Vérifiés</h2>
                <button onclick="loadVerifiedContacts()" class="action-btn">🔄 Actualiser</button>
            </div>

            <!-- Filtres -->
            <div class="filters">
                <div class="filter-group">
                    <label>Statut</label>
                    <select id="filterStatus" onchange="applyFilters()">
                        <option value="">Tous</option>
                        <option value="verified">Vérifiés</option>
                        <option value="processed">Traités</option>
                        <option value="replied">Répondus</option>
                        <option value="archived">Archivés</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Catégorie</label>
                    <select id="filterCategory" onchange="applyFilters()">
                        <option value="">Toutes</option>
                        <option value="general">Général</option>
                        <option value="partnership">Partenariat</option>
                        <option value="review">Test Produit</option>
                        <option value="support">Support</option>
                        <option value="feedback">Commentaires</option>
                        <option value="other">Autre</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Date Vérification</label>
                    <input type="date" id="filterDate" onchange="applyFilters()">
                </div>
            </div>

            <!-- Table des contacts -->
            <div id="contactsContainer">
                <div class="loading">Chargement des contacts vérifiés...</div>
            </div>
        </div>
    </div>

    <!-- Modal pour voir/répondre aux contacts -->
    <div id="contactModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Détails du Contact</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>

    <script>
        const API_URL = "api.php";
        let allContacts = [];
        let currentContact = null;

        // Charger les contacts vérifiés
        async function loadVerifiedContacts() {
            try {
                document.getElementById('contactsContainer').innerHTML = '<div class="loading">Chargement des contacts vérifiés...</div>';
                
                const response = await fetch(`${API_URL}?action=getVerifiedContacts`);
                const data = await response.json();
                
                if (data.success) {
                    allContacts = data.contacts || [];
                    displayContacts(allContacts);
                    updateStats(data.stats || {});
                } else {
                    document.getElementById('contactsContainer').innerHTML = `<div class="no-messages"><h3>Erreur</h3><p>${data.error}</p></div>`;
                }
                
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('contactsContainer').innerHTML = '<div class="no-messages"><h3>Erreur de connexion</h3><p>Impossible de charger les contacts.</p></div>';
            }
        }

        // Afficher les contacts
        function displayContacts(contacts) {
            const container = document.getElementById('contactsContainer');
            
            if (contacts.length === 0) {
                container.innerHTML = '<div class="no-messages"><h3>📭 Aucun contact vérifié</h3><p>Aucun contact vérifié trouvé.</p></div>';
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
                            <th>Vérification</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            contacts.forEach(contact => {
                const statusClass = `status-${contact.status}`;
                const verifiedDate = new Date(contact.verified_at).toLocaleDateString('fr-FR');
                const verifiedTime = new Date(contact.verified_at).toLocaleTimeString('fr-FR');
                
                tableHTML += `
                    <tr>
                        <td>#${contact.id}</td>
                        <td>${contact.name}</td>
                        <td><a href="mailto:${contact.email}">${contact.email}</a></td>
                        <td>${getCategoryLabel(contact.contact_category)}</td>
                        <td class="message-preview">${contact.message}</td>
                        <td>
                            <div class="verified-badge">✓ Vérifié</div>
                            <small style="color: #666;">${verifiedDate}<br>${verifiedTime}</small>
                        </td>
                        <td><span class="status-badge ${statusClass}">${getStatusLabel(contact.status)}</span></td>
                        <td>
                            <button class="action-btn" onclick="viewContact(${contact.id})">👁️ Voir</button>
                            <button class="action-btn reply" onclick="replyContact(${contact.id})">✉️ Répondre</button>
                            ${contact.status !== 'archived' ? `<button class="action-btn archive" onclick="archiveContact(${contact.id})">📁 Archiver</button>` : ''}
                        </td>
                    </tr>
                `;
            });
            
            tableHTML += '</tbody></table>';
            container.innerHTML = tableHTML;
        }

        // Mettre à jour les statistiques
        function updateStats(stats) {
            document.getElementById('totalVerified').textContent = stats.total_verified || 0;
            document.getElementById('pendingProcessing').textContent = stats.by_status?.verified || 0;
            document.getElementById('recentWeek').textContent = stats.recent_week || 0;
            document.getElementById('responseRate').textContent = stats.response_rate || 0;
        }

        // Labels des catégories
        function getCategoryLabel(category) {
            const labels = {
                'general': 'Général',
                'partnership': 'Partenariat',
                'review': 'Test Produit',
                'support': 'Support',
                'feedback': 'Commentaires',
                'other': 'Autre'
            };
            return labels[category] || category || 'Général';
        }

        // Labels des statuts
        function getStatusLabel(status) {
            const labels = {
                'verified': 'Vérifié',
                'processed': 'Traité',
                'replied': 'Répondu',
                'archived': 'Archivé'
            };
            return labels[status] || status;
        }

        // Appliquer les filtres
        function applyFilters() {
            const statusFilter = document.getElementById('filterStatus').value;
            const categoryFilter = document.getElementById('filterCategory').value;
            const dateFilter = document.getElementById('filterDate').value;
            
            let filteredContacts = allContacts.filter(contact => {
                if (statusFilter && contact.status !== statusFilter) return false;
                if (categoryFilter && contact.contact_category !== categoryFilter) return false;
                if (dateFilter) {
                    const contactDate = new Date(contact.verified_at).toISOString().split('T')[0];
                    if (contactDate !== dateFilter) return false;
                }
                return true;
            });
            
            displayContacts(filteredContacts);
        }

        // Voir un contact
        async function viewContact(contactId) {
            const contact = allContacts.find(c => c.id === contactId);
            if (!contact) return;
            
            currentContact = contact;
            
            const submittedDate = new Date(contact.submitted_at).toLocaleString('fr-FR');
            const verifiedDate = new Date(contact.verified_at).toLocaleString('fr-FR');
            
            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = `
                <div class="verification-info">
                    <div class="detail-label">✅ Contact Vérifié</div>
                    <div class="detail-value">Ce contact a validé son adresse email le ${verifiedDate}</div>
                </div>
                
                <div class="message-details">
                    <div class="detail-item">
                        <div class="detail-label">Contact</div>
                        <div class="detail-value">${contact.name}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><a href="mailto:${contact.email}">${contact.email}</a></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Sujet</div>
                        <div class="detail-value">${getCategoryLabel(contact.contact_category)}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Soumis le</div>
                        <div class="detail-value">${submittedDate}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Vérifié le</div>
                        <div class="detail-value">${verifiedDate}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Statut</div>
                        <div class="detail-value"><span class="status-badge status-${contact.status}">${getStatusLabel(contact.status)}</span></div>
                    </div>
                </div>
                
                <div class="message-content">
                    <div class="detail-label">Message :</div>
                    <div style="margin-top: 10px; white-space: pre-wrap;">${contact.message}</div>
                </div>
                
                <div class="form-actions">
                    <button class="btn btn-primary" onclick="replyContact(${contactId})">✉️ Répondre</button>
                    <button class="btn btn-secondary" onclick="markAsProcessed(${contactId})">✓ Marquer traité</button>
                    ${contact.status !== 'archived' ? `<button class="btn btn-secondary" onclick="archiveContact(${contactId})">📁 Archiver</button>` : ''}
                </div>
            `;
            
            document.getElementById('modalTitle').textContent = `Contact #${contactId}`;
            document.getElementById('contactModal').style.display = 'block';
        }

        // Répondre à un contact
        function replyContact(contactId) {
            const contact = allContacts.find(c => c.id === contactId);
            if (!contact) return;
            
            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = `
                <div class="verification-info">
                    <div class="detail-label">✅ Contact Vérifié</div>
                    <div class="detail-value">Email validé le ${new Date(contact.verified_at).toLocaleString('fr-FR')}</div>
                </div>
                
                <div class="message-details">
                    <div class="detail-item">
                        <div class="detail-label">À</div>
                        <div class="detail-value">${contact.name} (${contact.email})</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Sujet Original</div>
                        <div class="detail-value">${getCategoryLabel(contact.contact_category)}</div>
                    </div>
                </div>
                
                <div class="message-content">
                    <div class="detail-label">Message original :</div>
                    <div style="margin-top: 10px; white-space: pre-wrap; font-style: italic;">${contact.message}</div>
                </div>
                
                <div class="reply-form">
                    <div class="detail-label">Votre réponse :</div>
                    <textarea id="replyText" placeholder="Tapez votre réponse ici..."></textarea>
                    <div class="form-actions">
                        <button class="btn btn-primary" onclick="sendContactReply(${contactId})">📤 Envoyer Réponse</button>
                        <button class="btn btn-secondary" onclick="closeModal()">❌ Annuler</button>
                    </div>
                </div>
            `;
            
            document.getElementById('modalTitle').textContent = `Répondre à #${contactId}`;
            document.getElementById('contactModal').style.display = 'block';
        }

        // Envoyer une réponse
        async function sendContactReply(contactId) {
            const replyText = document.getElementById('replyText').value.trim();
            
            if (!replyText) {
                alert('Veuillez saisir une réponse');
                return;
            }
            
            if (!confirm('Envoyer cette réponse ?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'replyToVerifiedContact');
                formData.append('contact_id', contactId);
                formData.append('reply_message', replyText);
                
                const response = await fetch(API_URL, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Réponse envoyée avec succès !');
                    closeModal();
                    loadVerifiedContacts();
                } else {
                    throw new Error(data.error || 'Erreur inconnue');
                }
                
            } catch (error) {
                alert('Erreur lors de l\'envoi : ' + error.message);
            }
        }

        // Marquer comme traité
        async function markAsProcessed(contactId) {
            try {
                const formData = new FormData();
                formData.append('action', 'markContactAsProcessed');
                formData.append('contact_id', contactId);
                
                const response = await fetch(API_URL, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const contact = allContacts.find(c => c.id === contactId);
                    if (contact) {
                        contact.status = 'processed';
                        displayContacts(allContacts);
                    }
                    closeModal();
                } else {
                    console.error('Erreur:', data.error);
                }
                
            } catch (error) {
                console.error('Erreur:', error);
            }
        }

        // Archiver un contact
        async function archiveContact(contactId) {
            if (!confirm('Archiver ce contact ?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'archiveVerifiedContact');
                formData.append('contact_id', contactId);
                
                const response = await fetch(API_URL, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const contact = allContacts.find(c => c.id === contactId);
                    if (contact) {
                        contact.status = 'archived';
                        displayContacts(allContacts);
                    }
                    closeModal();
                } else {
                    alert('Erreur: ' + data.error);
                }
                
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'archivage');
            }
        }

        // Fermer la modal
        function closeModal() {
            document.getElementById('contactModal').style.display = 'none';
            currentContact = null;
        }

        // Fermer modal en cliquant à l'extérieur
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('contactModal');
            if (event.target === modal) {
                closeModal();
            }
        });

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            loadVerifiedContacts();
            console.log('✅ Verified Contacts Manager chargé');
        });
    </script>
</body>
</html>