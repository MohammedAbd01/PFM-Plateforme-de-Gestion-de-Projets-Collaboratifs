<?php
require_once 'classes/Fichier.php';
require_once 'includes/protection.php';

// Débogage pour voir les erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Vérifier si l'utilisateur est connecté
verifierConnexion();

// Vérifier si l'ID du fichier est spécifié
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo 'Erreur: ID du fichier non spécifié.';
    exit;
}

$id_fichier = intval($_GET['id']);

// Initialiser l'objet Fichier
$fichier_obj = new Fichier();

// Récupérer les informations du fichier
$resultat = $fichier_obj->getFichierParId($id_fichier);

// Déboguer le résultat
error_log("DEBUG - Informations du fichier $id_fichier: " . json_encode($resultat));

if (!$resultat['succes']) {
    echo 'Erreur: ' . $resultat['message'];
    exit;
}

$fichier = $resultat['fichier'];

// Vérifier si l'utilisateur a accès au fichier
$id_projet = $fichier['id_projet'];

// Vérifier les chemins du fichier
$chemin_fichier = $fichier['chemin_fichier'];
$chemin_complet = dirname(__FILE__) . '/' . $chemin_fichier;

// Vérifier si le fichier existe
if (!file_exists($chemin_complet)) {
    error_log("DEBUG - Fichier non trouvé à $chemin_complet");
    
    echo 'Erreur: Fichier non trouvé sur le serveur.';
    echo '<pre>Chemin attendu : ' . htmlspecialchars($chemin_complet) . '</pre>';
    exit;
}

// Obtenir les informations du fichier
$nom_fichier = $fichier['nom_fichier'];
$type_fichier = $fichier['type_fichier'];
$taille_fichier = filesize($chemin_complet);

// Débogage final
error_log("DEBUG - Téléchargement du fichier $id_fichier: $chemin_complet, taille: $taille_fichier octets");

// Définir les en-têtes pour le téléchargement
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $nom_fichier . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . $taille_fichier);

// Envoyer le fichier
readfile($chemin_complet);
exit;
