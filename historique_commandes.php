<?php
session_start();
require "config.php";

if (!isset($_SESSION['nom'])) {
    header("Location: login.php");
    exit;
}

$commandes = $pdo->prepare("SELECT * FROM commandes_club WHERE client = ? ORDER BY date_cmd DESC");
$commandes->execute([$_SESSION['nom']]);
$commandes = $commandes->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des commandes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f2f2f2; padding-bottom:80px;">
<?php include "menu.php"; ?>

<div class="container mt-5" style="max-width:800px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Historique des commandes</h2>
        <a href="profil.php" class="btn btn-secondary">Retour profil</a>
    </div>

    <?php if (empty($commandes)): ?>
        <div class="alert alert-info">Vous n'avez pas encore de commande.
            <a href="boutique.php">Voir la boutique</a>
        </div>
    <?php else: ?>
        <?php foreach ($commandes as $c): ?>
        <div class="bg-white shadow rounded p-4 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Commande #<?= $c['id'] ?></h5>
                <span class="badge bg-<?= $c['statut'] === 'livrée' ? 'success' : ($c['statut'] === 'en cours' ? 'warning' : 'secondary') ?>">
                    <?= htmlspecialchars($c['statut'] ?? 'en attente') ?>
                </span>
            </div>
            <p class="text-muted mb-1" style="font-size:0.85rem;">Le <?= date('d/m/Y à H:i', strtotime($c['date_cmd'])) ?></p>
            <p class="mb-1" style="font-size:0.9rem;"><?= htmlspecialchars($c['detail']) ?></p>
            <p class="fw-bold text-success mb-0">Total : <?= number_format($c['montant'], 2) ?> €</p>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>

<?php include "footer.php"; ?>
</body>
</html>