<?php
require_once __DIR__ . "/../models/Product.php";

class ProductController {
    private $productModel;

    public function __construct($conn) {
        $this->productModel = new Product($conn);
    }

    public function index() {
        $sort_by = $_GET['sort_by'] ?? 'name_asc';
        return $this->productModel->getAllProducts($sort_by);
    }

    public function searchByName($search_name = '') {
        if (empty($search_name)) {
            $search_name = $_GET['query'] ?? '';
        }
        return $this->productModel->searchByName($search_name);
    }
    
    public function show($id) {
        return $this->productModel->getProductById($id);
    }
}
?>
