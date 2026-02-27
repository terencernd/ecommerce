<?php
session_start();
require "config.php";

if (!isset($_SESSION['nom'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['panier'])) {
    header("Location: boutique.php");
    exit;
}

// Récupérer l'adresse choisie
$adresse_livraison = null;
if (!empty($_SESSION['adresse_livraison'])) {
    $stmt = $pdo->prepare("SELECT * FROM adresses WHERE id = ?");
    $stmt->execute([$_SESSION['adresse_livraison']]);
    $adresse_livraison = $stmt->fetch();
}

// Calcul du total
$total = 0;
foreach ($_SESSION['panier'] as $item) {
    $total += $item['prix'] * $item['quantite'];
}

$succes = false;

// Paiement validé depuis paiement.php
if (isset($_GET['paiement']) && $_GET['paiement'] === 'ok' && !empty($_SESSION['panier'])) {

    $lignes = [];
    foreach ($_SESSION['panier'] as $item) {
        $lignes[] = $item['nom'] . " x" . $item['quantite'] . " (" . number_format($item['prix'] * $item['quantite'], 2) . " €)";
    }
    $detail = implode(" | ", $lignes);

    $adresse_txt = "";
    if ($adresse_livraison) {
        $adresse_txt = " | Livraison : " . $adresse_livraison['prenom'] . " " . $adresse_livraison['nom']
            . " — " . $adresse_livraison['rue'] . ", "
            . $adresse_livraison['code_postal'] . " " . $adresse_livraison['ville'];
    }

    $methode = isset($_GET['methode']) ? htmlspecialchars($_GET['methode']) : 'carte';

    $stmt = $pdo->prepare("INSERT INTO commandes_club (client, detail, montant, statut) VALUES (?, ?, ?, 'en attente')");
    $stmt->execute([$_SESSION['nom'], $detail . $adresse_txt . " | Paiement: " . $methode, $total]);

    $_SESSION['panier'] = [];
    unset($_SESSION['adresse_livraison']);
    $total  = 0;
    $succes = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $lignes = [];
    foreach ($_SESSION['panier'] as $item) {
        $lignes[] = $item['nom'] . " x" . $item['quantite'] . " (" . number_format($item['prix'] * $item['quantite'], 2) . " €)";
    }
    $detail = implode(" | ", $lignes);

    // Construire l'adresse en texte
    $adresse_txt = "";
    if ($adresse_livraison) {
        $adresse_txt = $adresse_livraison['prenom']." ".$adresse_livraison['nom']." — "
            .$adresse_livraison['rue'].", "
            .$adresse_livraison['code_postal']." "
            .$adresse_livraison['ville'].", "
            .$adresse_livraison['pays'];
    }

    $stmt = $pdo->prepare("INSERT INTO commandes_club (client, detail, montant, statut) VALUES (?, ?, ?, 'en attente')");
    $stmt->execute([$_SESSION['nom'], $detail . ($adresse_txt ? " | Livraison : ".$adresse_txt : ""), $total]);

    // Vider panier et adresse sélectionnée
    $_SESSION['panier'] = [];
    unset($_SESSION['adresse_livraison']);
    $total = 0;

    $succes = true;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commander - Volley Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f2f2f2; padding-bottom:80px;">

<?php include "menu.php"; ?>

<div class="container mt-5" style="max-width:700px;">

    <?php if ($succes): ?>

        <div class="p-5 bg-white shadow rounded text-center">
            <h2 class="text-success mt-3">Commande confirmee !</h2>
            <p class="fs-5 mt-3">
                Merci <strong><?= htmlspecialchars($_SESSION['nom'] ?? '') ?></strong>,<br>
                votre commande a bien ete enregistree.
            </p>
            <p class="text-muted">Vous serez contacte prochainement pour la livraison.</p>
            <a href="boutique.php" class="btn btn-primary mt-4 px-5">Retour a la boutique</a>
        </div>

    <?php else: ?>

        <div class="p-4 bg-white shadow rounded mb-4">
            <h2 class="mb-4">Recapitulatif de la commande</h2>

            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Produit</th>
                        <th>Prix unit.</th>
                        <th>Qte</th>
                        <th>Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['panier'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nom']) ?></td>
                        <td><?= number_format($item['prix'], 2) ?> €</td>
                        <td><?= $item['quantite'] ?></td>
                        <td><?= number_format($item['prix'] * $item['quantite'], 2) ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-success fw-bold">
                        <td colspan="3" class="text-end">Total :</td>
                        <td><?= number_format($total, 2) ?> €</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- ADRESSE DE LIVRAISON -->
        <div class="p-4 bg-white shadow rounded mb-4">
            <h5 class="mb-2">Adresse de livraison</h5>
            <?php if ($adresse_livraison): ?>
                <p class="mb-0">
                    <strong><?= htmlspecialchars($adresse_livraison['prenom']) ?> <?= htmlspecialchars($adresse_livraison['nom']) ?></strong><br>
                    <?= htmlspecialchars($adresse_livraison['rue']) ?><br>
                    <?= htmlspecialchars($adresse_livraison['code_postal']) ?> <?= htmlspecialchars($adresse_livraison['ville']) ?><br>
                    <?= htmlspecialchars($adresse_livraison['pays']) ?>
                </p>
            <?php else: ?>
                <p class="text-muted">Aucune adresse selectionnee. <a href="panier.php">Retour au panier</a></p>
            <?php endif; ?>
        </div>

        <div class="p-4 bg-white shadow rounded">
            <p class="text-muted mb-3">Commande pour : <strong><?= htmlspecialchars($_SESSION['nom']) ?></strong></p>
            <form method="post">
                <div class="d-flex justify-content-between">
                    <a href="panier.php" class="btn btn-secondary">Retour au panier</a>
                    <a href="paiement.php" class="btn btn-success btn-lg px-5">Proceder au paiement</a>
                </div>
            </form>
        </div>

    <?php endif; ?>

</div>

<?php include "footer.php"; ?>
</body>
</html>