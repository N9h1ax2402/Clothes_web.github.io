<?php
require_once BASE_PATH . '/app/controllers/HomeController.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';


switch ($page) {
    case 'home':
        $controller = new HomeController();
        $controller->index();
        break;
    case 'product':
        $controller = new HomeController();
        $controller->product();
        break;
    case 'authentication':
        $controller = new HomeController();
        $controller->authentication();
        break;
    case 'logout':
        session_destroy();
        header("Location: " . BASE_URL . "/index.php");
        exit();
        break;
    default:
        require_once "./views/404.php";
        break;
}
