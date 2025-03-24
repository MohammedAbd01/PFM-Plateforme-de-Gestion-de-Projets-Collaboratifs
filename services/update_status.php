<?php
require_once '../includes/auth_check.php';
require_once '../classes/Tache.php';
require_once '../classes/Projet.php';
require_once '../classes/BaseDeDonnees.php';

// Initialiser la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier la méthode et les paramètres
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_tache']) && isset($_POST['statut'])) {
    $idTache = (int)$_POST['id_tache'];
    $statut = $_POST['statut'];
    $idUtilisateur = $_SESSION['utilisateur']['id'];
    
    // Vérifier les valeurs autorisées pour le statut
    $statutsAutorises = ['À faire', 'En cours', 'Terminé'];
    if (!in_array($statut, $statutsAutorises)) {
        $_SESSION['message'] = 'Statut non valide.';
        $_SESSION['message_type'] = 'danger';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Initialiser les objets
    $tacheObj = new Tache();
    $projetObj = new Projet();
    
    // Obtenir la tâche
    $tache = $tacheObj->obtenirTache($idTache);
    
    if (!$tache) {
        $_SESSION['message'] = 'Tâche non trouvée.';
        $_SESSION['message_type'] = 'danger';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    // Récupérer l'ID du projet
    $idProjet = $tache['id_projet'];
    
    // Vérifier que l'utilisateur est membre du projet ou le propriétaire
    if ($projetObj->estMembre($idProjet, $idUtilisateur) || $projetObj->estProprietaire($idProjet, $idUtilisateur)) {
        // Mettre à jour le statut
        $db = new BaseDeDonnees();
        $stmt = $db->getPdo()->prepare("UPDATE taches SET statut = ? WHERE id = ?");
        $result = $stmt->execute([$statut, $idTache]);
        
        if ($result) {
            $_SESSION['message'] = 'Statut mis à jour avec succès!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Erreur lors de la mise à jour du statut.';
            $_SESSION['message_type'] = 'danger';
        }
    } else {
        $_SESSION['message'] = 'Vous n\'êtes pas autorisé à modifier cette tâche.';
        $_SESSION['message_type'] = 'danger';
    }
} else {
    $_SESSION['message'] = 'Paramètres manquants.';
    $_SESSION['message_type'] = 'danger';
}

// Rediriger vers la page précédente
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit; 