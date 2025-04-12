<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'check_login') {
    header('Content-Type: application/json');
    echo json_encode([
        'loggedIn' => isset($_SESSION['user']),
        'userId' => isset($_SESSION['user']) ? $_SESSION['user']['id'] : null
    ]);
    exit;
}

require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../../config/database.php';

// Initialize database connection and cart model
$cartModel = new Cart($conn);
$productModel = new Product($conn);

// For GET requests (mainly for the cart status check)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_cart') {
    handleGetCart();
    exit;
}

// For POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
        exit;
    }
    
    switch ($data['action'] ?? '') {
        case 'updateCart':
            handleUpdateCart($data['cart'] ?? []);
            break;
            
        case 'getCart':
            handleGetCart();
            break;
            
        case 'sync':
            handleSyncCart($data['cart'] ?? []);
            break;
            
        case 'addItem':
            handleAddItem($data['productId'] ?? 0, $data['quantity'] ?? 1);
            break;
            
        case 'removeItem':
            handleRemoveItem($data['productId'] ?? 0);
            break;
            
        case 'updateQuantity':
            handleUpdateQuantity($data['productId'] ?? 0, $data['quantity'] ?? 1);
            break;
            
        case 'clearCart':
            handleClearCart();
            break;
            
        case 'mergeCartsAfterLogin':
            handleMergeCartsAfterLogin();
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    exit;
}

/**
 * Handle updating cart with a completely new cart
 */
function handleUpdateCart($cartData) {
    global $cartModel;
    
    $userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;
    
    if ($userId) {
        // Store cart data in session for syncing with localStorage
        $_SESSION['local_cart'] = $cartData;
        // User is logged in, update the database cart
        $success = $cartModel->updateUserCart($userId, $cartData);
    } else {
        // User is not logged in, update the session cart
        $_SESSION['local_cart'] = $cartData;
        $success = true;
    }
    
    echo json_encode([
        'success' => $success,
        'cart' => $cartModel->getCart()
    ]);
}

/**
 * Handle retrieving current cart
 */
function handleGetCart() {
    global $cartModel;
    
    $cart = $cartModel->getCart();
    
    echo json_encode([
        'success' => true,
        'cart' => $cart
    ]);
}

/**
 * Handle syncing client-side cart with server-side
 */
function handleSyncCart($clientCart) {
    global $cartModel;
    
    // Store the client cart in session for merging after login
    $_SESSION['local_cart'] = $clientCart;
    
    $mergedCart = $cartModel->syncCart($clientCart);
    
    echo json_encode([
        'success' => true,
        'cart' => $mergedCart
    ]);
}

/**
 * Handle adding an item to the cart
 */
function handleAddItem($productId, $quantity) {
    global $cartModel, $productModel;
    
    $productId = (int)$productId;
    $quantity = (int)$quantity;
    
    if ($productId <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID or quantity']);
        return;
    }
    
    $userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;
    
    if ($userId) {
        // User is logged in, add to database cart
        $product = $productModel->getProductById($productId);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }
        
        // Get current cart
        $cart = $cartModel->getUserCart($userId);
        
        // Check if product already exists in cart
        $found = false;
        foreach ($cart as &$item) {
            if ($item['id'] == $productId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            // Add new item
            $cart[] = [
                'id' => $productId,
                'name' => $product['name'],
                'price' => (int)$product['price'],
                'image' => $product['image'],
                'quantity' => $quantity
            ];
        }
        
        $success = $cartModel->updateUserCart($userId, $cart);
        $updatedCart = $cartModel->getUserCart($userId);
    } else {
        // User is not logged in, add to session cart
        $updatedCart = $cartModel->addToSessionCart($productId, $quantity);
        $success = true;
    }
    
    echo json_encode([
        'success' => $success,
        'cart' => $updatedCart
    ]);
}

/**
 * Handle removing an item from the cart
 */
function handleRemoveItem($productId) {
    global $cartModel;
    
    $productId = (int)$productId;
    
    if ($productId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        return;
    }
    
    $success = $cartModel->removeFromCart($productId);
    
    echo json_encode([
        'success' => $success,
        'cart' => $cartModel->getCart()
    ]);
}

/**
 * Handle updating the quantity of an item
 */
function handleUpdateQuantity($productId, $quantity) {
    global $cartModel;
    
    $productId = (int)$productId;
    $quantity = (int)$quantity;
    
    if ($productId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        return;
    }
    
    $success = $cartModel->updateCartItemQuantity($productId, $quantity);
    
    echo json_encode([
        'success' => $success,
        'cart' => $cartModel->getCart()
    ]);
}

/**
 * Handle clearing the entire cart
 */
function handleClearCart() {
    global $cartModel;
    
    $success = $cartModel->clearCart();
    
    echo json_encode([
        'success' => $success,
        'cart' => []
    ]);
}

/**
 * Handle merging carts after login
 */
function handleMergeCartsAfterLogin() {
    global $cartModel;
    
    $userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;
    
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        return;
    }
    
    $mergedCart = $cartModel->mergeSessionCartWithUserCart($userId);
    
    echo json_encode([
        'success' => true,
        'cart' => $mergedCart
    ]);
}
?>