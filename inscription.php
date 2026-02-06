<?php
session_start();
require "config.php"; // Connexion à la base de données

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validation
    if (empty($nom) || empty($email) || empty($password)) {
        $error = "Tous les champs sont obligatoires";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        // Vérifier si l'utilisateur existe déjà
        $sql = "SELECT * FROM users WHERE nom = :nom OR email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['nom' => $nom, 'email' => $email]);
        
        if ($stmt->fetch()) {
            $error = "Ce nom ou email est déjà utilisé";
        } else {
            // Créer le nouvel utilisateur
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (nom, email, password, role) VALUES (:nom, :email, :password, 'user')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nom' => $nom,
                'email' => $email,
                'password' => $hashed_password
            ]);
            
            $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
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
<body>

<?php include "menu.php"; ?>

<div class="container mt-5" style="max-width: 500px;">
    <div class="card p-4 shadow">
        <h2>Inscription</h2>

        <?php if (!empty($error)) echo "<p class='text-danger'>$error</p>"; ?>
        <?php if (!empty($success)) echo "<p class='text-success'>$success</p>"; ?>

        <form method="post">
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
                <input type="password" name="confirm_password" class="form-control" required>
            </div>

            <button class="btn btn-success w-100">S'inscrire</button>
        </form>

        <p class="mt-3 text-center">Déjà inscrit ? <a href="login.php">Se connecter</a></p>
    </div>
</div>

<?php include "footer.php"; ?>

</body>
</html>