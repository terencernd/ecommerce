<?php
session_start();
require "config.php";

// Optionnel : empêcher les non-admin d'accéder
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     header("Location: index.php");
//     exit;
// }

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titre = trim($_POST['titre']);
    $contenu = trim($_POST['contenu']);

    if ($titre === "" || $contenu === "") {
        $message = "<p class='text-danger'>Veuillez remplir tous les champs.</p>";
    } else {
        $sql = $pdo->prepare("INSERT INTO actualites (titre, contenu) VALUES (?, ?)");
        $sql->execute([$titre, $contenu]);

        $message = "<p class='text-success'>Actualité ajoutée avec succès !</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une actualité</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#f2f2f2; padding-bottom:80px;">

<?php include "menu.php"; ?>

<div class="container mt-5">
    <div class="p-4 bg-white shadow rounded" style="max-width: 700px; margin:auto;">

        <h2 class="text-center mb-4">Ajouter une actualité</h2>

        <?= $message ?>

        <form method="post">

            <div class="mb-3">
                <label class="form-label">Titre</label>
                <input type="text" name="titre" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Contenu</label>
                <textarea name="contenu" class="form-control" rows="6" required></textarea>
            </div>

            <button class="btn btn-primary w-100">Publier l'actualité</button>

        </form>

    </div>
</div>

</body>
</html>