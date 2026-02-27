<?php
session_start();
require "config.php";

if (!isset($_SESSION['nom']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Création automatique des tables si elles n'existent pas
$pdo->exec("CREATE TABLE IF NOT EXISTS commandes_club (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client VARCHAR(255) NOT NULL,
    detail TEXT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    date_cmd DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Stats
$nbUsers     = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$nbCommandes = $pdo->query("SELECT COUNT(*) FROM commandes_club")->fetchColumn();
$totalCA     = $pdo->query("SELECT SUM(montant) FROM commandes_club")->fetchColumn() ?? 0;
$nbActus     = $pdo->query("SELECT COUNT(*) FROM actualites")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f2f2f2; padding-bottom:80px;">

<?php include "menu.php"; ?>

<div class="container mt-5">

    <div class="p-4 bg-white shadow rounded mb-4 text-center">
        <h2> Espace Admin — <?= htmlspecialchars($_SESSION['nom']) ?></h2>
        <p class="text-muted">Tableau de bord de gestion du site Volley Club</p>
    </div>

    <!-- STATS -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-primary shadow text-center p-3">
                <div style="font-size:2.5rem;"></div>
                <h3><?= $nbUsers ?></h3>
                <p class="mb-0">Utilisateurs</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-success shadow text-center p-3">
                <div style="font-size:2.5rem;"></div>
                <h3><?= $nbCommandes ?></h3>
                <p class="mb-0">Commandes</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-warning shadow text-center p-3">
                <div style="font-size:2.5rem;"></div>
                <h3><?= number_format($totalCA, 2) ?> €</h3>
                <p class="mb-0">Chiffre d'affaires</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-white bg-info shadow text-center p-3">
                <div style="font-size:2.5rem;"></div>
                <h3><?= $nbActus ?></h3>
                <p class="mb-0">Actualités</p>
            </div>
        </div>
    </div>

    <!-- MENU ADMIN -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card shadow h-100 text-center p-4">
                <div style="font-size:3rem;"></div>
                <h4 class="mt-2">Utilisateurs</h4>
                <p class="text-muted">Voir et gérer les comptes</p>
                <a href="admin_users.php" class="btn btn-primary mt-auto">Gérer</a>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow h-100 text-center p-4">
                <div style="font-size:3rem;"></div>
                <h4 class="mt-2">Commandes</h4>
                <p class="text-muted">Voir toutes les commandes</p>
                <a href="admin_commandes.php" class="btn btn-success mt-auto">Gérer</a>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow h-100 text-center p-4">
                <div style="font-size:3rem;"></div>
                <h4 class="mt-2">Actualités</h4>
                <p class="text-muted">Ajouter ou supprimer des actus</p>
                <a href="admin_actus.php" class="btn btn-info mt-auto">Gérer</a>
            </div>
        </div>
    </div>

    <div class="text-center">
        <a href="logout.php" class="btn btn-danger px-5">Se déconnecter</a>
    </div>

</div>

<?php include "footer.php"; ?>
</body>
</html>