<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Boutique Volleyball</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "menu.php"; ?>

<div class="container mt-5" style="max-width: 900px;">
    <h2 class="mb-4">Boutique Volleyball</h2>

    <div class="row">

        <div class="col-md-4">
            <div class="card shadow">
                <img src="https://via.placeholder.com/300x200" class="card-img-top">
                <div class="card-body">
                    <h5 class="card-title">Ballon Mikasa</h5>
                    <p class="card-text">Ballon officiel FIVB</p>
                    <p class="fw-bold">49,99 €</p>
                    <button class="btn btn-primary w-100">Ajouter au panier</button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow">
                <img src="https://via.placeholder.com/300x200" class="card-img-top">
                <div class="card-body">
                    <h5 class="card-title">Maillot Officiel</h5>
                    <p class="card-text">Maillot respirant haute qualité</p>
                    <p class="fw-bold">29,99 €</p>
                    <button class="btn btn-primary w-100">Ajouter au panier</button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow">
                <img src="https://via.placeholder.com/300x200" class="card-img-top">
                <div class="card-body">
                    <h5 class="card-title">Filet Pro</h5>
                    <p class="card-text">Filet de compétition</p>
                    <p class="fw-bold">89,99 €</p>
                    <button class="btn btn-primary w-100">Ajouter au panier</button>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include "footer.php"; ?>

</body>
</html>