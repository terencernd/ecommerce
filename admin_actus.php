<?php
session_start();
require "config.php";

if (!isset($_SESSION['nom']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";

// Supprimer une actualité
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    $pdo->prepare("DELETE FROM actualites WHERE id = ?")->execute([$id]);
    header("Location: admin_actus.php");
    exit;
}

// Ajouter une actualité
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre   = trim($_POST['titre']);
    $contenu = trim($_POST['contenu']);

    if ($titre === "" || $contenu === "") {
        $message = "<div class='alert alert-danger'>Veuillez remplir tous les champs.</div>";
    } else {
        $pdo->prepare("INSERT INTO actualites (titre, contenu) VALUES (?, ?)")->execute([$titre, $contenu]);
        $message = "<div class='alert alert-success'>Actualité publiée avec succès !</div>";
    }
}

$actus = $pdo->query("SELECT * FROM actualites ORDER BY date_publication DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Actualités</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f2f2f2; padding-bottom:80px;">

<?php include "menu.php"; ?>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📰 Gestion des actualités</h2>
        <a href="admin.php" class="btn btn-secondary">← Retour admin</a>
    </div>

    <!-- FORMULAIRE AJOUT -->
    <div class="p-4 bg-white shadow rounded mb-4">
        <h4 class="mb-3">Ajouter une actualité</h4>
        <?= $message ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Titre</label>
                <input type="text" name="titre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contenu</label>
                <textarea name="contenu" class="form-control" rows="4" required></textarea>
            </div>
            <button class="btn btn-info w-100">Publier</button>
        </form>
    </div>

    <!-- LISTE DES ACTUS -->
    <div class="p-4 bg-white shadow rounded">
        <h4 class="mb-3">Actualités publiées (<?= count($actus) ?>)</h4>

        <?php if (empty($actus)): ?>
            <p class="text-muted">Aucune actualité pour l'instant.</p>
        <?php else: ?>
            <?php foreach ($actus as $a): ?>
            <div class="border rounded p-3 mb-3 bg-light">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5><?= htmlspecialchars($a['titre']) ?></h5>
                        <small class="text-muted"><?= $a['date_publication'] ?></small>
                        <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars(substr($a['contenu'], 0, 150))) ?>...</p>
                    </div>
                    <a href="admin_actus.php?supprimer=<?= $a['id'] ?>"
                       class="btn btn-sm btn-danger ms-3"
                       onclick="return confirm('Supprimer cette actualité ?')">
                        
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php include "footer.php"; ?>
</body>
</html>