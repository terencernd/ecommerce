<?php
session_start();

// Vérifier si les paramètres existent
if (!isset($_GET['id']) || !isset($_GET['nom']) || !isset($_GET['prix'])) {
    header("Location: boutique.php");
    exit;
}

$id = $_GET['id'];
$nom = $_GET['nom'];
$prix = $_GET['prix'];

// Initialiser le panier si vide
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Si le produit existe déjà → augmenter la quantité
if (isset($_SESSION['panier'][$id])) {
    $_SESSION['panier'][$id]['quantite']++;
} else {
    // Sinon → ajouter le produit
    $_SESSION['panier'][$id] = [
        "nom" => $nom,
        "prix" => $prix,
        "quantite" => 1
    ];
}

// Redirection vers la boutique
header("Location: boutique.php");
exit;