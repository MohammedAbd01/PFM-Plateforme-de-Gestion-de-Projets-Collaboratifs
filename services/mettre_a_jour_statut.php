<?php
require_once '../includes/ajax_protection.php';
require_once '../classes/Tache.php';
require_once '../classes/Projet.php';
require_once '../classes/BaseDeDonnees.php';

// Initialiser la réponse
$response = ['success' => false];

// Vérifier la méthode et les paramètres
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_tache']) && isset($_POST['statut'])) {
    $idTache = (int)$_POST['id_tache'];
    $statut = $_POST['statut'];
    
    // Vérifier les valeurs autorisées pour le statut
    $statutsAutorises = ['À faire', 'En cours', 'Terminé'];
    if (!in_array($statut, $statutsAutorises)) {
        $response['message'] = 'Statut non valide.';
        echo json_encode($response);
        exit;
    }
    
    // Initialiser les objets
    $tacheObj = new Tache();
    $projetObj = new Projet();
    
    // Obtenir la tâche
    $tache = $tacheObj->obtenirTache($idTache);
    
    if (!$tache) {
        $response['message'] = 'Tâche non trouvée.';
        echo json_encode($response);
        exit;
    }
    
    // Récupérer l'ID du projet
    $idProjet = $tache['id_projet'];
    
    // Mettre à jour le statut directement avec PDO
    $db = new BaseDeDonnees();
    $stmt = $db->getPdo()->prepare("UPDATE taches SET statut = ? WHERE id = ?");
    $result = $stmt->execute([$statut, $idTache]);
    
    if ($result) {
        // Calculer le nouveau score du projet directement
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
            'message' => 'Statut mis à jour avec succès!',
            'score' => $score,
            'scoreClass' => $scoreClass
        ];
    } else {
        $response['message'] = 'Erreur lors de la mise à jour du statut.';
    }
}

// Envoyer la réponse JSON
header('Content-Type: application/json');
echo json_encode($response); 