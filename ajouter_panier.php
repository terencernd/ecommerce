<?php
session_start();

if (!isset($_GET['id']) || !isset($_GET['nom']) || !isset($_GET['prix'])) {
    header("Location: boutique.php");
    exit;
}

$id       = $_GET['id'];
$nom      = $_GET['nom'];
$prix     = (float)$_GET['prix'];
$quantite = isset($_GET['quantite']) ? max(1, (int)$_GET['quantite']) : 1;

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

if (isset($_SESSION['panier'][$id])) {
    $_SESSION['panier'][$id]['quantite'] += $quantite;
} else {
    $_SESSION['panier'][$id] = [
        "nom"      => $nom,
        "prix"     => $prix,
        "quantite" => $quantite
    ];
}

header("Location: boutique.php");
exit;