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
    $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([(int)$_GET['supprimer']]);
    header("Location: admin_articles.php?msg=supprime");
    exit;
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'supprime') $message = "<div class='alert alert-success'>Article supprime !</div>";
    if ($_GET['msg'] === 'modifie')  $message = "<div class='alert alert-success'>Article modifie !</div>";
    if ($_GET['msg'] === 'ajoute')   $message = "<div class='alert alert-success'>Article ajoute !</div>";
    if ($_GET['msg'] === 'stock')    $message = "<div class='alert alert-success'>Stock mis a jour !</div>";
}

// MODIFIER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'modifier') {
    $pdo->prepare("UPDATE articles SET nom=?, description=?, prix=?, stock=?, promotion=?, categorie_id=?, image_url=? WHERE id=?")
        ->execute([
            trim($_POST['nom']),
            trim($_POST['description']),
            (float)$_POST['prix'],
            (int)$_POST['stock'],
            (int)$_POST['promotion'],
            !empty($_POST['categorie_id']) ? (int)$_POST['categorie_id'] : null,
            trim($_POST['image_url']),
            (int)$_POST['id_article']
        ]);
    header("Location: admin_articles.php?msg=modifie");
    exit;
}

// AJOUTER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'ajouter') {
    $nom = trim($_POST['nom']);
    $prix = (float)$_POST['prix'];
    if ($nom && $prix) {
        $pdo->prepare("INSERT INTO articles (nom, description, prix, stock, promotion, categorie_id, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)")
            ->execute([
                $nom,
                trim($_POST['description']),
                $prix,
                (int)$_POST['stock'],
                (int)$_POST['promotion'],
                !empty($_POST['categorie_id']) ? (int)$_POST['categorie_id'] : null,
                trim($_POST['image_url'])
            ]);
        header("Location: admin_articles.php?msg=ajoute");
        exit;
    } else {
        $message = "<div class='alert alert-danger'>Nom et prix obligatoires.</div>";
    }
}

// UPDATE STOCK RAPIDE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update_stock') {
    $pdo->prepare("UPDATE articles SET stock = ? WHERE id = ?")
        ->execute([(int)$_POST['stock'], (int)$_POST['id_article']]);
    header("Location: admin_articles.php?msg=stock");
    exit;
}

$articles   = $pdo->query("SELECT a.*, c.nom as categorie_nom FROM articles a LEFT JOIN categories c ON a.categorie_id = c.id ORDER BY a.date_ajout DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Articles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f2f2f2; padding-bottom:80px;">
<?php include "menu.php"; ?>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestion des articles (<?= count($articles) ?>)</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAjouter">
                + Ajouter un article
            </button>
            <a href="admin.php" class="btn btn-secondary">Retour admin</a>
        </div>
    </div>

    <?= $message ?>

    <!-- TABLEAU -->
    <div class="bg-white shadow rounded p-4">
        <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Categorie</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Promo %</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($articles)): ?>
                    <tr><td colspan="7" class="text-center text-muted">Aucun article</td></tr>
                <?php endif; ?>
                <?php foreach ($articles as $a): ?>
                <tr>
                    <td><?= $a['id'] ?></td>
                    <td>
                        <?php if ($a['image_url']): ?>
                            <img src="<?= htmlspecialchars($a['image_url']) ?>"
                                 style="width:50px;height:50px;object-fit:cover;border-radius:6px;">
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= htmlspecialchars($a['nom']) ?></strong></td>
                    <td><?= $a['categorie_nom'] ?? '<span class="text-muted">Aucune</span>' ?></td>
                    <td><?= number_format($a['prix'], 2) ?> €</td>
                    <td>
                        <!-- STOCK MODIFIABLE DIRECTEMENT -->
                        <form method="post" class="d-flex align-items-center gap-1">
                            <input type="hidden" name="action" value="update_stock">
                            <input type="hidden" name="id_article" value="<?= $a['id'] ?>">
                            <input type="number" name="stock" value="<?= $a['stock'] ?>" min="0"
                                   style="width:70px;"
                                   class="form-control form-control-sm <?= $a['stock'] > 0 ? 'border-success' : 'border-danger' ?>">
                            <button type="submit" class="btn btn-sm btn-success">OK</button>
                        </form>
                    </td>
                    <td>
                        <?php if ($a['promotion'] > 0): ?>
                            <span class="badge bg-danger">-<?= $a['promotion'] ?>%</span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <!-- BOUTON MODIFIER → ouvre la modal pré-remplie -->
                            <button class="btn btn-sm btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#modalModifier"
                                data-id="<?= $a['id'] ?>"
                                data-nom="<?= htmlspecialchars($a['nom'], ENT_QUOTES) ?>"
                                data-prix="<?= $a['prix'] ?>"
                                data-stock="<?= $a['stock'] ?>"
                                data-description="<?= htmlspecialchars($a['description'] ?? '', ENT_QUOTES) ?>"
                                data-image="<?= htmlspecialchars($a['image_url'] ?? '', ENT_QUOTES) ?>"
                                data-categorie="<?= $a['categorie_id'] ?>"
                                data-promotion="<?= $a['promotion'] ?>">
                                Modifier
                            </button>
                            <!-- BOUTON SUPPRIMER -->
                            <a href="admin_articles.php?supprimer=<?= $a['id'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Supprimer <?= htmlspecialchars($a['nom'], ENT_QUOTES) ?> ?')">
                                Supprimer
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- MODAL MODIFIER -->
<div class="modal fade" id="modalModifier" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Modifier l'article</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="modifier">
                    <input type="hidden" name="id_article" id="edit_id">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom *</label>
                            <input type="text" name="nom" id="edit_nom" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Prix (€) *</label>
                            <input type="number" step="0.01" name="prix" id="edit_prix" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" id="edit_stock" class="form-control" min="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Promotion (%)</label>
                            <input type="number" name="promotion" id="edit_promotion" class="form-control" min="0" max="100" placeholder="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categorie</label>
                            <select name="categorie_id" id="edit_categorie" class="form-select">
                                <option value="">-- Aucune --</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">URL Image</label>
                            <input type="text" name="image_url" id="edit_image" class="form-control" placeholder="https://...">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-5">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL AJOUTER -->
<div class="modal fade" id="modalAjouter" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Ajouter un article</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="ajouter">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nom *</label>
                            <input type="text" name="nom" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Prix (€) *</label>
                            <input type="number" step="0.01" name="prix" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Promotion (%)</label>
                            <input type="number" name="promotion" class="form-control" value="0" min="0" max="100">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categorie</label>
                            <select name="categorie_id" class="form-select">
                                <option value="">-- Aucune --</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">URL Image</label>
                            <input type="text" name="image_url" class="form-control" placeholder="https://...">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-5">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Pré-remplir la modal modifier avec les données de l'article
const modalModifier = document.getElementById('modalModifier');
modalModifier.addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('edit_id').value          = btn.dataset.id;
    document.getElementById('edit_nom').value         = btn.dataset.nom;
    document.getElementById('edit_prix').value        = btn.dataset.prix;
    document.getElementById('edit_stock').value       = btn.dataset.stock;
    document.getElementById('edit_promotion').value   = btn.dataset.promotion;
    document.getElementById('edit_description').value = btn.dataset.description;
    document.getElementById('edit_image').value       = btn.dataset.image;

    // Sélectionner la bonne catégorie
    const select = document.getElementById('edit_categorie');
    for (let opt of select.options) {
        opt.selected = (opt.value == btn.dataset.categorie);
    }
});
</script>

</body>
</html>