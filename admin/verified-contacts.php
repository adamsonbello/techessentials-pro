<?php
session_start();

// Vérifier l'authentification
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: index.php');
    exit;
}

$admin_user = $_SESSION['admin_user'] ?? 'Admin';
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
            color: #28a745;
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
        
        .contacts-section {
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
        
        .contacts-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .contacts-table th,
        .contacts-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .contacts-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        .contacts-table tr:hover {
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
            background: #cce5ff;
            color: #004085;
        }
        
        .status-archived {
            background: #e2e3e5;
            color: #383d41;
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
        }
        
        .action-btn:hover {
            background: #218838;
        }
        
        .action-btn.reply {
            background: #007bff;
        }
        
        .action-btn.archive {
            background: #6c757d;
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
            max-width: 700px;
        }
        
        .modal-header {
            background: #28a745;
            color: white;
            padding: 20px;
            border-radius: 12px 12px 0 0;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .close-modal {
            color: white;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .detail-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #28a745;
        }
        
        textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            resize: vertical;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .btn-primary {
            background: #28a745;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
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
            <a href="verified-contacts.php" class="nav-link active">✅ Contacts Vérifiés</a>
        </div>
        <div>
            <span>Connecté : <?= htmlspecialchars($admin_user) ?></span>
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
                <div class="stat-number" id="verifiedStatus">-</div>
                <div class="stat-label">Vérifiés</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="repliedStatus">-</div>
                <div class="stat-label">Répondus</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="responseRate">-%</div>
                <div class="stat-label">Taux Réponse</div>
            </div>
        </div>

        <!-- Section Contacts -->
        <div class="contacts-section">
            <div class="section-header">
                <h2>📋 Liste des Contacts Vérifiés</h2>
                <button onclick="loadContacts()" class="action-btn">🔄 Actualiser</button>
            </div>

            <div id="contactsContainer">
                <div class="loading">Chargement des contacts...</div>
            </div>
        </div>
    </div>

    <!-- Modal Réponse -->
    <div id="replyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close-modal" onclick="closeModal()">&times;</span>
                <h2>Répondre au Contact</h2>
            </div>
            <div class="modal-body" id="modalBody"></div>
        </div>
    </div>

    <script>
        const API_URL = "api.php";
        let allContacts = [];

        // Charger les contacts
        async function loadContacts() {
            try {
                document.getElementById('contactsContainer').innerHTML = '<div class="loading">Chargement...</div>';
                
                const response = await fetch(`${API_URL}?action=getVerifiedContacts`);
                const data = await response.json();
                
                if (data.success) {
                    allContacts = data.contacts || [];
                    displayContacts(allContacts);
                    updateStats(data.stats || {});
                } else {
                    document.getElementById('contactsContainer').innerHTML = `<div class="loading">Erreur: ${data.error}</div>`;
                }
                
            } catch (error) {
                console.error('Erreur:', error);
                document.getElementById('contactsContainer').innerHTML = '<div class="loading">Erreur de connexion</div>';
            }
        }

        // Afficher les contacts
        function displayContacts(contacts) {
            const container = document.getElementById('contactsContainer');
            
            if (contacts.length === 0) {
                container.innerHTML = '<div class="loading">Aucun contact vérifié</div>';
                return;
            }
            
            let tableHTML = `
                <table class="contacts-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Sujet</th>
                            <th>Catégorie</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            contacts.forEach(contact => {
                const statusClass = `status-${contact.status}`;
                const date = new Date(contact.verified_at).toLocaleDateString('fr-FR');
                
                tableHTML += `
                    <tr>
                        <td>#${contact.id}</td>
                        <td>${contact.name}</td>
                        <td><a href="mailto:${contact.email}">${contact.email}</a></td>
                        <td>${contact.subject || '-'}</td>
                        <td>${contact.contact_category || '-'}</td>
                        <td><span class="status-badge ${statusClass}">${getStatusLabel(contact.status)}</span></td>
                        <td>${date}</td>
                        <td>
                            <button class="action-btn" onclick="viewContact(${contact.id})">👁️</button>
                            <button class="action-btn reply" onclick="replyContact(${contact.id})">✉️</button>
                            <button class="action-btn" onclick="markProcessed(${contact.id})">✓</button>
                            <button class="action-btn archive" onclick="archiveContact(${contact.id})">📦</button>
                        </td>
                    </tr>
                `;
            });
            
            tableHTML += '</tbody></table>';
            container.innerHTML = tableHTML;
        }

        // Mettre à jour stats
        function updateStats(stats) {
            document.getElementById('totalVerified').textContent = stats.total_verified || 0;
            document.getElementById('verifiedStatus').textContent = stats.by_status?.verified || 0;
            document.getElementById('repliedStatus').textContent = stats.by_status?.replied || 0;
            document.getElementById('responseRate').textContent = stats.response_rate || 0;
        }

        // Labels statuts
        function getStatusLabel(status) {
            const labels = {
                'verified': 'Vérifié',
                'processed': 'Traité',
                'replied': 'Répondu',
                'archived': 'Archivé'
            };
            return labels[status] || status;
        }

        // Voir contact
        function viewContact(id) {
            const contact = allContacts.find(c => c.id === id);
            if (!contact) return;
            
            alert(`Contact #${id}\n${contact.name}\n${contact.email}\n\n${contact.message}`);
        }

        // Répondre
        function replyContact(id) {
            const contact = allContacts.find(c => c.id === id);
            if (!contact) return;
            
            document.getElementById('modalBody').innerHTML = `
                <div class="detail-item">
                    <div class="detail-label">À : ${contact.name} (${contact.email})</div>
                </div>
                <div style="margin: 20px 0;">
                    <label><strong>Votre réponse :</strong></label>
                    <textarea id="replyText" placeholder="Tapez votre réponse..."></textarea>
                </div>
                <button class="btn btn-primary" onclick="sendReply(${id})">📤 Envoyer</button>
                <button class="btn btn-secondary" onclick="closeModal()">Annuler</button>
            `;
            
            document.getElementById('replyModal').style.display = 'block';
        }

        // Envoyer réponse
        async function sendReply(id) {
            const replyText = document.getElementById('replyText').value.trim();
            
            if (!replyText) {
                alert('Veuillez saisir une réponse');
                return;
            }
            
            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'replyToVerifiedContact',
                        contact_id: id,
                        reply_message: replyText
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✅ Réponse envoyée !');
                    closeModal();
                    loadContacts();
                } else {
                    alert('❌ Erreur: ' + data.error);
                }
            } catch (error) {
                alert('❌ Erreur: ' + error.message);
            }
        }

        // Marquer comme traité
        async function markProcessed(id) {
            if (!confirm('Marquer comme traité ?')) return;
            
            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'markContactAsProcessed',
                        contact_id: id
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadContacts();
                } else {
                    alert('Erreur: ' + data.error);
                }
            } catch (error) {
                alert('Erreur: ' + error.message);
            }
        }

        // Archiver
        async function archiveContact(id) {
            if (!confirm('Archiver ce contact ?')) return;
            
            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'archiveVerifiedContact',
                        contact_id: id
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadContacts();
                } else {
                    alert('Erreur: ' + data.error);
                }
            } catch (error) {
                alert('Erreur: ' + error.message);
            }
        }

        // Fermer modal
        function closeModal() {
            document.getElementById('replyModal').style.display = 'none';
        }

        // Init
        document.addEventListener('DOMContentLoaded', loadContacts);
    </script>
</body>
</html>