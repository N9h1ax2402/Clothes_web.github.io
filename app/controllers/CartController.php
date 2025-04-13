<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../../config/database.php';

$cartModel = new Cart($conn);
$productModel = new Product($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'check_login':
            header('Content-Type: application/json');
            echo json_encode([
                'loggedIn' => isset($_SESSION['user']),
                'userId' => isset($_SESSION['user']) ? $_SESSION['user']['id'] : null
            ]);
            exit;
            
        case 'get_cart':
            handleGetCart();
            exit;
            
        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
    }
}

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
            handleMergeCartsAfterLogin($data['cart'] ?? []);
            break;
            
        case 'logout':
            handleLogout();
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    exit;
}

function handleGetCart() {
    global $cartModel;
    
    $cart = $cartModel->getCart();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'cart' => $cart
    ]);
}

function handleUpdateCart($cartData) {
    global $cartModel;
    
    $userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;
    
    if ($userId) {
        $success = $cartModel->updateUserCart($userId, $cartData);
    } else {
        $success = true; // Client handles anonymous cart
    }
    
    echo json_encode([
        'success' => $success,
        'cart' => $cartModel->getCart()
    ]);
}

function handleSyncCart($clientCart) {
    global $cartModel;

    $mergedCart = $cartModel->syncCart($clientCart);

    echo json_encode([
        'success' => true,
        'cart' => $mergedCart
    ]);
}

function handleAddItem($productId, $quantity) {
    global $cartModel, $productModel;
    
    $productId = (int)$productId;
    $quantity = max(1, (int)$quantity); // Ensure quantity is at least 1
    
    if ($productId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        return;
    }
    
    $userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;
    
    if ($userId) {
        $product = $productModel->getProductById($productId);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }
        
        $cart = $cartModel->getUserCart($userId);
        
        $found = false;
        foreach ($cart as &$item) {
            if ($item['id'] == $productId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $cart[] = [
                'id' => $productId,
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $quantity
            ];
        }
        
        $success = $cartModel->updateUserCart($userId, $cart);
        $updatedCart = $cartModel->getUserCart($userId);
    } else {
        $updatedCart = $cartModel->addToSessionCart($productId, $quantity);
        $success = true;
    }
    
    echo json_encode([
        'success' => $success,
        'cart' => $updatedCart
    ]);
}

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

function handleClearCart() {
    global $cartModel;
    
    $success = $cartModel->clearCart();
    
    echo json_encode([
        'success' => $success,
        'cart' => []
    ]);
}

function handleMergeCartsAfterLogin($localCart) {
    global $cartModel;
    
    $userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;
    
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        return;
    }
    
    $mergedCart = $cartModel->mergeSessionCartWithUserCart($userId, $localCart);
    
    echo json_encode([
        'success' => true,
        'cart' => $mergedCart
    ]);
}

function handleLogout() {
    global $cartModel;

    $cartModel->clearCartOnLogout();
    unset($_SESSION['user']);
    session_destroy();

    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully',
        'cart' => [] // Ensure cart display is cleared
    ]);
}
?>