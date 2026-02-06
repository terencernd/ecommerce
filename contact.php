<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "menu.php"; ?>

<div class="container mt-5">
    <h2>Contact</h2>

    <form class="mt-4">
        <div class="mb-3">
            <label>Nom</label>
            <input type="text" class="form-control">
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" class="form-control">
        </div>

        <div class="mb-3">
            <label>Message</label>
            <textarea class="form-control"></textarea>
        </div>

        <button class="btn btn-primary">Envoyer</button>
    </form>
</div>

<?php include "footer.php"; ?>

</body>
</html>