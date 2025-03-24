<?php
/**
 * Fichier de vérification d'authentification
 * 
 * Vérifie si l'utilisateur est connecté, sinon le redirige vers la page de connexion
 */

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['utilisateur'])) {
    // Enregistrer un message d'erreur
    $_SESSION['message'] = 'Vous devez être connecté pour accéder à cette page.';
    $_SESSION['message_type'] = 'danger';
    
    // Rediriger vers la page de connexion
    header('Location: connexion.php');
    exit;
}
?> 