<?php require_once __DIR__ . '/../templates/header.php'; ?>

<main class="container my-5">
    <h1 class="mb-4"><i class="bi bi-cart3"></i> Mon Panier</h1>
    
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
    
    <?php if (empty($cartItems)): ?>
        <!-- Panier vide -->
        <div class="card text-center">
            <div class="card-body py-5">
                <i class="bi bi-cart-x" style="font-size: 5rem; color: #ccc;"></i>
                <h3 class="mt-3">Votre panier est vide</h3>
                <p class="text-muted">Découvrez nos produits et commencez vos achats !</p>
                <a href="index.php?action=home" class="btn btn-primary mt-3">
                    <i class="bi bi-shop"></i> Découvrir nos produits
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Contenu du panier -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Articles (<?php echo count($cartItems); ?>)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produit</th>
                                        <th class="text-center">Prix unitaire</th>
                                        <th class="text-center">Quantité</th>
                                        <th class="text-end">Sous-total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo htmlspecialchars($item['product']['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['product']['nom']); ?>"
                                                         style="width: 80px; height: 80px; object-fit: cover;"
                                                         class="rounded me-3">
                                                    <div>
                                                        <h6 class="mb-0">
                                                            <?php echo htmlspecialchars($item['product']['nom']); ?>
                                                        </h6>
                                                        <small class="text-muted">
                                                            Stock: <?php echo $item['product']['stock']; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <?php echo number_format($item['product']['prix'], 2, ',', ' '); ?> €
                                            </td>
                                            <td class="text-center align-middle">
                                                <form action="index.php?action=updateCart" method="POST" class="d-inline">
                                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                                    <div class="input-group" style="max-width: 150px; margin: 0 auto;">
                                                        <input type="number" 
                                                               name="quantity" 
                                                               value="<?php echo $item['quantity']; ?>" 
                                                               min="1" 
                                                               max="<?php echo $item['product']['stock']; ?>"
                                                               class="form-control form-control-sm">
                                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-check"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td class="text-end align-middle">
                                                <strong><?php echo number_format($item['subtotal'], 2, ',', ' '); ?> €</strong>
                                            </td>
                                            <td class="text-center align-middle">
                                                <form action="index.php?action=removeFromCart" method="POST" class="d-inline">
                                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                            onclick="return confirm('Supprimer cet article ?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="d-flex justify-content-between">
                    <form action="index.php?action=clearCart" method="POST" class="d-inline">
                        <button type="submit" class="btn btn-outline-danger" 
                                onclick="return confirm('Êtes-vous sûr de vouloir vider le panier ?')">
                            <i class="bi bi-trash"></i> Vider le panier
                        </button>
                    </form>
                    <a href="index.php?action=home" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Continuer mes achats
                    </a>
                </div>
            </div>
            
            <!-- Récapitulatif -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 100px;">
                    <div class="card-header">
                        <h5 class="mb-0">Récapitulatif</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Sous-total:</span>
                            <span><?php echo number_format($total, 2, ',', ' '); ?> €</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Livraison:</span>
                            <span>
                                <?php echo $shippingCost > 0 ? number_format($shippingCost, 2, ',', ' ') . ' €' : 'Gratuite'; ?>
                            </span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total TTC:</strong>
                            <strong class="text-primary" style="font-size: 1.3rem;">
                                <?php echo number_format($totalFinal, 2, ',', ' '); ?> €
                            </strong>
                        </div>
                        
                        <?php if ($shippingCost > 0): ?>
                            <div class="alert alert-info py-2 small">
                                <i class="bi bi-info-circle"></i> 
                                Livraison gratuite à partir de 50€
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success py-2 small">
                                <i class="bi bi-check-circle"></i> 
                                Livraison gratuite !
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-grid">
                            <a href="index.php?action=checkout" class="btn btn-primary btn-lg">
                                <i class="bi bi-credit-card"></i> Valider ma commande
                            </a>
                        </div>
                        
                        <div class="mt-3 text-center small text-muted">
                            <i class="bi bi-shield-check"></i> Paiement sécurisé<br>
                            <i class="bi bi-truck"></i> Livraison sous 3-5 jours
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
