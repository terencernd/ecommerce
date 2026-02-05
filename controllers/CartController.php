<?php
// Contrôleur pour la gestion du panier
require_once __DIR__ . '/../models/Ball.php';

// Créer la connexion à la base de données
$database = new Database();
$db = $database->getConnection();

// Créer une instance du modèle Ball
$ball = new Ball($db);

// Initialiser le panier si nécessaire
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Gérer les actions du panier
$action = $_GET['action'] ?? 'cart';

switch($action) {
    case 'addToCart':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = intval($_POST['product_id']);
            $quantity = intval($_POST['quantity'] ?? 1);
            
            // Vérifier si le produit existe et si le stock est suffisant
            if ($ball->checkStock($productId, $quantity)) {
                // Ajouter au panier ou mettre à jour la quantité
                if (isset($_SESSION['cart'][$productId])) {
                    $_SESSION['cart'][$productId] += $quantity;
                } else {
                    $_SESSION['cart'][$productId] = $quantity;
                }
                $_SESSION['message'] = "Produit ajouté au panier !";
            } else {
                $_SESSION['error'] = "Stock insuffisant !";
            }
            
            $redirect = $_POST['redirect'] ?? 'index.php?action=cart';
            header('Location: ' . $redirect);
            exit();
        }
        break;
    
    case 'updateCart':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = intval($_POST['product_id']);
            $quantity = intval($_POST['quantity']);
            
            if ($quantity > 0) {
                if ($ball->checkStock($productId, $quantity)) {
                    $_SESSION['cart'][$productId] = $quantity;
                    $_SESSION['message'] = "Quantité mise à jour !";
                } else {
                    $_SESSION['error'] = "Stock insuffisant !";
                }
            }
            header('Location: index.php?action=cart');
            exit();
        }
        break;
    
    case 'removeFromCart':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $productId = intval($_POST['product_id']);
            unset($_SESSION['cart'][$productId]);
            $_SESSION['message'] = "Produit retiré du panier !";
            header('Location: index.php?action=cart');
            exit();
        }
        break;
    
    case 'clearCart':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['cart'] = [];
            $_SESSION['message'] = "Panier vidé !";
            header('Location: index.php?action=cart');
            exit();
        }
        break;
    
    case 'cart':
    default:
        // Récupérer les détails des produits du panier
        $cartItems = [];
        $total = 0;
        
        foreach ($_SESSION['cart'] as $productId => $quantity) {
            $product = $ball->getById($productId);
            if ($product) {
                $subtotal = $product['prix'] * $quantity;
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal
                ];
                $total += $subtotal;
            }
        }
        
        // Calculer les frais de livraison
        $shippingCost = ($total >= 50) ? 0 : 5.90;
        $totalFinal = $total + $shippingCost;
        
        $pageTitle = "Mon Panier - VolleyShop";
        
        // Charger la vue
        require_once __DIR__ . '/../view/produits/cart.php';
        break;
}
?>
