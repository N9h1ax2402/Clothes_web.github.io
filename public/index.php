<?php
session_start(); 
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '/mywebsite/public');

require_once BASE_PATH . '/config/routes.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

switch ($page) {
    case 'home':
        require_once BASE_PATH . '/app/views/home.php';
        break;
    case 'product':
        require_once BASE_PATH . '/app/views/product.php';
        break;
    case 'authentication':
        require_once BASE_PATH . '/app/views/authentication.php';
        break;
    case 'logout':
        session_destroy();
        header("Location: " . BASE_URL . "/index.php");
        exit();
        break;
    default:
        require_once BASE_PATH . '/app/views/404.php'; 
        break;
}
