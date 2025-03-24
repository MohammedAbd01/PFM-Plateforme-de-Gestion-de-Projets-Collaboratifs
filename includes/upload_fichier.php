<?php
require_once '../classes/Fichier.php';
require_once '../includes/protection.php';

header('Content-Type: application/json');

try {
    // Vérifier que l'utilisateur est connecté
    session_start();
    if (!isset($_SESSION['utilisateur_id'])) {
        echo json_encode([
            'succes' => false,
            'message' => 'Vous devez être connecté pour téléverser des fichiers.'
        ]);
        exit;
    }
    
    // Vérifier que la requête est bien une requête POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'succes' => false,
            'message' => 'Méthode non autorisée.'
        ]);
        exit;
    }
    
    // Vérifier qu'on a bien les données nécessaires
    if (!isset($_POST['id_projet']) || !isset($_FILES['fichier'])) {
        echo json_encode([
            'succes' => false,
            'message' => 'Données manquantes: ID du projet ou fichier.',
            'debug' => ['post' => $_POST, 'files' => $_FILES]
        ]);
        exit;
    }
    
    $id_projet = intval($_POST['id_projet']);
    $id_utilisateur = $_SESSION['utilisateur_id'];
    $nom_fichier = trim($_POST['nom_fichier'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Vérifier que le nom du fichier n'est pas vide
    if (empty($nom_fichier)) {
        echo json_encode([
            'succes' => false,
            'message' => 'Veuillez indiquer un nom pour le fichier.'
        ]);
        exit;
    }
    
    // Initialiser l'objet fichier et créer la table si nécessaire
    $fichier_obj = new Fichier();
    $check_table = $fichier_obj->creerTableFichiers();
    
    // Journaliser les détails avant upload pour débogage
    error_log("TABLE CHECK: " . json_encode($check_table));
    error_log("POST DATA: " . json_encode($_POST));
    error_log("FILE DATA: " . json_encode($_FILES));
    
    // Téléverser le fichier
    $resultat = $fichier_obj->telechargerFichier($id_projet, $id_utilisateur, $nom_fichier, $description, $_FILES['fichier']);
    
    // Journaliser le résultat
    error_log("UPLOAD RESULT: " . json_encode($resultat));
    
    // Vérifier les fichiers existants après l'upload
    $check_files = $fichier_obj->getFichiersProjet($id_projet);
    error_log("FILES AFTER UPLOAD: " . json_encode($check_files));
    
    // Ajouter des informations de débogage au résultat
    $resultat['debug_files'] = [
        'post' => $_POST,
        'files' => $_FILES,
        'files_after' => $check_files
    ];
    
    // Renvoyer la réponse
    echo json_encode($resultat);

} catch (Exception $e) {
    // Capturer toute exception et la journaliser
    error_log("UPLOAD ERROR: " . $e->getMessage());
    echo json_encode([
        'succes' => false,
        'message' => 'Erreur lors du téléversement: ' . $e->getMessage(),
        'debug' => ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]
    ]);
}
