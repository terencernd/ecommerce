<?php
session_start();
require "config.php";

if (!isset($_SESSION['nom']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";

// SUPPRIMER
if (isset($_GET['supprimer'])) {
    // Vérifier si des articles utilisent cette catégorie
    $nb = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE categorie_id = ?");
    $nb->execute([(int)$_GET['supprimer']]);
    if ($nb->fetchColumn() > 0) {
        $message = "<div class='alert alert-danger'>Impossible de supprimer : des articles utilisent cette categorie. Reassignez-les d'abord.</div>";
    } else {
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([(int)$_GET['supprimer']]);
        header("Location: admin_categories.php?msg=supprime");
        exit;
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'supprime') $message = "<div class='alert alert-success'>Categorie supprimee !</div>";
    if ($_GET['msg'] === 'ajoute')   $message = "<div class='alert alert-success'>Categorie ajoutee !</div>";
    if ($_GET['msg'] === 'modifie')  $message = "<div class='alert alert-success'>Categorie modifiee !</div>";
}

// AJOUTER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'ajouter') {
    $nom         = trim($_POST['nom']);
    $description = trim($_POST['description']);
    if ($nom) {
        $pdo->prepare("INSERT INTO categories (nom, description) VALUES (?, ?)")->execute([$nom, $description]);
        header("Location: admin_categories.php?msg=ajoute");
        exit;
    } else {
        $message = "<div class='alert alert-danger'>Le nom est obligatoire.</div>";
    }
}

// MODIFIER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'modifier') {
    $pdo->prepare("UPDATE categories SET nom=?, description=? WHERE id=?")
        ->execute([trim($_POST['nom']), trim($_POST['description']), (int)$_POST['id_cat']]);
    header("Location: admin_categories.php?msg=modifie");
    exit;
}

$categories = $pdo->query("
    SELECT c.*, COUNT(a.id) as nb_articles
    FROM categories c
    LEFT JOIN articles a ON a.categorie_id = c.id
    GROUP BY c.id
    ORDER BY c.nom
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; }
        .cat-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .cat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
        }
        .badge-nb {
            background: #EFF6FF;
            color: #2563EB;
            font-weight: 600;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body style="padding-bottom:80px;">
<?php include "menu.php"; ?>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Categories boutique</h2>
            <p class="text-muted mb-0"><?= count($categories) ?> categorie(s) au total</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAjouter">
                + Nouvelle categorie
            </button>
            <a href="admin.php" class="btn btn-outline-secondary">Retour admin</a>
        </div>
    </div>

    <?= $message ?>

    <!-- GRILLE DES CATEGORIES -->
    <?php if (empty($categories)): ?>
        <div class="alert alert-info">Aucune categorie. Ajoutez-en une !</div>
    <?php else: ?>
    <div class="row mb-4">
        <?php
        $colors = ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899','#06B6D4','#84CC16'];
        foreach ($categories as $i => $c):
            $color = $colors[$i % count($colors)];
        ?>
        <div class="col-md-4 col-lg-3 mb-3">
            <div class="card cat-card shadow-sm h-100">
                <!-- Bande colorée en haut -->
                <div style="height:6px; background:<?= $color ?>; border-radius:12px 12px 0 0;"></div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="mb-0 fw-bold"><?= htmlspecialchars($c['nom']) ?></h5>
                        <span class="badge-nb"><?= $c['nb_articles'] ?> article<?= $c['nb_articles'] > 1 ? 's' : '' ?></span>
                    </div>
                    <?php if ($c['description']): ?>
                        <p class="text-muted mb-3" style="font-size:0.85rem;"><?= htmlspecialchars($c['description']) ?></p>
                    <?php else: ?>
                        <p class="text-muted mb-3" style="font-size:0.85rem; font-style:italic;">Pas de description</p>
                    <?php endif; ?>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary w-50"
                                data-bs-toggle="modal"
                                data-bs-target="#modalModifier"
                                data-id="<?= $c['id'] ?>"
                                data-nom="<?= htmlspecialchars($c['nom'], ENT_QUOTES) ?>"
                                data-description="<?= htmlspecialchars($c['description'] ?? '', ENT_QUOTES) ?>">
                            Modifier
                        </button>
                        <a href="admin_categories.php?supprimer=<?= $c['id'] ?>"
                           class="btn btn-sm btn-outline-danger w-50"
                           onclick="return confirm('Supprimer <?= htmlspecialchars($c['nom'], ENT_QUOTES) ?> ?')">
                            Supprimer
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- LIEN VERS BOUTIQUE -->
    <div class="text-center mt-2">
        <a href="boutique.php" class="btn btn-outline-primary">Voir la boutique</a>
        <a href="admin_articles.php" class="btn btn-outline-dark ms-2">Gerer les articles</a>
    </div>

</div>

<!-- MODAL AJOUTER -->
<div class="modal fade" id="modalAjouter" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:#10B981; color:white;">
                <h5 class="modal-title">Nouvelle categorie</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="ajouter">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nom *</label>
                        <input type="text" name="nom" class="form-control" required placeholder="Ex : Equipements">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Decrivez cette categorie..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-4">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL MODIFIER -->
<div class="modal fade" id="modalModifier" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:#3B82F6; color:white;">
                <h5 class="modal-title">Modifier la categorie</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="modifier">
                    <input type="hidden" name="id_cat" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nom *</label>
                        <input type="text" name="nom" id="edit_nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('modalModifier').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('edit_id').value          = btn.dataset.id;
    document.getElementById('edit_nom').value         = btn.dataset.nom;
    document.getElementById('edit_description').value = btn.dataset.description;
});
</script>
</body>
</html>