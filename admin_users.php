<?php
session_start();
require "config.php";

if (!isset($_SESSION['nom']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Supprimer un utilisateur
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    header("Location: admin_users.php");
    exit;
}

// Récupérer tous les utilisateurs avec leurs adresses
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();

// Récupérer toutes les adresses groupées par user_id
$adresses_raw = $pdo->query("SELECT * FROM adresses ORDER BY user_id, date_ajout DESC")->fetchAll();
$adresses = [];
foreach ($adresses_raw as $a) {
    $adresses[$a['user_id']][] = $a;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f2f2f2; padding-bottom:80px;">

<?php include "menu.php"; ?>

<div class="container mt-5">
    <div class="p-4 bg-white shadow rounded">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestion des utilisateurs (<?= count($users) ?>)</h2>
            <a href="admin.php" class="btn btn-secondary">Retour admin</a>
        </div>

        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Adresse(s)</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['nom']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="badge <?= $u['role'] === 'admin' ? 'bg-danger' : 'bg-primary' ?>">
                            <?= htmlspecialchars($u['role']) ?>
                        </span>
                    </td>
                    <td style="font-size:0.85rem;">
                        <?php if (!empty($adresses[$u['id']])): ?>
                            <?php foreach ($adresses[$u['id']] as $a): ?>
                                <div class="mb-1 p-2 bg-light rounded">
                                    <strong><?= htmlspecialchars($a['prenom']) ?> <?= htmlspecialchars($a['nom']) ?></strong><br>
                                    <?= htmlspecialchars($a['rue']) ?><br>
                                    <?= htmlspecialchars($a['code_postal']) ?> <?= htmlspecialchars($a['ville']) ?><br>
                                    <?= htmlspecialchars($a['pays']) ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-muted">Aucune adresse</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($u['nom'] !== $_SESSION['nom']): ?>
                            <a href="admin_users.php?supprimer=<?= $u['id'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Supprimer cet utilisateur ?')">
                                Supprimer
                            </a>
                        <?php else: ?>
                            <span class="text-muted">Vous-même</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>
</div>

<?php include "footer.php"; ?>
</body>
</html>