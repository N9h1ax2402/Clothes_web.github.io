<?php
require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../../config/database.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Fetch cart data from the session
$cart = $_SESSION['cart'] ?? [];
$totalAmount = 0;




// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user ID from session
    $userId = $_SESSION['user']['id'] ?? null;
    $cart = $_SESSION['cart'] ?? [];

    $postData = json_decode(file_get_contents('php://input'), true);
    $formData = $postData['formData'] ?? [];

    

    // Check if user is logged in
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        header('Location: /mywebsite/public/index.php?page=authentication');
    }

    // Check if cart is empty
    if (empty($cart)) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit;
    }
    
    // Validate form data
    if (empty($formData['firstName']) || empty($formData['lastName']) || 
        empty($formData['address']) || empty($formData['phone'])) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all delivery details']);
        exit;
    }
    
    try {
        $orderController = new OrderController($conn);
        $allOrdersSuccessful = true;
        $orderMessage = 'Order placed successfully!';
        
        foreach ($cart as $item) {
            if (!isset($item['id']) || !isset($item['quantity'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid item in cart']);
                exit;
            }

            if (!isset($item['price']) || !isset($item['quantity'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid item in cart']);
                exit;
            }
            
            // Accumulate the total
            $totalAmount = $item['price'] * $item['quantity'];
            
            $productId = $item['id'];
            $quantity = $item['quantity'];
           
            // Create order for each item in the cart with delivery details
            $result = $orderController->createOrder(
                $userId, 
                $productId, 
                $quantity, 
                $totalAmount,
                $formData['address'], 
                $formData['firstName'], 
                $formData['lastName'], 
                $formData['phone']
            );
            
            // Check if order creation was successful
            if (!isset($result['success']) || $result['success'] === false) {
                $allOrdersSuccessful = false;
                $orderMessage = $result['message'] ?? 'Failed to place order. Please try again.';
                break;
            }
        }
        
        // Clear cart if all orders were successful
        if ($allOrdersSuccessful) {
            $_SESSION['cart'] = [];
        }
        
        // Return JSON response
        echo json_encode([
            'success' => $allOrdersSuccessful, 
            'message' => $orderMessage
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Page</title>
    <script src="<?= BASE_URL ?>/js/cart.js"></script>
    <link href="<?= BASE_URL ?>/css/node_modules/bootstrap/dist/css/bootstrap.css" rel="stylesheet">

    <style>
        body {
            
            background-color: #f5f5f5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #fff;

            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center; /* Center the navbar-brand */
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-brand {
            font-size: 35px;
           
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #000;
            text-decoration: none;
        }
        .navbar-brand:hover {
            color: #333;
        }
        .nav-left {
            position: absolute;
            left: 20px; /* Keep the back arrow on the left */
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 20px;
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .left, .right {
            flex: 1;
            height: 450px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        label {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        input {
            width: 95%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease;
        }
        input:focus {
            border-color: #333;
            outline: none;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eaeaea;
            font-size: 14px;
        }
        .summary-item-container {
            max-height: 250px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        .summary-item-container::-webkit-scrollbar {
            width: 6px;
        }
        .summary-item-container::-webkit-scrollbar-thumb {
            background-color: #ccc;
            border-radius: 3px;
        }
        .total {
            font-size: 18px;
            font-weight: 600;
            margin-top: 30px;
        }
        .button-wrapper {
            display: flex;
            margin-top: 30px;
            
        }
        .button {
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .pay-button {
            background-color: #000;
            color: #fff;
            width: 100%; /* Make the button full width */
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .pay-button:hover {
            background-color: #333;
        }
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                align-items: center;
            }
            .left, .right {
                width: 100%;
                height: auto;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="nav-left" onclick="history.back()">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
        </svg>
    </div>
    <a class="navbar-brand" href="/mywebsite/public/index.php?page=home">PVI</a>
</header>

<div class="container">
    <!-- Left Section: Delivery Details -->
    <div class="left">
        <h2>Delivery Details</h2>
        <form id="delivery-form" >
            <label for="firstName">First Name</label>
            <input type="text" id="firstName" name="firstName" required>
            <label for="lastName">Last Name</label>
            <input type="text" id="lastName" name="lastName" required>
            <label for="address">Address</label>
            <input type="text" id="address" name="address" required>
            <label for="Phone">Phone</label>
            <input type="text" id="phone" name="phone" required>
        </form>
    </div>
    
    <!-- Right Section: Order Summary -->
    <div class="right">
        <h2>Order Summary</h2>
        <div class="summary-item-container">
            <?php if (!empty($cart)): ?>
                <?php foreach ($cart as $item): ?>
                    <div class="summary-item">
                        <span><?= htmlspecialchars($item['name'] ?? 'Unknown Product') ?> (x<?= $item['quantity'] ?? 0 ?>)</span>
                        <span><?= number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 0), 0, ',', '.') ?>VNĐ</span>
                    </div>
                    <?php $totalAmount += ($item['price'] ?? 0) * ($item['quantity'] ?? 0); ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>
        <?php if (!empty($cart)): ?>
            <div class="summary-item total">
                <span>Total:</span>
                <span><?= number_format($totalAmount, 0, ',', '.') ?>VNĐ</span>
            </div>
            <div class="button-wrapper">
                <button id="pay-now-btn" class="button pay-button">Pay Now</button>
            </div>
        <?php endif; ?>
    </div>
</div>

    <script>
    document.getElementById('pay-now-btn')?.addEventListener('click', function() {
        // Disable the button and show loading state
        this.disabled = true;
        this.textContent = 'Processing...';
        
        // Validate form if needed
        const form = document.getElementById('delivery-form');
        if (!form.checkValidity()) {
            form.reportValidity();
            this.disabled = false;
            this.textContent = 'Pay Now';
            return;
        }
        if (!<?php echo isset($_SESSION['user']) ? 'true' : 'false'; ?>) {
                alert('You need to log in to place an order.');
                window.location.href = '/mywebsite/public/index.php?page=authentication';
            }
        
        fetch(window.location.href, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ 
                action: 'pay_now',
                // You can add form data here if needed
                formData: {
                    firstName: document.getElementById('firstName').value,
                    lastName: document.getElementById('lastName').value,
                    address: document.getElementById('address').value,
                    phone: document.getElementById('phone').value
                }
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            return response.text();
        })
        .then(text => {
            if (!text) {
                throw new Error('Empty response from server');
            }
            
            try {
                return JSON.parse(text);
            } catch (error) {
                console.error('Failed to parse response as JSON:', text);
                throw new Error('Invalid JSON response from server');
            }
        })
        .then(data => {
            if (data.success) {
                alert(data.message);
                if (typeof clearCartItems === 'function') {
                    clearCartItems();
                } else {
                    console.error('clearCartItems function not found!');
                }
                window.location.href = '/mywebsite/public/index.php?page=product'; 
            } else {
                alert(data.message);
                // Reset button
                this.disabled = false;
                this.textContent = 'Pay Now';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your order. Please try again.');
            // Reset button
            this.disabled = false;
            this.textContent = 'Pay Now';
        });
    });
    </script>
</body>
</html>