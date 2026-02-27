<?php
session_start();
require "config.php";

// Créer la table tokens si elle n'existe pas
$pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    expire DATETIME NOT NULL,
    utilise TINYINT(1) DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$message = "";
$token_valide = false;
$email_token  = "";

// ── ÉTAPE 3 : Changement du mot de passe ─────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'changer') {

    $token     = trim($_POST['token']);
    $password  = trim($_POST['password']);
    $password2 = trim($_POST['password2']);

    $errors = [];

    if (strlen($password) < 8)          $errors[] = "Au moins 8 caracteres.";
    if (!preg_match('/[A-Z]/', $password)) $errors[] = "Au moins 1 majuscule.";
    if (!preg_match('/[0-9]/', $password)) $errors[] = "Au moins 1 chiffre.";
    if (!preg_match('/[\W_]/', $password)) $errors[] = "Au moins 1 caractere special.";
    if ($password !== $password2)       $errors[] = "Les mots de passe ne correspondent pas.";

    if (empty($errors)) {
        // Vérifier le token
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND utilise = 0 AND expire > NOW()");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if ($reset) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password = ? WHERE email = ?")->execute([$hash, $reset['email']]);
            $pdo->prepare("UPDATE password_resets SET utilise = 1 WHERE token = ?")->execute([$token]);
            $message = "<div class='alert alert-success'>Mot de passe modifie ! <a href='login.php'>Se connecter</a></div>";
        } else {
            $message = "<div class='alert alert-danger'>Lien invalide ou expire.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'><ul class='mb-0'>";
        foreach ($errors as $e) $message .= "<li>$e</li>";
        $message .= "</ul></div>";
        $token_valide = true;
        $email_token  = $_POST['token'];
    }
}

// ── ÉTAPE 2 : Vérification du token via URL ───────────────────
if (isset($_GET['token'])) {
    $token = trim($_GET['token']);
    $stmt  = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND utilise = 0 AND expire > NOW()");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();

    if ($reset) {
        $token_valide = true;
        $email_token  = $token;
    } else {
        $message = "<div class='alert alert-danger'>Ce lien est invalide ou a expire. <a href='mot_de_passe_oublie.php'>Reessayer</a></div>";
    }
}

// ── ÉTAPE 1 : Envoi du lien de réinitialisation ───────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'envoyer') {

    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Générer un token unique
        $token  = bin2hex(random_bytes(32));
        $expire = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Supprimer les anciens tokens de cet email
        $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

        // Insérer le nouveau token
        $pdo->prepare("INSERT INTO password_resets (email, token, expire) VALUES (?, ?, ?)")
            ->execute([$email, $token, $expire]);

        // Construire le lien
        $lien = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/mot_de_passe_oublie.php?token=" . $token;

        $message = "<div class='alert alert-info'>
            <strong>En production, un email serait envoye.</strong><br>
            Pour tester, voici votre lien de reinitialisation :<br>
            <a href='$lien'>$lien</a>
        </div>";
    } else {
        // On ne dit pas si l'email existe ou non (sécurité)
        $message = "<div class='alert alert-success'>Si cet email existe, un lien de reinitialisation a ete envoye.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="padding-bottom:80px; background:#f2f2f2;">

<?php include "menu.php"; ?>

<div class="d-flex justify-content-center mt-5">
    <div class="p-4 shadow bg-white rounded" style="width:520px;">

        <?php if ($token_valide): ?>

            <!-- ÉTAPE 2 : Formulaire nouveau mot de passe -->
            <h2 class="text-center mb-4">Nouveau mot de passe</h2>

            <?= $message ?>

            <form method="post" id="formMdp">
                <input type="hidden" name="action" value="changer">
                <input type="hidden" name="token" value="<?= htmlspecialchars($email_token) ?>">

                <div class="mb-3">
                    <label class="form-label">Nouveau mot de passe *</label>
                    <input type="password" name="password" id="password" class="form-control" required
                           oninput="verifierMdp(this.value)">
                    <div class="mt-2" style="font-size:0.82rem;">
                        <span id="ind_len"  class="text-danger">✗ Au moins 8 caracteres</span><br>
                        <span id="ind_maj"  class="text-danger">✗ Au moins 1 majuscule</span><br>
                        <span id="ind_chif" class="text-danger">✗ Au moins 1 chiffre</span><br>
                        <span id="ind_spec" class="text-danger">✗ Au moins 1 caractere special</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirmer le mot de passe *</label>
                    <input type="password" name="password2" id="password2" class="form-control" required
                           oninput="verifierConfirm()">
                    <div id="ind_confirm" style="font-size:0.82rem;" class="mt-1"></div>
                </div>

                <button type="submit" class="btn btn-success w-100">Changer le mot de passe</button>
            </form>

        <?php else: ?>

            <!-- ÉTAPE 1 : Demande de réinitialisation -->
            <h2 class="text-center mb-2">Mot de passe oublie</h2>
            <p class="text-muted text-center mb-4" style="font-size:0.9rem;">
                Entrez votre email, vous recevrez un lien pour reinitialiser votre mot de passe.
            </p>

            <?= $message ?>

            <form method="post">
                <input type="hidden" name="action" value="envoyer">

                <div class="mb-3">
                    <label class="form-label">Adresse email</label>
                    <input type="email" name="email" class="form-control" required placeholder="votre@email.com">
                </div>

                <button type="submit" class="btn btn-primary w-100">Envoyer le lien</button>
            </form>

            <p class="text-center mt-3">
                <a href="login.php" class="text-muted" style="font-size:0.9rem;">Retour a la connexion</a>
            </p>

        <?php endif; ?>

    </div>
</div>

<?php include "footer.php"; ?>

<script>
function verifierMdp(val) {
    const set = (id, ok, txt) => {
        const el = document.getElementById(id);
        el.className   = ok ? 'text-success' : 'text-danger';
        el.textContent = (ok ? '✓ ' : '✗ ') + txt;
    };
    set('ind_len',  val.length >= 8,        'Au moins 8 caracteres');
    set('ind_maj',  /[A-Z]/.test(val),      'Au moins 1 majuscule');
    set('ind_chif', /[0-9]/.test(val),      'Au moins 1 chiffre');
    set('ind_spec', /[\W_]/.test(val),      'Au moins 1 caractere special');
    verifierConfirm();
}
function verifierConfirm() {
    const p1 = document.getElementById('password').value;
    const p2 = document.getElementById('password2').value;
    const el = document.getElementById('ind_confirm');
    if (!p2) { el.textContent = ''; return; }
    el.className   = p1 === p2 ? 'text-success' : 'text-danger';
    el.textContent = p1 === p2 ? '✓ Les mots de passe correspondent' : '✗ Les mots de passe ne correspondent pas';
}
</script>
</body>
</html>