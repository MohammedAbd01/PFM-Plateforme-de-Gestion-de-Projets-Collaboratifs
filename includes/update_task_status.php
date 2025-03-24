<?php
require_once '../classes/Tache.php';
require_once '../includes/protection.php';

// Vu00e9rifier si l'utilisateur est connectu00e9
verifierConnexion();

// Vu00e9rifier si les donnu00e9es requises sont fournies
if (!isset($_POST['id_tache']) || !isset($_POST['statut'])) {
    echo json_encode([
        'succes' => false,
        'message' => 'Les donnu00e9es requises sont manquantes.'
    ]);
    exit;
}

// Ru00e9cupu00e9rer les donnu00e9es
$id_tache = intval($_POST['id_tache']);
$statut = $_POST['statut'];

// Mettre u00e0 jour le statut de la tu00e2che
$tache_obj = new Tache();
$resultat = $tache_obj->mettreAJourStatutTache($id_tache, $statut);

// Renvoyer le ru00e9sultat
echo json_encode($resultat);
