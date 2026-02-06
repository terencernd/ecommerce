<?php
session_start();

// Vider le panier
$_SESSION['panier'] = [];

// Rediriger vers le panier
header("Location: panier.php");
exit;
?>