<?php
// Contrôleur de la page d'accueil
require_once __DIR__ . '/../models/Ball.php';

// Créer la connexion à la base de données
$database = new Database();
$db = $database->getConnection();

// Créer une instance du modèle Ball
$ball = new Ball($db);

// Récupérer tous les produits
$produits = $ball->getAll();

// Définir le titre de la page
$pageTitle = "Accueil - VolleyShop";

// Charger la vue
require_once __DIR__ . '/../view/produits/index.php';
?>
