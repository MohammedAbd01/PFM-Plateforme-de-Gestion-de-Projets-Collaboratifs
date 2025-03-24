<?php
require_once '../classes/Tache.php';
require_once '../includes/protection.php';

// Vu00e9rifier si l'utilisateur est connectu00e9
verifierConnexion();

// Vu00e9rifier si l'ID de la tu00e2che est fourni
if (!isset($_POST['id_tache'])) {
    echo json_encode([
        'succes' => false,
        'message' => 'ID de la tu00e2che manquant.'
    ]);
    exit;
}

// Ru00e9cupu00e9rer l'ID de la tu00e2che
$id_tache = intval($_POST['id_tache']);

// Supprimer la tu00e2che
$tache_obj = new Tache();
$resultat = $tache_obj->supprimerTache($id_tache);

// Renvoyer le ru00e9sultat
echo json_encode($resultat);
