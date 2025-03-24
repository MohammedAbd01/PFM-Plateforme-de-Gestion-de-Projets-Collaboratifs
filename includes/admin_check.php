<?php
/**
 * Fichier de vérification du rôle administrateur
 * 
 * Vérifie si l'utilisateur est connecté et a le rôle d'administrateur, 
 * sinon le redirige vers la page appropriée
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

// Vérifier si l'utilisateur est un administrateur
if ($_SESSION['utilisateur']['role'] !== 'Administrateur') {
    // Enregistrer un message d'erreur
    $_SESSION['message'] = 'Vous n\'avez pas les droits nécessaires pour accéder à cette page.';
    $_SESSION['message_type'] = 'danger';
    
    // Rediriger vers le tableau de bord
    header('Location: tableau_de_bord.php');
    exit;
}
?> 