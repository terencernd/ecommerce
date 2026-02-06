<?php
session_start();
require "config.php";

$sql = $pdo->query("SELECT * FROM actualites ORDER BY date_publication DESC");
$actus = $sql->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Actualités</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#f2f2f2; padding-bottom:80px;">

<?php include "menu.php"; ?>

<div class="container mt-5">

    <h2 class="mb-4">Actualités du club</h2>

    <?php foreach ($actus as $a): ?>
        <div class="p-4 bg-white shadow rounded mb-4">
            <h3><?= htmlspecialchars($a['titre']) ?></h3>
            <p class="text-muted"><?= $a['date_publication'] ?></p>
            <p><?= nl2br(htmlspecialchars($a['contenu'])) ?></p>
        </div>
    <?php endforeach; ?>

</div>

</body>
</html>