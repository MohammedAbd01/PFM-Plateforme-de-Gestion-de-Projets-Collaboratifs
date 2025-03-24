<?php
// Initialiser la session
session_start();

/**
 * Vérifie si l'utilisateur est connecté
 * 
 * @return bool True si l'utilisateur est connecté, false sinon
 */
function estConnecte() {
    return isset($_SESSION['utilisateur_id']);
}

/**
 * Vérifie si la requête est une requête AJAX
 * 
 * @return bool True si la requête est AJAX, false sinon
 */
function isAjaxRequest() {
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
}

// Vérifier si l'utilisateur est connecté
if (!estConnecte()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté']);
    exit;
}
?>
