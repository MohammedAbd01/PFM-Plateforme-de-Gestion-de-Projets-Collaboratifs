<?php
require_once __DIR__ . '/../config/database.php';

class Fichier {
    private $db;
    private $dossierTelechargements = __DIR__ . '/../telechargements/';
    
    public function __construct() {
        $this->db = new BaseDeDonnees();
        
        // Créer le dossier de téléchargements s'il n'existe pas
        if (!file_exists($this->dossierTelechargements)) {
            mkdir($this->dossierTelechargements, 0777, true);
        }
    }
    
    public function telecharger($idProjet, $fichier) {
        $pdo = $this->db->getPdo();
        
        // Générer un nom de fichier unique
        $nomFichierOriginal = $fichier['name'];
        $extension = pathinfo($nomFichierOriginal, PATHINFO_EXTENSION);
        $nomFichier = uniqid() . '.' . $extension;
        $chemin = 'telechargements/' . $nomFichier;
        
        // Déplacer le fichier téléchargé
        if (move_uploaded_file($fichier['tmp_name'], $this->dossierTelechargements . $nomFichier)) {
            // Enregistrer les informations du fichier dans la base de données
            $stmt = $pdo->prepare("
                INSERT INTO fichiers (id_projet, nom_fichier, chemin_fichier, date_televersement) 
                VALUES (?, ?, ?, NOW())
            ");
            $result = $stmt->execute([$idProjet, $nomFichierOriginal, $chemin]);
            
            if ($result) {
                return ['success' => true, 'id_fichier' => $pdo->lastInsertId()];
            } else {
                // Supprimer le fichier si l'enregistrement dans la base de données a échoué
                unlink($this->dossierTelechargements . $nomFichier);
                return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement du fichier.'];
            }
        } else {
            return ['success' => false, 'message' => 'Erreur lors du téléversement du fichier.'];
        }
    }
    
    public function obtenirFichiersProjet($idProjet) {
        $pdo = $this->db->getPdo();
        
        $stmt = $pdo->prepare("
            SELECT * FROM fichiers 
            WHERE id_projet = ? 
            ORDER BY date_televersement DESC
        ");
        $stmt->execute([$idProjet]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function estExtensionAutorisee($fichier) {
        $extensionsAutorisees = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'jpg', 'jpeg', 'png', 'gif'];
        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
        return in_array($extension, $extensionsAutorisees);
    }
    
    public function supprimerFichier($idFichier) {
        $pdo = $this->db->getPdo();
        
        // Récupérer les informations du fichier avant la suppression
        $stmt = $pdo->prepare("SELECT * FROM fichiers WHERE id = ?");
        $stmt->execute([$idFichier]);
        $fichier = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$fichier) {
            return ['success' => false, 'message' => 'Fichier non trouvé.'];
        }
        
        // Obtenir le chemin physique complet du fichier
        $cheminComplet = dirname(__DIR__) . '/' . $fichier['chemin_fichier'];
        
        // Supprimer de la base de données
        $stmt = $pdo->prepare("DELETE FROM fichiers WHERE id = ?");
        $result = $stmt->execute([$idFichier]);
        
        if ($result) {
            // Supprimer le fichier physique
            if (file_exists($cheminComplet)) {
                if (unlink($cheminComplet)) {
                    return ['success' => true, 'message' => 'Fichier supprimé avec succès.'];
                } else {
                    // Le fichier a été supprimé de la BDD mais le fichier physique n'a pas pu être supprimé
                    return ['success' => true, 'message' => 'Fichier supprimé de la base de données, mais le fichier physique n\'a pas pu être supprimé.'];
                }
            } else {
                // Le fichier n'existe pas physiquement mais a été supprimé de la BDD
                return ['success' => true, 'message' => 'Fichier supprimé de la base de données.'];
            }
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la suppression du fichier.'];
        }
    }
    
    public function getFichierParId($idFichier) {
        $pdo = $this->db->getPdo();
        
        $stmt = $pdo->prepare("SELECT * FROM fichiers WHERE id = ?");
        $stmt->execute([$idFichier]);
        $fichier = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($fichier) {
            return ['succes' => true, 'fichier' => $fichier];
        } else {
            return ['succes' => false, 'message' => 'Fichier non trouvé.'];
        }
    }
}
?> 