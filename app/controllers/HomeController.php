<?php
class HomeController {
    public function index() {
        require_once BASE_PATH . '/app/views/home.php';
    }
    
    public function product() {
        require_once BASE_PATH . '/app/views/product.php';
    }
    
    public function productDetail($id) {
        $product = $id;
        require_once BASE_PATH . '/app/views/productDetail.php';
    }

    public function contact() {
        require_once BASE_PATH . '/app/views/contact.php';
    }

    public function authentication() {
        require_once BASE_PATH . '/app/views/authentication.php'; 
    }

    public function account() {
        
        require_once BASE_PATH . '/app/views/account.php';
    }

    public function payment() {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "/index.php?page=authentication");
            exit();
        }
        require_once BASE_PATH . '/app/views/payment.php';
    }
}

?>
