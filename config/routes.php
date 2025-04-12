<?php
require_once BASE_PATH . '/app/controllers/HomeController.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

$id = isset($_GET['id']) ? $_GET['id'] : null;

switch ($page) {
    case 'home':
        $controller = new HomeController();
        $controller->index();
        break;
    case 'product':
        $controller = new HomeController();
        $controller->product();
        break;
    case 'productDetail':
        $controller = new HomeController();
        $controller->productDetail($id);
        break;
    case 'contact':
        $controller = new HomeController();
        $controller->contact();
        break;
    case 'payment':
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "/index.php?page=authentication");
            exit();
        }
        $controller = new HomeController();
        $controller->payment();
        break;
    case 'authentication':
        if (isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "/index.php?page=account");
            exit();
        }
        $controller = new HomeController();
        $controller->authentication();
        break;
    case 'account':
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "/index.php?page=authentication");
            exit();
        }
        $controller = new HomeController();
        $controller->account();
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
