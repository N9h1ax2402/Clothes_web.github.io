<?php
require_once __DIR__ . '/../models/Order.php';

class OrderController {
    private $order;

    public function __construct($conn) {
        $this->order = new Order($conn);
    }

    public function createOrder($userId, $productId, $quantity, $total_price, $address, $fname, $lname, $phone) {
        // Validate input data
        return $this->order->createOrder($userId, $productId, $quantity, $total_price, $address, $fname, $lname, $phone);
        
    }



    public function getOrdersByUserId($userId) {
        return $this->order->getOrdersByUserId($userId);
    }
}
    
?>