<?php
session_start();
require "config.php";

// Si pas connecté → redirection
if (!isset($_SESSION['nom'])) {
    header("Location: login.php");
    exit;
}

// Récupérer les infos de l'utilisateur
$sql = $pdo->prepare("SELECT * FROM users WHERE nom = ?");
$sql->execute([$_SESSION['nom']]);
$user = $sql->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="padding-bottom: 80px; background:#f2f2f2;">

<?php include "menu.php"; ?>

<div class="d-flex justify-content-center mt-5">
    <div class="p-4 shadow bg-white rounded" style="width: 600px;">

        <h2 class="text-center mb-4">Mon Profil</h2>

        <p><strong>Nom :</strong> <?= htmlspecialchars($user['nom']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Rôle :</strong> <?= htmlspecialchars($user['role']) ?></p>

        <div class="mt-4 text-center">
            <a href="boutique.php" class="btn btn-primary">Retour à la boutique</a>
        </div>

    </div>
</div>

<?php include "footer.php"; ?>