<?php
class BaseDeDonnees {
    private $pdo;
    
    public function __construct() {
        try {
            $dsn = "mysql:host=localhost;dbname=plateforme_projets;charset=utf8";
            $utilisateur = "root";
            $mot_de_passe = "";
            $this->pdo = new PDO($dsn, $utilisateur, $mot_de_passe);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
    
    public function getPdo() {
        return $this->pdo;
    }
}
?>
