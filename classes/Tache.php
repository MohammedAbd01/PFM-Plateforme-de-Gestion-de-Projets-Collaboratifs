<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/BaseDeDonnees.php';

class Tache {
    private $db;
    
    public function __construct() {
        $this->db = new BaseDeDonnees();
    }
    
    public function creer($idProjet, $nomTache, $description, $assigneA, $statut, $dateEcheance) {
        $pdo = $this->db->getPdo();
        
        $stmt = $pdo->prepare("
            INSERT INTO taches (id_projet, nom_tache, description, assigne_a, statut, date_echeance) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([$idProjet, $nomTache, $description, $assigneA, $statut, $dateEcheance]);
        
        if ($result) {
            return ['success' => true, 'id_tache' => $pdo->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la création de la tâche.'];
        }
    }
    
    public function obtenirTache($idTache) {
        $pdo = $this->db->getPdo();
        
        $stmt = $pdo->prepare("
            SELECT t.*, u.nom_utilisateur AS assigne_nom
            FROM taches t
            LEFT JOIN utilisateurs u ON t.assigne_a = u.id
            WHERE t.id = ?
        ");
        $stmt->execute([$idTache]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function obtenirTachesProjet($idProjet) {
        $pdo = $this->db->getPdo();
        
        $stmt = $pdo->prepare("
            SELECT t.*, u.nom_utilisateur AS assigne_nom
            FROM taches t
            LEFT JOIN utilisateurs u ON t.assigne_a = u.id
            WHERE t.id_projet = ?
            ORDER BY t.date_echeance ASC
        ");
        $stmt->execute([$idProjet]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function obtenirTachesUtilisateur($idUtilisateur) {
        $pdo = $this->db->getPdo();
        
        $stmt = $pdo->prepare("
            SELECT t.*, p.nom_projet
            FROM taches t
            JOIN projets p ON t.id_projet = p.id
            WHERE t.assigne_a = ?
            ORDER BY t.date_echeance ASC
        ");
        $stmt->execute([$idUtilisateur]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function mettreAJourStatut($idTache, $statut) {
        $pdo = $this->db->getPdo();
        
        $stmt = $pdo->prepare("UPDATE taches SET statut = ? WHERE id = ?");
        $result = $stmt->execute([$statut, $idTache]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Statut mis à jour avec succès!'];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour du statut.'];
        }
    }
    
    public function estAssigneA($idTache, $idUtilisateur) {
        $pdo = $this->db->getPdo();
        
        $stmt = $pdo->prepare("SELECT assigne_a FROM taches WHERE id = ?");
        $stmt->execute([$idTache]);
        $tache = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $tache && $tache['assigne_a'] == $idUtilisateur;
    }
}
?> 