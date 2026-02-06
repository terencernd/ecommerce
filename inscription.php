<?php
session_start();
require "config.php";

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $password2 = trim($_POST['password2']);

    if ($password !== $password2) {
        $message = "<p class='text-danger'>Les mots de passe ne correspondent pas.</p>";
    } else {

        // Vérifier si l'email existe déjà
        $check = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $message = "<p class='text-danger'>Un compte existe déjà avec cet email.</p>";
        } else {

            // Hash du mot de passe
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Insertion
            $sql = $pdo->prepare("INSERT INTO users (nom, email, password, role) VALUES (?, ?, ?, 'user')");
            $sql->execute([$nom, $email, $hash]);

            // 🔥 Connexion automatique
            $_SESSION['nom'] = $nom;
            $_SESSION['role'] = "user";

            // 🔥 Redirection vers la boutique
            header("Location: boutique.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="padding-bottom: 80px; background:#f2f2f2;">

<?php include "menu.php"; ?>

<div class="d-flex justify-content-center mt-5">
    <div class="p-4 shadow bg-white rounded" style="width: 500px; min-height: 300px;">

        <h2 class="text-center">Créer un compte</h2>

        <?= $message ?>

        <form method="post" class="mt-3">

            <div class="mb-3">
                <label>Nom</label>
                <input type="text" name="nom" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Mot de passe</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Confirmer le mot de passe</label>
                <input type="password" name="password2" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100">S'inscrire</button>

        </form>

        <p class="text-center mt-3">
            Déjà un compte ? <a href="login.php">Connexion</a>
        </p>

    </div>
</div>

<?php include "footer.php"; ?>