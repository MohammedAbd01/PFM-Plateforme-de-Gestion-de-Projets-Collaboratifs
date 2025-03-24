<?php
session_start();

// Vérifier si l'utilisateur est connecté
function verifierConnexion() {
    if (!isset($_SESSION['utilisateur_id'])) {
        header('Location: connexion.php');
        exit;
    }
}

// Vérifier si l'utilisateur est administrateur
function verifierAdmin() {
    if (!isset($_SESSION['utilisateur_id']) || $_SESSION['utilisateur_role'] != 'Administrateur') {
        header('Location: connexion.php');
        exit;
    }
}

// Vérifier si l'utilisateur est membre du projet
function verifierMembreProjet($id_projet) {
    require_once '../classes/Projet.php';
    
    $projet = new Projet();
    $est_membre = $projet->estMembreProjet($id_projet, $_SESSION['utilisateur_id']);
    $est_proprietaire = $projet->estProprietaire($id_projet, $_SESSION['utilisateur_id']);
    
    if (!$est_membre && !$est_proprietaire && $_SESSION['utilisateur_role'] != 'Administrateur') {
        header('Location: tableau_de_bord.php');
        exit;
    }
}

// Vérifier si l'utilisateur est le propriétaire du projet
function verifierProprietaireProjet($id_projet) {
    require_once '../classes/Projet.php';
    
    $projet = new Projet();
    $est_proprietaire = $projet->estProprietaire($id_projet, $_SESSION['utilisateur_id']);
    
    if (!$est_proprietaire && $_SESSION['utilisateur_role'] != 'Administrateur') {
        header('Location: projet.php?id=' . $id_projet);
        exit;
    }
}
?>
