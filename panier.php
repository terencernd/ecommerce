<?php
session_start();
require "config.php"; // Connexion à la base de données

// Initialiser le panier si nécessaire
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Calculer le total
$total = 0;
foreach ($_SESSION['panier'] as $item) {
    $total += $item['prix'] * $item['quantite'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Panier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "menu.php"; ?>

<div class="container mt-5" style="max-width: 900px;">
    <h2>Mon Panier</h2>

    <?php if (empty($_SESSION['panier'])): ?>
        <div class="alert alert-info mt-4">
            Votre panier est vide. <a href="boutique.php">Continuer vos achats</a>
        </div>
    <?php else: ?>
        <table class="table table-striped mt-4">
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Prix unitaire</th>
                    <th>Quantité</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['panier'] as $id => $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['nom']); ?></td>
                    <td><?php echo number_format($item['prix'], 2); ?> €</td>
                    <td><?php echo $item['quantite']; ?></td>
                    <td><?php echo number_format($item['prix'] * $item['quantite'], 2); ?> €</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td colspan="3" class="text-end">Total :</td>
                    <td><?php echo number_format($total, 2); ?> €</td>
                </tr>
            </tfoot>
        </table>

        <div class="d-flex justify-content-between mt-4">
            <a href="boutique.php" class="btn btn-secondary">Continuer mes achats</a>
            <div>
                <a href="vider_panier.php" class="btn btn-danger">Vider le panier</a>
                <button class="btn btn-success">Commander</button>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include "footer.php"; ?>

</body>
</html>