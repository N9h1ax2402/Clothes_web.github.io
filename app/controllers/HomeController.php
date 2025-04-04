<?php
class HomeController {
    public function index() {
        require_once BASE_PATH . '/app/views/home.php';
    }
    
    public function product() {
        require_once BASE_PATH . '/app/views/product.php';
    }

    public function authentication() {
        require_once BASE_PATH . '/app/views/authentication.php'; 
    }
}

?>
