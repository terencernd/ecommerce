<?php
// Configuration de la base de données
class Database {
    private $host = 'localhost';
    private $db_name = 'volleyshop';
    private $username = 'root';
    private $password = '';
    private $conn;

    // Méthode pour obtenir la connexion
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Erreur de connexion: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>
