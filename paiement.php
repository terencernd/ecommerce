<?php
session_start();
require "config.php";

if (!isset($_SESSION['nom'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['panier'])) {
    header("Location: boutique.php");
    exit;
}

// Récupérer l'adresse choisie
$adresse = null;
if (!empty($_SESSION['adresse_livraison'])) {
    $stmt = $pdo->prepare("SELECT * FROM adresses WHERE id = ?");
    $stmt->execute([$_SESSION['adresse_livraison']]);
    $adresse = $stmt->fetch();
}

// Calcul total
$total = 0;
foreach ($_SESSION['panier'] as $item) {
    $total += $item['prix'] * $item['quantite'];
}

// Clé publique Stripe (mode test)
// ⚠️ Remplace par ta vraie clé sur https://dashboard.stripe.com
$stripe_public_key = "pk_test_51T5RczDekdQUNs3qMyHiWSQaMfJd0i2hRF5Ujv8GONGVfHXWszZAQ4gIIApT8N0QiJBNy1LCOXpV51JvmnNDZ9Hv006XgRCaD5";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement - Volley Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SDK Stripe -->
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        .StripeElement {
            background: white;
            border: 1px solid #ced4da;
            border-radius: 6px;
            padding: 12px;
            font-size: 1rem;
        }
        .StripeElement--focus  { border-color: #86b7fe; box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25); }
        .StripeElement--invalid { border-color: #dc3545; }

        .methode-card {
            cursor: pointer;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            transition: 0.2s;
        }
        .methode-card.active  { border-color: #0d6efd; background: #f0f6ff; }
        .methode-card:hover   { border-color: #86b7fe; }
        .logo-paiement { height: 30px; object-fit: contain; }
    </style>
</head>
<body style="background:#f2f2f2; padding-bottom:80px;">

<?php include "menu.php"; ?>

<div class="container mt-5" style="max-width:750px;">

    <h2 class="mb-4">Paiement</h2>

    <div class="row">

        <!-- COLONNE GAUCHE : Récap + Adresse -->
        <div class="col-md-5 mb-4">

            <!-- RECAPITULATIF -->
            <div class="bg-white shadow rounded p-3 mb-3">
                <h5 class="mb-3">Recapitulatif</h5>
                <?php foreach ($_SESSION['panier'] as $item): ?>
                <div class="d-flex justify-content-between mb-1" style="font-size:0.9rem;">
                    <span><?= htmlspecialchars($item['nom']) ?> x<?= $item['quantite'] ?></span>
                    <span><?= number_format($item['prix'] * $item['quantite'], 2) ?> €</span>
                </div>
                <?php endforeach; ?>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total</span>
                    <span class="text-success"><?= number_format($total, 2) ?> €</span>
                </div>
            </div>

            <!-- ADRESSE -->
            <?php if ($adresse): ?>
            <div class="bg-white shadow rounded p-3">
                <h6 class="mb-2">Livraison</h6>
                <p class="mb-0 text-muted" style="font-size:0.85rem;">
                    <?= htmlspecialchars($adresse['prenom']) ?> <?= htmlspecialchars($adresse['nom']) ?><br>
                    <?= htmlspecialchars($adresse['rue']) ?><br>
                    <?= htmlspecialchars($adresse['code_postal']) ?> <?= htmlspecialchars($adresse['ville']) ?>
                </p>
            </div>
            <?php endif; ?>

        </div>

        <!-- COLONNE DROITE : Formulaire paiement -->
        <div class="col-md-7">
            <div class="bg-white shadow rounded p-4">

                <h5 class="mb-3">Choisir un mode de paiement</h5>

                <!-- CHOIX METHODE -->
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <div class="methode-card active text-center" onclick="choisirMethode('carte', this)">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Visa.svg"
                                 class="logo-paiement me-1" alt="Visa">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg"
                                 class="logo-paiement" alt="Mastercard">
                            <p class="mb-0 mt-1" style="font-size:0.8rem;">Carte bancaire</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="methode-card text-center" onclick="choisirMethode('paypal', this)">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg"
                                 class="logo-paiement" alt="PayPal">
                            <p class="mb-0 mt-1" style="font-size:0.8rem;">PayPal</p>
                        </div>
                    </div>
                </div>

                <!-- FORMULAIRE CARTE STRIPE -->
                <div id="section-carte">
                    <form id="formPaiement">

                        <div class="mb-3">
                            <label class="form-label">Nom sur la carte</label>
                            <input type="text" id="nom_carte" class="form-control"
                                   placeholder="Jean Dupont"
                                   value="<?= htmlspecialchars($_SESSION['nom']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Numero de carte</label>
                            <div id="stripe-card-number" class="StripeElement"></div>
                        </div>

                        <div class="row">
                            <div class="col-7 mb-3">
                                <label class="form-label">Date d'expiration</label>
                                <div id="stripe-card-expiry" class="StripeElement"></div>
                            </div>
                            <div class="col-5 mb-3">
                                <label class="form-label">CVV</label>
                                <div id="stripe-card-cvc" class="StripeElement"></div>
                            </div>
                        </div>

                        <div id="erreur-paiement" class="alert alert-danger d-none"></div>

                        <button type="submit" id="btn-payer" class="btn btn-success w-100 btn-lg">
                            Payer <?= number_format($total, 2) ?> €
                        </button>

                        <p class="text-muted text-center mt-2" style="font-size:0.75rem;">
                            Paiement securise par Stripe — vos donnees sont protegees
                        </p>

                    </form>
                </div>

                <!-- SECTION PAYPAL -->
                <div id="section-paypal" class="d-none text-center">
                    <p class="text-muted mb-3">Vous allez etre redirige vers PayPal pour finaliser votre paiement.</p>
                    <a href="https://www.paypal.com/checkoutnow?amount=<?= $total ?>&currency=EUR"
                       target="_blank"
                       class="btn btn-warning btn-lg w-100"
                       onclick="simulerPaypal()">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg"
                             style="height:22px;" class="me-2" alt="">
                        Payer avec PayPal <?= number_format($total, 2) ?> €
                    </a>
                    <p class="text-muted mt-2" style="font-size:0.75rem;">
                        Vous serez redirige sur le site officiel de PayPal.
                    </p>
                </div>

            </div>
        </div>

    </div>

    <div class="text-center mt-3">
        <a href="commander.php" class="text-muted" style="font-size:0.9rem;">Retour au recapitulatif</a>
    </div>

</div>

<?php include "footer.php"; ?>

<script>
// ── Stripe init ────────────────────────────────────────────────
const stripe   = Stripe('<?= $stripe_public_key ?>');
const elements = stripe.elements();

const style = {
    base: {
        fontSize: '16px',
        color: '#212529',
        '::placeholder': { color: '#adb5bd' }
    },
    invalid: { color: '#dc3545' }
};

const cardNumber = elements.create('cardNumber', { style });
const cardExpiry = elements.create('cardExpiry', { style });
const cardCvc    = elements.create('cardCvc',    { style });

cardNumber.mount('#stripe-card-number');
cardExpiry.mount('#stripe-card-expiry');
cardCvc.mount('#stripe-card-cvc');

// ── Choix méthode ──────────────────────────────────────────────
function choisirMethode(methode, el) {
    document.querySelectorAll('.methode-card').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('section-carte').classList.toggle('d-none',  methode !== 'carte');
    document.getElementById('section-paypal').classList.toggle('d-none', methode !== 'paypal');
}

// ── Soumission paiement carte ──────────────────────────────────
document.getElementById('formPaiement').addEventListener('submit', async (e) => {
    e.preventDefault();

    const btn = document.getElementById('btn-payer');
    const err = document.getElementById('erreur-paiement');
    btn.disabled    = true;
    btn.textContent = 'Traitement en cours...';
    err.classList.add('d-none');

    // En mode test : simuler le paiement
    // En production : appeler ton backend pour créer un PaymentIntent Stripe
    try {
        const { paymentMethod, error } = await stripe.createPaymentMethod({
            type: 'card',
            card: cardNumber,
            billing_details: {
                name: document.getElementById('nom_carte').value
            }
        });

        if (error) {
            err.textContent = error.message;
            err.classList.remove('d-none');
            btn.disabled    = false;
            btn.textContent = 'Payer <?= number_format($total, 2) ?> €';
        } else {
            // Paiement valide → rediriger vers confirmation commande
            window.location.href = 'commander.php?paiement=ok&methode=carte';
        }
    } catch (ex) {
        err.textContent = 'Erreur de connexion. Veuillez reessayer.';
        err.classList.remove('d-none');
        btn.disabled    = false;
        btn.textContent = 'Payer <?= number_format($total, 2) ?> €';
    }
});

function simulerPaypal() {
    setTimeout(() => {
        window.location.href = 'commander.php?paiement=ok&methode=paypal';
    }, 2000);
}
</script>

</body>
</html>