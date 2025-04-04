<?php
require_once __DIR__ . '/../models/Authentication.php';

class AuthController {
    private $authModel;

    public function __construct($conn) {
        $this->authModel = new Authentication($conn);
    }

    public function login() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        
        
        if (!$email || !$password) {
            $_SESSION['login_error'] = "Please enter both email and password!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $user = $this->authModel->login($email, $password);
            if ($user) {
                $_SESSION['user'] = $user;
                unset($_SESSION['login_error']); 
                header("Location: /mywebsite/public/index.php"); 
                exit();
            } else {
                $_SESSION['login_error'] = "Login failed! Please check your email or password.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    }
}

public function register() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        
        if (!$name || !$email || !$password) {
            $_SESSION['register_error'] = "Please complete all fields!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            try {
                $user = $this->authModel->register($email, $password, $name);
                if ($user) {
                    $_SESSION['user'] = $user;
                    header("Location: /mywebsite/public/index.php"); 
                    exit();
                } else {
                    $_SESSION['register_error'] = "Registration failed!";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { 
                    $_SESSION['register_error'] = "Email already in use!";
                } else {
                    $_SESSION['register_error'] = "An error occurred. Please try again.";
                }
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    }
}
}
?>
