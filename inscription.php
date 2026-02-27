<?php
session_start();
require "config.php";

$message = "";
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom             = trim($_POST['nom']);
    $email           = trim($_POST['email']);
    $telephone       = trim($_POST['telephone']);
    $date_naissance  = trim($_POST['date_naissance']);
    $password        = trim($_POST['password']);
    $password2       = trim($_POST['password2']);

    // ── VALIDATION DATE DE NAISSANCE ─────────────────────────
    if (empty($date_naissance)) {
        $errors[] = "La date de naissance est obligatoire.";
    } else {
        $naissance = new DateTime($date_naissance);
        $aujourdhui = new DateTime();
        $age = $aujourdhui->diff($naissance)->y;

        if ($age < 16) {
            $errors[] = "Vous devez avoir au moins 16 ans pour vous inscrire. Vous avez " . $age . " an" . ($age > 1 ? "s" : "") . ".";
        }
    }

    // ── VALIDATION EMAIL ─────────────────────────────────────
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Adresse email invalide.";
    }

    // ── VALIDATION TELEPHONE ─────────────────────────────────
    $tel_clean = preg_replace('/[\s\-\.]/', '', $telephone);
    if (!preg_match('/^(\+33|0033|0)[1-9][0-9]{8}$/', $tel_clean)) {
        $errors[] = "Numero de telephone invalide (ex: 06 12 34 56 78).";
    }

    // ── VALIDATION MOT DE PASSE ──────────────────────────────
    if (strlen($password) < 8)          $errors[] = "Le mot de passe doit contenir au moins 8 caracteres.";
    if (!preg_match('/[A-Z]/', $password)) $errors[] = "Le mot de passe doit contenir au moins une majuscule.";
    if (!preg_match('/[0-9]/', $password)) $errors[] = "Le mot de passe doit contenir au moins un chiffre.";
    if (!preg_match('/[\W_]/', $password)) $errors[] = "Le mot de passe doit contenir au moins un caractere special (!@#\$%...).";
    if ($password !== $password2)        $errors[] = "Les mots de passe ne correspondent pas.";

    // ── SI PAS D'ERREURS ─────────────────────────────────────
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->rowCount() > 0) {
            $errors[] = "Un compte existe deja avec cet email.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (nom, email, password, telephone, age, role) VALUES (?, ?, ?, ?, ?, 'user')")
                ->execute([$nom, $email, $hash, $tel_clean, $age]);

            $_SESSION['nom']  = $nom;
            $_SESSION['role'] = 'user';

            $message = "<div class='alert alert-success text-center fs-5'>
                Felicitations <strong>" . htmlspecialchars($nom) . "</strong>, vous avez reussi a creer votre compte !
                <br><small>Vous allez etre redirige dans 3 secondes...</small>
            </div>
            <script>setTimeout(() => window.location.href = 'boutique.php', 3000);</script>";
        }
    }

    if (!empty($errors)) {
        $message = "<div class='alert alert-danger'><ul class='mb-0'>";
        foreach ($errors as $e) $message .= "<li>$e</li>";
        $message .= "</ul></div>";
    }
}

// Date max = aujourd'hui - 16 ans
$date_max = date('Y-m-d', strtotime('-16 years'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="padding-bottom:80px; background:#f2f2f2;">

<?php include "menu.php"; ?>

<div class="d-flex justify-content-center mt-5">
    <div class="p-4 shadow bg-white rounded" style="width:550px;">

        <h2 class="text-center mb-4">Creer un compte</h2>

        <?= $message ?>

        <form method="post" class="mt-3">

            <div class="mb-3">
                <label class="form-label">Nom complet *</label>
                <input type="text" name="nom" class="form-control" required
                       value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" required
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                <div class="form-text">Ex : exemple@email.com</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Telephone *</label>
                <input type="tel" name="telephone" class="form-control" required
                       placeholder="06 12 34 56 78"
                       value="<?= isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : '' ?>">
                <div class="form-text">Format : 06 XX XX XX XX ou +33 X XX XX XX XX</div>
            </div>

            <!-- DATE DE NAISSANCE -->
            <div class="mb-3">
                <label class="form-label">Date de naissance *</label>
                <input type="date" name="date_naissance" class="form-control" required
                       max="<?= $date_max ?>"
                       value="<?= isset($_POST['date_naissance']) ? htmlspecialchars($_POST['date_naissance']) : '' ?>">
                <div class="form-text text-danger">Vous devez avoir au moins 16 ans.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Mot de passe *</label>
                <input type="password" name="password" id="password" class="form-control" required
                       oninput="verifierMdp(this.value)">
                <div class="mt-2" style="font-size:0.82rem;">
                    <span id="ind_len"  class="text-danger">✗ Au moins 8 caracteres</span><br>
                    <span id="ind_maj"  class="text-danger">✗ Au moins 1 majuscule</span><br>
                    <span id="ind_chif" class="text-danger">✗ Au moins 1 chiffre</span><br>
                    <span id="ind_spec" class="text-danger">✗ Au moins 1 caractere special (!@#$%...)</span>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Confirmer le mot de passe *</label>
                <input type="password" name="password2" id="password2" class="form-control" required
                       oninput="verifierConfirm()">
                <div id="ind_confirm" style="font-size:0.82rem;" class="mt-1"></div>
            </div>

            <button class="btn btn-primary w-100" type="submit">S'inscrire</button>

        </form>

        <p class="text-center mt-3">
            Deja un compte ? <a href="login.php">Connexion</a>
        </p>

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
    set('ind_len',  val.length >= 8,     'Au moins 8 caracteres');
    set('ind_maj',  /[A-Z]/.test(val),   'Au moins 1 majuscule');
    set('ind_chif', /[0-9]/.test(val),   'Au moins 1 chiffre');
    set('ind_spec', /[\W_]/.test(val),   'Au moins 1 caractere special (!@#$%...)');
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