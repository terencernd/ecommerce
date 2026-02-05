<?php
// Contrôleur pour les détails d'un produit
require_once __DIR__ . '/../models/Ball.php';

// Créer la connexion à la base de données
$database = new Database();
$db = $database->getConnection();

// Créer une instance du modèle Ball
$ball = new Ball($db);

// Récupérer l'ID du produit
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Récupérer les détails du produit
$produit = $ball->getById($productId);

// Si le produit n'existe pas, rediriger
if (!$produit) {
    header('Location: index.php?action=home');
    exit();
}

// Définir le titre de la page
$pageTitle = $produit['nom'] . " - VolleyShop";

// Charger la vue
require_once __DIR__ . '/../view/produits/details.php';
?>
