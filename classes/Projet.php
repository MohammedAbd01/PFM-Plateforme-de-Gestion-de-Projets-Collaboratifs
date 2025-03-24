<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Classe Projet
 * 
 * Cette classe gère toutes les opérations liées aux projets
 */
class Projet {
    private $conn;
    private $table = 'projets';
    private $table_membres = 'membres_projet';
    private $table_taches = 'taches';
    
    /**
     * Constructeur
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Créer un nouveau projet
     * 
     * @param string $nomProjet Le nom du projet
     * @param string $description La description du projet
     * @param int $idProprietaire L'identifiant du propriétaire
     * @return array Résultat de l'opération
     */
    public function creer($nomProjet, $description, $idProprietaire) {
        // Insérer le projet
        $query = "INSERT INTO {$this->table} (nom_projet, description, id_proprietaire) VALUES (:nom_projet, :description, :id_proprietaire)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom_projet', $nomProjet);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':id_proprietaire', $idProprietaire);
        
        if ($stmt->execute()) {
            $idProjet = $this->conn->lastInsertId();
            
            // Ajouter automatiquement le propriétaire comme membre du projet
            $query = "INSERT INTO {$this->table_membres} (id_projet, id_utilisateur, statut) VALUES (:id_projet, :id_utilisateur, 'accepte')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_projet', $idProjet);
            $stmt->bindParam(':id_utilisateur', $idProprietaire);
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Projet créé avec succès!',
                'id_projet' => $idProjet
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la création du projet.'
        ];
    }
    
    /**
     * Obtenir un projet par son ID
     * 
     * @param int $id L'identifiant du projet
     * @return array Le projet ou null
     */
    public function obtenirProjet($id) {
        $query = "SELECT p.*, u.nom_utilisateur AS proprietaire_nom 
                 FROM {$this->table} p
                 JOIN utilisateurs u ON p.id_proprietaire = u.id
                 WHERE p.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        
        return null;
    }
    
    /**
     * Obtenir tous les projets dont l'utilisateur est membre
     * 
     * @param int $idUtilisateur L'identifiant de l'utilisateur
     * @return array Liste des projets
     */
    public function obtenirProjetsUtilisateur($idUtilisateur) {
        $query = "SELECT p.*, u.nom_utilisateur AS proprietaire_nom,
                 (SELECT COUNT(*) FROM {$this->table_taches} WHERE id_projet = p.id) AS nombre_taches
                 FROM {$this->table} p
                 JOIN {$this->table_membres} mp ON p.id = mp.id_projet
                 JOIN utilisateurs u ON p.id_proprietaire = u.id
                 WHERE mp.id_utilisateur = :id_utilisateur AND mp.statut = 'accepte'
                 ORDER BY p.date_creation DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_utilisateur', $idUtilisateur);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir tous les projets (pour l'administration)
     * 
     * @return array Liste de tous les projets
     */
    public function obtenirTousProjets() {
        $query = "SELECT p.*, u.nom_utilisateur AS proprietaire_nom,
                 (SELECT COUNT(*) FROM {$this->table_membres} WHERE id_projet = p.id AND statut = 'accepte') AS nombre_membres
                 FROM {$this->table} p
                 JOIN utilisateurs u ON p.id_proprietaire = u.id
                 ORDER BY p.date_creation DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtenir les invitations en attente d'un utilisateur
     * 
     * @param int $idUtilisateur L'identifiant de l'utilisateur
     * @return array Liste des invitations
     */
    public function obtenirInvitationsEnAttente($idUtilisateur) {
        $query = "SELECT p.*, u.nom_utilisateur AS proprietaire_nom
                 FROM {$this->table} p
                 JOIN {$this->table_membres} mp ON p.id = mp.id_projet
                 JOIN utilisateurs u ON p.id_proprietaire = u.id
                 WHERE mp.id_utilisateur = :id_utilisateur AND mp.statut = 'en_attente'
                 ORDER BY mp.date_ajout DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_utilisateur', $idUtilisateur);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Accepter une invitation à un projet
     * 
     * @param int $idProjet L'identifiant du projet
     * @param int $idUtilisateur L'identifiant de l'utilisateur
     * @return array Résultat de l'opération
     */
    public function accepterInvitation($idProjet, $idUtilisateur) {
        $query = "UPDATE {$this->table_membres} 
                 SET statut = 'accepte' 
                 WHERE id_projet = :id_projet AND id_utilisateur = :id_utilisateur AND statut = 'en_attente'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_projet', $idProjet);
        $stmt->bindParam(':id_utilisateur', $idUtilisateur);
        
        if ($stmt->execute() && $stmt->rowCount() > 0) {
            return [
                'success' => true,
                'message' => 'Invitation acceptée avec succès!'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Une erreur est survenue ou l\'invitation n\'existe pas.'
        ];
    }
    
    /**
     * Inviter un membre à un projet
     * 
     * @param int $idProjet L'identifiant du projet
     * @param int $idUtilisateur L'identifiant de l'utilisateur à inviter
     * @return array Résultat de l'opération
     */
    public function inviterMembre($idProjet, $idUtilisateur) {
        // Vérifier si l'utilisateur est déjà membre du projet
        $query = "SELECT * FROM {$this->table_membres} 
                 WHERE id_projet = :id_projet AND id_utilisateur = :id_utilisateur";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_projet', $idProjet);
        $stmt->bindParam(':id_utilisateur', $idUtilisateur);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $membre = $stmt->fetch();
            if ($membre['statut'] === 'accepte') {
                return [
                    'success' => false,
                    'message' => 'Cet utilisateur est déjà membre du projet.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Cet utilisateur a déjà été invité au projet.'
                ];
            }
        }
        
        // Inviter l'utilisateur
        $query = "INSERT INTO {$this->table_membres} (id_projet, id_utilisateur, statut) 
                 VALUES (:id_projet, :id_utilisateur, 'en_attente')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_projet', $idProjet);
        $stmt->bindParam(':id_utilisateur', $idUtilisateur);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Invitation envoyée avec succès!'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Une erreur est survenue lors de l\'envoi de l\'invitation.'
        ];
    }
    
    /**
     * Obtenir tous les membres d'un projet
     * 
     * @param int $idProjet L'identifiant du projet
     * @return array Liste des membres
     */
    public function obtenirMembres($idProjet) {
        $query = "SELECT u.id, u.nom_utilisateur, mp.statut
                 FROM {$this->table_membres} mp
                 JOIN utilisateurs u ON mp.id_utilisateur = u.id
                 WHERE mp.id_projet = :id_projet
                 ORDER BY mp.statut DESC, u.nom_utilisateur";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_projet', $idProjet);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Supprimer un projet (pour l'administration ou le propriétaire)
     * 
     * @param int $idProjet L'identifiant du projet
     * @return array Résultat de l'opération
     */
    public function supprimerProjet($idProjet) {
        $query = "DELETE FROM {$this->table} WHERE id = :id_projet";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_projet', $idProjet);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Projet supprimé avec succès!'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la suppression du projet.'
        ];
    }
    
    /**
     * Vérifier si un utilisateur est membre d'un projet
     * 
     * @param int $idProjet L'identifiant du projet
     * @param int $idUtilisateur L'identifiant de l'utilisateur
     * @return bool True si l'utilisateur est membre, sinon False
     */
    public function estMembre($idProjet, $idUtilisateur) {
        $query = "SELECT * FROM {$this->table_membres} 
                 WHERE id_projet = :id_projet AND id_utilisateur = :id_utilisateur AND statut = 'accepte'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_projet', $idProjet);
        $stmt->bindParam(':id_utilisateur', $idUtilisateur);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Vérifier si un utilisateur est propriétaire d'un projet
     * 
     * @param int $idProjet L'identifiant du projet
     * @param int $idUtilisateur L'identifiant de l'utilisateur
     * @return bool True si l'utilisateur est propriétaire, sinon False
     */
    public function estProprietaire($idProjet, $idUtilisateur) {
        $query = "SELECT * FROM {$this->table} 
                 WHERE id = :id_projet AND id_proprietaire = :id_utilisateur";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_projet', $idProjet);
        $stmt->bindParam(':id_utilisateur', $idUtilisateur);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Calculer le score (pourcentage) de progression d'un projet
     * 
     * @param int $idProjet L'identifiant du projet
     * @return int Score entre 0 et 100
     */
    public function calculerScore($idProjet) {
        // Obtenir le nombre total de tâches
        $query = "SELECT COUNT(*) as total FROM {$this->table_taches} WHERE id_projet = :id_projet";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_projet', $idProjet);
        $stmt->execute();
        $result = $stmt->fetch();
        $totalTaches = $result['total'];
        
        if ($totalTaches === 0) {
            return 0;
        }
        
        // Obtenir le nombre de tâches terminées
        $query = "SELECT COUNT(*) as terminees FROM {$this->table_taches} WHERE id_projet = :id_projet AND statut = 'Terminé'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_projet', $idProjet);
        $stmt->execute();
        $result = $stmt->fetch();
        $tachesTerminees = $result['terminees'];
        
        // Obtenir le nombre de tâches en cours
        $query = "SELECT COUNT(*) as en_cours FROM {$this->table_taches} WHERE id_projet = :id_projet AND statut = 'En cours'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_projet', $idProjet);
        $stmt->execute();
        $result = $stmt->fetch();
        $tachesEnCours = $result['en_cours'];
        
        // Calculer le score (les tâches terminées comptent pour 100%, les tâches en cours pour 50%)
        $score = round((($tachesTerminees * 1) + ($tachesEnCours * 0.5)) / $totalTaches * 100);
        
        return $score;
    }
}
?> 