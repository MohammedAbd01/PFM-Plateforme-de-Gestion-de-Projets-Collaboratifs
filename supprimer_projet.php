<?php
require_once 'includes/protection.php';
require_once 'classes/Projet.php';

// Vérifier si l'utilisateur est connecté
verifierConnexion();

// Vérifier si l'ID du projet est spécifié
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: tableau_de_bord.php');
    exit;
}

$id_projet = intval($_GET['id']);

// Initialiser l'objet projet
$projet_obj = new Projet();

// Vérifier si l'utilisateur est propriétaire du projet
if (!$projet_obj->estProprietaire($id_projet, $_SESSION['utilisateur_id']) && $_SESSION['utilisateur_role'] !== 'Administrateur') {
    header('Location: tableau_de_bord.php?message=Vous n\'avez pas le droit de supprimer ce projet.&type=danger');
    exit;
}

// Supprimer le projet
$resultat = $projet_obj->supprimerProjet($id_projet);

if ($resultat['succes']) {
    header('Location: tableau_de_bord.php?message=Projet supprimé avec succès.&type=success');
} else {
    header('Location: projet.php?id=' . $id_projet . '&message=' . $resultat['message'] . '&type=danger');
}
exit;
?>
