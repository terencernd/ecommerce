<?php
session_start();
require "config.php";

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

// Récupérer les adresses de l'utilisateur connecté
$adresses = [];
if (isset($_SESSION['nom'])) {
    $stmt = $pdo->prepare("SELECT u.id, adresses.* FROM users u JOIN adresses ON adresses.user_id = u.id WHERE u.nom = ?");
    $stmt->execute([$_SESSION['nom']]);
    $adresses = $stmt->fetchAll();
}

// Sauvegarder l'adresse choisie en session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adresse_id'])) {
    $_SESSION['adresse_livraison'] = (int)$_POST['adresse_id'];
    header("Location: panier.php");
    exit;
}

// Calculer le total
$total = 0;
foreach ($_SESSION['panier'] as $item) {
    $total += $item['prix'] * $item['quantite'];
}

// Adresse sélectionnée
$adresse_choisie = null;
if (!empty($_SESSION['adresse_livraison']) && !empty($adresses)) {
    foreach ($adresses as $a) {
        if ($a['id'] == $_SESSION['adresse_livraison']) {
            $adresse_choisie = $a;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Panier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f2f2f2; padding-bottom:80px;">

<?php include "menu.php"; ?>

<div class="container mt-5" style="max-width: 900px;">
    <h2 class="mb-4">Mon Panier</h2>

    <?php if (empty($_SESSION['panier'])): ?>
        <div class="alert alert-info">
            Votre panier est vide. <a href="boutique.php">Continuer vos achats</a>
        </div>
    <?php else: ?>

        <!-- TABLEAU DU PANIER -->
        <div class="bg-white shadow rounded p-4 mb-4">
            <table class="table table-striped">
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
                        <td><?= htmlspecialchars($item['nom']) ?></td>
                        <td><?= number_format($item['prix'], 2) ?> €</td>
                        <td><?= $item['quantite'] ?></td>
                        <td><?= number_format($item['prix'] * $item['quantite'], 2) ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="3" class="text-end">Total :</td>
                        <td><?= number_format($total, 2) ?> €</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- ADRESSE DE LIVRAISON -->
        <div class="bg-white shadow rounded p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Adresse de livraison</h4>
                <a href="profil.php" class="btn btn-sm btn-outline-primary">Gérer mes adresses</a>
            </div>

            <?php if (!isset($_SESSION['nom'])): ?>
                <div class="alert alert-warning">
                    Vous devez être <a href="login.php">connecté</a> pour choisir une adresse.
                </div>

            <?php elseif (empty($adresses)): ?>
                <div class="alert alert-info">
                    Vous n'avez pas encore d'adresse enregistrée.
                    <a href="profil.php">Ajouter une adresse</a>
                </div>

            <?php else: ?>

                <!-- Adresse actuellement sélectionnée -->
                <?php if ($adresse_choisie): ?>
                    <div class="alert alert-success mb-3">
                        <strong>Adresse sélectionnée :</strong><br>
                        <?= htmlspecialchars($adresse_choisie['prenom']) ?> <?= htmlspecialchars($adresse_choisie['nom']) ?><br>
                        <?= htmlspecialchars($adresse_choisie['rue']) ?><br>
                        <?= htmlspecialchars($adresse_choisie['code_postal']) ?> <?= htmlspecialchars($adresse_choisie['ville']) ?> — <?= htmlspecialchars($adresse_choisie['pays']) ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-3">Veuillez choisir une adresse de livraison.</div>
                <?php endif; ?>

                <!-- Choix de l'adresse -->
                <form method="post">
                    <div class="row">
                        <?php foreach ($adresses as $a): ?>
                        <div class="col-md-6 mb-3">
                            <label class="d-block cursor-pointer">
                                <div class="card h-100 <?= (!empty($_SESSION['adresse_livraison']) && $_SESSION['adresse_livraison'] == $a['id']) ? 'border-success border-2' : 'border' ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?= htmlspecialchars($a['prenom']) ?> <?= htmlspecialchars($a['nom']) ?></strong><br>
                                                <span style="font-size:0.85rem;" class="text-muted">
                                                    <?= htmlspecialchars($a['rue']) ?><br>
                                                    <?= htmlspecialchars($a['code_postal']) ?> <?= htmlspecialchars($a['ville']) ?><br>
                                                    <?= htmlspecialchars($a['pays']) ?>
                                                </span>
                                            </div>
                                            <div class="ms-2">
                                                <input type="radio" name="adresse_id" value="<?= $a['id'] ?>"
                                                    <?= (!empty($_SESSION['adresse_livraison']) && $_SESSION['adresse_livraison'] == $a['id']) ? 'checked' : '' ?>>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-outline-success w-100">Confirmer cette adresse</button>
                </form>

            <?php endif; ?>
        </div>

        <!-- BOUTONS -->
        <div class="d-flex justify-content-between">
            <a href="boutique.php" class="btn btn-secondary">Continuer mes achats</a>
            <div class="d-flex gap-2">
                <a href="vider_panier.php" class="btn btn-danger">Vider le panier</a>
                <?php if ($adresse_choisie): ?>
                    <a href="commander.php" class="btn btn-success">Commander</a>
                <?php else: ?>
                    <button class="btn btn-success" disabled title="Choisissez une adresse">Commander</button>
                <?php endif; ?>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php include "footer.php"; ?>
</body>
</html>