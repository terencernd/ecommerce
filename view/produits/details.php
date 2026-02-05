<?php require_once __DIR__ . '/../templates/header.php'; ?>

<main class="container my-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?action=home">Accueil</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($produit['nom']); ?></li>
        </ol>
    </nav>
    
    <!-- Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo htmlspecialchars($_SESSION['message']); 
            unset($_SESSION['message']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Détails du produit -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <img src="<?php echo htmlspecialchars($produit['image']); ?>" 
                 class="img-fluid rounded shadow" 
                 alt="<?php echo htmlspecialchars($produit['nom']); ?>">
        </div>
        
        <div class="col-md-6">
            <h1 class="mb-3"><?php echo htmlspecialchars($produit['nom']); ?></h1>
            
            <h2 class="price mb-4"><?php echo number_format($produit['prix'], 2, ',', ' '); ?> €</h2>
            
            <!-- Description -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Description</h5>
                    <p class="card-text"><?php echo htmlspecialchars($produit['description']); ?></p>
                </div>
            </div>
            
            <!-- Caractéristiques -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Caractéristiques</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">
                            <strong>Matériau:</strong> Cuir synthétique premium
                        </li>
                        <li class="list-group-item">
                            <strong>Taille:</strong> Officielle (circonférence 65-67 cm)
                        </li>
                        <li class="list-group-item">
                            <strong>Poids:</strong> 260-280g
                        </li>
                        <li class="list-group-item">
                            <strong>Utilisation:</strong> Intérieur et extérieur
                        </li>
                        <li class="list-group-item">
                            <strong>Garantie:</strong> 1 an constructeur
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Stock -->
            <div class="alert <?php echo $produit['stock'] > 0 ? 'alert-success' : 'alert-danger'; ?> mb-4">
                <?php if ($produit['stock'] > 0): ?>
                    <i class="bi bi-check-circle"></i> En stock (<?php echo $produit['stock']; ?> unités disponibles)
                <?php else: ?>
                    <i class="bi bi-x-circle"></i> Rupture de stock
                <?php endif; ?>
            </div>
            
            <!-- Formulaire d'ajout au panier -->
            <?php if ($produit['stock'] > 0): ?>
                <div class="card">
                    <div class="card-body">
                        <form action="index.php?action=addToCart" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $produit['id']; ?>">
                            <input type="hidden" name="redirect" value="index.php?action=product&id=<?php echo $produit['id']; ?>">
                            
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantité:</label>
                                <input type="number" 
                                       id="quantity" 
                                       name="quantity" 
                                       value="1" 
                                       min="1" 
                                       max="<?php echo $produit['stock']; ?>" 
                                       class="form-control" 
                                       style="max-width: 120px;">
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-cart-plus"></i> Ajouter au panier
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Bouton retour -->
            <div class="mt-3">
                <a href="index.php?action=home" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Retour aux produits
                </a>
            </div>
        </div>
    </div>
    
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
