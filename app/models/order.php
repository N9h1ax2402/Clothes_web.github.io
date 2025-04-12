<?php
require_once __DIR__ . '/../../config/database.php';

class Order {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->ensureOrdersTableExists();
    }

    public function ensureOrdersTableExists() {
        $sql = "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT(6) UNSIGNED NOT NULL,
            quantity INT NOT NULL,
            total_price DECIMAL(10, 2) NOT NULL,
            order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            address VARCHAR(255) NOT NULL,
            fname VARCHAR(255) NOT NULL,
            lname VARCHAR(255) NOT NULL,
            phone varchar(15) NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB";
        $this->conn->exec($sql);
    }

    public function createOrder($userId, $productId, $quantity, $total_price, $address, $fname, $lname, $phone) {
        try {
            // Basic input validation
            if (empty($userId) || empty($productId) || empty($quantity) || empty($total_price) ||
                empty($address) || empty($fname) || empty($lname) || empty($phone)) {
                return ['success' => false, 'message' => 'All fields are required'];
            }
            
            // Prepare the SQL statement
            $sql = "INSERT INTO orders (user_id, product_id, quantity, total_price, address, fname, lname, phone) 
                    VALUES (:user_id, :product_id, :quantity, :total_price, :address, :fname, :lname, :phone)";
            
            $stmt = $this->conn->prepare($sql);
            
            // Execute with parameters
            $result = $stmt->execute([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => (int)$quantity, // Ensure quantity is an integer
                'total_price' => (float)$total_price, // Ensure total_price is a float
                'address' => $address,
                'fname' => $fname,
                'lname' => $lname,
                'phone' => $phone
            ]);
            
            // Check if the insertion was successful
            if (!$result) {
                throw new Exception("Database error: " . implode(" ", $stmt->errorInfo()));
            }
            
            // Get the ID of the inserted order
            $orderId = $this->conn->lastInsertId();
            
            return [
                'success' => true, 
                'message' => 'Order placed successfully!',
                'order_id' => $orderId
            ];
        } 
        catch (Exception $e) {
            error_log("Order creation error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Failed to create order: ' . $e->getMessage()
            ];
        }
    }

    public function getOrdersByUserId($userId) {
        $sql = "SELECT * FROM orders WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>