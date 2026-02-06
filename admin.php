<?php
session_start();
if (!isset($_SESSION['nom']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "menu.php"; ?>

<div class="container mt-5" style="max-width: 900px;">
    <div class="card p-4 shadow text-center">
        <h2>Bienvenue Admin <?php echo $_SESSION['nom']; ?> !</h2>
        <p>Gestion du site Volleyball</p>

        <a href="user.php" class="btn btn-primary">Voir espace utilisateur</a>
        <a href="logout.php" class="btn btn-danger mt-3">Se déconnecter</a>
    </div>
</div>

<?php include "footer.php"; ?>

</body>
</html>