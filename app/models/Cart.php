<?php
require_once __DIR__ . '/../../config/database.php';

class Cart {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function createCart($userId) {
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
            $cart[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'price' => (int)$row['price'],
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
    
    public function mergeSessionCartWithUserCart($userId) {
        // Get carts from both localStorage (via $_SESSION['local_cart']) and database
        $localCart = isset($_SESSION['local_cart']) ? $_SESSION['local_cart'] : [];
        $dbCart = $this->getUserCart($userId);
        
        $mergedCart = $this->mergeCartItems($localCart, $dbCart);
        
        // Update the database with merged cart
        $this->updateUserCart($userId, $mergedCart);
        
        // Clear the session local cart
        unset($_SESSION['local_cart']);
        
        return $mergedCart;
    }
    
    public function syncCart($clientCart) {
        // Check if user is logged in
        $userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;
        
        if ($userId) {
            // User is logged in, merge cart from client with database cart
            $dbCart = $this->getUserCart($userId);
            $mergedCart = $this->mergeCartItems($clientCart, $dbCart);
            
            // Save merged cart to database
            $this->updateUserCart($userId, $mergedCart);
            
            return $mergedCart;
        } else {
            // User is not logged in, store cart in session for later merging
            $_SESSION['local_cart'] = $clientCart;
            
            // Just return the same cart back
            return $clientCart;
        }
    }
    
    public function addToSessionCart($productId, $quantity = 1) {
        if (!isset($_SESSION['local_cart'])) {
            $_SESSION['local_cart'] = [];
        }
        
        $found = false;
        foreach ($_SESSION['local_cart'] as &$item) {
            if ($item['id'] == $productId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            // Get product details
            $stmt = $this->conn->prepare("SELECT * FROM product WHERE id = :id");
            $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                $_SESSION['local_cart'][] = [
                    'id' => (int)$productId,
                    'name' => $product['name'],
                    'price' => (int)$product['price'],
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
        // Create a map for faster item lookup
        $mergedItems = [];
        
        // Add all database items to the merged cart first
        foreach ($dbCart as $dbItem) {
            $mergedItems[$dbItem['id']] = $dbItem;
        }
        
        // Now merge client items - either add new ones or update quantities
        foreach ($clientCart as $clientItem) {
            $itemId = $clientItem['id'];
            
            if (isset($mergedItems[$itemId])) {
                // Item exists in both carts, combine quantities
                $mergedItems[$itemId]['quantity'] += $clientItem['quantity'];
            } else {
                // New item from client cart
                $mergedItems[$itemId] = $clientItem;
            }
        }
        
        // Convert back to indexed array
        return array_values($mergedItems);
    }
    
    public function removeFromCart($productId) {
        $userId = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;
        
        if ($userId) {
            // Remove from database
            $stmt = $this->conn->prepare("DELETE FROM cart_items WHERE user_id = :userId AND product_id = :productId");
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
            return $stmt->execute();
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
            // Update in database
            if ($quantity <= 0) {
                // Remove item if quantity is 0 or negative
                return $this->removeFromCart($productId);
            }
            
            $stmt = $this->conn->prepare("UPDATE cart_items SET quantity = :quantity 
                                        WHERE user_id = :userId AND product_id = :productId");
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':productId', $productId, PDO::PARAM_INT);
            return $stmt->execute();
        } else {
            // Update in session
            if (isset($_SESSION['local_cart'])) {
                foreach ($_SESSION['local_cart'] as &$item) {
                    if ($item['id'] == $productId) {
                        if ($quantity <= 0) {
                            // Remove item if quantity is 0 or negative
                            return $this->removeFromCart($productId);
                        }
                        $item['quantity'] = $quantity;
                        return true;
                    }
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
}
?>