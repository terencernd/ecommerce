<?php
session_start();
require "config.php";

if (!isset($_SESSION['nom'])) {
    header("Location: login.php");
    exit;
}

$sql = $pdo->prepare("SELECT * FROM users WHERE nom = ?");
$sql->execute([$_SESSION['nom']]);
$user = $sql->fetch();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveau_nom   = trim($_POST['nom']);
    $nouveau_email = trim($_POST['email']);
    $ancien_mdp    = trim($_POST['ancien_password']);
    $nouveau_mdp   = trim($_POST['nouveau_password']);
    $confirm_mdp   = trim($_POST['confirm_password']);

    // Vérifier ancien mot de passe
    if (!password_verify($ancien_mdp, $user['password'])) {
        $message = "<div class='alert alert-danger'>Ancien mot de passe incorrect.</div>";
    } elseif ($nouveau_mdp && $nouveau_mdp !== $confirm_mdp) {
        $message = "<div class='alert alert-danger'>Les nouveaux mots de passe ne correspondent pas.</div>";
    } else {
        if ($nouveau_mdp) {
            $hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET nom=?, email=?, password=? WHERE id=?")
                ->execute([$nouveau_nom, $nouveau_email, $hash, $user['id']]);
        } else {
            $pdo->prepare("UPDATE users SET nom=?, email=? WHERE id=?")
                ->execute([$nouveau_nom, $nouveau_email, $user['id']]);
        }
        $_SESSION['nom'] = $nouveau_nom;
        $message = "<div class='alert alert-success'>Compte mis à jour avec succès !</div>";

        // Recharger les infos
        $sql = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $sql->execute([$user['id']]);
        $user = $sql->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier mon compte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f2f2f2; padding-bottom:80px;">
<?php include "menu.php"; ?>

<div class="container mt-5" style="max-width:600px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Modifier mon compte</h2>
        <a href="profil.php" class="btn btn-secondary">Retour profil</a>
    </div>

    <div class="bg-white shadow rounded p-4">
        <?= $message ?>
        <form method="post">

            <div class="mb-3">
                <label class="form-label">Nom</label>
                <input type="text" name="nom" class="form-control" required
                       value="<?= htmlspecialchars($user['nom']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required
                       value="<?= htmlspecialchars($user['email']) ?>">
            </div>

            <hr>
            <p class="text-muted">Changer le mot de passe (laisser vide pour ne pas le modifier)</p>

            <div class="mb-3">
                <label class="form-label">Ancien mot de passe *</label>
                <input type="password" name="ancien_password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nouveau mot de passe</label>
                <input type="password" name="nouveau_password" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Confirmer le nouveau mot de passe</label>
                <input type="password" name="confirm_password" class="form-control">
            </div>

            <button type="submit" class="btn btn-success w-100">Enregistrer les modifications</button>
        </form>
    </div>
</div>

<?php include "footer.php"; ?>
</body>
</html>