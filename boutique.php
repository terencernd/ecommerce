<?php
session_start();
require "config.php";

$pdo->exec("CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    description TEXT,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description TEXT,
    prix DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    promotion INT DEFAULT 0,
    categorie_id INT,
    image_url VARCHAR(500),
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL
)");

$nbCat = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
if ($nbCat == 0) {
    $pdo->exec("INSERT INTO categories (nom) VALUES ('Ballons'), ('Maillots'), ('Chaussures'), ('Accessoires'), ('Equipements')");
}

$nbArt = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
if ($nbArt == 0) {
    $cats = $pdo->query("SELECT id, nom FROM categories")->fetchAll(PDO::FETCH_KEY_PAIR);
    $c = array_flip($cats);
    $articles = [
        ['Ballon Mikasa',    'Ballon officiel de volleyball', 49.99, 15, 10, $c['Ballons']      ?? 1, 'https://images.unsplash.com/photo-1592656094267-764a45160876?w=400&h=267&fit=crop'],
        ['Maillot Officiel', 'Maillot du club couleur officielle', 29.99, 30, 0, $c['Maillots'] ?? 2, 'https://images.unsplash.com/photo-1593341646782-e0b495cff86d?w=400&h=267&fit=crop'],
        ['Filet Pro',        'Filet professionnel homologué', 89.99, 5, 20, $c['Equipements']   ?? 5, 'https://images.unsplash.com/photo-1592656094267-764a45160876?w=400&h=267&fit=crop'],
        ['Chaussures Indoor','Chaussures spéciales parquet', 69.99, 20, 0, $c['Chaussures']     ?? 3, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=267&fit=crop'],
        ['Sac de Sport',     'Sac spacieux avec compartiments', 39.99, 25, 15, $c['Accessoires'] ?? 4, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400&h=267&fit=crop'],
        ['Sweat Club',       'Sweat officiel du club', 49.99, 18, 0, $c['Maillots']             ?? 2, 'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=400&h=267&fit=crop'],
        ['Casquette Club',   'Casquette brodée logo du club', 14.99, 40, 0, $c['Accessoires']   ?? 4, 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=400&h=267&fit=crop'],
        ['Chaussettes Pro',  'Chaussettes hautes performance', 9.99, 60, 30, $c['Accessoires']  ?? 4, 'https://images.unsplash.com/photo-1586350977771-b3b0abd50c82?w=400&h=267&fit=crop'],
        ['Serviette Club',   'Serviette microfibre aux couleurs du club', 12.99, 35, 0, $c['Accessoires'] ?? 4, 'https://images.unsplash.com/photo-1616627561839-074385245ff6?w=400&h=267&fit=crop'],
        ['Gourde Club',      'Gourde isotherme 750ml', 9.99, 50, 0, $c['Accessoires']           ?? 4, 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=400&h=267&fit=crop'],
    ];
    $stmt = $pdo->prepare("INSERT INTO articles (nom, description, prix, stock, promotion, categorie_id, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($articles as $a) $stmt->execute($a);
}

$categorie_filtre = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

if ($categorie_filtre > 0) {
    $stmt = $pdo->prepare("SELECT a.*, c.nom as categorie_nom FROM articles a LEFT JOIN categories c ON a.categorie_id = c.id WHERE a.categorie_id = ? ORDER BY a.date_ajout DESC");
    $stmt->execute([$categorie_filtre]);
} else {
    $stmt = $pdo->query("SELECT a.*, c.nom as categorie_nom FROM articles a LEFT JOIN categories c ON a.categorie_id = c.id ORDER BY a.date_ajout DESC");
}
$articles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Boutique Volleyball</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-img-top { height: 200px; object-fit: cover; }
        .card { transition: transform 0.2s; }
        .card:hover { transform: translateY(-4px); }
        .badge-promo {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #dc3545;
            color: white;
            font-size: 0.85rem;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 20px;
            z-index: 1;
        }
        .prix-barre {
            text-decoration: line-through;
            color: #999;
            font-size: 0.85rem;
        }
        .prix-promo {
            color: #dc3545;
            font-weight: bold;
            font-size: 1.1rem;
        }
    </style>
</head>
<body style="background:#f2f2f2; padding-bottom:80px;">

<?php include "menu.php"; ?>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Boutique Volleyball</h2>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin_articles.php" class="btn btn-success">+ Ajouter un article</a>
        <?php endif; ?>
    </div>

    <!-- FILTRES CATEGORIE -->
    <div class="mb-4 d-flex flex-wrap gap-2">
        <a href="boutique.php" class="btn btn-outline-primary <?= $categorie_filtre === 0 ? 'active' : '' ?>">
            Tous les articles
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="boutique.php?categorie=<?= $cat['id'] ?>"
               class="btn btn-outline-primary <?= $categorie_filtre === $cat['id'] ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['nom']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- ARTICLES -->
    <?php if (empty($articles)): ?>
        <div class="alert alert-info">Aucun article dans cette categorie.</div>
    <?php else: ?>
    <div class="row">
        <?php foreach ($articles as $a):
            $a_promo = $a['promotion'] > 0;
            $prix_final = $a_promo ? round($a['prix'] * (1 - $a['promotion'] / 100), 2) : $a['prix'];
        ?>
        <div class="col-md-3 mb-4">
            <div class="card shadow h-100" style="position:relative;">

                <!-- BADGE PROMOTION -->
                <?php if ($a_promo): ?>
                    <div class="badge-promo">-<?= $a['promotion'] ?>%</div>
                <?php endif; ?>

                <?php if ($a['image_url']): ?>
                    <img src="<?= htmlspecialchars($a['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($a['nom']) ?>">
                <?php else: ?>
                    <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center text-white">
                        Pas d'image
                    </div>
                <?php endif; ?>

                <div class="card-body d-flex flex-column">
                    <?php if ($a['categorie_nom']): ?>
                        <span class="badge bg-secondary mb-1" style="width:fit-content;">
                            <?= htmlspecialchars($a['categorie_nom']) ?>
                        </span>
                    <?php endif; ?>

                    <h5 class="card-title"><?= htmlspecialchars($a['nom']) ?></h5>

                    <?php if ($a['description']): ?>
                        <p class="text-muted" style="font-size:0.85rem;"><?= htmlspecialchars($a['description']) ?></p>
                    <?php endif; ?>

                    <!-- PRIX -->
                    <div class="mt-auto mb-2">
                        <?php if ($a_promo): ?>
                            <span class="prix-barre"><?= number_format($a['prix'], 2) ?> €</span>
                            <span class="prix-promo ms-2"><?= number_format($prix_final, 2) ?> €</span>
                        <?php else: ?>
                            <span class="fw-bold"><?= number_format($a['prix'], 2) ?> €</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($a['stock'] > 0): ?>
                        <form action="ajouter_panier.php" method="get" class="mt-auto">
                            <input type="hidden" name="id"   value="<?= $a['id'] ?>">
                            <input type="hidden" name="nom"  value="<?= htmlspecialchars($a['nom']) ?>">
                            <input type="hidden" name="prix" value="<?= $prix_final ?>">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <label class="form-label mb-0 text-muted" style="font-size:0.85rem; white-space:nowrap;">Qte :</label>
                                <input type="number" name="quantite" value="1" min="1" max="<?= $a['stock'] ?>"
                                       class="form-control form-control-sm" style="width:70px;">
                                <small class="text-muted" style="font-size:0.75rem;">/ <?= $a['stock'] ?> dispo</small>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Ajouter au panier</button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-secondary w-100" disabled>Rupture de stock</button>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <div class="d-flex gap-1 mt-2">
                            <a href="admin_articles.php?modifier=<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary w-50">Modifier</a>
                            <a href="admin_articles.php?supprimer=<?= $a['id'] ?>"
                               class="btn btn-sm btn-outline-danger w-50"
                               onclick="return confirm('Supprimer ?')">Supprimer</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>