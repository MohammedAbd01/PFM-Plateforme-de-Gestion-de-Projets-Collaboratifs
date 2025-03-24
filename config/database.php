<?php
/**
 * Classe de connexion à la base de données
 * 
 * Cette classe gère la connexion à la base de données en utilisant PDO
 */
class Database {
    // Paramètres de connexion à la base de données
    private $host = 'localhost';
    private $db_name = 'plateforme_projets';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    private $conn;
    
    /**
     * Obtenir la connexion à la base de données
     * 
     * @return PDO Instance de connexion PDO
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $e) {
            echo "Erreur de connexion à la base de données: " . $e->getMessage();
        }
        
        return $this->conn;
    }
}
?> 