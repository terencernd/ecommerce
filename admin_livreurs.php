<?php
session_start();
require "config.php";

if (!isset($_SESSION['nom']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";

// SUPPRIMER
if (isset($_GET['supprimer'])) {
    $pdo->prepare("DELETE FROM livreurs WHERE id = ?")->execute([(int)$_GET['supprimer']]);
    header("Location: admin_livreurs.php");
    exit;
}

// AJOUTER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'ajouter') {
    $nom        = trim($_POST['nom']);
    $prenom     = trim($_POST['prenom']);
    $telephone  = trim($_POST['telephone']);
    $email      = trim($_POST['email']);
    $zone       = trim($_POST['zone']);
    $disponible = isset($_POST['disponible']) ? 1 : 0;

    if ($nom && $prenom) {
        $pdo->prepare("INSERT INTO livreurs (nom, prenom, telephone, email, zone, disponible) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$nom, $prenom, $telephone, $email, $zone, $disponible]);
        $message = "<div class='alert alert-success'>Livreur ajouté !</div>";
    } else {
        $message = "<div class='alert alert-danger'>Nom et prénom obligatoires.</div>";
    }
}

// MODIFIER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'modifier') {
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    $pdo->prepare("UPDATE livreurs SET nom=?, prenom=?, telephone=?, email=?, zone=?, disponible=? WHERE id=?")
        ->execute([
            trim($_POST['nom']),
            trim($_POST['prenom']),
            trim($_POST['telephone']),
            trim($_POST['email']),
            trim($_POST['zone']),
            $disponible,
            (int)$_POST['id_livreur']
        ]);
    $message = "<div class='alert alert-success'>Livreur modifié !</div>";
}

$livreurs = $pdo->query("SELECT * FROM livreurs ORDER BY nom")->fetchAll();

$edit_id  = isset($_GET['modifier']) ? (int)$_GET['modifier'] : null;
$edit_liv = null;
if ($edit_id) {
    foreach ($livreurs as $l) { if ($l['id'] == $edit_id) { $edit_liv = $l; break; } }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Livreurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f2f2f2; padding-bottom:80px;">
<?php include "menu.php"; ?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestion des livreurs (<?= count($livreurs) ?>)</h2>
        <a href="admin.php" class="btn btn-secondary">Retour admin</a>
    </div>

    <?= $message ?>

    <!-- LISTE -->
    <div class="bg-white shadow rounded p-4 mb-4">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Telephone</th>
                    <th>Email</th>
                    <th>Zone</th>
                    <th>Disponible</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($livreurs)): ?>
                    <tr><td colspan="7" class="text-center text-muted">Aucun livreur</td></tr>
                <?php endif; ?>
                <?php foreach ($livreurs as $l): ?>
                <tr>
                    <td><?= $l['id'] ?></td>
                    <td><?= htmlspecialchars($l['prenom']) ?> <?= htmlspecialchars($l['nom']) ?></td>
                    <td><?= htmlspecialchars($l['telephone']) ?></td>
                    <td><?= htmlspecialchars($l['email']) ?></td>
                    <td><?= htmlspecialchars($l['zone']) ?></td>
                    <td>
                        <span class="badge <?= $l['disponible'] ? 'bg-success' : 'bg-danger' ?>">
                            <?= $l['disponible'] ? 'Disponible' : 'Indisponible' ?>
                        </span>
                    </td>
                    <td class="d-flex gap-2">
                        <a href="admin_livreurs.php?modifier=<?= $l['id'] ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                        <a href="admin_livreurs.php?supprimer=<?= $l['id'] ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Supprimer ce livreur ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- FORMULAIRE -->
    <div class="bg-white shadow rounded p-4">
        <h4 class="mb-3"><?= $edit_liv ? 'Modifier le livreur' : 'Ajouter un livreur' ?></h4>
        <form method="post">
            <input type="hidden" name="action" value="<?= $edit_liv ? 'modifier' : 'ajouter' ?>">
            <?php if ($edit_liv): ?>
                <input type="hidden" name="id_livreur" value="<?= $edit_liv['id'] ?>">
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Prenom *</label>
                    <input type="text" name="prenom" class="form-control" required
                           value="<?= $edit_liv ? htmlspecialchars($edit_liv['prenom']) : '' ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom *</label>
                    <input type="text" name="nom" class="form-control" required
                           value="<?= $edit_liv ? htmlspecialchars($edit_liv['nom']) : '' ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Telephone</label>
                    <input type="text" name="telephone" class="form-control"
                           value="<?= $edit_liv ? htmlspecialchars($edit_liv['telephone']) : '' ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= $edit_liv ? htmlspecialchars($edit_liv['email']) : '' ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Zone de livraison</label>
                <input type="text" name="zone" class="form-control" placeholder="Ex : Paris, Banlieue Nord..."
                       value="<?= $edit_liv ? htmlspecialchars($edit_liv['zone']) : '' ?>">
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="disponible" class="form-check-input" id="disponible"
                       <?= (!$edit_liv || $edit_liv['disponible']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="disponible">Disponible</label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success w-100">
                    <?= $edit_liv ? 'Enregistrer' : 'Ajouter le livreur' ?>
                </button>
                <?php if ($edit_liv): ?>
                    <a href="admin_livreurs.php" class="btn btn-secondary">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php include "footer.php"; ?>
</body>
</html>