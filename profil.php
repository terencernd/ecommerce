<?php
session_start();
require "config.php";

if (!isset($_SESSION['nom'])) {
    header("Location: login.php");
    exit;
}

$sql = $pdo->prepare("SELECT * FROM users WHERE nom = ?");
$sql->execute([$_SESSION['nom']]);
$user = $sql->fetch();
$user_id = $user['id'];

$message = "";

// AJOUTER une adresse
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    $prenom      = trim($_POST['prenom']);
    $nom         = trim($_POST['nom']);
    $rue         = trim($_POST['rue']);
    $ville       = trim($_POST['ville']);
    $code_postal = trim($_POST['code_postal']);
    $pays        = trim($_POST['pays']);
    if ($prenom && $nom && $rue && $ville && $code_postal) {
        $pdo->prepare("INSERT INTO adresses (user_id, prenom, nom, rue, ville, code_postal, pays) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([$user_id, $prenom, $nom, $rue, $ville, $code_postal, $pays]);
        $message = "<div class='alert alert-success'>Adresse ajoutée !</div>";
    } else {
        $message = "<div class='alert alert-danger'>Veuillez remplir tous les champs.</div>";
    }
}

// MODIFIER une adresse
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier') {
    $pdo->prepare("UPDATE adresses SET prenom=?, nom=?, rue=?, ville=?, code_postal=?, pays=? WHERE id=? AND user_id=?")
        ->execute([
            trim($_POST['prenom']), trim($_POST['nom']), trim($_POST['rue']),
            trim($_POST['ville']), trim($_POST['code_postal']), trim($_POST['pays']),
            (int)$_POST['id_adresse'], $user_id
        ]);
    $message = "<div class='alert alert-success'>Adresse modifiée !</div>";
}

// SUPPRIMER une adresse
if (isset($_GET['supprimer'])) {
    $pdo->prepare("DELETE FROM adresses WHERE id = ? AND user_id = ?")->execute([(int)$_GET['supprimer'], $user_id]);
    header("Location: profil.php");
    exit;
}

$adresses = $pdo->prepare("SELECT * FROM adresses WHERE user_id = ? ORDER BY date_ajout DESC");
$adresses->execute([$user_id]);
$adresses = $adresses->fetchAll();

$edit_id = isset($_GET['modifier']) ? (int)$_GET['modifier'] : null;
$edit_adr = null;
if ($edit_id) {
    foreach ($adresses as $a) { if ($a['id'] === $edit_id) { $edit_adr = $a; break; } }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f2f2f2; padding-bottom:80px;">

<?php include "menu.php"; ?>

<div class="container mt-5" style="max-width:750px;">

    <!-- INFOS DU COMPTE -->
    <div class="p-4 bg-white shadow rounded mb-4">
        <h2 class="text-center mb-4">Mon Profil</h2>
        <p><strong>Nom :</strong> <?= htmlspecialchars($user['nom']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Role :</strong>
            <span class="badge <?= $user['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?>">
                <?= htmlspecialchars($user['role']) ?>
            </span>
        </p>

        <!-- LIENS DU PROFIL CLIENT -->
        <div class="row mt-4 g-2">
            <div class="col-md-4">
                <a href="modifier_compte.php" class="btn btn-outline-primary w-100">Modifier mon compte</a>
            </div>
            <div class="col-md-4">
                <a href="historique_commandes.php" class="btn btn-outline-success w-100">Historique des commandes</a>
            </div>
            <div class="col-md-4">
                <a href="boutique.php" class="btn btn-outline-secondary w-100">Retour boutique</a>
            </div>
        </div>
    </div>

    <?= $message ?>

    <!-- ADRESSES -->
    <div class="p-4 bg-white shadow rounded mb-4">
        <h4 class="mb-3">Mes adresses</h4>

        <?php if (empty($adresses)): ?>
            <p class="text-muted">Aucune adresse enregistrée.</p>
        <?php else: ?>
            <div class="row">
                <?php foreach ($adresses as $a): ?>
                <div class="col-md-6 mb-3">
                    <div class="card border h-100">
                        <div class="card-body">
                            <h6 class="fw-bold"><?= htmlspecialchars($a['prenom']) ?> <?= htmlspecialchars($a['nom']) ?></h6>
                            <p class="text-muted mb-0" style="font-size:0.9rem;">
                                <?= htmlspecialchars($a['rue']) ?><br>
                                <?= htmlspecialchars($a['code_postal']) ?> <?= htmlspecialchars($a['ville']) ?><br>
                                <?= htmlspecialchars($a['pays']) ?>
                            </p>
                        </div>
                        <div class="card-footer d-flex justify-content-end gap-2 p-2">
                            <a href="profil.php?modifier=<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                            <a href="profil.php?supprimer=<?= $a['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Supprimer cette adresse ?')">Supprimer</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- FORMULAIRE ADRESSE -->
    <div class="p-4 bg-white shadow rounded mb-4">
        <h4 class="mb-3"><?= $edit_adr ? "Modifier l'adresse" : "Ajouter une adresse" ?></h4>
        <form method="post">
            <input type="hidden" name="action" value="<?= $edit_adr ? 'modifier' : 'ajouter' ?>">
            <?php if ($edit_adr): ?>
                <input type="hidden" name="id_adresse" value="<?= $edit_adr['id'] ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Prenom</label>
                    <input type="text" name="prenom" class="form-control" required
                           value="<?= $edit_adr ? htmlspecialchars($edit_adr['prenom']) : '' ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" required
                           value="<?= $edit_adr ? htmlspecialchars($edit_adr['nom']) : '' ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Rue</label>
                <input type="text" name="rue" class="form-control" required
                       value="<?= $edit_adr ? htmlspecialchars($edit_adr['rue']) : '' ?>">
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Code postal</label>
                    <input type="text" name="code_postal" class="form-control" required
                           value="<?= $edit_adr ? htmlspecialchars($edit_adr['code_postal']) : '' ?>">
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Ville</label>
                    <input type="text" name="ville" class="form-control" required
                           value="<?= $edit_adr ? htmlspecialchars($edit_adr['ville']) : '' ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Pays</label>
                <input type="text" name="pays" class="form-control"
                       value="<?= $edit_adr ? htmlspecialchars($edit_adr['pays']) : 'France' ?>">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success w-100">
                    <?= $edit_adr ? 'Enregistrer les modifications' : "Ajouter l'adresse" ?>
                </button>
                <?php if ($edit_adr): ?>
                    <a href="profil.php" class="btn btn-secondary">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="text-center">
        <a href="logout.php" class="btn btn-danger px-5">Deconnexion</a>
    </div>

</div>

<?php include "footer.php"; ?>
</body>
</html>
