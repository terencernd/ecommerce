<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Matchs & Résultats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "menu.php"; ?>

<div class="container mt-5" style="max-width: 900px;">

    <h2>Résultats récents</h2>

    <table class="table table-striped mt-4">
        <thead>
            <tr>
                <th>Date</th>
                <th>Adversaire</th>
                <th>Score</th>
                <th>Lieu</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>12/02/2026</td>
                <td>Volley Paris</td>
                <td>3 - 1</td>
                <td>Paris</td>
            </tr>
            <tr>
                <td>05/02/2026</td>
                <td>Nice Volley</td>
                <td>2 - 3</td>
                <td>Nice</td>
            </tr>
        </tbody>
    </table>

    <h2 class="mt-5">Matchs à venir</h2>

    <ul class="list-group mt-3">
        <li class="list-group-item">
            <strong>20/02/2026</strong> — Marseille Volley (Domicile)
        </li>
        <li class="list-group-item">
            <strong>27/02/2026</strong> — Lyon Volley (Extérieur)
        </li>
        <li class="list-group-item">
            <strong>05/03/2026</strong> — Toulouse Volley (Domicile)
        </li>
    </ul>

</div>

<?php include "footer.php"; ?>

</body>
</html>