<?php
session_start();
require "config.php";

$sql = "SELECT * FROM produits";
$stmt = $pdo->query($sql);
$produits = $stmt->fetchAll();
?>
<?php include "menu.php"; ?>

<div class="container mt-4">
    <h2>Boutique</h2>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Produit ajouté au panier</div>
    <?php endif; ?>

    <div class="row mt-3">
        <?php foreach ($produits as $produit): ?>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5><?php echo htmlspecialchars($produit['nom']); ?></h5>
                    <p><?php echo htmlspecialchars($produit['description']); ?></p>
                    <p class="fw-bold"><?php echo number_format($produit['prix'], 2); ?> €</p>
                    
                    <?php if ($produit['stock'] > 0): ?>
                        <form method="post" action="ajouter_panier.php">
                            <input type="hidden" name="produit_id" value="<?php echo $produit['id']; ?>">
                            <div class="input-group">
                                <input type="number" name="quantite" value="1" min="1" max="<?php echo $produit['stock']; ?>" class="form-control">
                                <button type="submit" class="btn btn-primary">Ajouter</button>
                            </div>
                        </form>
                        <small>Stock: <?php echo $produit['stock']; ?></small>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled>Rupture</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include "footer.php"; ?>