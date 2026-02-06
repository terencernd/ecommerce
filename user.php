<?php
session_start();
if (!isset($_SESSION['nom']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Espace Utilisateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "menu.php"; ?>

<div class="container mt-5 text-center">
    <div class="card p-4 shadow">
        <h2>Bienvenue <?php echo $_SESSION['nom']; ?> !</h2>
        <p>Vous êtes connecté en tant qu'utilisateur.</p>
        <a href="logout.php" class="btn btn-danger">Se déconnecter</a>
    </div>
</div>

<?php include "footer.php"; ?>

</body>
</html>