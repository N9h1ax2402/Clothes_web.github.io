<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../controllers/ProductController.php";

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

$productController = new ProductController($conn);
$results = $productController->searchByName($query);

echo json_encode($results);
?>