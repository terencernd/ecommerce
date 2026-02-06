<?php
session_start();
require "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE nom = :nom";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['nom' => $nom]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['nom'] = $user['nom'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: user.php");
        }
        exit;
    }

    $error = "Identifiants incorrects";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "menu.php"; ?>

<div class="container mt-5" style="max-width: 500px;">
    <div class="card p-4 shadow">
        <h2>Connexion</h2>

        <?php if (!empty($error)) echo "<p class='text-danger'>$error</p>"; ?>

        <form method="post">
            <div class="mb-3">
                <label>Nom</label>
                <input type="text" name="nom" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button class="btn btn-success w-100">Se connecter</button>
        </form>
    </div>
</div>

<?php include "footer.php"; ?>

</body>
</html>