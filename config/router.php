<?php
// Démarrer la session
session_start();

// Inclure la configuration
require_once __DIR__ . '/../config/database.php';

// Récupérer l'action depuis l'URL
$action = $_GET['action'] ?? 'home';

// Router les actions vers les contrôleurs appropriés
switch($action) {
    // Pages principales
    case 'home':
        require_once __DIR__ . '/../controllers/homeController.php';
        break;
    
    case 'product':
        require_once __DIR__ . '/../controllers/ProductController.php';
        break;
    
    // Panier
    case 'cart':
    case 'addToCart':
    case 'updateCart':
    case 'removeFromCart':
    case 'clearCart':
        require_once __DIR__ . '/../controllers/CartController.php';
        break;
    
    // Commande
    case 'checkout':
    case 'processOrder':
        require_once __DIR__ . '/../controllers/OrderController.php';
        break;
    
    // Contact
    case 'contact':
    case 'submitContact':
        require_once __DIR__ . '/../controllers/ContactController.php';
        break;
    
    default:
        require_once __DIR__ . '/../controllers/homeController.php';
        break;
}
?>
