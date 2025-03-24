<?php
require_once '../includes/ajax_protection.php';
require_once '../classes/Projet.php';
require_once '../classes/BaseDeDonnees.php';

// Initialiser la réponse
$response = ['success' => false];

// Vérifier la méthode et les paramètres
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_projet'])) {
    $idProjet = (int)$_POST['id_projet'];
    
    // Initialiser l'objet Projet
    $projetObj = new Projet();
    
    // Vérifier que le projet existe
    $projet = $projetObj->obtenirProjet($idProjet);
    
    if (!$projet) {
        $response['message'] = 'Projet non trouvé.';
        echo json_encode($response);
        exit;
    }
    
    // Calculer le score du projet
    $score = $projetObj->calculerScore($idProjet);
    
    // Déterminer la classe de style pour la barre de progression
    $scoreClass = '';
    if ($score < 30) {
        $scoreClass = 'bg-danger';
    } elseif ($score < 70) {
        $scoreClass = 'bg-warning';
    } else {
        $scoreClass = 'bg-success';
    }
    
    $response = [
        'success' => true,
        'message' => 'Score calculé avec succès!',
        'score' => $score,
        'scoreClass' => $scoreClass
    ];
}

// Envoyer la réponse JSON
header('Content-Type: application/json');
echo json_encode($response); 