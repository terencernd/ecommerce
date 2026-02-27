<?php
session_start();
require "config.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $sql->execute([$email]);
    $user = $sql->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['nom']  = $user['nom'];
        $_SESSION['role'] = $user['role'];
        header("Location: boutique.php");
        exit;
    }

    $error = "Email ou mot de passe incorrect.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="padding-bottom:80px; background:#f2f2f2;">

<?php include "menu.php"; ?>

<div class="d-flex justify-content-center mt-5">
    <div class="p-4 shadow bg-white rounded" style="width:500px;">

        <h2 class="text-center mb-4">Connexion</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="post">

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button class="btn btn-success w-100">Se connecter</button>

        </form>

        <div class="text-center mt-3">
            <a href="mot_de_passe_oublie.php" class="text-muted" style="font-size:0.9rem;">
                Mot de passe oublie ?
            </a>
        </div>

        <p class="text-center mt-2">
            Pas de compte ? <a href="inscription.php">S'inscrire</a>
        </p>

    </div>
</div>

<?php include "footer.php"; ?>
</body>
</html>