<?php
// Modèle pour les balles de volley (produits)
class Ball {
    private $conn;
    private $table = 'produits';

    // Propriétés
    public $id;
    public $nom;
    public $description;
    public $prix;
    public $stock;
    public $image;
    public $date_ajout;

    // Constructeur avec connexion DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Récupérer tous les produits
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer un produit par ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Vérifier le stock disponible
    public function checkStock($id, $quantity) {
        $product = $this->getById($id);
        return ($product && $product['stock'] >= $quantity);
    }

    // Mettre à jour le stock
    public function updateStock($id, $quantity) {
        $query = "UPDATE " . $this->table . " SET stock = stock - :quantity WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Rechercher des produits
    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE nom LIKE :keyword OR description LIKE :keyword 
                  ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
