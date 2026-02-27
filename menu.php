<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nbArticles = 0;
if (!empty($_SESSION['panier'])) {
    $nbArticles = array_sum(array_column($_SESSION['panier'], 'quantite'));
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-3 shadow">
    <div class="container">

        <a class="navbar-brand" href="index.php">
            <img src="logo_volley.svg" alt="Volley Club" style="height:55px;">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="menu">
            <ul class="navbar-nav gap-4">

                <li class="nav-item"><a class="nav-link fs-5" href="equipe.php">Équipe</a></li>
                <li class="nav-item"><a class="nav-link fs-5" href="matchs.php">Matchs</a></li>
                <li class="nav-item"><a class="nav-link fs-5" href="boutique.php">Boutique</a></li>
                <li class="nav-item"><a class="nav-link fs-5" href="contact.php">Contact</a></li>

                <!-- Panier -->
                <li class="nav-item">
                    <a class="btn btn-outline-light px-4" href="panier.php">
                        Panier <?= $nbArticles > 0 ? "(".$nbArticles.")" : ""; ?>
                    </a>
                </li>

                <?php if (isset($_SESSION['nom'])): ?>

                    <!-- ADMIN (si admin) -->
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="btn btn-danger px-4" href="admin.php">Admin</a>
                    </li>
                    <?php endif; ?>

                    <!-- PROFIL -->
                    <li class="nav-item">
                        <a class="btn btn-info px-4" href="profil.php">Profil</a>
                    </li>

                    <!-- DÉCONNEXION -->
                    <li class="nav-item">
                        <a class="btn btn-danger px-4" href="logout.php">Déconnexion</a>
                    </li>

                <?php else: ?>

                    <!-- INSCRIPTION -->
                    <li class="nav-item">
                        <a class="btn btn-warning px-4" href="inscription.php">Inscription</a>
                    </li>

                    <!-- CONNEXION -->
                    <li class="nav-item">
                        <a class="btn btn-light px-4" href="login.php">Connexion</a>
                    </li>

                <?php endif; ?>

            </ul>
        </div>

    </div>
</nav>