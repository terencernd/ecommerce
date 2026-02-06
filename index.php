<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - Volley Club</title>

    <!-- BOOTSTRAP CSS UNIQUEMENT -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="background:#f2f2f2; padding-bottom:80px;">

<?php include "menu.php"; ?>

<div class="container mt-5">

    <!-- BLOC PRINCIPAL -->
    <div class="p-5 bg-white shadow rounded text-center mb-5">
        <h1 class="mb-4">Bienvenue au Volley Club site officiel de moureu fc</h1>
        <p class="fs-5">Découvrez nos actualités, nos matchs et notre boutique officielle.</p>
    </div>

    <!-- 3 BLOCS -->
    <div class="row">

        <!-- ACTUALITÉS -->
        <div class="col-md-4 mb-4">
            <div class="p-4 bg-white shadow rounded h-100">
                <h3 class="mb-3">Actualités</h3>
                <p>Retrouvez les dernières infos du club : résultats, annonces, événements.</p>
                <a href="actualites.php" class="btn btn-outline-primary w-100">Voir les actualités</a>
            </div>
        </div>

        <!-- ACTUALITÉS BOUTIQUE -->
        <div class="col-md-4 mb-4">
            <div class="p-4 bg-white shadow rounded h-100">
                <h3 class="mb-3">Actualités Boutique</h3>
                <p>Nouveaux produits, promotions, arrivages exclusifs pour les supporters.</p>
                <a href="boutique.php" class="btn btn-outline-success w-100">Voir la boutique</a>
            </div>
        </div>

        <!-- PROCHAIN MATCH -->
        <div class="col-md-4 mb-4">
            <div class="p-4 bg-white shadow rounded h-100">
                <h3 class="mb-3">Prochain Match</h3>
                <p><strong>Volley Club vs Paris Est</strong></p>
                <p>Samedi 14 février – 18h00</p>
                <a href="matchs.php" class="btn btn-outline-danger w-100">Voir les matchs</a>
            </div>
        </div>

    </div>

</div>

</body>
</html>