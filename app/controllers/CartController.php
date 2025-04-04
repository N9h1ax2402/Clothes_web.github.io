<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
            
        case 'clearCart':
            handleClearCart();
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}


function handleUpdateCart($cart) {
    if (!is_array($cart)) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart data']);
        exit;
    }
    
    $_SESSION['cart'] = $cart;
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart updated',
        'cartCount' => getCartCount(),
        'cartTotal' => getCartTotal()
    ]);
    exit;
}


function handleGetCart() {
    $cart = $_SESSION['cart'] ?? [];
    
    echo json_encode([
        'success' => true,
        'cart' => $cart,
        'cartCount' => getCartCount(),
        'cartTotal' => getCartTotal()
    ]);
    exit;
}


function handleClearCart() {
    $_SESSION['cart'] = [];
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart cleared',
        'cartCount' => 0,
        'cartTotal' => 0
    ]);
    exit;
}


function getCartCount() {
    $cart = $_SESSION['cart'] ?? [];
    $count = 0;
    
    foreach ($cart as $item) {
        $count += $item['quantity'] ?? 0;
    }
    
    return $count;
}


function getCartTotal() {
    $cart = $_SESSION['cart'] ?? [];
    $total = 0;
    
    foreach ($cart as $item) {
        $total += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
    }
    
    return $total;
}
?>