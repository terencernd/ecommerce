<?php require_once __DIR__ . '/../templates/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1 class="display-4 mb-3">Bienvenue chez VolleyShop</h1>
        <p class="lead">Votre spécialiste en balles de volleyball de qualité professionnelle</p>
    </div>
</section>

<!-- Contenu principal -->
<main class="container my-5">
    
    <!-- Titre -->
    <h2 class="text-center mb-5">Nos Produits</h2>
    
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
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo htmlspecialchars($_SESSION['error']); 
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Grille de produits -->
    <div class="row g-4">
        <?php foreach ($produits as $produit): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card card-product h-100">
                    <img src="<?php echo htmlspecialchars($produit['image']); ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($produit['nom']); ?>"
                         style="height: 250px; object-fit: cover;">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo htmlspecialchars($produit['nom']); ?></h5>
                        <p class="card-text text-muted flex-grow-1">
                            <?php echo htmlspecialchars(substr($produit['description'], 0, 100)) . '...'; ?>
                        </p>
                        <p class="price mb-2"><?php echo number_format($produit['prix'], 2, ',', ' '); ?> €</p>
                        <p class="text-success mb-3">
                            <i class="bi bi-check-circle"></i> Stock: <?php echo $produit['stock']; ?> unités
                        </p>
                        
                        <div class="d-grid gap-2">
                            <a href="index.php?action=product&id=<?php echo $produit['id']; ?>" 
                               class="btn btn-outline-primary">
                                <i class="bi bi-eye"></i> Voir détails
                            </a>
                            
                            <form action="index.php?action=addToCart" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $produit['id']; ?>">
                                <input type="hidden" name="redirect" value="index.php?action=home">
                                <div class="input-group">
                                    <input type="number" name="quantity" value="1" min="1" 
                                           max="<?php echo $produit['stock']; ?>" 
                                           class="form-control" style="max-width: 80px;">
                                    <button type="submit" class="btn btn-primary flex-grow-1">
                                        <i class="bi bi-cart-plus"></i> Ajouter au panier
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
