<?php
require_once 'config/Database.php';

/**
 * Classe Utilisateur
 * 
 * Cette classe gère toutes les opérations liées aux utilisateurs
 */
class Utilisateur {
    private $conn;
    private $table = 'utilisateurs';
    
    /**
     * Constructeur
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * Créer un nouvel utilisateur (inscription)
     * 
     * @param string $nomUtilisateur Le nom d'utilisateur
     * @param string $email L'email de l'utilisateur
     * @param string $motDePasse Le mot de passe en clair
     * @param string $role Le rôle de l'utilisateur
     * @return array Résultat de l'opération
     */
    public function inscrire($nomUtilisateur, $email, $motDePasse, $role = 'Utilisateur') {
        // Vérifier si le nom d'utilisateur ou l'email est déjà utilisé
        $query = "SELECT id FROM {$this->table} WHERE nom_utilisateur = :nom_utilisateur OR email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom_utilisateur', $nomUtilisateur);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return [
                'success' => false,
                'message' => 'Ce nom d\'utilisateur ou cette adresse email est déjà utilisé(e).'
            ];
        }
        
        // Hasher le mot de passe
        $motDePasseHash = password_hash($motDePasse, PASSWORD_DEFAULT);
        
        // Insérer le nouvel utilisateur
        $query = "INSERT INTO {$this->table} (nom_utilisateur, email, mot_de_passe, role) VALUES (:nom_utilisateur, :email, :mot_de_passe, :role)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom_utilisateur', $nomUtilisateur);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':mot_de_passe', $motDePasseHash);
        $stmt->bindParam(':role', $role);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Inscription réussie!',
                'id' => $this->conn->lastInsertId()
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Une erreur est survenue lors de l\'inscription.'
        ];
    }
    
    /**
     * Connecter un utilisateur
     * 
     * @param string $nomUtilisateur Le nom d'utilisateur
     * @param string $motDePasse Le mot de passe en clair
     * @return array Résultat de l'opération
     */
    public function connecter($nomUtilisateur, $motDePasse) {
        // Rechercher l'utilisateur par son nom d'utilisateur
        $query = "SELECT * FROM {$this->table} WHERE nom_utilisateur = :nom_utilisateur";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nom_utilisateur', $nomUtilisateur);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $utilisateur = $stmt->fetch();
            
            // Vérifier le mot de passe
            if (password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
                // Supprimer le mot de passe avant de stocker dans la session
                unset($utilisateur['mot_de_passe']);
                
                // Stocker les informations de l'utilisateur dans la session
                $_SESSION['utilisateur'] = $utilisateur;
                
                return [
                    'success' => true,
                    'message' => 'Connexion réussie!',
                    'utilisateur' => $utilisateur,
                    'role' => $utilisateur['role']
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'Nom d\'utilisateur ou mot de passe incorrect.'
        ];
    }
    
    /**
     * Obtenir les informations d'un utilisateur
     * 
     * @param int $id L'identifiant de l'utilisateur
     * @return array Les informations de l'utilisateur
     */
    public function obtenirUtilisateur($id) {
        $query = "SELECT id, nom_utilisateur, email, role, date_creation FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Obtenir tous les utilisateurs (pour l'administration)
     * 
     * @return array Liste des utilisateurs
     */
    public function obtenirTousLesUtilisateurs() {
        $query = "SELECT id, nom_utilisateur, email, role, date_creation FROM {$this->table} ORDER BY date_creation DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Modifier le profil d'un utilisateur
     * 
     * @param int $id L'identifiant de l'utilisateur
     * @param string $email Le nouvel email
     * @param string|null $motDePasse Le nouveau mot de passe (peut être null)
     * @return array Résultat de l'opération
     */
    public function modifierProfil($id, $email, $motDePasse = null) {
        // Vérifier si l'email est déjà utilisé par un autre utilisateur
        $query = "SELECT id FROM {$this->table} WHERE email = :email AND id != :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return [
                'success' => false,
                'message' => 'Cette adresse email est déjà utilisée par un autre utilisateur.'
            ];
        }
        
        // Si un nouveau mot de passe est fourni, le hasher
        if ($motDePasse) {
            $motDePasseHash = password_hash($motDePasse, PASSWORD_DEFAULT);
            $query = "UPDATE {$this->table} SET email = :email, mot_de_passe = :mot_de_passe WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':mot_de_passe', $motDePasseHash);
            $stmt->bindParam(':id', $id);
        } else {
            $query = "UPDATE {$this->table} SET email = :email WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $id);
        }
        
        if ($stmt->execute()) {
            // Mettre à jour l'email dans la session
            if (isset($_SESSION['utilisateur']) && $_SESSION['utilisateur']['id'] == $id) {
                $_SESSION['utilisateur']['email'] = $email;
            }
            
            return [
                'success' => true,
                'message' => 'Profil mis à jour avec succès!'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la mise à jour du profil.'
        ];
    }
    
    /**
     * Supprimer un utilisateur (pour l'administration)
     * 
     * @param int $id L'identifiant de l'utilisateur
     * @return array Résultat de l'opération
     */
    public function supprimerUtilisateur($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Utilisateur supprimé avec succès!'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la suppression de l\'utilisateur.'
        ];
    }
    
    /**
     * Rechercher des utilisateurs par nom d'utilisateur
     * 
     * @param string $nomUtilisateur Le nom d'utilisateur à rechercher
     * @return array Liste des utilisateurs correspondants
     */
    public function rechercher($nomUtilisateur) {
        $search = "%{$nomUtilisateur}%";
        $query = "SELECT id, nom_utilisateur, email, role FROM {$this->table} WHERE nom_utilisateur LIKE :search";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':search', $search);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?> 