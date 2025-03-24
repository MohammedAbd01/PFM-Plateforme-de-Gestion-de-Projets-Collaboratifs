<?php
require_once __DIR__ . '/../config/database.php';

class Message {
    private $db;
    
    public function __construct() {
        $this->db = new BaseDeDonnees();
    }
    
    public function envoyer($idProjet, $idExpediteur, $idDestinataire, $message) {
        $pdo = $this->db->getPdo();
        
        $stmt = $pdo->prepare("
            INSERT INTO messages (id_projet, id_expediteur, id_destinataire, message, date_envoi) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $result = $stmt->execute([$idProjet, $idExpediteur, $idDestinataire, $message]);
        
        if ($result) {
            return ['success' => true, 'id_message' => $pdo->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de l\'envoi du message.'];
        }
    }
    
    public function obtenirMessagesProjet($idProjet) {
        $pdo = $this->db->getPdo();
        
        $stmt = $pdo->prepare("
            SELECT m.*, 
                exp.nom_utilisateur AS expediteur_nom,
                dest.nom_utilisateur AS destinataire_nom
            FROM messages m
            JOIN utilisateurs exp ON m.id_expediteur = exp.id
            LEFT JOIN utilisateurs dest ON m.id_destinataire = dest.id
            WHERE m.id_projet = ?
            ORDER BY m.date_envoi DESC
        ");
        $stmt->execute([$idProjet]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenirMessagesUtilisateur($idUtilisateur) {
        $pdo = $this->db->getPdo();
        
        $stmt = $pdo->prepare("
            SELECT m.*, 
                exp.nom_utilisateur AS expediteur_nom,
                p.nom_projet
            FROM messages m
            JOIN utilisateurs exp ON m.id_expediteur = exp.id
            JOIN projets p ON m.id_projet = p.id
            WHERE m.id_destinataire = ? OR (m.id_destinataire IS NULL AND m.id_projet IN (
                SELECT id_projet FROM membres_projet WHERE id_utilisateur = ? AND statut = 'accepte'
            ))
            ORDER BY m.date_envoi DESC
        ");
        $stmt->execute([$idUtilisateur, $idUtilisateur]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 