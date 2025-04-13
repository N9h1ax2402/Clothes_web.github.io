<?php
require_once __DIR__ . "/../controllers/OrderController.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../controllers/ProductController.php";

if (!isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "/index.php?page=authentication");
    exit();
}

$user = $_SESSION['user'];
$orderList = new OrderController($conn);
$orders = $orderList->getOrdersByUserId($user['id']);
$productList = new ProductController($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <link href="<?= BASE_URL ?>/css/node_modules/bootstrap/dist/css/bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css" type="text/css"/>
    <style>
        ::-webkit-scrollbar {
            display: none;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
        }
       
        main {
            flex: 1;
            padding-top: 60px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .account-container {
            max-width: 900px;
            min-height: 500px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            width: 100%;
        }
    
        @media (max-width: 991.98px) {
            .account-container {
                height: calc(100vh - 100px);
                margin: 20px auto;
                padding: 15px;
            }
            .account-container .user-info, 
            .account-container .order-history {
                height: calc(82vh - 100px);
            }
        }

        .user-info {
            padding: 20px;
            height: 350px;
            background-color: white;
            border-radius: 6px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.05);
        }
        
        .order-history {
            padding: 20px;
            height: 350px;
            background-color: white;
            border-radius: 6px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.05);
            overflow: auto;
        }
        
        .nav-tabs {
            margin-bottom: 20px;
        }

        header {
            background-color: white;
            color: black;
        }

        nav {
            background-color: white;
            color: black;
            box-shadow: 0 1px 5px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-size: 35px;
            color: black;
            letter-spacing: 1px;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            padding-top: 10px;
            margin: 0;
        }

        .navbar {
            height: 50px;
        }

        footer {
            text-align: center;
            padding: 10px 0;
            background-color: #f8f9fa;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg fixed-top">
            <div class="container-fluid px-2 px-sm-3 px-md-5">
                <div class="nav-left" onclick="history.back()" style="cursor: pointer;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
                    </svg>
                </div>
                <a class="navbar-brand" href="<?= BASE_URL ?>/index.php?page=home">PVI</a>
            </div>
        </nav>
    </header>
    
    <main>
        <div class="container account-container">
            <div class="row mb-4">
                <div class="col">
                    <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                </div>
                <div class="col-auto">
                    <a href="<?= BASE_URL ?>/index.php?page=logout" class="btn btn-outline-dark">Logout</a>
                </div>
            </div>
            
            <ul class="nav nav-tabs" id="accountTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" style="color: black;" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">Profile</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" style="color: black;" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab" aria-controls="orders" aria-selected="false">Order History</button>
                </li>
            </ul>
            
            <div class="tab-content" id="accountTabContent">
                <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <div class="user-info">
                        <h3>My Profile</h3>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                    <div class="order-history">
                        <h3>Order History</h3>
                        <hr>
                        
                        <?php if (empty($orders)): ?>
                            <div class="alert alert-info">
                                You haven't placed any orders yet.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Information</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; foreach ($orders as $order): ?>
                                            <?php $product = $productList->show($order['product_id']) ?>
                                            <tr style="cursor: pointer;" onclick="window.location='<?php echo BASE_URL; ?>/index.php?page=productDetail&id=<?php echo $product['id']; ?>'">
                                                <td><?php echo $i ?></td>
                                                <td><?php echo $order['order_date']; ?></td>
                                                <td>
                                                    <img src="<?php echo $product['image']; ?>" style="width: 50px; height: 50px; border-radius: 5px;">
                                                    <?php echo $product['name']; ?>
                                                    (<?php echo $order['quantity']; ?> x <?php echo ($product['price']); ?>)
                                                </td>
                                                <td><?php echo number_format($order['total_price'], 0, '', '.'); ?> VNĐ</td>
                                            </tr>
                                        <?php $i++;  endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <footer class="text-center">
        <p>© 2025 My Website. All Rights Reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>