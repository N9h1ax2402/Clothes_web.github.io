<?php


if (isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/index.php?page=account");
    exit();
}

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../controllers/AuthController.php";

$authController = new AuthController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $authController->login();
    } else if (isset($_POST['register'])) {
        $authController->register();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>My New Website</title>

 <script src="<?= BASE_URL ?>/js/web.js"></script>
 <link href="<?= BASE_URL ?>/css/node_modules/bootstrap/dist/css/bootstrap.css" rel="stylesheet">
 <style>
  body{
    height: 100vh;
    margin: 0; 
    padding: 0;
    display: flex;
    justify-content: center; 
    align-items: center; 
    background-color: #f0f0f0; 
  }

  main{
    height: 85%;
    background-color: white;
    padding: 40px;
    border-radius: 16px;
    width: 90%; 
    max-width: 450px;
  }
  
  .brand{
    font-size: 30px;

    color: black;
    margin-bottom: 30px;
    letter-spacing: 1px;
  }
  
  .Sign-title{
    font-size: 20px;
    font-weight: bold;
    color: black;
    letter-spacing: 1px;
  }

  .form-control:focus{
    outline: none;
    box-shadow: none;
    border: 1px solid black;
  }
  
  .nav-pills {
    display: none; /* Hide the nav pills visually but keep them for tab functionality */
  }
 </style>
</head>
<body>
 <main>

  <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
    <li class="nav-item" role="presentation">
      <a class="nav-link active" id="tab-login" data-bs-toggle="pill" href="#pills-login" role="tab" aria-controls="pills-login" aria-selected="true">Login</a>
    </li>
    <li class="nav-item" role="presentation">
      <a class="nav-link" id="tab-register" data-bs-toggle="pill" href="#pills-register" role="tab" aria-controls="pills-register" aria-selected="false">Register</a>
    </li>
  </ul>

  <div class="tab-content">
    <div class="brand text-center" >
    <a href="/mywebsite/public/index.php?page=home" class="text-black text-decoration-none">PVI</a>
    </div>
    <div class="tab-pane fade show active" id="pills-login" role="tabpanel" aria-labelledby="tab-login">
      <form method="POST">
        <p class="Sign-title">Sign in</p>

        <div class="form-outline mb-4">
          <input type="email" id="loginName" name="email" class="form-control" placeholder="Email" required/>
        </div>

        <div class="form-outline mb-4" >
          <input type="password" id="loginPassword" name="password" class="form-control" placeholder="Password" required/>
        </div>

        <div class="d-grid">
          <button type="submit" name="login" class="btn btn-outline-dark mb-4">Sign in</button>
        </div>
        

        <div class="text-center">
          <p>Not a member? <a href="#" id="go-to-register">Register</a></p>
        </div>
      </form>
    </div>
    
    <div class="tab-pane fade" id="pills-register" role="tabpanel" aria-labelledby="tab-register">
      <form method="POST">
        <p class="Sign-title">Sign up</p>

        <div class="form-outline mb-4">
          <input type="text" id="registerUsername" name="name" class="form-control" placeholder="Username" required />
        </div>

        <div class="form-outline mb-4">
          <input type="email" id="registerEmail" name="email" class="form-control" placeholder="Email" required />
        </div>

        <div class="form-outline mb-4">
          <input type="password" id="registerPassword" name="password" class="form-control" placeholder="Password" required />
        </div>

        <div class="form-outline mb-4">
          <input type="password" id="registerRepeatPassword" class="form-control" placeholder="Repeat password" required/>
        </div>

        
        
         <div class="d-grid">  
        <button type="submit" name="register" class="btn btn-outline-dark  mb-3">Sign up</button>
        </div>
        
        <div class="text-center">
          <p>Already a member? <a href="#" id="go-to-login">Login</a></p>
        </div>
      </form>
    </div>
  </div>
 </main>

 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
 <script>
  document.addEventListener('DOMContentLoaded', function() {
    const loginTab = document.getElementById('tab-login');
    const registerTab = document.getElementById('tab-register');
    
    const goToRegisterLink = document.getElementById('go-to-register');
    const goToLoginLink = document.getElementById('go-to-login');
    
    goToRegisterLink.addEventListener('click', function(e) {
      e.preventDefault();
      registerTab.click();
    });
    
    goToLoginLink.addEventListener('click', function(e) {
      e.preventDefault();
      loginTab.click();
    });
  });

  document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.querySelector('#pills-register form');
    
    registerForm.addEventListener('submit', function(e) {
      const password = document.getElementById('registerPassword').value;
      const confirmPassword = document.getElementById('registerRepeatPassword').value;
      
      if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
      }
    });
  });

  
 </script>
</body>
</html>