<?php
session_start();
require "config.php"; // Connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $produit_id = intval($_POST['produit_id']);
    $nom_produit = trim($_POST['nom_produit']);
    $prix = floatval($_POST['prix']);
    $quantite = isset($_POST['quantite']) ? intval($_POST['quantite']) : 1;
    
    // Initialiser le panier si nécessaire
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }
    
    // Ajouter ou mettre à jour le produit dans le panier
    if (isset($_SESSION['panier'][$produit_id])) {
        $_SESSION['panier'][$produit_id]['quantite'] += $quantite;
    } else {
        $_SESSION['panier'][$produit_id] = [
            'nom' => $nom_produit,
            'prix' => $prix,
            'quantite' => $quantite
        ];
    }
    
    // Rediriger vers le panier
    header("Location: panier.php");
    exit;
}
?>