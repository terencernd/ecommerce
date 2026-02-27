<?php
session_start();
require "config.php";

// Récupérer les dernières actus
$actus = $pdo->query("SELECT * FROM actualites ORDER BY date_publication DESC LIMIT 3")->fetchAll();

// Récupérer le prochain match (premier à venir)
$prochain = "Moureu FC vs Paris Est — Sam. 14 fév. 18h00";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volley Club — Site Officiel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bleu:    #0F2B5B;
            --or:      #F5A623;
            --or2:     #E8890A;
            --clair:   #F8FAFF;
            --gris:    #64748B;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--clair);
            font-family: 'Lato', sans-serif;
            overflow-x: hidden;
            padding-bottom: 80px;
        }

        /* ── HERO ───────────────────────────────────────── */
        .hero {
            position: relative;
            min-height: 92vh;
            background: var(--bleu);
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        /* Grille de points décoratifs */
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(245,166,35,0.15) 1px, transparent 1px);
            background-size: 36px 36px;
            z-index: 0;
        }

        /* Cercle géant balle de volley derrière */
        .hero-ball {
            position: absolute;
            right: -120px;
            top: 50%;
            transform: translateY(-50%);
            width: 620px;
            height: 620px;
            border-radius: 50%;
            background: radial-gradient(circle at 38% 32%, #fff 0%, #F5A623 35%, #C97A00 70%, #7A4A00 100%);
            opacity: 0.12;
            z-index: 0;
        }

        /* Lignes courbes balle dans le cercle */
        .hero-ball-lines {
            position: absolute;
            right: -120px;
            top: 50%;
            transform: translateY(-50%);
            width: 620px;
            height: 620px;
            z-index: 0;
            opacity: 0.07;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            padding: 0 6vw;
        }

        .hero-eyebrow {
            font-family: 'Lato', sans-serif;
            font-weight: 300;
            font-size: 0.9rem;
            letter-spacing: 6px;
            text-transform: uppercase;
            color: var(--or);
            margin-bottom: 1rem;
            opacity: 0;
            animation: fadeUp 0.6s ease 0.2s forwards;
        }

        .hero-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(3.5rem, 9vw, 8rem);
            line-height: 0.95;
            color: #fff;
            opacity: 0;
            animation: fadeUp 0.7s ease 0.4s forwards;
        }

        .hero-title span {
            color: var(--or);
            display: block;
        }

        .hero-sub {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.65);
            max-width: 480px;
            margin: 1.5rem 0 2.5rem;
            line-height: 1.7;
            font-weight: 300;
            opacity: 0;
            animation: fadeUp 0.7s ease 0.6s forwards;
        }

        .hero-ctas {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            opacity: 0;
            animation: fadeUp 0.7s ease 0.8s forwards;
        }

        .btn-or {
            background: var(--or);
            color: var(--bleu);
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 14px 32px;
            border-radius: 4px;
            border: none;
            transition: background 0.2s, transform 0.15s;
            text-decoration: none;
        }
        .btn-or:hover { background: var(--or2); transform: translateY(-2px); color: var(--bleu); }

        .btn-ghost {
            border: 2px solid rgba(255,255,255,0.4);
            color: #fff;
            font-weight: 400;
            font-size: 0.9rem;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 12px 32px;
            border-radius: 4px;
            background: transparent;
            transition: border-color 0.2s, transform 0.15s;
            text-decoration: none;
        }
        .btn-ghost:hover { border-color: var(--or); color: var(--or); transform: translateY(-2px); }

        /* Stats bar */
        .stats-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255,255,255,0.1);
            display: flex;
            z-index: 2;
            opacity: 0;
            animation: fadeIn 0.6s ease 1.2s forwards;
        }

        .stat-item {
            flex: 1;
            padding: 20px 24px;
            text-align: center;
            border-right: 1px solid rgba(255,255,255,0.08);
        }
        .stat-item:last-child { border-right: none; }

        .stat-num {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 2.2rem;
            color: var(--or);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.72rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.45);
            margin-top: 4px;
        }

        /* ── MATCH BANNER ────────────────────────────────── */
        .match-banner {
            background: var(--or);
            color: var(--bleu);
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
            font-weight: 700;
            letter-spacing: 1px;
            font-size: 0.95rem;
            text-transform: uppercase;
        }

        .match-vs {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.8rem;
            line-height: 1;
        }

        .match-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--bleu);
            opacity: 0.4;
        }

        /* ── SECTIONS ────────────────────────────────────── */
        .section { padding: 80px 0; }

        .section-label {
            font-size: 0.75rem;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: var(--or);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .section-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(2rem, 4vw, 3.2rem);
            color: var(--bleu);
            line-height: 1.05;
            margin-bottom: 1rem;
        }

        /* ── CARTES ACTUS ────────────────────────────────── */
        .actu-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
            border: 1px solid #E8EDF5;
        }
        .actu-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(15,43,91,0.12); }

        .actu-top {
            height: 6px;
            background: linear-gradient(90deg, var(--or), var(--bleu));
        }

        .actu-body { padding: 24px; }

        .actu-date {
            font-size: 0.75rem;
            color: var(--gris);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .actu-titre {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.4rem;
            color: var(--bleu);
            line-height: 1.2;
            margin-bottom: 0.75rem;
        }

        .actu-contenu {
            font-size: 0.88rem;
            color: var(--gris);
            line-height: 1.65;
        }

        /* ── CARTES NAVIGATION ───────────────────────────── */
        .nav-card {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            height: 220px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .nav-card:hover { transform: scale(1.02); }

        .nav-card-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            transition: transform 0.4s ease;
        }
        .nav-card:hover .nav-card-bg { transform: scale(1.06); }

        .nav-card-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(15,43,91,0.85) 0%, rgba(15,43,91,0.4) 100%);
        }

        .nav-card-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 24px;
            z-index: 2;
        }

        .nav-card-tag {
            font-size: 0.7rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--or);
            font-weight: 700;
            margin-bottom: 6px;
        }

        .nav-card-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.8rem;
            color: #fff;
            line-height: 1;
        }

        .nav-card-arrow {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--or);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: var(--bleu);
            font-weight: 700;
            transition: transform 0.2s;
        }
        .nav-card:hover .nav-card-arrow { transform: rotate(45deg); }

        /* ── FOOTER CTA ──────────────────────────────────── */
        .footer-cta {
            background: var(--bleu);
            padding: 80px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .footer-cta::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle, rgba(245,166,35,0.08) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        .footer-cta-content { position: relative; z-index: 1; }

        /* ── ANIMATIONS ──────────────────────────────────── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.7s ease, transform 0.7s ease;
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>

<?php include "menu.php"; ?>

<!-- ══ HERO ══════════════════════════════════════════════════ -->
<section class="hero">
    <!-- Balle décorative -->
    <div class="hero-ball"></div>

    <!-- SVG lignes de balle -->
    <svg class="hero-ball-lines" viewBox="0 0 620 620" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="310" cy="310" r="305" stroke="white" stroke-width="3"/>
        <path d="M310 5 C180 80, 110 190, 120 310 C130 430, 200 520, 310 615" stroke="white" stroke-width="3"/>
        <path d="M310 5 C440 80, 510 190, 500 310 C490 430, 420 520, 310 615" stroke="white" stroke-width="3"/>
        <path d="M5 310 C80 180, 190 110, 310 120 C430 130, 520 200, 615 310" stroke="white" stroke-width="3"/>
        <path d="M5 310 C80 440, 190 510, 310 500 C430 490, 520 420, 615 310" stroke="white" stroke-width="3"/>
    </svg>

    <div class="hero-content">
        <p class="hero-eyebrow">Site officiel</p>
        <h1 class="hero-title">
            Moureu
            <span>Volley Club</span>
        </h1>
        <p class="hero-sub">
            Passion, dépassement et esprit d'équipe. Rejoignez l'aventure et vivez le volleyball à son plus haut niveau.
        </p>
        <div class="hero-ctas">
            <a href="boutique.php" class="btn-or">Notre boutique</a>
            <a href="inscription.php" class="btn-ghost">Rejoindre le club</a>
        </div>
    </div>

    <!-- Stats bar -->
    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-num">11</div>
            <div class="stat-label">Joueurs</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">8</div>
            <div class="stat-label">Victoires</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">3</div>
            <div class="stat-label">Titres</div>
        </div>
        <div class="stat-item">
            <div class="stat-num">2020</div>
            <div class="stat-label">Fondé en</div>
        </div>
    </div>
</section>

<!-- ══ MATCH BANNER ══════════════════════════════════════════ -->
<div class="match-banner">
    <div class="match-dot"></div>
    <span>Prochain match</span>
    <div class="match-dot"></div>
    <span class="match-vs">Moureu FC</span>
    <span style="opacity:0.5; font-size:1.2rem;">VS</span>
    <span class="match-vs">Paris Est</span>
    <div class="match-dot"></div>
    <span>Sam. 14 fév. — 18h00</span>
    <div class="match-dot"></div>
    <a href="matchs.php" style="color:var(--bleu); text-decoration:underline; font-size:0.8rem;">Voir le calendrier</a>
</div>

<!-- ══ NAVIGATION CARDS ═══════════════════════════════════════ -->
<section class="section" style="background:#fff;">
    <div class="container">
        <div class="row g-3 reveal">
            <div class="col-md-4">
                <a href="actualites.php" style="text-decoration:none;">
                    <div class="nav-card">
                        <div class="nav-card-bg" style="background: linear-gradient(135deg, #0F2B5B 0%, #1E4A8C 100%);"></div>
                        <div class="nav-card-overlay"></div>
                        <div class="nav-card-arrow">→</div>
                        <div class="nav-card-content">
                            <div class="nav-card-tag">Infos</div>
                            <div class="nav-card-title">Actualites</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="matchs.php" style="text-decoration:none;">
                    <div class="nav-card">
                        <div class="nav-card-bg" style="background: linear-gradient(135deg, #1A3A6B 0%, #F5A623 100%);"></div>
                        <div class="nav-card-overlay"></div>
                        <div class="nav-card-arrow">→</div>
                        <div class="nav-card-content">
                            <div class="nav-card-tag">Calendrier</div>
                            <div class="nav-card-title">Matchs</div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="boutique.php" style="text-decoration:none;">
                    <div class="nav-card">
                        <div class="nav-card-bg" style="background: linear-gradient(135deg, #C97A00 0%, #F5A623 100%);"></div>
                        <div class="nav-card-overlay"></div>
                        <div class="nav-card-arrow">→</div>
                        <div class="nav-card-content">
                            <div class="nav-card-tag">Supporters</div>
                            <div class="nav-card-title">Boutique</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ══ ACTUALITES ═════════════════════════════════════════════ -->
<?php if (!empty($actus)): ?>
<section class="section" style="background:var(--clair);">
    <div class="container">
        <div class="reveal mb-5">
            <p class="section-label">Dernières nouvelles</p>
            <h2 class="section-title">Actualites du club</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($actus as $a): ?>
            <div class="col-md-4 reveal">
                <div class="actu-card">
                    <div class="actu-top"></div>
                    <div class="actu-body">
                        <p class="actu-date"><?= date('d M Y', strtotime($a['date_publication'])) ?></p>
                        <h3 class="actu-titre"><?= htmlspecialchars($a['titre']) ?></h3>
                        <p class="actu-contenu"><?= htmlspecialchars(substr($a['contenu'], 0, 120)) ?>...</p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4 reveal">
            <a href="actualites.php" class="btn-or" style="display:inline-block;">Toutes les actualites</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ══ EQUIPE HIGHLIGHT ═══════════════════════════════════════ -->
<section class="section" style="background:#fff;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-5 reveal">
                <p class="section-label">Notre effectif</p>
                <h2 class="section-title">Une equipe de passionnes</h2>
                <p style="color:var(--gris); line-height:1.8; margin-bottom:1.5rem;">
                    11 joueurs unis par la passion du volleyball. Des attaquants aux liberos, chaque membre donne le meilleur pour les couleurs du club.
                </p>
                <a href="equipe.php" class="btn-or" style="display:inline-block;">Decouvrir l'equipe</a>
            </div>
            <div class="col-md-7 reveal" style="padding-left:3rem;">
                <div class="row g-3">
                    <?php
                    $postes = [
                        ['Attaquant',      '2 joueurs',  '#0F2B5B'],
                        ['Passeur',        '2 joueurs',  '#1E4A8C'],
                        ['Libero',         '1 joueur',   '#F5A623'],
                        ['Central',        '2 joueurs',  '#0F2B5B'],
                        ['Receptionneur',  '2 joueurs',  '#1E4A8C'],
                        ['Pointu',         '2 joueurs',  '#C97A00'],
                    ];
                    foreach ($postes as $p):
                    ?>
                    <div class="col-6">
                        <div style="padding:16px 20px; background:var(--clair); border-radius:8px; border-left:4px solid <?= $p[2] ?>;">
                            <div style="font-family:'Bebas Neue',sans-serif; font-size:1.1rem; color:<?= $p[2] ?>;"><?= $p[0] ?></div>
                            <div style="font-size:0.8rem; color:var(--gris);"><?= $p[1] ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ CTA FINAL ══════════════════════════════════════════════ -->
<section class="footer-cta">
    <div class="footer-cta-content">
        <p class="section-label" style="color:var(--or);">Rejoignez-nous</p>
        <h2 style="font-family:'Bebas Neue',sans-serif; font-size:clamp(2.5rem,5vw,4rem); color:#fff; margin-bottom:1rem;">
            Pret a jouer pour Moureu FC ?
        </h2>
        <p style="color:rgba(255,255,255,0.55); margin-bottom:2rem; font-size:1.05rem; font-weight:300;">
            Inscrivez-vous et rejoignez notre communaute de passionnes.
        </p>
        <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
            <a href="inscription.php" class="btn-or">Creer un compte</a>
            <a href="contact.php" class="btn-ghost">Nous contacter</a>
        </div>
    </div>
</section>

<?php include "footer.php"; ?>

<script>
// Reveal on scroll
const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, i) => {
        if (entry.isIntersecting) {
            setTimeout(() => entry.target.classList.add('visible'), i * 100);
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>

</body>
</html>