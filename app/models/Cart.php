<?php
require_once __DIR__ . '/../../config/database.php';

class Cart {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->createCart();
    }

    public function createCart() {
        $stmt = "CREATE TABLE IF NOT EXISTS cart_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT(6) UNSIGNED NOT NULL,
            quantity INT NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB";
        $this->conn->exec($stmt);
    }
    
    public function getUserCart($userId) {
        $stmt = $this->conn->prepare("SELECT c.product_id as id, p.name, p.price, p.image, c.quantity 
                                FROM cart_items c 
                                JOIN product p ON c.product_id = p.id 
                                WHERE c.user_id = :userId");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $cart = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $price = (int)str_replace('.', '', $row['price']); // Convert price to integer
            $cart[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'price' => $price, // Store as integer for calculations
                'formatted_price' => number_format($price, 0, ',', '.') . ' VND', // Add formatted price
                'image' => $row['image'],
                'quantity' => (int)$row['quantity']
            ];
        }
        
        return $cart;
    }
    
    public function updateUserCart($userId, $cartItems) {
        try {
            // Begin transaction
            $this->conn->beginTransaction();
            
            // Clear existing cart first
            $stmt = $this->conn->prepare("DELETE FROM cart_items WHERE user_id = :userId");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Insert new items
            if (!empty($cartItems)) {
                $stmt = $this->conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) 
                                            VALUES (:userId, :productId, :quantity)");
                
                foreach ($cartItems as $item) {
                    $productId = $item['id'];
                    $quantity = $item['quantity'];
                    
                    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
                    $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
                    $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
            
            // Commit transaction
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback in case of error
            $this->conn->rollBack();
            return false;
        }
    }
    
    public function mergeSessionCartWithUserCart($userId, $localCart = null) {
        // If localCart is not provided, fall back to session (for backward compatibility)
        $localCart = $localCart ?? (isset($_SESSION['local_cart']) ? $_SESSION['local_cart'] : []);
        $dbCart = $this->getUserCart($userId);
        
        $mergedCart = $this->mergeCartItems($localCart, $dbCart);
        
        // Update the database with merged cart
        $this->updateUserCart($userId, $mergedCart);
        
        // Clear the session local cart
        unset($_SESSION['local_cart']);
        
        return $mergedCart;
    }
    
    public function syncCart($clientCart) {
        $userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;

        if ($userId) {
            $dbCart = $this->getUserCart($userId);
            $mergedCart = $this->mergeCartItems($clientCart, $dbCart);
            $this->updateUserCart($userId, $mergedCart);
            return $mergedCart;
        } else {
            $sessionCart = $this->getSessionCart();
            $mergedCart = $this->mergeCartItems($clientCart, $sessionCart);
            $_SESSION['local_cart'] = $mergedCart;
            return $mergedCart;
        }
    }
    
    public function addToSessionCart($productId, $quantity = 1) {
        if (!isset($_SESSION['local_cart'])) {
            $_SESSION['local_cart'] = [];
        }
        
        $quantity = max(1, (int)$quantity); // Ensure quantity is at least 1
        
        $found = false;
        foreach ($_SESSION['local_cart'] as &$item) {
            if ($item['id'] == $productId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $stmt = $this->conn->prepare("SELECT * FROM product WHERE id = :id");
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                $_SESSION['local_cart'][] = [
                    'id' => (int)$productId,
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'image' => $product['image'],
                    'quantity' => $quantity
                ];
            }
        }
        
        return $_SESSION['local_cart'];
    }
    
    public function getSessionCart() {
        return isset($_SESSION['local_cart']) ? $_SESSION['local_cart'] : [];
    }
    
    public function getCart() {
        // Check if user is logged in
        $userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;
        
        if ($userId) {
            return $this->getUserCart($userId);
        } else {
            return $this->getSessionCart();
        }
    }
    
    private function mergeCartItems($clientCart, $dbCart) {
        $mergedItems = [];
        
        // Start with database cart as the base
        foreach ($dbCart as $dbItem) {
            $mergedItems[$dbItem['id']] = $dbItem;
        }
        
        // Update with client cart, preferring client quantities to avoid duplication
        foreach ($clientCart as $clientItem) {
            $itemId = (int)$clientItem['id'];
            if ($itemId <= 0 || !isset($clientItem['quantity']) || (int)$clientItem['quantity'] <= 0) {
                continue;
            }
            
            if (isset($mergedItems[$itemId])) {
                // Use client quantity to prevent accumulation
                $mergedItems[$itemId]['quantity'] = (int)$clientItem['quantity'];
            } else {
                $mergedItems[$itemId] = [
                    'id' => $itemId,
                    'name' => $clientItem['name'],
                    'price' => $clientItem['price'],
                    'image' => $clientItem['image'],
                    'quantity' => (int)$clientItem['quantity']
                ];
            }
        }
        
        return array_values(array_filter($mergedItems, function($item) {
            return $item['quantity'] > 0;
        }));
    }
    
    public function removeFromCart($productId) {
        $userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;
        
        if ($userId) {
            // Remove from database
            $stmt = $this->conn->prepare("DELETE FROM cart_items WHERE user_id = :userId AND product_id = :productId");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
            $result = $stmt->execute();
            error_log("Delete result: " . ($result ? "success" : "failed"));
            return $result;
            
        } else {
            // Remove from session
            if (isset($_SESSION['local_cart'])) {
                foreach ($_SESSION['local_cart'] as $key => $item) {
                    if ($item['id'] == $productId) {
                        unset($_SESSION['local_cart'][$key]);
                        $_SESSION['local_cart'] = array_values($_SESSION['local_cart']); // Reindex array
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    public function updateCartItemQuantity($productId, $quantity) {
        $userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;

        if ($userId) {
            if ($quantity <= 0) {
                // Ensure the item is deleted from the database
                return $this->removeFromCart($productId);
            }

            $stmt = $this->conn->prepare("UPDATE cart_items SET quantity = :quantity 
                                        WHERE user_id = :userId AND product_id = :productId");
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
            return $stmt->execute();
        }

        if (isset($_SESSION['local_cart'])) {
            foreach ($_SESSION['local_cart'] as $key => &$item) {
                if ($item['id'] == $productId) {
                    if ($quantity <= 0) {
                        // Remove the item from the session cart
                        unset($_SESSION['local_cart'][$key]);
                        $_SESSION['local_cart'] = array_values($_SESSION['local_cart']); // Reindex array
                        return true;
                    }
                    $item['quantity'] = $quantity;
                    return true;
                }
            }
        }

        return false;
    }

    public function clearCart() {
        $userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;
        
        if ($userId) {
            // Clear database cart
            $stmt = $this->conn->prepare("DELETE FROM cart_items WHERE user_id = :userId");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } else {
            // Clear session cart
            $_SESSION['local_cart'] = [];
            return true;
        }
    }

    public function clearCartOnLogout() {
        $userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;

        if ($userId) {
            // Clear database cart
            $stmt = $this->conn->prepare("DELETE FROM cart_items WHERE user_id = :userId");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
        }

        // Clear session cart
        $_SESSION['local_cart'] = [];
    }
}
?>