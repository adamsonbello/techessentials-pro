<?php
// admin-blog/tags/index.php - Redirection vers la gestion des tags
session_start();

// Vérification auth
if (!isset($_SESSION['blog_admin_logged']) || $_SESSION['blog_admin_logged'] !== true) {
    header('Location: ../index.php');
    exit;
}

// Redirection vers l'onglet tags de la page catégories
header('Location: ../categories/index.php#tags');
exit;
?>