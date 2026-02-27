<?php
session_start();
require "config.php";
require "generer_facture.php"; // ← Générateur de facture

if (!isset($_SESSION['nom']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Creation table si besoin
$pdo->exec("CREATE TABLE IF NOT EXISTS commandes_club (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client VARCHAR(255) NOT NULL,
    detail TEXT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    statut VARCHAR(50) DEFAULT 'en attente',
    livreur_id INT DEFAULT NULL,
    date_cmd DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Supprimer
if (isset($_GET['supprimer'])) {
    $pdo->prepare("DELETE FROM commandes_club WHERE id = ?")->execute([(int)$_GET['supprimer']]);
    header("Location: admin_commandes.php");
    exit;
}

// ── Regénérer la facture d'une commande depuis l'admin ──
if (isset($_GET['facture'])) {
    $id_cmd = (int)$_GET['facture'];
    $stmt   = $pdo->prepare("SELECT * FROM commandes_club WHERE id = ?");
    $stmt->execute([$id_cmd]);
    $cmd = $stmt->fetch();

    if ($cmd) {
        // Reconstruire le panier depuis le champ detail
        $panier = [];
        $lignes = explode(' | ', $cmd['detail']);
        foreach ($lignes as $ligne) {
            // Format : "Nom produit xQTE (TOTAL €)" ou lignes d'adresse/paiement
            if (preg_match('/^(.+) x(\d+) \((.+) €\)$/', trim($ligne), $m)) {
                $qty        = (int)$m[2];
                $line_total = (float)str_replace(',', '.', $m[3]);
                $prix_unit  = $qty > 0 ? $line_total / $qty : 0;
                $panier[]   = [
                    'nom'      => $m[1],
                    'quantite' => $qty,
                    'prix'     => $prix_unit,
                ];
            }
        }

        // Extraire la méthode de paiement depuis le detail
        $methode = 'carte';
        if (preg_match('/Paiement: (\w+)/', $cmd['detail'], $m)) {
            $methode = $m[1];
        }

        // Chemin facture
        $invoice_number = 'CMD-' . date('Y', strtotime($cmd['date_cmd'])) . '-' . str_pad($cmd['id'], 5, '0', STR_PAD_LEFT);
        $facture_file   = 'factures/' . $invoice_number . '.html';

        // Regénérer si elle n'existe pas
        if (!file_exists($facture_file) && !empty($panier)) {
            genererFacturePDF($panier, null, (float)$cmd['montant'], $cmd['client'], $methode, $cmd['id']);
        }

        if (file_exists($facture_file)) {
            header("Location: " . $facture_file);
        } else {
            echo "<div style='font-family:Arial;padding:30px;'>
                    <p style='color:red;'>Facture introuvable pour la commande #$id_cmd.</p>
                    <p style='color:#555;font-size:13px;'>Elle n'a peut-être pas été générée lors de la commande.</p>
                    <a href='admin_commandes.php'>Retour</a>
                  </div>";
        }
        exit;
    }
}

// Modifier statut et livreur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier') {
    $pdo->prepare("UPDATE commandes_club SET statut = ?, livreur_id = ? WHERE id = ?")
        ->execute([
            $_POST['statut'],
            !empty($_POST['livreur_id']) ? (int)$_POST['livreur_id'] : null,
            (int)$_POST['id_commande']
        ]);
    header("Location: admin_commandes.php");
    exit;
}

$commandes = $pdo->query("
    SELECT c.*, l.prenom as livreur_prenom, l.nom as livreur_nom
    FROM commandes_club c
    LEFT JOIN livreurs l ON c.livreur_id = l.id
    ORDER BY c.date_cmd DESC
")->fetchAll();

$total_ca = $pdo->query("SELECT SUM(montant) FROM commandes_club")->fetchColumn() ?? 0;
$livreurs = $pdo->query("SELECT * FROM livreurs WHERE disponible = 1 ORDER BY nom")->fetchAll();

$edit_id  = isset($_GET['modifier']) ? (int)$_GET['modifier'] : null;
$edit_cmd = null;
if ($edit_id) {
    foreach ($commandes as $c) { if ($c['id'] == $edit_id) { $edit_cmd = $c; break; } }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Commandes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background:#f2f2f2; padding-bottom:80px;">

<?php include "menu.php"; ?>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestion des commandes (<?= count($commandes) ?>)</h2>
        <a href="admin.php" class="btn btn-secondary">Retour admin</a>
    </div>

    <div class="alert alert-success mb-4">
        Chiffre d'affaires total : <strong><?= number_format($total_ca, 2) ?> €</strong>
    </div>

    <!-- FORMULAIRE MODIFICATION SI EDIT -->
    <?php if ($edit_cmd): ?>
    <div class="bg-white shadow rounded p-4 mb-4">
        <h5 class="mb-3">Modifier la commande #<?= $edit_cmd['id'] ?> — <?= htmlspecialchars($edit_cmd['client']) ?></h5>
        <form method="post" class="row g-3">
            <input type="hidden" name="action" value="modifier">
            <input type="hidden" name="id_commande" value="<?= $edit_cmd['id'] ?>">

            <div class="col-md-5">
                <label class="form-label">Statut</label>
                <select name="statut" class="form-select">
                    <?php foreach (['en attente', 'en cours', 'expediee', 'livree', 'annulee'] as $s): ?>
                        <option value="<?= $s ?>" <?= $edit_cmd['statut'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-5">
                <label class="form-label">Livreur</label>
                <select name="livreur_id" class="form-select">
                    <option value="">-- Aucun livreur --</option>
                    <?php foreach ($livreurs as $l): ?>
                        <option value="<?= $l['id'] ?>" <?= $edit_cmd['livreur_id'] == $l['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($l['prenom']) ?> <?= htmlspecialchars($l['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-success w-100">Enregistrer</button>
                <a href="admin_commandes.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- LISTE DES COMMANDES -->
    <div class="bg-white shadow rounded p-4">
        <?php if (empty($commandes)): ?>
            <p class="text-muted">Aucune commande pour l'instant.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Detail</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Livreur</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $c): ?>
                    <?php
                        // Vérifier si la facture existe déjà
                        $invoice_number = 'CMD-' . date('Y', strtotime($c['date_cmd'])) . '-' . str_pad($c['id'], 5, '0', STR_PAD_LEFT);
                        $facture_existe = file_exists('factures/' . $invoice_number . '.html');
                    ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['client']) ?></td>
                        <td style="font-size:0.8rem; max-width:200px;"><?= htmlspecialchars($c['detail']) ?></td>
                        <td class="fw-bold text-success"><?= number_format($c['montant'], 2) ?> €</td>
                        <td>
                            <?php
                            $badges = [
                                'en attente' => 'secondary',
                                'en cours'   => 'warning',
                                'expediee'   => 'info',
                                'livree'     => 'success',
                                'annulee'    => 'danger'
                            ];
                            $badge = $badges[$c['statut']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $badge ?>"><?= ucfirst($c['statut'] ?? 'en attente') ?></span>
                        </td>
                        <td>
                            <?= $c['livreur_prenom'] ? htmlspecialchars($c['livreur_prenom'].' '.$c['livreur_nom']) : '<span class="text-muted">Non assigné</span>' ?>
                        </td>
                        <td style="font-size:0.85rem;"><?= date('d/m/Y H:i', strtotime($c['date_cmd'])) ?></td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="admin_commandes.php?modifier=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                                <a href="admin_commandes.php?facture=<?= $c['id'] ?>"
                                   class="btn btn-sm <?= $facture_existe ? 'btn-outline-success' : 'btn-outline-secondary' ?>"
                                   target="_blank"
                                   title="<?= $facture_existe ? 'Voir la facture' : 'Regénérer la facture' ?>">
                                    Facture
                                </a>
                                <a href="admin_commandes.php?supprimer=<?= $c['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Supprimer cette commande ?')">Supprimer</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include "footer.php"; ?>
</body>
</html>